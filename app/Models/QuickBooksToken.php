<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickBooksToken extends Model
{
    // 👇 tell Eloquent which table to use
    protected $table = 'quickbooks_tokens';

    protected $fillable = [
        'user_id',
        'realm_id',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
    ];
}
