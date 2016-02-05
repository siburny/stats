<?php

require_once('MY_Model.php');

class Cache_stats_model extends MY_Model
{
	protected $primary_key = 'cache_id';
	protected $_table = 'cache_stats';
	//public $before_create = array( 'created_at', 'updated_at' );
	//public $before_update = array( 'updated_at' );
}
