<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    // Define the relationship with User if needed
    public function user()
    {
        return $this->belongsToMany(User::class, 'registration_event_users');
    }

    public function feedback() {
        return $this->hasMany(Feedback::class);
    }
    
}
