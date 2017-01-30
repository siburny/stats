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
		$this->load->model("Post_stats_model", "post_stats");

		if(!$this->ion_auth->logged_in())
			return;

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);
	}

	function get_graph_data()
	{
		$this->load->library("google_php_client", $this->user_company);

		$date_from = $this->input->get("date_from");
		$date_to = $this->input->get("date_to");
		if(!isset($this->user) || !isset($this->user_company))
		{
			set_status_header(401);
			return;
		}

		if($date_from != NULL)
		{
			$date_from = strtolower($date_from);
			switch($date_from)
			{
				case "today":
				case "yesterday":
					$date_to = new DateTime($date_from);
					$date_from = clone $date_to;
					break;
				case "7days":
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-6 days');
					break;
				case "30days":
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-29 days');
					break;
				default:
					if($date_to != NULL)
					{
						if(preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date_from) && preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date_to))
						{
							$date_from = DateTime::createFromFormat("m-d-Y", $date_from);
							$date_to = DateTime::createFromFormat("m-d-Y", $date_to);
							break;
						}
					}
					$date_to = NULL;
					break;
			}
		}
		if($date_to == NULL)
		{
			$date_to = (new DateTime());
			$date_from = clone $date_to;
			$date_from->modify('-29 days');
		}

		$data = "Can't connect to Google";

		$post_id = NULL;
		$author = NULL;
		$search_param = array();
		if($this->input->get("post_id") != NULL && is_numeric($this->input->get("post_id")))
		{
			$post_id = $this->input->get("post_id");
		}
		elseif($this->input->get("author_name") != NULL && !empty($this->input->get("author_name")))
		{
			$search_param['author'] = $this->input->get("author_name");
		}
		elseif($this->input->get("search"))
		{
			$search_param['search'] = $this->input->get("search");
		}

		$rows = $this->google_php_client->get_stats($search_param, $date_to->format('Y-m-d'), $date_from->format('Y-m-d'), 'date');

			$data = 'x,Views'.PHP_EOL;
			if($date_to == $date_from)
			{
				$date = $date_to->format('Y-m-d');
				foreach($rows as $index => $row)
				{
					$rows[$index][0] = $date.' '.$row[0].':00';
				}
			}
			else
			{
				foreach($rows as $index => $row)
				{
					$rows[$index][0] = substr($rows[$index][0], 0, 4).'-'.substr($rows[$index][0], 4, 2).'-'.substr($rows[$index][0], 6, 2).' 00:00';
				}
			}


			/*elseif(!is_null($post_id))
			{
				$rows = $this->post_stats->get_manager_graph_post_data($this->user_company->company_id, $post_id, $date_to, $date_from);
			}
			elseif(!is_null($author))
			{
				$rows = $this->post_stats->get_author_graph_data($this->user_company->company_id, $author, $date_to, $date_from);
			}
			else
			{
				$rows = Post_stats_model::get_manager_graph_data($this->user_company->company_id, $date_to, $date_from);
			}*/

			foreach($rows as $row)
			{
				$data .= implode(",", $row).PHP_EOL;
			}

		$this->output->set_output($data);
	}

	function get_post_graph_data()
	{
		$this->load->library("google_php_client", $this->user_company);

		if(!isset($this->user) || !isset($this->user_company))
		{
			set_status_header(401);
			return;
		}

		$data = "Can't connect to Google";
		$url = $this->input->get("url");

		$data = 'x,Views'.PHP_EOL;
		if(!empty($url))
		{
			$rows = $this->google_php_client->get_graph_data('yesterday', '7daysAgo', $url);

			foreach($rows as $row)
			{
				$row[0] = substr($row[0], 0, 4).'-'.substr($row[0], 4, 2).'-'.substr($row[0], 6, 2);
				$data .= implode(",", $row).PHP_EOL;
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

}
