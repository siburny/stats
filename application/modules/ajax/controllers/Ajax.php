<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

	private $user = null;
	private $user_company = null;

	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->load->model("Company_model", "company");

		if(!$this->ion_auth->logged_in())
			return;

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);
	}

	function get_graph_data()
	{
		if(!isset($this->user) || !isset($this->user_company))
		{
			set_status_header(401);
			return;
		}
		
		$data = "Can't connect to Google";

		if($this->user_company->ga_token && $this->user_company->view_id)
		{
			$this->load->library("google_php_client", $this->user_company);
			$rows = $this->google_php_client->get_post_data();
			$data = 'x,Views'.PHP_EOL;
			foreach($rows as $row)
			{
				$data .= implode(",", $row).PHP_EOL;
			}
		}
		
		$this->output->set_output($data);
	}

	function get_post_graph_data()
	{
		if(!isset($this->user) || !isset($this->user_company))
		{
			set_status_header(401);
			return;
		}
		
		$data = "Can't connect to Google";
		$url = $this->input->get("url");
		$update = FALSE;

		$this->load->model("Cache_stats_model", 'cache_stats');
		$cache = $this->cache_stats->get_by('url', $url);
		if($cache !== null)
		{
			$date = (new DateTime($cache->cached_time))->modify("+30 minutes");
			$now = new DateTime();
			if($date >= $now)
			{
				$this->output->set_output($cache->stats);
				return;
			}
			$update = TRUE;
		}

		if($this->user_company->ga_token && $this->user_company->view_id && $url)
		{
			$this->load->library("google_php_client", $this->user_company);
			$client = $this->google_php_client->get_client();
			$analytics = new Google_Service_Analytics($client);

			$res = $analytics->data_ga->get(
				'ga:' . $this->user_company->view_id,
				'7daysAgo',
				'today',
				'ga:sessions',
				array(
					'dimensions' => 'ga:date',
					'sort' => 'ga:date',
					'filters' => 'ga:eventCategory==Author;ga:eventLabel=='.$url
				));

			$data = 'x,Views'.PHP_EOL;
			$rows = $res->getRows();
			foreach($rows as $row)
			{
				$data .= implode(",", $row).PHP_EOL;
			}

			$obj = array(
				'url' => $url,
				'stats' => $data,
				'cached_time' => (new DateTime())->format(DateTime::ISO8601)
			);
			if($update)
			{
				unset($obj['url']);
				$this->cache_stats->update_by('url', $url, $obj);
			}
			else
			{
				$this->cache_stats->insert($obj);
			}
		}
		
		$this->output->set_output($data);
	}

	function get_url_suggestions()
	{
		if(!isset($this->user) || !isset($this->user_company))
		{
			set_status_header(401);
			return;
		}
		
		require_once(APPPATH.'third_party/querypath-3.0.4/src/qp.php');

		$data = [];

		if($url = $this->input->get("url"))
		{
			libxml_use_internal_errors(true);
			$qp = htmlqp($url);

			//Check GA
			$html = strtolower($qp->html());
			if(strpos($html, 'www.google-analytics.com/analytics.js') !== FALSE)
			{
				$data['ga'] = 1;
			}
			elseif(strpos($html, 'www.google-analytics.com/ga.js') !== FALSE)
			{
				$data['ga'] = 2;
			}

			// Check Author
			$author1 = $qp->find(".author");
			if($author1->count() == 1)
			{
				$data['author_text'] = $author1->text();
				$data['author_class'] = '.author';
			}

			if(!isset($data['author_class']))
			{
				$author2 = $qp->find("meta[name=author]");
				if($author2->count() == 1)
				{
					$data['author_text'] = $author2->attr('content');
					$data['author_class'] = 'meta[name=author]';
				}
			}

			if(!isset($data['author_class']))
			{
				$author3 = $qp->find("*[class*='author']");
				
				$classes = [];
				foreach($author3->get() as $el)
				{
					$classes[] = $el->getAttribute('class');
				}
				$classes = array_count_values(array_map('strtolower', $classes));
				
				foreach($classes as $class => $count)
				{
					if($count == 1)
					{
						$data['author_text'] = $qp->find('.'.str_replace(' ', '.', $class))->text();
						$data['author_class'] = '.'.str_replace(' ', '.', $class);
						break;
					}
				}
			}

			//check URL
			$url1 = $qp->find("meta[property='og:url']");
			if($url1->count() >= 1)
			{
				$data['url_text'] = $url1->attr('content');
				$data['url_option'] = 1;
			}

			if(!isset($data['url_option']))
			{
				$url2 = $qp->find("link[rel='canonical']");
				if($url2->count() >= 1)
				{
					$data['url_text'] = $url2->attr('href');
					$data['url_option'] = 2;
				}
			}

			if(!isset($data['url_option']))
			{
				$data['url_text'] = $this->input->get("url");
				$data['url_option'] = 3;
			}
		}
		else
		{
			$data["error"] = "We couldn't retrieve the page.";
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}
	
	function get_post_cache()
	{
		if(!($url = $this->input->get('url')))
			return;
		
		if(!isset($this->user) || !isset($this->user_company))
		{
			set_status_header(401);
			return;
		}

		$this->load->model('Cache_model', 'cache');
		
		require_once(APPPATH.'third_party/querypath-3.0.4/src/qp.php');
		libxml_use_internal_errors(true);
		$qp = htmlqp($url);
		$data = array('url' => $url);
		
		//title
		$title = $qp->find("meta[property='og:title']");
		if($title->count())
		{
			$data['title'] = $title->attr('content');
		}
		else
		{
			$title = $qp->find("meta[property='twitter:title']");
			if($title->count())
			{
				$data['title'] = $title->attr('content');
			}
			else
			{
				$data['title'] = $qp->find("title")->text();
			}
		}
			
		//image
		$image = $qp->find("meta[property='og:image']");
		if($image->count())
		{
			$data['image'] = $image->attr('content');
		}
		else
		{
			$image = $qp->find("meta[property='twitter:image:src']");
			if($image->count())
			{
				$data['image'] = $image->attr('content');
			}
		}
		
		//save images
		if($data['image'])
		{
			$original_url = $data['image'];
			$md5 = substr(md5($original_url.mt_rand()), 0, 12);
				
			$local_dir = substr($md5, 0, 2).DIRECTORY_SEPARATOR.substr($md5, 2, 2).DIRECTORY_SEPARATOR;
			$local = $local_dir.substr($md5, 4);
			$url = substr($md5, 0, 2).'/'.substr($md5, 2, 2).'/'.substr($md5, 4);
			
			$path = $original_url;
			$qpos = strpos($path, "?"); 
			if ($qpos!==false) $path = substr($path, 0, $qpos); 
			$extension = pathinfo($path, PATHINFO_EXTENSION);
			if($extension != "")
			{
				$local .= ".".$extension;
				$url .= ".".$extension;
			}

			$local = FCPATH."images".DIRECTORY_SEPARATOR ."cache".DIRECTORY_SEPARATOR.$local;
			$local_dir = FCPATH."images".DIRECTORY_SEPARATOR ."cache".DIRECTORY_SEPARATOR.$local_dir;
			$url = "/images/cache/".$url;
			
			mkdir($local_dir, 0777, TRUE);
			
			copy($original_url, $local);
			if(file_exists($local))
			{
				$data['image'] = $url;
			}
			else
			{
				unset($data['image']);
			}
		}

		//Date
		$date = $qp->find("meta[property='article:published_time']");
		if($date->count() > 1)
		{
			$data['date_published'] = $date->attr('content');
		}
		else
		{
			$date = $qp->find('time');
			if($date->count() == 1)
			{
				if($date->attr('datetime'))
				{
					$data['date_published'] = $date->attr('datetime');
				}
				else
				{
					$data['date_published'] = $date->text();
				}
			}
			else
			{
				$date = $qp->find('article time');
				if($date->count() == 1)
				{
					if($date->attr('datetime'))
					{
						$data['date_published'] = $date->attr('datetime');
					}
					else
					{
						$data['date_published'] = $date->text();
					}
				}
				else
				{
					$date = $qp->find('article header time');
					if($date->count() == 1)
					{
						if($date->attr('datetime'))
						{
							$data['date_published'] = $date->attr('datetime');
						}
						else
						{
							$data['date_published'] = $date->text();
						}
					}
					else
					{
					}
				}
			}
			//jQuery('article header time')
		}

		// Add DB entry
		$this->cache->insert($data);

		if($data['date_published'])
		{
			$time = strtotime($data['date_published']);
			if($time !== FALSE)
			{
				$data['date_published'] = date('M j, Y', $time);
			}
			else
			{
				$data['date_published'] = null;
			}
		}
		else
		{
			$data['date_published'] = null;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}
}
