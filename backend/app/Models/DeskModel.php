<?php

namespace App\Models;

use CodeIgniter\Model;

class DeskModel extends Model
{
    protected $table            = 'office_desks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'room_id',
        'desk_code',
        'position_x',
        'position_y',
        'position_z',
        'rotation_y',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
