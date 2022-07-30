<?php 
namespace App\Controllers;

class Filters {

    public function filterWhereIn($params, $route = null) {
        
        $data = [];

        // CLIENT BASED FILTER
        if( !empty($params['client_id']) ) {
            $data['client_id'] = string_to_int_array($params['client_id']);
        }

        // COMMON FILTERS
        foreach( ['status'] as $column ) {
            // append to the array
            if( !empty($params[$column]) ) {
                $data["a.{$column}"] = string_to_array($params[$column]);
            }
        }

        // HEALTH ROUTING FILTER
        if(in_array($route, ['health'])) {

            // multiple filters
            foreach( ['constituency_id', 'region_id', 'district_id', 'facility_id'] as $column ) {
                // append to the array
                if( !empty($params[$column]) ) {
                    $data["a.{$column}"] = string_to_array($params[$column]);
                }
            }

        }

        // EDUCATION ROUTING FILTER
        if(in_array($route, ['education'])) {

            // multiple filters
            foreach( ['institution_id'] as $column ) {
                // append to the array
                if( !empty($params[$column]) ) {
                    $data["a.{$column}"] = string_to_array($params[$column]);
                }
            }

        }

        return $data;
    }

    public function filterWhereLike($params, $route = null) {
        
        $data = [];

        // WHERE LIKE FILTERS
        foreach(['name', 'ghanapostgps', 'generic_name'] as $name) {
            
            if(!empty($params[$name])) {
                // value into an array if comma separated
                $string = string_to_array($params[$name]);

                // loop through each value
                foreach($string as $item) {
                    $data["a.{$name}"][] = $item;
                }
            }
        }

        // if the route is health
        if(in_array($route, ['health'])) {
            
            // search from the description content if the param matches these
            foreach(['symptom', 'causes', 'treatment'] as $item) {
                
                // if the parameter is not empty
                if(!empty($params[$item])) {

                    // value into an array if comma separated
                    $string = string_to_array($params[$item]);

                    // loop through each value
                    foreach($string as $item) {
                        $data["a.description"][] = $item;
                    }
                    
                }
            }
        }

        return $data;

    }
    
}
