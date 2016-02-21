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

	function get_history_posts($debug = FALSE)
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
					$url = $row[0];
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

	function get_history_stats($debug = FALSE)
	{
		$this->load->library("google_php_client");

		$today = new DateTime();
		$start_date = $today->format('Y-m-d');
		$end_date = $today->modify("-30 days")->format('Y-m-d');

		$companies = $this->company->get_all();
		foreach($companies as $company)
		{
			if($company->ga_token && $company->view_id)
			{
				$posts = $this->post->where('company_id', $company->company_id)->get_all();

				$this->google_php_client->set_user_company($company);
				foreach($posts as $post)
				{
					$count = $this->db->from('post_stats')->where('post_id', $post->post_id)->where('date_updated >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)')->count_all_results();

					if(!$count)
					{
						if($debug)
						{
							echo "Checking post URL ".$post->url.PHP_EOL;
						}

						$rows = array();
						$rows = $this->google_php_client->get_post_stats($post->url, $start_date, $end_date);

						foreach($rows as $row)
						{
							$this->db->query("INSERT INTO post_stats VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE sessions = ?, pageviews = ?, date_updated = NOW()",
								array($post->post_id, $row[0], $row[1], $row[2], $row[1], $row[2]));
							if($debug)
							{
								echo "Updated post_id #".$post->post_id." for ".$row[0]." (".$row[1].", ".$row[2].")".PHP_EOL;
							}
						}

						/*for($week=1;$week<=4;$week++)
						{
							$week_date = new DateTime();
							$week_start = $week_date->modify("-".(7*$week)." days")->format("Y-m-d");
							$week_end = $week_date->modify("-6 days")->format("Y-m-d");

							$count = $this->db->from('post_stats')->where('post_id', $post->post_id)->where('date >=', $week_end)->where('date <=', $week_start)->count_all_results();

							if(!$count)
							{
								$rows = $this->google_php_client->get_post_stats($post->url, $week_start, $week_end);

								foreach($rows as $row)
								{
									$this->db->query("INSERT INTO post_stats VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE sessions = ?, pageviews = ?, date_updated = NOW()",
										array($post->post_id, $row[0], $row[1], $row[2], $row[1], $row[2]));
									if($debug)
									{
										echo "Updated post_id #".$post->post_id." for ".$row[0]." (".$row[1].", ".$row[2].")".PHP_EOL;
									}
								}
								usleep(500000);
							}
						}*/

						usleep(500000);
					}
					elseif($debug)
					{
						echo 'Skipping post URL '.$post->url.' - already up-to-date'.PHP_EOL;
					}

				}
			}
			elseif($debug)
			{
				echo "Skipping company ID #".$company->company_id." - no GA data".PHP_EOL;
			}
		}
	}

	function get_today($debug = FALSE)
	{
		$today = new DateTime();
		return $this->_get_latest($today->format('Y-m-d'), $debug);
	}

	function _get_latest($start_date, $debug = FALSE)
	{
		$this->load->library("google_php_client");

		$end_date = $start_date;

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
						echo $this->db->last_query();
					}
					return;
				}

				usleep(500000);
			}
			elseif($debug)
			{
				echo "Skipping company ID #".$company->company_id." - no GA data".PHP_EOL;
			}
		}
	}
}
