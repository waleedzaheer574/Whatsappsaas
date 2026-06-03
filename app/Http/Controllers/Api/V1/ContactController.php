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
        return $this->success(Contact::query()->where('workspace_id', $request->query('workspace_id', 1))->latest()->paginate(25));
    }

    public function store(StoreContactRequest $request)
    {
        return $this->success(Contact::query()->create($request->validated()), 'Contact created successfully', status: 201);
    }

    public function show(Contact $contact)
    {
        return $this->success($contact->load('conversations.messages'));
    }

    public function update(StoreContactRequest $request, Contact $contact)
    {
        $contact->update($request->validated());

        return $this->success($contact->fresh(), 'Contact updated successfully');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return $this->success([], 'Contact deleted successfully');
    }
}
