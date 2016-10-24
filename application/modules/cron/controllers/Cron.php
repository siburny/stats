<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{
	const HISTORY_DAYS = 30;

	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->load->model("Company_model", "company");
		$this->load->model("Post_model", "post");
	}

	function update_thumbs($debug = FALSE)
	{
		$companies = $this->company->get_all();
		
		foreach($companies as $company)
		{
			$rows = $this->post->list_posts($company->company_id);
				
			$i = 0; $count = count($rows);
			print_r($rows[0]);
			foreach($rows as $row)
			{
				$data = Post_model::get_post($row->url);
				unset($data['url']);

				$this->post->update($row->post_id, $data);
				if($debug)
				{
					echo "[".++$i."/".$count."] Updated thumb ".$row->url.PHP_EOL;
				}
			}
		}
	}

	function get_all_posts($debug = FALSE)
	{
		$this->load->library("google_php_client");
		$companies = $this->company->get_all();
		
		foreach($companies as $company)
		{
			if($company->ga_token && $company->view_id)
			{
				$this->google_php_client->set_user_company($company);
				$rows = $this->google_php_client->get_posts(NULL, NULL, FALSE);
				
				$i = 0; $count = count($rows);
				foreach($rows as $row)
				{
					$url = Post_model::process_url($row[0]);
					$domain = parse_url($url, PHP_URL_HOST);
					if(strpos($domain, $company->domain) !== FALSE)
					{
						if(!$this->post->get_by('url', $url))
						{
							$data = Post_model::get_post($url);
							$data['company_id'] = $company->company_id;
							$this->post->insert($data);
							
							if($debug)
							{
								echo "[".$i."/".$count."] Added URL ".$url.PHP_EOL;
							}
						}
						elseif($debug)
						{
							echo "Skipping URL ".$url." - already added".PHP_EOL;
						}
					}
					elseif($debug)
					{
						echo "Skipping URL ".$url." - not our domain".PHP_EOL;
					}
					$i++;
				}
			}
			elseif($debug)
			{
				echo "Skipping company ID #".$company->company_id." - no GA data".PHP_EOL;
			}
		}
	}

	function get_all_stats($debug = FALSE)
	{
		$today = new DateTime();
		for($i=0;$i<30;$i++)
		{
			$this->_get_latest($today->format('Y-m-d'), $debug);
			$today->modify("-1 days");
		}
	}

	function get_today($debug = FALSE)
	{
		$today = new DateTime();
		return $this->_get_latest($today->format('Y-m-d'), $debug);
	}

	function get_yesterday($debug = FALSE)
	{
		$today = new DateTime();
		$today->modify("-1 days");
		return $this->_get_latest($today->format('Y-m-d'), $debug);
	}

	function update($debug = FALSE)
	{
		$this->get_yesterday($debug);
		$this->get_today($debug);
	}

	function _get_latest($start_date, $debug = FALSE)
	{
		$this->load->library("google_php_client");

		$end_date = $start_date;

		$start_time = microtime(TRUE);

		$companies = $this->company->get_all();
		foreach($companies as $company)
		{
			if($company->ga_token && $company->view_id)
			{
				$posts = $this->post->where('company_id', $company->company_id)->as_array()->get_all();
				$posts = array_column($posts, 'post_id', 'url');

				$this->google_php_client->set_user_company($company);
				$rows = $this->google_php_client->get_posts_stats($start_date, $end_date);

				foreach($rows as $row)
				{
					$url = $row[0];
					if(array_key_exists($url, $posts))
					{
						$post_id = $posts[$url];
					}
					else
					{
						if($debug)
						{
							echo "Post is not found: adding ".$url.PHP_EOL;
						}
						$data = Post_model::get_post($url);
						$data['company_id'] = $company->company_id;
						$post_id = $this->post->insert($data);
					}

					$this->db->query("INSERT INTO post_stats VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE sessions = ?, pageviews = ?, date_updated = NOW()",
					array($post_id, $start_date, $row[1], $row[2], $row[1], $row[2]));
					if($debug)
					{
						echo "Updated post_id #".$post_id." for ".$start_date." (".$row[1].", ".$row[2].")".PHP_EOL;
					}
				}

				usleep(500000);
			}
			elseif($debug)
			{
				echo "Skipping company ID #".$company->company_id." - no GA data".PHP_EOL;
			}
		}
		if($debug)
		{
			echo 'Done: '.round(microtime(TRUE) - $start_time, 2)." secs, ".$this->google_php_client->queries." queries.".PHP_EOL.PHP_EOL;
		}
	}
}
