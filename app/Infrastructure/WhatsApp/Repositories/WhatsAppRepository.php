<?php

namespace App\Infrastructure\WhatsApp\Repositories;

use App\Domain\WhatsApp\Contracts\WhatsAppRepositoryInterface;
use App\Domain\WhatsApp\Models\WhatsAppConversation;
use App\Domain\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WhatsAppRepository implements WhatsAppRepositoryInterface
{
    public function paginateConversations(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = WhatsAppConversation::with(['assignedTo:id,name,avatar', 'contact:id,first_name,last_name'])
            ->orderByDesc('last_message_at');

        if ($filters['search']      ?? null) {
            $query->where(fn ($q) => $q->where('phone_number', 'like', "%{$filters['search']}%")
                                       ->orWhere('contact_name',  'like', "%{$filters['search']}%"));
        }
        if ($filters['status']      ?? null) $query->where('status',      $filters['status']);
        if ($filters['assigned_to'] ?? null) $query->where('assigned_to', $filters['assigned_to']);
        if ($filters['unread']      ?? null) $query->where('unread_count', '>', 0);

        return $query->paginate($perPage);
    }

    public function findConversationById(int $id): ?WhatsAppConversation
    {
        return WhatsAppConversation::with(['assignedTo', 'contact', 'lead'])->find($id);
    }

    public function findConversationByPhone(int $tenantId, string $phone): ?WhatsAppConversation
    {
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        return WhatsAppConversation::where('tenant_id', $tenantId)
            ->where('phone_number', $normalized)
            ->first();
    }

    public function createConversation(array $data): WhatsAppConversation
    {
        if (isset($data['phone_number'])) {
            $data['phone_number'] = preg_replace('/[^0-9]/', '', $data['phone_number']);
        }

        return WhatsAppConversation::create($data);
    }

    public function updateConversation(WhatsAppConversation $conv, array $data): WhatsAppConversation
    {
        $conv->update($data);
        return $conv->refresh();
    }

    public function paginateMessages(int $conversationId, int $perPage = 50): LengthAwarePaginator
    {
        return WhatsAppMessage::with('user:id,name,avatar')
            ->where('conversation_id', $conversationId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function createMessage(array $data): WhatsAppMessage
    {
        return WhatsAppMessage::create($data);
    }

    public function updateMessageStatus(string $waMessageId, string $status, array $timestamps = []): bool
    {
        return (bool) WhatsAppMessage::where('wa_message_id', $waMessageId)
            ->update(array_merge(['status' => $status], $timestamps));
    }

    public function markConversationRead(WhatsAppConversation $conv): void
    {
        $conv->update(['unread_count' => 0]);
    }

    public function getStats(int $tenantId): array
    {
        $conv = WhatsAppConversation::where('tenant_id', $tenantId);
        $msg  = WhatsAppMessage::whereHas('conversation', fn ($q) => $q->where('tenant_id', $tenantId));

        return [
            'total_conversations'  => (clone $conv)->count(),
            'active_conversations' => (clone $conv)->where('status', 'active')->count(),
            'unread_conversations' => (clone $conv)->where('unread_count', '>', 0)->count(),
            'total_messages'       => (clone $msg)->count(),
            'sent_today'           => (clone $msg)->where('direction', 'outbound')->whereDate('created_at', today())->count(),
            'received_today'       => (clone $msg)->where('direction', 'inbound')->whereDate('created_at', today())->count(),
        ];
    }

    public function paginateAllMessages(array $filters = [], int $perPage = 30): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = WhatsAppMessage::with(['conversation:id,phone_number,contact_name', 'user:id,name'])
            ->orderByDesc('created_at');

        if ($filters['direction'] ?? null) $query->where('direction', $filters['direction']);
        if ($filters['status']    ?? null) $query->where('status',    $filters['status']);
        if ($filters['date_from'] ?? null) $query->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to']   ?? null) $query->whereDate('created_at', '<=', $filters['date_to']);

        return $query->paginate($perPage);
    }

    public function getTemplates(int $tenantId): array
    {
        return \App\Domain\WhatsApp\Models\WhatsAppTemplate::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function upsertTemplate(int $tenantId, array $data): array
    {
        $tpl = \App\Domain\WhatsApp\Models\WhatsAppTemplate::updateOrCreate(
            ['tenant_id' => $tenantId, 'name' => $data['name'], 'language' => $data['language']],
            [
                'category'   => $data['category']   ?? 'utility',
                'status'     => $data['status']      ?? 'pending',
                'components' => $data['components']  ?? [],
            ]
        );
        return $tpl->toArray();
    }
}
