<?php
namespace App\Controllers\v1;

use App\Controllers\Filters;
use App\Models\v1\UsersModel;

class UsersController {
    
    private $users_model;
    private $db_filter;

    public function __construct()
    {
        $this->users_model = new UsersModel;
        $this->db_filter = new Filters;
    }

    public function list($params = []) {

        try {

            // perform the request
            $builder = $this->users_model
                            ->select('users.*')
                            ->orderBy('users.id', 'ASC');

            // filter where in
            $whereInArray = $this->db_filter->filterWhereIn($params);

            // loop through the filter in in array clause
            foreach($whereInArray as $key => $whereIn) {
                $builder->whereIn($key, $whereIn);
            }

            // get the data
            $result = $builder->get();

            $data = !empty($result) ? $result->getResultArray() : [];

            return $data;

        } catch(\Exception $e) {

        }
        
    }

    public function show($params = [], $unique_id = null) {

        try {
            
            $data = $this->users_model
                            ->select('*')
                            ->where(['users.id' => $unique_id])
                            ->first();

            return $data;

        } catch(\Exception $e) {
            return [];
        }
        
    }
    
    public function delete($params = [], $unique_id = null) {

        try {
        
        } catch(\Exception $e) {
            return [];
        }

    }

}
?>