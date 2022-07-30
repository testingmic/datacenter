<?php
namespace App\Controllers\v1;

use App\Controllers\Filters;
use App\Models\v1\HealthModel;

class HealthController {

    public $db_model;
    private $db_filter;
    private $db_limit = 100;
    private $db_offset = 0;
    
    public $facility_table = 'health_facilities';

    public function __construct($endpoint = [])
    {
        $this->db_model = new HealthModel;
        $this->db_filter = new Filters;
        $this->endpoint = $endpoint;
        $this->route = 'health';
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

    public function facilities(array $params = [], $primary_key = null) {
        
        try {

            // apply the limit
            $limit = isset($params['limit']) ? (int) $params['limit'] : $this->db_limit;
            $limit = !empty($primary_key) ? 1 : $limit;
            $limit = $limit > $this->db_limit ? $this->db_limit : $limit;

            // apply the offset
            $offset = isset($params['offset']) ? (int) $params['offset'] : $this->db_offset;

            // set the builder
            $builder = $this->db_model->db
                        ->table("{$this->facility_table} a")
                        ->select('a.*, r.name AS region_name, d.name AS district_name, c.name AS constituency_name')
                        ->join('regions r', 'r.id = a.region_id', 'left')
                        ->join('districts d', 'd.id = a.district_id', 'left')
                        ->join('constituency c', 'c.id = a.constituency_id', 'left');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params, $this->route);

            // loop through the filter in array list
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // filter where like
            $whereLikeArray = $this->db_filter->filterWhereLike($params, $this->route);

            // loop through the filter like array list
            foreach($whereLikeArray as $key => $whereLike) {
                $builder->like($key, $whereLike, 'both');
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('a.id', $primary_key);
            }

            // apply limit
            $builder->limit($limit, $offset);

            // get the data
            $result = $builder->get();

            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {
            
            return [];

        }

    }

    public function add_facility(array $params = []) {

        try {

            return $this->db_model->db->table($this->facility_table)->insert($params);

        } catch(\Exception $e) {
            return [];
        }

    }

    public function update_facility(array $params = [], $facility_id = null) {

        try {

            if(empty($facility_id) && !isset($params['id'])) {
                return [];
            }

            if(empty($facility_id)) {
                $facility_id = $params['id'];
                unset($params['id']);
            }

            // update the row
            return $this->db_model->db->table($this->facility_table)
                    ->set($params)
                    ->where(['id' => $facility_id])
                    ->limit(1)
                    ->update();

        } catch(\Exception $e) {
            return [];            
        }
        
    }

    public function professionals(array $params = [], $primary_key = null) {

        try {

            $builder = $this->db_model->db
                        ->table('health_professionals a')
                        ->select('a.*, f.name AS facility_name, f.address AS facility_address, f.contact AS facility_contact, r.name AS facility_region')
                        ->orderBy('a.id', 'DESC')
                        ->join("{$this->facility_table} f", 'f.id = a.facility_id', 'left')
                        ->join('regions r', 'r.id = f.region_id', 'left');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params, $this->route);

            // loop through the filter in in array clause
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('a.id', $primary_key);
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