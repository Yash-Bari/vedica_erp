@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Project Header -->
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
                    <p class="text-sm text-gray-600">Client: {{ $project->client->name }}</p>
                </div>
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
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->location ?? 'Not specified' }}</dd>
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

            <!-- Machine & Meter Readings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Machine Details & Meter Readings</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Assigned Machine</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ optional($project->machine)->name ?? 'Not Assigned' }}</dd>
                    </div>
                    
                    @php
                        $latestTimeLog = $project->timeLogs()->latest()->first();
                    @endphp

                    @if($latestTimeLog && $latestTimeLog->meter_reading_start_image)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Start Meter Reading</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $latestTimeLog->meter_reading_start ?? '' }}
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $latestTimeLog->meter_reading_start_image) }}" 
                                         alt="Start Meter Reading" 
                                         class="w-full max-w-md rounded-lg shadow-sm">
                                </div>
                            </dd>
                        </div>
                    @endif

                    @if($latestTimeLog && $latestTimeLog->meter_reading_end_image)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">End Meter Reading</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $latestTimeLog->meter_reading_end ?? '' }}
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $latestTimeLog->meter_reading_end_image) }}" 
                                         alt="End Meter Reading" 
                                         class="w-full max-w-md rounded-lg shadow-sm">
                                </div>
                            </dd>
                        </div>
                    @endif

                    @if($latestTimeLog && $latestTimeLog->meter_reading_hold_image)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Hold Meter Reading</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $latestTimeLog->meter_reading_hold_image) }}" 
                                         alt="Hold Meter Reading" 
                                         class="w-full max-w-md rounded-lg shadow-sm">
                                </div>
                            </dd>
                        </div>
                    @endif
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
                        <dt class="text-sm font-medium text-gray-500">Operator Status</dt>
                        <dd class="mt-1 text-sm {{ $project->operator && $project->status === 'in_progress' ? 'text-green-600' : 'text-gray-900' }}">
                            {{ $project->operator && $project->status === 'in_progress' ? 'Currently Working' : 'Not Active' }}
                        </dd>
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

            <!-- Project Timeline -->
            <div class="px-6 pb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Project Timeline</h2>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($project->timeLogs as $timeLog)
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm text-gray-500">
                                                    @if($timeLog->start_time)
                                                        <p>Started: {{ $timeLog->start_time->format('M d, Y H:i') }}</p>
                                                    @endif
                                                    @if($timeLog->hold_time)
                                                        <p>Paused: {{ $timeLog->hold_time->format('M d, Y H:i') }}</p>
                                                    @endif
                                                    @if($timeLog->resume_time)
                                                        <p>Resumed: {{ $timeLog->resume_time->format('M d, Y H:i') }}</p>
                                                    @endif
                                                    @if($timeLog->end_time)
                                                        <p>Ended: {{ $timeLog->end_time->format('M d, Y H:i') }}</p>
                                                    @endif
                                                </div>
                                                
                                                @if($timeLog->meter_reading_start_image || $timeLog->meter_reading_hold_image || $timeLog->meter_reading_end_image)
                                                    <div class="mt-2 grid grid-cols-3 gap-4">
                                                        @if($timeLog->meter_reading_start_image)
                                                            <div>
                                                                <p class="text-xs text-gray-500">Start Reading</p>
                                                                <img src="{{ asset('storage/' . $timeLog->meter_reading_start_image) }}" 
                                                                     alt="Start Reading" 
                                                                     class="mt-1 w-full rounded shadow-sm">
                                                            </div>
                                                        @endif
                                                        @if($timeLog->meter_reading_hold_image)
                                                            <div>
                                                                <p class="text-xs text-gray-500">Hold Reading</p>
                                                                <img src="{{ asset('storage/' . $timeLog->meter_reading_hold_image) }}" 
                                                                     alt="Hold Reading" 
                                                                     class="mt-1 w-full rounded shadow-sm">
                                                            </div>
                                                        @endif
                                                        @if($timeLog->meter_reading_end_image)
                                                            <div>
                                                                <p class="text-xs text-gray-500">End Reading</p>
                                                                <img src="{{ asset('storage/' . $timeLog->meter_reading_end_image) }}" 
                                                                     alt="End Reading" 
                                                                     class="mt-1 w-full rounded shadow-sm">
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center text-gray-500">No timeline entries yet</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Project Expenses -->
            <div class="px-6 pb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Project Expenses</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No expenses recorded</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Project Actions -->
            @if($project->status !== 'completed')
                <div class="px-6 pb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Project Actions</h2>
                        <div class="flex space-x-4">
                            @if($project->status === 'created' || $project->status === 'on_hold')
                                <form action="{{ route('projects.start', $project) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                        {{ $project->status === 'created' ? 'Start Project' : 'Resume Project' }}
                                    </button>
                                </form>
                            @endif
                            
                            @if($project->status === 'in_progress')
                                <form action="{{ route('projects.complete', $project) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                                        Complete Project
                                    </button>
                                </form>
                                
                                <form action="{{ route('projects.hold', $project) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md">
                                        Put on Hold
                                    </button>
                                </form>
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
                    @if($project->status === 'in_progress')
                        <div class="mt-4">
                            <dt class="text-sm font-medium text-gray-500">Duration</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ now()->diffForHumans($project->start_time, true) }}
                            </dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
