<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Application\Contact\DTOs\ContactFilterDTO;
use App\Application\Contact\DTOs\CreateContactDTO;
use App\Application\Contact\DTOs\CreateContactNoteDTO;
use App\Application\Contact\DTOs\UpdateContactDTO;
use App\Application\Contact\Services\ContactService;
use App\Domain\Contact\Models\Contact;
use App\Domain\Contact\Models\ContactNote;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\CreateContactNoteRequest;
use App\Http\Requests\Contact\CreateContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Http\Resources\ContactNoteResource;
use App\Http\Resources\ContactResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contactService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('contacts.view');

        $paginated = $this->contactService->list(
            ContactFilterDTO::fromArray($request->query())
        );

        return ApiResponse::paginated($paginated->through(
            fn ($contact) => new ContactResource($contact)
        ));
    }

    public function store(CreateContactRequest $request): JsonResponse
    {
        $contact = $this->contactService->create(
            CreateContactDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new ContactResource($contact), 'Contact created.', 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $this->authorize('contacts.view');

        return ApiResponse::success(
            new ContactResource($contact->load(['assignedTo', 'contactNotes.user']))
        );
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $updated = $this->contactService->update(
            $contact,
            UpdateContactDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new ContactResource($updated), 'Contact updated.');
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('contacts.delete');
        $this->contactService->delete($contact);

        return ApiResponse::success(null, 'Contact deleted.');
    }

    public function stats(): JsonResponse
    {
        $this->authorize('contacts.view');

        return ApiResponse::success($this->contactService->stats());
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $this->authorize('contacts.delete');

        $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']]);

        $count = $this->contactService->bulkDelete($request->input('ids'));

        return ApiResponse::success(['deleted' => $count], "{$count} contacts deleted.");
    }

    public function storeNote(CreateContactNoteRequest $request, Contact $contact): JsonResponse
    {
        $note = $this->contactService->addNote(
            $contact,
            CreateContactNoteDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            new ContactNoteResource($note->load('user')),
            'Note added.',
            201
        );
    }

    public function destroyNote(Contact $contact, ContactNote $note): JsonResponse
    {
        $this->authorize('contacts.update');
        $this->contactService->deleteNote($note);

        return ApiResponse::success(null, 'Note deleted.');
    }
}
