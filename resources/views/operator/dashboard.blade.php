@extends('layouts.app')

@section('title', 'Operator Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Assigned Projects Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Assigned Projects</h2>
        
        @if($assignedProjects->count() > 0)
            @foreach($assignedProjects as $project)
                <div class="border rounded-lg p-4 mb-4 {{ $project->status === 'in_progress' ? 'border-green-500' : 'border-gray-200' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Project Details -->
                        <div>
                            <h3 class="text-xl font-semibold mb-2">{{ $project->name }}</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Location:</span> {{ $project->location }}</p>
                                <p><span class="font-medium">Client:</span> {{ $project->client->name }}</p>
                                <p><span class="font-medium">Description:</span> {{ $project->description }}</p>
                                <div class="project-details">
                                    <p><strong>Status:</strong> {{ ucfirst($project->status) }}</p>
                                    <p><strong>Total Hours:</strong> {{ number_format($project->total_hours, 2) }}</p>
                                    <p><strong>Total Revenue:</strong> ₹{{ number_format($project->total_revenue, 2) }}</p>
                                    <p><strong>Hourly Rate:</strong> ₹{{ number_format($project->hourly_rate, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Machine Details and Project Controls -->
                        <div>
                            @if($project->machine)
                                <div class="mb-4">
                                    <h4 class="font-medium mb-2">Assigned Machine</h4>
                                    <p>{{ $project->machine->name }}</p>
                                </div>
                            @endif

                            @if($project->attachments->count() > 0)
                                <div class="mb-4">
                                    <h4 class="font-medium mb-2">Project Attachments</h4>
                                    <div class="space-y-1">
                                        @foreach($project->attachments as $attachment)
                                            <a href="{{ route('projects.download-attachment', $attachment->id) }}" 
                                               class="text-blue-600 hover:text-blue-800 block">
                                                {{ $attachment->original_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Project Controls -->
                            <div class="space-y-3 mt-4">
                                @if($project->status === 'created')
                                    <form action="{{ route('operator.start-project', $project->id) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Take Initial Meter Reading Picture
                                            </label>
                                            <input type="file" name="meter_reading_image" 
                                                   class="w-full border border-gray-300 rounded-md p-2"
                                                   accept="image/*"
                                                   capture="environment"
                                                   required>
                                        </div>
                                        <button type="submit" 
                                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                            Start Project
                                        </button>
                                    </form>
                                @endif

                                @if($project->status === 'in_progress')
                                    <div class="mb-4">
                                        <h4 class="font-medium mb-2">Time Worked</h4>
                                        <div id="timer-{{ $project->id }}" class="text-lg font-bold text-green-600">00:00:00</div>
                                    </div>
                                    <form action="{{ route('operator.hold-project', $project->id) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Take Hold Meter Reading Picture
                                            </label>
                                            <input type="file" name="meter_reading_image" 
                                                   class="w-full border border-gray-300 rounded-md p-2"
                                                   accept="image/*"
                                                   capture="environment">
                                        </div>
                                        <button type="submit" 
                                                class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                                            Hold Project
                                        </button>
                                    </form>
                                    <form action="{{ route('operator.stop-project', $project->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Take Final Meter Reading Picture
                                            </label>
                                            <input type="file" name="meter_reading_image" 
                                                   class="w-full border border-gray-300 rounded-md p-2"
                                                   accept="image/*"
                                                   capture="environment"
                                                   required>
                                        </div>
                                        <button type="submit" 
                                                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                            Complete Project
                                        </button>
                                    </form>
                                @endif

                                @if($project->status === 'on_hold')
                                    <form action="{{ route('operator.resume-project', $project->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                            Resume Project
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    
                </div>
            @endforeach
        @else
            <p class="text-gray-600">No projects are currently assigned to you.</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function startTimer(projectId) {
        let seconds = 0;
        const timerElement = document.getElementById('timer-' + projectId);

        setInterval(() => {
            seconds++;
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            timerElement.innerText = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        @foreach($assignedProjects as $project)
            @if($project->status === 'in_progress')
                startTimer({{ $project->id }});
            @endif
        @endforeach
    });
</script>
@endpush
@endsection
