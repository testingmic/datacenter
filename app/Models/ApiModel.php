<?php 
namespace App\Models;

class ApiModel {

	/**
     * Log the request made by the user
     * 
     * @param Array $default_params     The parameters that was parsed in the request
     * @param String $code                  This is the response code
     * 
     * @return Bool
     */
	final function logRequest($request) {

		// if the request is last_activity then update the session time
		if($request === 'last_activity') {
			// log the current time
			session()->set(['_last_activity_timer' => time()]);
		}
		
		return true;

	}
	
}
?>