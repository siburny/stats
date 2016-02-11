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
			$key = "viewstats_".$this->user_company->view_id;
			if(($val = $this->cache->get($key)) !== FALSE)
			{
				$data = $val;
			}
			else
			{
				$this->load->library("google_php_client", $this->user_company);
				$rows = $this->google_php_client->get_post_data();
				$data = 'x,Views'.PHP_EOL;
				foreach($rows as $row)
				{
					$data .= implode(",", $row).PHP_EOL;
				}
				$this->cache->save($key, $data, 1800);
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

		if($this->user_company->ga_token && $this->user_company->view_id && $url)
		{
			$key = "viewstats_".$this->user_company->view_id."_".$url;

			if(($val = $this->cache->get($key)) !== FALSE)
			{
				$this->output->set_output($val);
				return;
			}

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

			$this->cache->save($key, $data, 1800);
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

}
