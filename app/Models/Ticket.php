<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {
    protected $fillable = ['title', 'description', 'priority', 'status', 'user_id', 'assigned_to'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}

