@extends('layouts.app')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                Ticket #{{ $ticket->id }}: {{ $ticket->title }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('tickets.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
                @if(Auth::user()->role->name === 'Admin' || Auth::user()->role->name === 'IT Staff')
                    <a href="{{ route('tickets.edit', $ticket) }}"
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Ticket
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Ticket Information</h3>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ match($ticket->status) {
                                    'open' => 'bg-yellow-100 text-yellow-800',
                                    'in_progress' => 'bg-blue-100 text-blue-800',
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'closed' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ match($ticket->priority) {
                                    'high' => 'bg-red-100 text-red-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'low' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                } }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $ticket->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Description</h3>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Comments</h3>
            <div class="space-y-4 mb-6">
                @forelse($ticket->comments as $comment)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $comment->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <p class="mt-2 text-gray-700">{{ $comment->content }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No comments yet.</p>
                @endforelse
            </div>

            <!-- Add Comment Form -->
            <form action="{{ route('comments.store', $ticket) }}" method="POST" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700">Add a Comment</label>
                    <textarea name="content" id="content" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                              required></textarea>
                </div>
                <div class="flex justify-end">
                   <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Comment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
