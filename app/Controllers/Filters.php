<?php 
namespace App\Controllers;

class Filters {

    public function filterWhereIn($params, $route = null) {
        
        $data = [];

        // set the client id
        if( !empty($params['client_id']) ) {
            $data['client_id'] = string_to_int_array($params['client_id']);
        }

        // if the status was parsed
        if( !empty($params['status']) ) {
            $data['status'] = string_to_array($params['status']);
        }

        // HEALTH ROUTING FILTER
        if(in_array($route, ['health'])) {

            // if the counstituency_id was parsed
            if( !empty($params['counstituency_id']) ) {
                $data['a.counstituency_id'] = string_to_array($params['counstituency_id']);
            }

            // if the region_id was parsed
            if( !empty($params['region_id']) ) {
                $data['a.region_id'] = string_to_array($params['region_id']);
            }

            // if the district_id was parsed
            if( !empty($params['district_id']) ) {
                $data['a.district_id'] = string_to_array($params['district_id']);
            }

            // if the facility_id was parsed
            if( !empty($params['facility_id']) ) {
                $data['a.facility_id'] = string_to_array($params['facility_id']);
            }

        }

        // EDUCATION ROUTING FILTER
        if(in_array($route, ['education'])) {

            // if the institution_id was parsed
            if( !empty($params['institution_id']) ) {
                $data['institution_id'] = string_to_array($params['institution_id']);
            }

        }

        return $data;
    }
    
}
