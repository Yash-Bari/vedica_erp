@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Projects</h1>
        @can('create', App\Models\Project::class)
        <a href="{{ route('projects.create') }}" class="btn btn-primary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Create New Project
        </a>
        @endcan
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 bg-gray-100 border-b">
            <form method="GET" action="{{ route('projects.index') }}" class="flex space-x-4">
                <input type="text" name="search" placeholder="Search projects..." 
                       value="{{ request('search') }}"
                       class="flex-grow px-3 py-2 border rounded-md">
                
                <select name="client_id" class="px-3 py-2 border rounded-md">
                    <option value="">All Clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" 
                                {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
        </div>

        <table class="w-full">
        <thead>
    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
        <th class="py-3 px-6 text-left">Project Name</th>
        <th class="py-3 px-6 text-left">Client</th>
        <th class="py-3 px-6 text-left">Operator</th>
        <th class="py-3 px-6 text-center">Total Hours</th>
        <th class="py-3 px-6 text-center">Total Revenue</th>
        <th class="py-3 px-6 text-center">Status</th>
        <th class="py-3 px-6 text-center">Actions</th>
    </tr>
</thead>
<tbody class="text-gray-600 text-sm font-light">
    @forelse($projects as $project)
        <tr class="border-b border-gray-200 hover:bg-gray-100">
            <td class="py-3 px-6 text-left whitespace-nowrap">
                <div class="flex items-center">
                    <span class="font-medium">{{ $project->name }}</span>
                </div>
            </td>
            <td class="py-3 px-6 text-left">
                {{ $project->client->name }}
            </td>
            <td class="py-3 px-6 text-left">
                {{ $project->operator->first_name }} {{ $project->operator->last_name }}
            </td>
            <td class="py-3 px-6 text-center">
                {{ $project->total_hours }}
            </td>
            <td class="py-3 px-6 text-center">
                {{ $project->total_revenue }}
            </td>
            <td class="py-3 px-6 text-center">
                <span class="
                    px-3 py-1 rounded-full text-xs 
                    {{ $project->status === 'Pending' ? 'bg-yellow-200 text-yellow-800' : '' }}
                    {{ $project->status === 'In Progress' ? 'bg-blue-200 text-blue-800' : '' }}
                    {{ $project->status === 'Completed' ? 'bg-green-200 text-green-800' : '' }}
                    {{ $project->status === 'On Hold' ? 'bg-red-200 text-red-800' : '' }}
                    {{ $project->status === 'Cancelled' ? 'bg-gray-200 text-gray-800' : '' }}
                ">
                    {{ $project->status }}
                </span>
            </td>
            <td class="py-3 px-6 text-center">
                <div class="flex item-center justify-center">
                    <a href="{{ route('projects.show', $project->id) }}" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('projects.edit', $project->id) }}" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-4 transform hover:text-red-500 hover:scale-110">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center py-4 text-gray-500">
                No projects found.
            </td>
        </tr>
    @endforelse
</tbody>
        </table>

        <div class="p-4">
            {{ $projects->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Optional: Add any client-side interactivity for the projects index
    });
</script>
@endpush
