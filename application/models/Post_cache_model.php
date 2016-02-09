<?php

require_once('MY_Model.php');

class Post_cache_model extends MY_Model
{
	protected $primary_key = 'cache_id';
	protected $_table = 'post_cache';
	//public $before_create = array( 'created_at', 'updated_at' );
	//public $before_update = array( 'updated_at' );

	static function get_graph_data($url)
	{

	}
}
