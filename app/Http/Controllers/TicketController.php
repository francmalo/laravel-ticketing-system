<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;


class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     */

    /**
     * Display a listing of the tickets.
     */
    public function index(): View
    {
        $userRole = Auth::user()->role->name;

        $tickets = match($userRole) {
            'Admin', 'IT Staff' => Ticket::with(['assignedTo', 'user'])->latest()->get(),
            'Employee' => Ticket::where('user_id', Auth::id())->with(['assignedTo', 'user'])->latest()->get(),
            default => collect(),
        };

        return view('tickets.index', compact('tickets'));
    }
    /**
     * Show the form for creating a new ticket.
     */
    public function create(): View
    {
        return view('tickets.create');
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open', // Set default status
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): View
    {
        // Check if user can view this ticket
        if (Auth::user()->role->name === 'Employee' && $ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->load(['assignedTo', 'user', 'comments.user']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket): View
    {
        // Only Admin and IT Staff can access this (already handled in constructor middleware)
        $users = User::whereHas('role', function($query) {
            $query->whereIn('name', ['Admin', 'IT Staff']);
        })->get();

        return view('tickets.edit', compact('ticket', 'users'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $ticket->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to']
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        // Only Admin and IT Staff can delete tickets (already handled in constructor middleware)
        $ticket->comments()->delete(); // Delete associated comments first
        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Update the ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!in_array(Auth::user()->role->name, ['Admin', 'IT Staff'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Assign ticket to a staff member.
     */
    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!in_array(Auth::user()->role->name, ['Admin', 'IT Staff'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $ticket->update(['assigned_to' => $validated['assigned_to']]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }
}
