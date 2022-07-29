<?php
namespace App\Controllers\v1;

use App\Controllers\Filters;
use App\Models\v1\HealthModel;

class HealthController {

    private $health_model;
    private $db_filter;

    public function __construct()
    {
        $this->health_model = new HealthModel;
        $this->db_filter = new Filters;
        $this->route = 'health';
    }

    public function list() {
        
    }

    public function facilities(array $params = [], $primary_key = null) {
        
        try {

            $builder = $this->health_model->db
                        ->table('health_facilities a')
                        ->select('a.*, r.name AS region_name, d.name AS district_name, c.name AS constituency_name')
                        ->orderBy('a.id', 'DESC')
                        ->join('regions r', 'r.id = a.region_id', 'left')
                        ->join('districts d', 'd.id = a.district_id', 'left')
                        ->join('counstituency c', 'c.id = a.counstituency_id', 'left');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params, $this->route);

            // loop through the filter in in array clause
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('id', $primary_key);
            }

            // get the data
            $result = $builder->get();

            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {
            
            return [];

        }

    }

    public function professionals(array $params = [], $primary_key = null) {

        try {

            $builder = $this->health_model->db
                        ->table('health_professionals a')
                        ->select('a.*, f.name AS facility_name, f.address AS facility_address, f.contact AS facility_contact, r.name AS facility_region')
                        ->orderBy('a.id', 'DESC')
                        ->join('health_facilities f', 'f.id = a.facility_id', 'left')
                        ->join('regions r', 'r.id = f.region_id', 'left');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params, $this->route);

            // loop through the filter in in array clause
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('id', $primary_key);
            }

            // get the data
            $result = $builder->get();

            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {
            
            return [];

        }

    }

    public function diseases(array $params = [], $primary_key = null) {

        try {

            

        } catch(\Exception $e) {
            
            return [];

        }

    }

}