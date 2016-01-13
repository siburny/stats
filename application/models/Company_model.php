<?php

require_once('MY_Model.php');

class Company_model extends MY_Model
{
		public $primary_key = 'company_id';
    //public $before_create = array( 'created_at', 'updated_at' );
    //public $before_update = array( 'updated_at' );
}
