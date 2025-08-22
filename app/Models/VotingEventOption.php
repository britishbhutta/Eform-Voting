<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingEventOption extends Model
{
    protected $fillable = [
        'voting_event_id',
        'option_text',
        'votes_count',
        'status',
    ];

    public function votingEvent()
    {
        return $this->belongsTo(VotingEvent::class);
    }
}
