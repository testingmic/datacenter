<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Api extends BaseConfig
{

    /**
     * --------------------------------------------------------------------------
     * Api Version
     * --------------------------------------------------------------------------
     *
     * This is the current Api Version for the Application
     *
     * @var String
     */
    public $api_version = "v1";

    /**
     * --------------------------------------------------------------------------
     * Api Versioning List
     * --------------------------------------------------------------------------
     *
     * These are the list of all Api Versions that are currently in play
     *
     * @var Array
     */
    public $versions_list = ['v1', 'v2', 'v3'];

    /**
     * --------------------------------------------------------------------------
     * Allowed Methods
     * --------------------------------------------------------------------------
     *
     * These are the list of all allowed request methods and their corresponding class method to use.
     *
     * @var Array
     */
    public $allowed_methods = [
        "POST" => "create",
        "PUT" => "update",
        "GET" => "list",
        "DELETE" => "delete"
    ];
}
?>