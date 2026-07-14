<?php

namespace App\Domain\Contact\Contracts;

use App\Domain\Contact\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Contact;
    public function create(array $data): Contact;
    public function update(Contact $contact, array $data): Contact;
    public function delete(Contact $contact): bool;
    public function bulkDelete(array $ids): int;
    public function getStatsByTenant(int $tenantId): array;
    public function findByEmail(int $tenantId, string $email): ?Contact;
}
