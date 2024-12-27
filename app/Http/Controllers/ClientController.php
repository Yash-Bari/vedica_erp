<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $sourceOptions = Client::getSourceOptions();
        return view('clients.create', compact('sourceOptions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => [
                'required', 
                'string', 
                'max:20', 
                'unique:clients,phone'
            ],
            'email' => 'nullable|email|unique:clients,email',
            'address' => 'nullable|string',
            'source' => [
                'required', 
                'in:' . implode(',', array_keys(Client::getSourceOptions()))
            ]
        ], [
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email is already registered.',
            'source.in' => 'Invalid source selected.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Remove any non-digit characters from phone number
        $cleanPhone = preg_replace('/\D/', '', $request->phone);

        $client = Client::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $cleanPhone,
            'email' => $request->email,
            'address' => $request->address,
            'source' => $request->source
        ]);

        // If the request is from project creation, redirect back to project creation
        if ($request->has('from_project_create')) {
            return redirect()->route('projects.create')
                ->with('success', 'Client created successfully')
                ->with('selected_client_id', $client->id);
        }

        // Otherwise, redirect to client index or show page
        return redirect()->route('clients.create')
            ->with('success', 'Client created successfully');
    }

    public function show(Client $client)
    {
        $recentProjects = $client->projects()->latest()->take(5)->get();
        return view('clients.show', compact('client', 'recentProjects'));
    }

    public function edit(Client $client)
    {
        $sourceOptions = Client::getSourceOptions();
        return view('clients.edit', compact('client', 'sourceOptions'));
    }

    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:clients,phone,' . $client->id
            ],
            'email' => 'nullable|email|unique:clients,email,' . $client->id,
            'address' => 'nullable|string',
            'source' => [
                'required',
                'in:' . implode(',', array_keys(Client::getSourceOptions()))
            ]
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Remove any non-digit characters from phone number
        $cleanPhone = preg_replace('/\D/', '', $request->phone);

        $client->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $cleanPhone,
            'email' => $request->email,
            'address' => $request->address,
            'source' => $request->source
        ]);

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Client updated successfully');
    }
}
