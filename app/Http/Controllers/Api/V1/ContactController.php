<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->success(Contact::query()->where('workspace_id', $request->attributes->get('workspace_id'))->latest()->paginate(25));
    }

    public function store(StoreContactRequest $request)
    {
        return $this->success(Contact::query()->create([
            ...$request->validated(),
            'workspace_id' => $request->attributes->get('workspace_id'),
        ]), 'Contact created successfully', status: 201);
    }

    public function show(Request $request, Contact $contact)
    {
        abort_unless($contact->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        return $this->success($contact->load('conversations.messages'));
    }

    public function update(StoreContactRequest $request, Contact $contact)
    {
        abort_unless($contact->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $contact->update($request->validated());

        return $this->success($contact->fresh(), 'Contact updated successfully');
    }

    public function destroy(Request $request, Contact $contact)
    {
        abort_unless($contact->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $contact->delete();

        return $this->success([], 'Contact deleted successfully');
    }
}
