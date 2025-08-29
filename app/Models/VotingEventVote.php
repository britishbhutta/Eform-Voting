<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingEventVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'voting_event_id',
        'voting_event_option_id',
        'email',
    ];

    public function votingEvent()
    {
        return $this->belongsTo(VotingEvent::class);
    }

    public function option()
    {
        return $this->belongsTo(VotingEventOption::class, 'voting_event_option_id');
    }
}


