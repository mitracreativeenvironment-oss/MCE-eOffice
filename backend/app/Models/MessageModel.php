<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table            = 'conversation_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'conversation_id',
        'sender_type',
        'content',
        'model_used',
        'tokens_prompt',
        'tokens_completion',
        'created_at',
    ];

    protected $useTimestamps = false;
}

