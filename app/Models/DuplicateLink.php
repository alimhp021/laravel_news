<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuplicateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'bare_message_id',
        'unique_news_id',
        'similarity_score',
    ];

    public function bareMessage()
    {
        return $this->belongsTo(BareMessage::class);
    }

    public function uniqueNews()
    {
        return $this->belongsTo(UniqueNews::class);
    }
}
