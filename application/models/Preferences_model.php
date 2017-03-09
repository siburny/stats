<?php

require_once('MY_Model.php');

/*
 * 
 */

class Preferences_model extends MY_Model
{
		public $primary_key = 'pref_id';
		public static $DATE_FORMAT = array('M j, Y', 'm-d-Y', 'd-m-Y', 'Y-m-d');
		public static $DATE_RANGE  = array('30days', '7days', 'yesterday', 'today');
		public static $SORTING     = array('sessions', 'pageviews');

		public $date_format = null;
		public $date_range = null;
		public $sorting = null;

		public function load($user_id)
		{
			$this->date_format = Preferences_model::$DATE_FORMAT[0];
			$this->date_range = Preferences_model::$DATE_RANGE[0];
			$this->sorting = Preferences_model::$SORTING[0];

			$set = $this->get_many_by('user_id', $user_id);
			$set = array_column($set, 'value', 'name');
			if(!empty($set))
			{
				if(isset($set['date_format']))
				{
					$this->date_format = $set['date_format'];
				}
				if(isset($set['date_range']))
				{
					$this->date_range = $set['date_range'];
				}
				if(isset($set['sorting']))
				{
					$this->sorting = $set['sorting'];
				}
			}
		}

    public $before_create = array( 'updated_at' );
    public $before_update = array( 'updated_at' );
    public $before_replace = array( 'updated_at' );
}
