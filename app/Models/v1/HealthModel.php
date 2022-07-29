<?php

namespace App\Models\v1;

use CodeIgniter\Model;

class HealthModel extends Model {
    
    protected $table = 'health';
    protected $primaryKey = 'id';
    protected $allowedFields = [];
}
?>