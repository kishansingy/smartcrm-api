<?php

namespace App\Infrastructure\Contact\Repositories;

use App\Domain\Contact\Contracts\ContactRepositoryInterface;
use App\Domain\Contact\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactRepository implements ContactRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Contact::with(['assignedTo:id,name,email']);

        if ($filters['search'] ?? null) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name',  'like', "%{$filters['search']}%")
                  ->orWhere('email',      'like', "%{$filters['search']}%")
                  ->orWhere('phone',      'like', "%{$filters['search']}%")
                  ->orWhere('company',    'like', "%{$filters['search']}%");
            });
        }

        if ($filters['type']        ?? null) $query->where('type',        $filters['type']);
        if ($filters['status']      ?? null) $query->where('status',      $filters['status']);
        if ($filters['source']      ?? null) $query->where('source',      $filters['source']);
        if ($filters['assigned_to'] ?? null) $query->where('assigned_to', $filters['assigned_to']);
        if ($filters['date_from']   ?? null) $query->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to']     ?? null) $query->whereDate('created_at', '<=', $filters['date_to']);

        if ($filters['tag'] ?? null) {
            $query->whereJsonContains('tags', $filters['tag']);
        }

        $allowed = ['created_at', 'updated_at', 'first_name', 'last_name', 'company'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowed) ? $filters['sort_by'] : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    public function findById(int $id): ?Contact
    {
        return Contact::with(['assignedTo', 'contactNotes.user'])->find($id);
    }

    public function findByEmail(int $tenantId, string $email): ?Contact
    {
        return Contact::where('tenant_id', $tenantId)->where('email', $email)->first();
    }

    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);
        return $contact->refresh();
    }

    public function delete(Contact $contact): bool
    {
        return $contact->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return Contact::whereIn('id', $ids)->delete();
    }

    public function getStatsByTenant(int $tenantId): array
    {
        $q = Contact::where('tenant_id', $tenantId);

        return [
            'total'      => (clone $q)->count(),
            'active'     => (clone $q)->where('status', 'active')->count(),
            'inactive'   => (clone $q)->where('status', 'inactive')->count(),
            'individual' => (clone $q)->where('type', 'individual')->count(),
            'business'   => (clone $q)->where('type', 'business')->count(),
            'this_month' => (clone $q)->whereMonth('created_at', now()->month)->count(),
            'this_week'  => (clone $q)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }
}
