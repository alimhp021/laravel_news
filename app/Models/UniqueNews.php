<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

/**
 * @property Vector $embedding
 */
class UniqueNews extends Model
{
    use HasFactory, HasNeighbors;

    protected $fillable = [
        'source_message_id',
        'title',
        'summary',
        'original_text',
        'embedding',
    ];

    protected $casts = [
        'embedding' => Vector::class,
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
