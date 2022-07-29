<?php

/**
 * Convert into an Array
 * 
 * @desc Converts a string to an array
 * @param $string The string that will be converted to the array
 * @param $delimeter The character for the separation
 * 
 * @return Array
 */
function string_to_array($string, $delimiter = ",", $key_name = [], $allowEmpty = false) {
    
    // if its already an array then return the data
    if(is_array($string) || empty($string)) {
        return $string;
    }
    
    $array = [];
    $expl = explode($delimiter, $string);
    
    foreach($expl as $key => $each) {
        if(!empty($each) || $allowEmpty) {
            if(!empty($key_name)) {
                $array[$key_name[$key]] = (trim($each) === "NULL" ? null : trim($each));
            } else{
                $array[] = (trim($each) === "NULL") ? null : trim($each, "\"");
            }
        }
    }
    return $array;
}

/**
 * Convert into an Integer Array
 * 
 * @desc Converts a string to an array
 * @param $string The string that will be converted to the array
 * @param $delimeter The character for the separation
 * 
 * @return Array
 */
function string_to_int_array($string, $delimiter = ",") {
    
    // if its already an array then return the data
    if(is_array($string) || empty($string)) {
        return $string;
    }
    
    $array = [];
    $expl = explode($delimiter, $string);
    
    foreach($expl as $key => $each) {
        $array[] = (int) $each;
    }
    return $array;
}

/**
 * Confirm if the text contains a word or string
 * 
 * @param String    $string     => The string to search for a word
 * @param Array     $words      => An array of words to look out for in the string
 * 
 * @return Bool
 */
function contains($string, array $words) {

    if(is_array($string)) {
        return false;
    }
    
    if(function_exists('str_contains')) {
        foreach($words as $word) {
            if(str_contains($string, $word)) {
                return true;
            }
        }
    } else {
        foreach($words as $word) {
            if(stristr($string, $word) !== false) {
                return true;
            }
        }
    }
    return false;
}

function min_length($min, $string, $variable_name = 'the variable') {
    if(strlen($string) < $min) {
        return "Minimum length for {$variable_name} is {$min}";
    }
    return true;
}

function max_length($max, $string, $variable_name = 'the variable') {
    if(strlen($string) > $max) {
        return "Maximum length for {$variable_name} is {$max}";
    }
    return true;
}

function matched($rule, $string, $variable_name = 'the variable') {
    if(!preg_match("/^[{$rule},]+$/", $string)) {
        return "The accepted characters for {$variable_name} are {$rule}";
    }
    return true;
}

function matches($rule, $string, $variable_name = 'the variable') {
    // convert the rule and string into an array
    $rule = string_to_array($rule);
    $string = string_to_array($string);

    // get the difference
    $difference = array_diff($string, $rule);

    // loop through the difference
    foreach($difference as $key) {
        if(!in_array($key, $rule)) {
            return "The accepted values for {$variable_name} are: ".implode(", ", $rule);
        }
    }

    return true;
}

function match_num($rule, $string, $variable_name = 'the variable') {
    if(!preg_match("/^[0-9]+$/", $string)) {
        return "The accepted characters for {$variable_name} are 0-9";
    }
    return true;
}

function match_alpha($rule, $string, $variable_name = 'the variable') {
    if(!preg_match("/^[a-zA-Z]+$/", $string)) {
        return "The accepted characters for {$variable_name} are a-zA-Z";
    }
    return true;
}

function match_alphanum($rule, $string, $variable_name = 'the variable') {
    if(!preg_match("/^[0-9a-zA-Z]+$/", $string)) {
        return "The accepted characters for {$variable_name} are 0-9a-zA-Z";
    }
    return true;
}

function is_email($rule, $string, $variable_name = null) {
    if(!filter_var($string, FILTER_VALIDATE_EMAIL)) {
        return "{$variable_name} must be a valid email address";
    }
    return true;
}

function is_url($rule, $string, $variable_name = 'the variable') {
    if(!filter_var($string, FILTER_VALIDATE_URL)) {
        return "{$variable_name} must be a valid website address.";
    }
    return true;
}

function is_contact($rule, $string, $variable_name = 'the variable') {
    if(!preg_match("/^[0-9+]+$/", $string)) {
        return "{$variable_name} should consist of the characters 0-9+";
    }
    return true;
}

/**
 * Validate the api endpoint values
 * 
 * @param       Array   $rules              This is an array of the rules to be used
 * @param       String  $param_value        This is the value of the variable
 * @param       String  $variable_name          This is the name of the variable
 * 
 * @return Mixed
 */
function validate_value($rules, $param_value, $variable_name) {
    
    // set the result
    $result = [];

    // loop through the rules
    foreach($rules as $rule => $format) {
        if(function_exists($rule) && !empty($param_value)) {
            $rule_result = $rule($format, $param_value, $variable_name);
            if(!empty($rule_result) && $rule_result !== true) {
                $result[] = $rule_result;
            }
        }
    }

    if(!empty($result)) {
        return $result;
    }
}

?>