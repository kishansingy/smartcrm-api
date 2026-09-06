<?php

namespace App\Http\Controllers\Api\V1\WhatsApp;

use App\Application\WhatsApp\DTOs\SendMessageDTO;
use App\Application\WhatsApp\Services\WhatsAppService;
use App\Domain\WhatsApp\Models\WhatsAppConversation;
use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsApp\SendMessageRequest;
use App\Http\Resources\WhatsAppConversationResource;
use App\Http\Resources\WhatsAppMessageResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsAppService) {}

    public function conversations(Request $request): JsonResponse
    {
        $this->authorize('whatsapp.view');

        $paginated = $this->whatsAppService->listConversations($request->query());

        return ApiResponse::paginated($paginated->through(
            fn ($c) => new WhatsAppConversationResource($c)
        ));
    }

    public function messages(Request $request, WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorize('whatsapp.view');

        $paginated = $this->whatsAppService->getMessages($conversation->id);

        return ApiResponse::paginated($paginated->through(
            fn ($m) => new WhatsAppMessageResource($m)
        ));
    }

    public function send(SendMessageRequest $request): JsonResponse
    {
        $message = $this->whatsAppService->sendMessage(
            SendMessageDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new WhatsAppMessageResource($message), 'Message sent.', 201);
    }

    public function markRead(WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorize('whatsapp.view');

        $this->whatsAppService->markRead($conversation);

        return ApiResponse::success(null, 'Conversation marked as read.');
    }

    public function stats(): JsonResponse
    {
        $this->authorize('whatsapp.view');

        return ApiResponse::success($this->whatsAppService->stats());
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $this->authorize('whatsapp.send');

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,mp4,pdf,doc,docx', 'max:16384'],
        ]);

        try {
            $result = $this->whatsAppService->uploadMedia($request->file('file'));
            return ApiResponse::success(['media_id' => $result['id']], 'Media uploaded.');
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }


    public function broadcast(Request $request): JsonResponse
    {
        $this->authorize('whatsapp.send');

        $request->validate([
            'phones'               => ['nullable', 'array'],
            'phones.*'             => ['nullable', 'string'],
            'recipients'           => ['nullable', 'array'],
            'recipients.*.phone'   => ['required_with:recipients', 'string'],
            'recipients.*.body_params' => ['nullable', 'array'],
            'template_name'        => ['required', 'string'],
            'template_language'    => ['nullable', 'string'],
            'template_components'  => ['nullable', 'array'],
            'scheduled_at'         => ['nullable', 'date'],
        ]);

        // Ensure at least one recipient source is provided
        if (empty($request->input('phones', [])) && empty($request->input('recipients', []))) {
            return ApiResponse::error('No recipients provided. Please select contacts or enter phone numbers.', 422);
        }

        // Support both plain phones[] and recipients[] with per-recipient body_params
        $phones     = $request->input('phones', []);
        $recipients = $request->input('recipients', []);

        // Normalise to recipients array
        if (empty($recipients) && !empty($phones)) {
            $recipients = array_map(fn($p) => ['phone' => $p, 'body_params' => []], $phones);
        }

        $result = $this->whatsAppService->broadcastQueued(
            $recipients,
            $request->input('template_name'),
            $request->input('template_language', 'en'),
            $request->input('template_components', []),
        );

        $total   = $result['total'];
        $message = "Queued broadcast to {$total} recipient" . ($total !== 1 ? 's' : '') . ". Messages will be delivered shortly.";

        return ApiResponse::success($result, $message);
    }

    public function messageLog(Request $request): JsonResponse
    {
        $this->authorize('whatsapp.view');

        $paginated = $this->whatsAppService->messageLog($request->query());

        return ApiResponse::paginated($paginated->through(
            fn ($m) => new WhatsAppMessageResource($m)
        ));
    }

    public function templates(): JsonResponse
    {
        $this->authorize('whatsapp.view');

        return ApiResponse::success($this->whatsAppService->getTemplates());
    }

    public function syncTemplates(): JsonResponse
    {
        $this->authorize('whatsapp.send');

        $templates = $this->whatsAppService->syncTemplatesFromMeta();

        return ApiResponse::success($templates, count($templates) . ' templates synced from Meta.');
    }
}
