<?php

namespace App\Domain\WhatsApp\Contracts;

use App\Domain\WhatsApp\Models\WhatsAppConversation;
use App\Domain\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WhatsAppRepositoryInterface
{
    public function paginateConversations(array $filters, int $perPage = 20): LengthAwarePaginator;
    public function findConversationById(int $id): ?WhatsAppConversation;
    public function findConversationByPhone(int $tenantId, string $phone): ?WhatsAppConversation;
    public function createConversation(array $data): WhatsAppConversation;
    public function updateConversation(WhatsAppConversation $conv, array $data): WhatsAppConversation;
    public function paginateMessages(int $conversationId, int $perPage = 50): LengthAwarePaginator;
    public function paginateAllMessages(array $filters, int $perPage = 30): LengthAwarePaginator;
    public function createMessage(array $data): WhatsAppMessage;
    public function updateMessageStatus(string $waMessageId, string $status, array $timestamps = []): bool;
    public function markConversationRead(WhatsAppConversation $conv): void;
    public function getStats(int $tenantId): array;
    public function getTemplates(int $tenantId): array;
    public function upsertTemplate(int $tenantId, array $data): array;
}
