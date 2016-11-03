<?php

require_once('MY_Model.php');

class Post_stats_model extends MY_Model
{
	protected $primary_key = 'stats_id';
	protected $_table = 'post_stats';

	static function get_manager_graph_data($company_id, $start, $end)
	{
		/*if($start == NULL || $end == NULL)
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
		}*/

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
SELECT DATE_FORMAT(date, '%Y-%m-%d %H:%i'), /*sum(sessions) as total_sessions,*/ sum(pageviews) as total_pageviews FROM post_stats
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

	function get_author_graph_data($company_id, $author, $start, $end)
	{
		if($start instanceof DateTime)
		{
			$start = $start->format("Y-m-d");
		}
		if($end instanceof DateTime)
		{
			$end = $end->format("Y-m-d");
		}

		$ci = &get_instance();

		return $ci->db->querySELECT
(" DATE_FORMAT(date, '%Y-%m-%d %H:%i'), /*sum(sessions) as total_sessions,*/ sum(pageviews) as total_pageviews FROM post_stats
WHERE
	date >= ? AND
	date <= ? AND
	post_id IN (
		SELECT post_id FROM posts WHERE company_id = ? and author = ?
	)
GROUP BY date
ORDER BY date ASC
			", array($end, $start, $company_id, $author))->result_array();
	}

	function get_manager_graph_post_data($company_id, $post_id, $start = null, $end = null)
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
SELECT DATE_FORMAT(date, '%Y-%m-%d %H:%i'), /*sum(sessions) as total_sessions,*/ sum(pageviews) as total_pageviews FROM post_stats
WHERE
	date >= ? AND
	date <= ? AND
	post_id IN (
		SELECT post_id FROM posts WHERE company_id = ? and post_id = ?
	)
GROUP BY date
ORDER BY date ASC
			", array($end, $start, $company_id, $post_id))->result_array();
	}

	function get_manager_graph_data_hourly($user_company, $start = null)
	{
		if($start == NULL)
		{
			if($start == NULL)
			{
				$start = new DateTime();
			}
		}

		if($start instanceof DateTime)
		{
			$start = $start->format("Y-m-d");
		}

		$this->load->library("google_php_client", $user_company);
		return $this->google_php_client->get_posts_stats_by_hour($start);
	}

	static function get_post_graph_data($company_id, $url, $start = null, $end = null)
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
			$end->modify("-7 days");
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
SELECT date, /*sum(sessions) as total_sessions,*/ sum(pageviews) as total_pageviews FROM post_stats
WHERE
	date >= ? AND
	date <= ? AND
	post_id IN (
		SELECT post_id FROM posts WHERE company_id = ? AND url = ?
	)
GROUP BY date
ORDER BY date ASC
			", array($end, $start, $company_id, $url))->result_array();
	}
}

