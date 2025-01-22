<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;

class TicketSeeder extends Seeder
{
    public function run()
    {
        // Example tickets
        Ticket::create([
            'title' => 'Network Connectivity Issue',
            'description' => 'Cannot connect to the office Wi-Fi.',
            'status' => 'open',
            'priority' => 'High',
            'user_id' => User::where('role_id', 3)->first()->id, // Assign to an Employee
            'assigned_to' => User::where('role_id', 2)->first()->id, // Assign to IT Staff
        ]);

        Ticket::create([
            'title' => 'Printer Not Working',
            'description' => 'The printer in the IT department is not responding.',
            'status' => 'in_progress',
            'priority' => 'Medium',
            'user_id' => User::where('role_id', 3)->first()->id,
            'assigned_to' => User::where('role_id', 2)->first()->id,
        ]);

        Ticket::create([
            'title' => 'Software Installation Request',
            'description' => 'Requesting installation of Adobe Photoshop.',
            'status' => 'closed',
            'priority' => 'Low',
            'user_id' => User::where('role_id', 3)->first()->id,
            'assigned_to' => User::where('role_id', 2)->first()->id,
        ]);
    }
}

