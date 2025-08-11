<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BareMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bare_messages';

    /**
     * Indicates if the model should be timestamped.
     * This is the fix. It disables the automatic 'updated_at' column.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_name',
        'message_id',
        'message_text',
        'message_timestamp',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'message_timestamp' => 'datetime',
    ];
}