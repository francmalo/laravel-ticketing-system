<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'content' => $validated['comment'],
        ]);

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Comment added successfully.');
    }
}



