<?php

namespace App\Application\Contact\Services;

use App\Application\Contact\DTOs\ContactFilterDTO;
use App\Application\Contact\DTOs\CreateContactDTO;
use App\Application\Contact\DTOs\CreateContactNoteDTO;
use App\Application\Contact\DTOs\UpdateContactDTO;
use App\Domain\Contact\Contracts\ContactRepositoryInterface;
use App\Domain\Contact\Events\ContactCreated;
use App\Domain\Contact\Events\ContactUpdated;
use App\Domain\Contact\Models\Contact;
use App\Domain\Contact\Models\ContactNote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ContactService
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
    ) {}

    public function list(ContactFilterDTO $dto): LengthAwarePaginator
    {
        return $this->contactRepository->paginate([
            'search'      => $dto->search,
            'type'        => $dto->type,
            'status'      => $dto->status,
            'source'      => $dto->source,
            'assigned_to' => $dto->assignedTo,
            'tag'         => $dto->tag,
            'date_from'   => $dto->dateFrom,
            'date_to'     => $dto->dateTo,
            'sort_by'     => $dto->sortBy,
            'sort_dir'    => $dto->sortDir,
        ], $dto->perPage);
    }

    public function create(CreateContactDTO $dto): Contact
    {
        $contact = $this->contactRepository->create([
            'tenant_id'   => Auth::user()->tenant_id,
            'first_name'  => $dto->firstName,
            'last_name'   => $dto->lastName,
            'email'       => $dto->email,
            'phone'       => $dto->phone,
            'mobile'      => $dto->mobile,
            'company'     => $dto->company,
            'job_title'   => $dto->jobTitle,
            'department'  => $dto->department,
            'website'     => $dto->website,
            'address'     => $dto->address,
            'type'        => $dto->type,
            'status'      => $dto->status,
            'source'      => $dto->source,
            'tags'        => $dto->tags,
            'notes'       => $dto->notes,
            'assigned_to' => $dto->assignedTo ?? Auth::id(),
            'meta'        => $dto->meta,
        ]);

        event(new ContactCreated($contact));

        return $contact;
    }

    public function update(Contact $contact, UpdateContactDTO $dto): Contact
    {
        $data = array_filter([
            'first_name'  => $dto->firstName,
            'last_name'   => $dto->lastName,
            'email'       => $dto->email,
            'phone'       => $dto->phone,
            'mobile'      => $dto->mobile,
            'company'     => $dto->company,
            'job_title'   => $dto->jobTitle,
            'department'  => $dto->department,
            'website'     => $dto->website,
            'address'     => $dto->address,
            'type'        => $dto->type,
            'status'      => $dto->status,
            'source'      => $dto->source,
            'tags'        => $dto->tags,
            'notes'       => $dto->notes,
            'assigned_to' => $dto->assignedTo,
            'meta'        => $dto->meta,
        ], fn ($v) => $v !== null);

        $updated = $this->contactRepository->update($contact, $data);

        event(new ContactUpdated($updated, $data));

        return $updated;
    }

    public function delete(Contact $contact): bool
    {
        return $this->contactRepository->delete($contact);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->contactRepository->bulkDelete($ids);
    }

    public function stats(): array
    {
        return $this->contactRepository->getStatsByTenant(Auth::user()->tenant_id);
    }

    public function addNote(Contact $contact, CreateContactNoteDTO $dto): ContactNote
    {
        return $contact->contactNotes()->create([
            'user_id' => Auth::id(),
            'content' => $dto->content,
            'type'    => $dto->type,
            'meta'    => $dto->meta,
        ]);
    }

    public function deleteNote(ContactNote $note): bool
    {
        return $note->delete();
    }
}
