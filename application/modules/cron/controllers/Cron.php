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
			if($debug)
			{
				echo "********************************************************************************".PHP_EOL;
				echo "* Company ID: ".str_pad($company->company_id, 6, ' ')."                                                           *".PHP_EOL;
				echo "********************************************************************************".PHP_EOL;
			}
			if($company->ga_token && $company->view_id)
			{
				$this->google_php_client->set_user_company($company);
				$rows = $this->google_php_client->get_posts(NULL, NULL, FALSE);

				$i = 1; $count = count($rows);
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
							echo "[".$i."/".$count."] Skipping URL ".$url." - already added".PHP_EOL;
						}
					}
					elseif($debug)
					{
						echo "[".$i."/".$count."] Skipping URL ".$url." - not our domain".PHP_EOL;
					}
					$i++;
				}
			}
			elseif($debug)
			{
				echo "Skipping company ID #".$company->company_id." - no GA data".PHP_EOL;
			}
            echo PHP_EOL.PHP_EOL;
		}
	}
}
