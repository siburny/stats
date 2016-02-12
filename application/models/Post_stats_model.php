<?php

require_once('MY_Model.php');

class Post_stats_model extends MY_Model
{
	protected $primary_key = 'stats_id';
	protected $_table = 'post_stats';
	
	static function get_total_graph_data($company_id, $start = null, $end = null)
	{
		if($start == NULL || $end == NULL)
		{
			if($start == NULL)
			{
				$start = new DateTime();
			}
			elseif($end == NULL)
			{
				$start = new DateTime($start);
			}
			$end = clone $start;
			$end->modify("-30 days");
		}
		elseif(is_numeric($end))
		{
			$start = new DateTime($start);
			$t = clone $start;
			$end = $t->modify("-".($end-1)." days");
		}
		
		if($start instanceof DateTime)
		{
			$start = $start->format("Y-m-d");
		}
		if($end instanceof DateTime)
		{
			$end = $end->format("Y-m-d");
		}
		
		$ci = &get_instance();
		
		return $ci->db->query("
SELECT date, sum(sessions) as total_sessions, sum(pageviews) as total_pageviews FROM post_stats
WHERE
	date >= ? AND
	date <= ? AND
	post_id IN (
		SELECT post_id FROM posts WHERE company_id = ?
	)
GROUP BY date
ORDER BY date ASC
			", array($end, $start, $company_id))->result_array();
	}
}

