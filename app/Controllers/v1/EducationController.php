<?php
namespace App\Controllers\v1;

use App\Controllers\Filters;
use App\Models\v1\EducationModel;

class EducationController {

    private $db_model;
    private $db_filter;

    public function __construct($endpoint = [])
    {
        $this->health_model = new EducationModel;
        $this->db_filter = new Filters;
        $this->endpoint = $endpoint;
        $this->route = 'education';
    }

    public function index() {
        $endpoints = [];

        if( is_array($this->endpoint) ) {
            foreach($this->endpoint as $key => $value) {
                $endpoints["{$this->route}/{$key}"] = $value;
            }
        }

        return [
            'enpoints' => $endpoints
        ];        
    }

    public function institutions(array $params = [], $primary_key = null) {
        
        try {

            

        } catch(\Exception $e) {
            
            return [];

        }
        
    }

}