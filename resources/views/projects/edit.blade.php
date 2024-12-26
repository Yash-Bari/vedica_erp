@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                Edit Project
            </h2>

            <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Project Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Project Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Project Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Project Description</label>
                    <textarea name="description" id="description" rows="4" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Project Attachments -->
                <div class="mb-4">
                    <label for="attachments" class="block text-sm font-medium text-gray-700">Project Attachments</label>
                    <input type="file" name="attachments[]" id="attachments" multiple 
                           class="mt-1 block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100">
                    @error('attachments')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- Current Attachments -->
                    @if($project->attachments && $project->attachments->count() > 0)
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Attachments</h4>
                            <div class="space-y-2">
                                @foreach($project->attachments as $attachment)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">{{ $attachment->filename }}</span>
                                        <button type="button" 
                                                onclick="deleteAttachment({{ $attachment->id }})" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end mt-6">
                    <a href="{{ route('projects.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Update Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteAttachment(attachmentId) {
        if (confirm('Are you sure you want to delete this attachment?')) {
            fetch(`/project-attachments/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the attachment element from the DOM
                    const element = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                    element?.parentElement.remove();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete attachment');
            });
        }
    }
</script>
@endpush
