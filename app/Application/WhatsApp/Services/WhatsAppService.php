<?php

namespace App\Application\WhatsApp\Services;

use App\Application\WhatsApp\DTOs\SendMessageDTO;
use App\Domain\WhatsApp\Contracts\WhatsAppRepositoryInterface;
use App\Domain\WhatsApp\Events\WhatsAppMessageReceived;
use App\Domain\WhatsApp\Events\WhatsAppMessageSent;
use App\Domain\WhatsApp\Models\WhatsAppConversation;
use App\Domain\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct(
        private readonly WhatsAppRepositoryInterface $waRepository,
        private readonly WhatsAppApiClient           $apiClient,
    ) {}

    public function listConversations(array $filters): LengthAwarePaginator
    {
        return $this->waRepository->paginateConversations($filters);
    }

    public function getMessages(int $conversationId): LengthAwarePaginator
    {
        return $this->waRepository->paginateMessages($conversationId);
    }

    public function sendMessage(SendMessageDTO $dto): WhatsAppMessage
    {
        $user     = Auth::user();
        $tenantId = $user->tenant_id;

        // Find or create conversation
        $conversation = $this->waRepository->findConversationByPhone($tenantId, $dto->to)
            ?? $this->waRepository->createConversation([
                'tenant_id'    => $tenantId,
                'assigned_to'  => $user->id,
                'phone_number' => $dto->to,
                'status'       => 'active',
            ]);

        // Send via API
        $response = match ($dto->type) {
            'text'     => $this->apiClient->sendText($dto->to, $dto->text),
            'template' => $this->apiClient->sendTemplate(
                $dto->to,
                $dto->templateName,
                $dto->templateLanguage,
                $dto->templateComponents ?? []
            ),
            default    => $this->apiClient->sendMedia($dto->to, $dto->type, $dto->mediaUrl),
        };

        $waMessageId = $response['messages'][0]['id'] ?? null;

        // Persist message
        $message = $this->waRepository->createMessage([
            'conversation_id' => $conversation->id,
            'user_id'         => $user->id,
            'wa_message_id'   => $waMessageId,
            'direction'       => 'outbound',
            'type'            => $dto->type,
            'content'         => $dto->text,
            'template_name'   => $dto->templateName,
            'status'          => 'sent',
            'sent_at'         => now(),
        ]);

        // Update conversation last message
        $this->waRepository->updateConversation($conversation, [
            'last_message'    => $dto->text ?? "Template: {$dto->templateName}",
            'last_message_at' => now(),
        ]);

        event(new WhatsAppMessageSent($message));

        return $message;
    }

    public function handleWebhook(array $payload): void
    {
        $entry = $payload['entry'][0] ?? null;
        if (! $entry) return;

        $changes = $entry['changes'][0]['value'] ?? null;
        if (! $changes) return;

        // Handle status updates (delivered, read)
        foreach ($changes['statuses'] ?? [] as $status) {
            $this->handleStatusUpdate($status);
        }

        // Handle incoming messages
        foreach ($changes['messages'] ?? [] as $message) {
            $this->handleIncomingMessage($message, $changes['contacts'][0] ?? []);
        }
    }

    private function handleIncomingMessage(array $message, array $contact): void
    {
        $phone    = $message['from'];
        $name     = $contact['profile']['name'] ?? $phone;

        // Find tenant by phone number ID — using first tenant for now
        // In production, map phone_number_id to tenant
        $tenantId = 1;

        $conversation = $this->waRepository->findConversationByPhone($tenantId, $phone)
            ?? $this->waRepository->createConversation([
                'tenant_id'    => $tenantId,
                'phone_number' => $phone,
                'contact_name' => $name,
                'status'       => 'active',
            ]);

        $content = match ($message['type']) {
            'text'  => $message['text']['body'] ?? '',
            'audio' => '[Audio message]',
            'image' => $message['image']['caption'] ?? '[Image]',
            default => "[{$message['type']} message]",
        };

        $inbound = $this->waRepository->createMessage([
            'conversation_id' => $conversation->id,
            'wa_message_id'   => $message['id'],
            'direction'       => 'inbound',
            'type'            => $message['type'],
            'content'         => $content,
            'status'          => 'read',
            'sent_at'         => now()->createFromTimestamp($message['timestamp']),
            'meta'            => $message,
        ]);

        $this->waRepository->updateConversation($conversation, [
            'last_message'    => $content,
            'last_message_at' => now(),
            'unread_count'    => $conversation->unread_count + 1,
            'contact_name'    => $name,
        ]);

        event(new WhatsAppMessageReceived($inbound->load('conversation')));
    }

    private function handleStatusUpdate(array $status): void
    {
        $timestamps = [];

        if ($status['status'] === 'delivered') {
            $timestamps['delivered_at'] = now();
        } elseif ($status['status'] === 'read') {
            $timestamps['read_at'] = now();
        }

        $this->waRepository->updateMessageStatus($status['id'], $status['status'], $timestamps);
    }

    public function markRead(WhatsAppConversation $conversation): void
    {
        $this->waRepository->markConversationRead($conversation);

        // Get last inbound message and mark read on WhatsApp
        $lastInbound = $conversation->messages()
            ->where('direction', 'inbound')
            ->latest()
            ->first();

        if ($lastInbound?->wa_message_id) {
            try {
                $this->apiClient->markRead($lastInbound->wa_message_id);
            } catch (\Throwable $e) {
                Log::warning('Failed to mark WhatsApp read: ' . $e->getMessage());
            }
        }
    }

    public function stats(): array
    {
        return $this->waRepository->getStats(Auth::user()->tenant_id);
    }

    public function broadcast(array $phones, string $templateName, string $language = 'en', ?string $scheduledAt = null, array $components = []): array
    {
        $user     = Auth::user();
        $queued   = 0;
        $failed   = 0;
        $errors   = [];

        // Debug log to verify components are being received
        Log::info('Broadcast started', [
            'template'   => $templateName,
            'language'   => $language,
            'components' => $components,
            'recipients' => count($phones),
        ]);

        foreach ($phones as $phone) {
            try {
                $dto = SendMessageDTO::fromArray([
                    'to'                   => $phone,
                    'type'                 => 'template',
                    'template_name'        => $templateName,
                    'template_language'    => $language,
                    'template_components'  => $components,
                ]);
                $this->sendMessage($dto);
                $queued++;
            } catch (\Throwable $e) {
                Log::error("Broadcast failed for {$phone}", [
                    'template'   => $templateName,
                    'components' => $components,
                    'error'      => $e->getMessage(),
                ]);
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        return [
            'queued' => $queued,
            'failed' => $failed,
            'total'  => count($phones),
            'errors' => array_unique($errors),
        ];
    }

    public function messageLog(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->waRepository->paginateAllMessages($filters);
    }

    public function getTemplates(): array
    {
        return $this->waRepository->getTemplates(Auth::user()->tenant_id);
    }

    public function syncTemplatesFromMeta(): array
    {
        $raw = $this->apiClient->getTemplates();

        $saved = [];
        foreach ($raw as $tpl) {
            $saved[] = $this->waRepository->upsertTemplate(Auth::user()->tenant_id, [
                'name'       => $tpl['name'],
                'language'   => $tpl['language'] ?? 'en',
                'category'   => strtolower($tpl['category'] ?? 'utility'),
                'status'     => strtolower($tpl['status'] ?? 'pending'),
                'components' => $tpl['components'] ?? [],
            ]);
        }

        return $saved;
    }
}
