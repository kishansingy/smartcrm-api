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

    public function broadcast(Request $request): JsonResponse
    {
        $this->authorize('whatsapp.send');

        $request->validate([
            'phones'               => ['required', 'array', 'min:1'],
            'phones.*'             => ['required', 'string'],
            'template_name'        => ['required', 'string'],
            'template_language'    => ['nullable', 'string'],
            'template_components'  => ['nullable', 'array'],
            'scheduled_at'         => ['nullable', 'date'],
        ]);

        $result = $this->whatsAppService->broadcast(
            $request->input('phones'),
            $request->input('template_name'),
            $request->input('template_language', 'en'),
            $request->input('scheduled_at'),
            $request->input('template_components', [])
        );

        return ApiResponse::success($result, "Broadcast queued for {$result['queued']} recipients.");
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
