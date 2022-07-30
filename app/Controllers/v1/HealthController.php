<?php
namespace App\Controllers\v1;

use App\Controllers\Filters;
use App\Models\v1\HealthModel;

class HealthController {

    public $db_model;
    private $db_filter;
    private $db_limit = 100;
    private $db_offset = 0;

    // reset the query limit and offsets
    private $qr_limit;
    private $qr_offset;
    
    // tables list
    public $facility_table = 'health_facilities';
    public $profession_table = 'health_professionals';
    public $disease_table = 'health_diseases';

    public function __construct($endpoint = [])
    {
        $this->db_model = new HealthModel;
        $this->db_filter = new Filters;
        $this->endpoint = $endpoint;
        $this->route = 'health';
    }

    /**
     * Return all available requests for the endpoint with its accepted parameters
     * 
     * @return Array
     */
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

    /**
     * Set the Limit and Offset Parameters
     * If the user submitted a limit then it will be applied. However, if the set limit is greater than the 
     * default value then it will be reset to the default
     * 
     * @return Mixed
     */
    private function limit_offset($params) {

        // apply the limit
        $limit = isset($params['limit']) ? (int) $params['limit'] : $this->db_limit;
        $limit = !empty($primary_key) ? 1 : $limit;

        // set the offset
        $this->qr_limit = $limit > $this->db_limit ? $this->db_limit : $limit;

        // apply the offset
        $this->qr_offset = isset($params['offset']) ? (int) $params['offset'] : $this->db_offset;
    }

    /**
     * Get the list of all facilities
     * 
     * The default to to load all records. However, when the primary_key is set then a single record is returned
     * The limit and offset filters are applied. The default limit is 100
     * 
     * @param Array     $params
     * @param Int       $primary_key
     * 
     * @return Array
     */
    public function facilities(array $params = [], $primary_key = null) {
        
        try {

            // apply the limit
            $this->limit_offset($params);

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

            // counter
            $count = 0;

            // loop through the like columns and append it to the query
            foreach($whereLikeArray as $key => $value) {
                if( !is_array($value) ) {
                    if($count == 0) {
                        $builder->like($key, $value, 'both');
                    } else {
                        $builder->orLike($key, $value, 'both');
                    }
                    $count++;
                } else {
                    foreach($value as $i => $v) {
                        if($i == 0) {
                            $builder->like($key, $v);
                        } else {
                            $builder->orLike($key, $v);
                        }                        
                        $count++;
                    }
                }
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('a.id', $primary_key);
            }

            // apply limit and offsets
            $builder->limit($this->qr_limit, $this->qr_offset);

            // get the data
            $result = $builder->get();

            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {
            
            return [];

        }

    }

    /**
     * Add a new facility record
     * 
     * @param Array     $params
     * 
     * @return Bool
     */
    public function add_facility(array $params = []) {

        try {

            return $this->db_model->db->table($this->facility_table)->insert($params);

        } catch(\Exception $e) {
            return [];
        }

    }

    /**
     * Update the Facility record using the id as unique key
     * 
     * @param Array     $params
     * @param Int       $facility_id
     * 
     * @return Bool
     */
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

    /**
     * Get the list of all professionals
     * 
     * The default to to load all records. However, when the primary_key is set then a single record is returned
     * The limit and offset filters are applied. The default limit is 100
     * 
     * @param Array     $params
     * @param Int       $primary_key
     * 
     * @return Array
     */
    public function professionals(array $params = [], $primary_key = null) {

        try {

            // apply the limit
            $this->limit_offset($params);

            // query builder
            $builder = $this->db_model->db
                        ->table("{$this->profession_table} a")
                        ->select('a.*, f.name AS facility_name, f.address AS facility_address, f.contact AS facility_contact, r.name AS facility_region')
                        ->join("{$this->facility_table} f", 'f.id = a.facility_id', 'left')
                        ->join('regions r', 'r.id = f.region_id', 'left');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params, $this->route);

            // loop through the filter in in array clause
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // filter where like
            $whereLikeArray = $this->db_filter->filterWhereLike($params, $this->route);

            // counter
            $count = 0;
            // loop through the like columns and append it to the query
            foreach($whereLikeArray as $key => $value) {
                if( !is_array($value) ) {
                    if($count == 0) {
                        $builder->like($key, $value, 'both');
                    } else {
                        $builder->orLike($key, $value, 'both');
                    }
                    $count++;
                } else {
                    foreach($value as $i => $v) {
                        if($i == 0) {
                            $builder->like($key, $v);
                        } else {
                            $builder->orLike($key, $v);
                        }                        
                        $count++;
                    }
                }
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('a.id', $primary_key);
            }

            // apply limit and offsets
            $builder->limit($this->qr_limit, $this->qr_offset);

            // get the data
            $result = $builder->get();

            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {
            
            return [];

        }

    }

    /**
     * Add a new professional record
     * 
     * @param Array     $params
     * 
     * @return Bool
     */
    public function add_professionals(array $params = []) {

        try {

            return $this->db_model->db->table($this->profession_table)->insert($params);

        } catch(\Exception $e) {
            return [];
        }

    }

    /**
     * Update the professional record using the id as unique key
     * 
     * @param Array     $params
     * @param Int       $professional_id
     * 
     * @return Bool
     */
    public function update_professionals(array $params = [], $professional_id = null) {

        try {

            if(empty($professional_id) && !isset($params['id'])) {
                return [];
            }

            if(empty($professional_id)) {
                $professional_id = $params['id'];
                unset($params['id']);
            }

            // update the row
            return $this->db_model->db->table($this->profession_table)
                    ->set($params)
                    ->where(['id' => $professional_id])
                    ->limit(1)
                    ->update();

        } catch(\Exception $e) {
            return [];            
        }
        
    }

    /**
     * Get the list of all diseases
     * 
     * The default to to load all records. However, when the primary_key is set then a single record is returned
     * The limit and offset filters are applied. The default limit is 100
     * 
     * @param Array     $params
     * @param Int       $primary_key
     * 
     * @return Array
     */
    public function diseases(array $params = [], $primary_key = null) {

        try {

            // apply the limit
            $this->limit_offset($params);

            // query builder
            $builder = $this->db_model->db
                        ->table("{$this->disease_table} a")
                        ->select('a.*');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params, $this->route);

            // loop through the filter in in array clause
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // filter where like
            $whereLikeArray = $this->db_filter->filterWhereLike($params, $this->route);

            // counter
            $count = 0;
            // loop through the like columns and append it to the query
            foreach($whereLikeArray as $key => $value) {
                if( !is_array($value) ) {
                    if($count == 0) {
                        $builder->like($key, $value, 'both');
                    } else {
                        $builder->orLike($key, $value, 'both');
                    }
                    $count++;
                } else {
                    foreach($value as $i => $v) {
                        if($i == 0) {
                            $builder->like($key, $v);
                        } else {
                            $builder->orLike($key, $v);
                        }                        
                        $count++;
                    }
                }
            }

            // if the primary key is set
            if(!empty($primary_key)) {
                $builder->where('a.id', $primary_key);
            }

            // apply limit and offsets
            $builder->limit($this->qr_limit, $this->qr_offset);

            // get the data
            $result = $builder->get();

            // get the array version of the result
            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {
            
            return [];

        }

    }

    /**
     * Add a new disease record
     * 
     * @param Array     $params
     * 
     * @return Bool
     */
    public function add_diseases(array $params = []) {

        try {

            return $this->db_model->db->table($this->disease_table)->insert($params);

        } catch(\Exception $e) {
            return [];
        }

    }

    /**
     * Update the Disease record using the id as unique key
     * 
     * @param Array     $params
     * @param Int       $disease_id
     * 
     * @return Bool
     */
    public function update_diseases(array $params = [], $disease_id = null) {

        try {

            if(empty($disease_id) && !isset($params['id'])) {
                return [];
            }

            if(empty($disease_id)) {
                $disease_id = $params['id'];
                unset($params['id']);
            }

            // update the row
            return $this->db_model->db->table($this->disease_table)
                    ->set($params)
                    ->where(['id' => $disease_id])
                    ->limit(1)
                    ->update();

        } catch(\Exception $e) {
            return [];            
        }
        
    }    

}