@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Machine Inventory</h1>
        
        @can('create', App\Models\Machine::class)
        <a href="{{ route('machines.create') }}" 
           class="btn btn-primary flex items-center">
            <x-heroicon-o-plus class="w-5 h-5 mr-2" />
            Add New Machine
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('machines.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Machine Type</label>
                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">All Types</option>
                    @foreach(App\Models\Machine::TYPES as $type)
                        <option value="{{ $type }}" 
                            {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">All Statuses</option>
                    @foreach(App\Models\Machine::STATUS as $status)
                        <option value="{{ $status }}" 
                            {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                <select name="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" 
                            {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-full flex justify-end space-x-4 mt-4">
                <button type="submit" class="btn btn-secondary">
                    Apply Filters
                </button>
                <a href="{{ route('machines.index') }}" class="btn btn-outline">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Machines Table --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Project
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($machines as $machine)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $machine->name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $machine->type === 'Excavator' ? 'bg-blue-100 text-blue-800' : 
                               ($machine->type === 'Bulldozer' ? 'bg-green-100 text-green-800' : 
                               ($machine->type === 'Crane' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-gray-100 text-gray-800')) }}">
                            {{ $machine->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $machine->status === 'Available' ? 'bg-green-100 text-green-800' : 
                               ($machine->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                               ($machine->status === 'Repair' ? 'bg-red-100 text-red-800' : 
                               'bg-gray-100 text-gray-800')) }}">
                            {{ $machine->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $machine->project ? $machine->project->name : 'Unassigned' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            @can('view', $machine)
                            <a href="{{ route('machines.show', $machine) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                <x-heroicon-o-eye class="w-5 h-5" />
                            </a>
                            @endcan

                            @can('update', $machine)
                            <a href="{{ route('machines.edit', $machine) }}" 
                               class="text-green-600 hover:text-green-900">
                                <x-heroicon-o-pencil class="w-5 h-5" />
                            </a>
                            @endcan

                            @can('delete', $machine)
                            <form action="{{ route('machines.destroy', $machine) }}" 
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this machine?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No machines found. 
                        @can('create', App\Models\Machine::class)
                        <a href="{{ route('machines.create') }}" class="text-blue-600 hover:underline">
                            Create a new machine
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $machines->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
