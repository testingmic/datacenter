<?php 
namespace App\Controllers;

class Filters {

    public function filterWhereIn($params, $route = null) {
        
        $data = [];

        # CLIENT BASED FILTER
        if( !empty($params['client_id']) ) {
            $data['client_id'] = string_to_int_array($params['client_id']);
        }

        # COMMON FILTERS
        foreach( ['status'] as $column ) {
            # append to the array
            if( !empty($params[$column]) ) {
                $data["a.{$column}"] = string_to_array($params[$column]);
            }
        }

        # HEALTH ROUTING FILTER
        if(in_array($route, ['health'])) {

            # multiple filters
            foreach( ['constituency_id', 'region_id', 'district_id', 'facility_id'] as $column ) {
                # append to the array
                if( !empty($params[$column]) ) {
                    $data["a.{$column}"] = string_to_array($params[$column]);
                }
            }

        }

        # EDUCATION ROUTING FILTER
        if(in_array($route, ['education'])) {

            # multiple filters
            foreach( ['institution_id'] as $column ) {
                # append to the array
                if( !empty($params[$column]) ) {
                    $data["a.{$column}"] = string_to_array($params[$column]);
                }
            }

        }

        return $data;
    }

    public function filterWhereLike($params, $route = null) {
        
        $data = [];

        # WHERE LIKE FILTERS
        foreach(['name', 'ghanapostgps', 'generic_name'] as $name) {
            if(!empty($params[$name])) {
                $data["a.{$name}"] = $params[$name];
            }
        }

        return $data;

    }
    
}
