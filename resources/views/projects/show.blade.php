@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Project Header -->
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
                <div class="flex space-x-2">
                    @if($project->status !== 'completed')
                        <a href="{{ route('projects.edit', $project) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            Edit Project
                        </a>
                    @endif
                    <a href="{{ route('projects.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Project Status Banner -->
        <div class="px-6 py-3 {{ $project->status === 'completed' ? 'bg-green-100' : ($project->status === 'in_progress' ? 'bg-blue-100' : 'bg-yellow-100') }}">
            <span class="font-semibold">Status:</span> 
            <span class="capitalize">{{ $project->status }}</span>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Project Details</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Client</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->client->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->description ?? 'No description provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Financial Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Financial Overview</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Hourly Rate</dt>
                        <dd class="mt-1 text-sm text-gray-900">₹{{ number_format($project->hourly_rate, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($project->total_hours, 2) }} hours</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Revenue</dt>
                        <dd class="mt-1 text-lg font-semibold text-green-600">₹{{ number_format($project->total_revenue, 2) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Resource Assignment -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Resource Assignment</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Operator</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ optional($project->operator)->name ?? 'Not Assigned' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Machine</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ optional($project->machine)->name ?? 'Not Assigned' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Project Timeline -->
            <div class="px-6 pb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Time Logs</h2>
                    @if($project->time_logs->count() > 0)
                        <div class="space-y-4">
                            @foreach($project->time_logs as $timeLog)
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Start Time</p>
                                            <p class="font-medium">{{ $timeLog->start_time->format('M d, Y h:i A') }}</p>
                                        </div>
                                        @if($timeLog->hold_time)
                                            <div>
                                                <p class="text-sm text-gray-600">Hold Time</p>
                                                <p class="font-medium">{{ $timeLog->hold_time->format('M d, Y h:i A') }}</p>
                                            </div>
                                        @endif
                                        @if($timeLog->resume_time)
                                            <div>
                                                <p class="text-sm text-gray-600">Resume Time</p>
                                                <p class="font-medium">{{ $timeLog->resume_time->format('M d, Y h:i A') }}</p>
                                            </div>
                                        @endif
                                        @if($timeLog->end_time)
                                            <div>
                                                <p class="text-sm text-gray-600">End Time</p>
                                                <p class="font-medium">{{ $timeLog->end_time->format('M d, Y h:i A') }}</p>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm text-gray-600">Total Hours</p>
                                            <p class="font-medium">{{ number_format($timeLog->total_hours, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Revenue</p>
                                            <p class="font-medium text-green-600">₹{{ number_format($timeLog->revenue, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No time logs available.</p>
                    @endif
                </div>
            </div>

            <!-- Project Expenses -->
            @if($project->expenses && $project->expenses->count() > 0)
            <div class="px-6 pb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Project Expenses</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($project->expenses as $expense)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $expense->date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $expense->description }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $expense->category }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₹{{ number_format($expense->amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                        Total Expenses:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                        ₹{{ number_format($project->expenses->sum('amount'), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Project Attachments -->
            @if($project->attachments && $project->attachments->count() > 0)
            <div class="px-6 pb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Project Attachments</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($project->attachments as $attachment)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-file text-gray-400 mr-3"></i>
                                <span class="text-sm text-gray-900">{{ $attachment->filename }}</span>
                            </div>
                            <a href="{{ Storage::url($attachment->file_path) }}" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Project Actions -->
            @if($project->status !== 'completed')
            <div class="px-6 pb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Project Actions</h2>
                    <div class="flex space-x-4">
                        @if($project->status === 'created' || $project->status === 'on_hold')
                            <button onclick="startProject({{ $project->id }})" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                Start Project
                            </button>
                        @endif
                        
                        @if($project->status === 'in_progress')
                            <button onclick="pauseProject({{ $project->id }})" 
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md">
                                Pause Project
                            </button>
                            <button onclick="completeProject({{ $project->id }})" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                                Complete Project
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Project Timeline -->
        <div class="px-6 pb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Project Timeline</h2>
                <div class="space-y-4">
                    @if($project->start_time)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Started At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->start_time->format('M d, Y h:i A') }}</dd>
                        </div>
                    @endif
                    @if($project->end_time)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->end_time->format('M d, Y h:i A') }}</dd>
                        </div>
                    @endif
                    @if($project->pause_time)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Paused At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $project->pause_time->format('M d, Y h:i A') }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function startProject(projectId) {
        if(confirm('Are you sure you want to start this project?')) {
            fetch(`/projects/${projectId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start project');
            });
        }
    }

    function pauseProject(projectId) {
        if (confirm('Are you sure you want to pause this project?')) {
            fetch(`/projects/${projectId}/pause`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to pause project');
            });
        }
    }

    function completeProject(projectId) {
        const hours = prompt('Enter total hours worked:');
        if (hours !== null) {
            fetch(`/projects/${projectId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ total_hours: hours })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to complete project');
            });
        }
    }
</script>
@endpush
