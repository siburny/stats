<?php

require_once('MY_Model.php');

class Preferences_model extends MY_Model
{
		public $primary_key = 'pref_id';
		public const DATE_FORMAT = array('M j, Y', 'm-d-Y', 'd-m-Y', 'Y-m-d');
		public const DATE_RANGE  = array('30daysAgo', '7daysAgo', 'yesterday', 'today');
		public const SORTING     = array('sessions', 'pageviews');

		public $date_format = Preferences_model::DATE_FORMAT[0];
		public $date_range = Preferences_model::DATE_RANGE[0];
		public $sorting = Preferences_model::SORTING[0];

		public function load($user_id)
		{
			$set = $this->get_many_by('user_id', $user_id);
			if(!empty($set))
			{
				//$this->date_format = $set->
				//return array('date_format' => 'M j, Y', 'date_range' => '30daysAgo', 'sorting' => 'sessions');
			}
		}

    //public $before_create = array( 'created_at', 'updated_at' );
    //public $before_update = array( 'updated_at' );
}
