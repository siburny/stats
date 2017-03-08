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
