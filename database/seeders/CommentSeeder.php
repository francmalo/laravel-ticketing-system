<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentSeeder extends Seeder
{
    public function run()
    {
        // Fetch tickets
        $ticket1 = Ticket::first();
        $ticket2 = Ticket::skip(1)->first();

        // Add comments to tickets
        Comment::create([
            'content' => 'We are looking into the issue. Please check back soon.',
            'ticket_id' => $ticket1->id,
            'user_id' => User::where('role_id', 2)->first()->id, // IT Staff
        ]);

        Comment::create([
            'content' => 'Can you provide more details about the software requirements?',
            'ticket_id' => $ticket2->id,
            'user_id' => User::where('role_id', 2)->first()->id,
        ]);
    }
}

