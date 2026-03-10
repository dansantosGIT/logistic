<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRequest extends Model
{
    protected $table = 'account_requests';

    protected $fillable = [
        'name', 'email', 'password_hash', 'department', 'position', 'phone', 'message', 'proof_path', 'status', 'requested_role', 'justification', 'invite_token', 'token_expires_at'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];
}
