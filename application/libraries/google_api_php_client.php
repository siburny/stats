<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class google_api_php_client
{
    public function __construct()
    {
        require_once APPPATH.'third_party/google-api-php-client-1.1.6/src/Google/autoload.php';
    }
}