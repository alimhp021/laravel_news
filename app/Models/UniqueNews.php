<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors; // <-- Import this

class UniqueNews extends Model
{
    use HasFactory, HasNeighbors; // <-- Add HasNeighbors

    protected $fillable = [
        'source_message_id',
        'title',
        'summary',
        'original_text',
        'embedding',
    ];

    // Tell Eloquent that 'embedding' should be cast to a Vector object
    protected $casts = [
        'embedding' => \Pgvector\Laravel\Vector::class,
    ];

    public function sourceMessage()
    {
        return $this->belongsTo(BareMessage::class, 'source_message_id');
    }

    public function duplicateLinks()
    {
        return $this->hasMany(DuplicateLink::class);
    }
}
