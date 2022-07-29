<?php
namespace App\Controllers\v1;

use App\Controllers\Filters;
use App\Models\v1\EducationModel;

class EducationController {

    private $education_model;
    private $db_filter;

    public function __construct()
    {
        $this->education_model = new EducationModel;
        $this->db_filter = new Filters;
    }

    public function list() {
        
        
    }

    public function institutions(array $params = [], $primary_key = null) {
        
        try {

            

        } catch(\Exception $e) {
            
            return [];

        }
        
    }

}