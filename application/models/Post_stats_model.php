<?php

require_once('MY_Model.php');

class Post_stats_model extends MY_Model
{
	protected $primary_key = 'stats_id';
	protected $_table = 'post_stats';
}
