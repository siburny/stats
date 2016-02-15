<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Portal extends CI_Controller {
	private $user = NULL;
	private $user_company = NULL;

	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->lang->load('auth');
		$this->load->model("Company_model", "company");

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);
	}

	function _remap()
	{
		$method = func_get_arg(0);
		if (method_exists($this, $method))
		{
			call_user_func_array(array($this, $method), func_get_arg(1));
		}
		else
		{
			$args = array_merge(array($method), func_get_arg(1));
			call_user_func_array(array($this, 'index'), $args);
		}
	}

	function index($page = "page1", $date_from = NULL, $date_to = NULL)
	{
		$data = array(
			"page_title" => "Welcome!"
		);

		if(preg_match('/^page[0-9]+$/i', $page))
		{
			$page = str_replace("page", "", strtolower($page));
			$page--;
		}
		else
			$page = 0;
		
		if($date_from != NULL)
		{
			$date_from = strtolower($date_from);
			switch($date_from)
			{
				case "today":
				case "yesterday":
					$data['date_selected'] = $date_from;
					$date_to = new DateTime($date_from);
					$date_from = clone $date_to;
					break;
				case "7days":
					$data['date_selected'] = $date_from;
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-6 days');
					break;
				case "30days":
					$data['date_selected'] = $date_from;
					$date_to = new DateTime("yesterday");
					$date_from = clone $date_to;
					$date_from->modify('-29 days');
					break;
				default:
					if($date_to != NULL)
					{
						if(preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date_from) && preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date_to))
						{
							$date_from = new DateTime($date_from);
							$date_to = new DateTime($date_to);
							break;
						}
					}
					$data['date_selected'] = "";
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
		$data['date_from'] = $date_from->format("M j, Y");
		$data['date_to'] = $date_to->format("M j, Y");

		$this->load->model("Post_model", "post");
		
		$this->user = $this->ion_auth->user()->row();
		if($this->ion_auth->in_group("manager"))
		{
			$data["is_admin"] = TRUE;
		}

		$rows = Post_model::get_posts($this->user_company->company_id, $date_to, $date_from);
		$day_diff = $date_to->diff($date_from)->days;
		$rows_prev = Post_model::get_posts($this->user_company->company_id, $date_from->modify('-1 days')->format("Y-m-d"), $date_from->modify("-".$day_diff." days")->format("Y-m-d"), FALSE, FALSE);
		$rows_prev = array_column((array)$rows_prev, 'total_pageviews', 'url');

			$data['rows'] = array();
			foreach($rows as $index => $row)
			{
				$prev = isset($rows_prev[$row->url]) ? $rows_prev[$row->url] : 0;
				if($prev && $row->total_pageviews - $prev)
				{
					$prev = round(100*($row->total_pageviews - $prev)/$prev, 1);
				}

				$ar = array(
					"n" => $index + 1,
					"image" => $row->image,
					"url" => $row->url,
					"title" => $row->title,
					"sessions" => $row->total_pageviews,
					"date_published" => date('M j, Y', strtotime($row->date_published)),
					"up_down_text" => $prev ? $prev."%" : "",
					'author' => $row->author
				);

				if($prev > 0)
				{
					$ar["up_arrow"] = TRUE;
				}
				elseif($prev < 0)
				{
					$ar["down_arrow"] = TRUE;
				}
				$data['rows'][] = $ar;
			}

		$this->parser->parse("portal/home", $data);
	}
	
	function connect()
	{
		$data = array(
			"page_title" => "Connect to Google Analytics!"
		);

		if(is_null($this->user_company->ga_token))
		{
			$data["status"] = "Not connected [<a href='/portal/oauth2/'>CONNECT</a>]";
		}
		elseif(!is_null($this->user_company->view_id))
		{
			$data["status"] = "Connected! [<a href='/portal/connect_view/'>Change GA View</a>]";
		}
		else
		{
			redirect('/portal/connect_view/');
		}
			
		$this->parser->parse("portal/connect", $data);
	}

	function connect_view($account = NULL, $property = NULL, $view = NULL)
	{
		$data = array(
			"page_title" => "Connect to Google Analytics!",
			'token' => array()
		);
		
		$this->load->library("google_php_client", $this->user_company);
		$client = $this->google_php_client->get_client();
		$analytics = new Google_Service_Analytics($client);

		if(is_null($account))
		{
			$data['selection'] = 'Account';
			$accounts = $analytics->management_accounts->listManagementAccounts();
			if (count($accounts->getItems()) > 0) {
				$items = $accounts->getItems();
				foreach($items as $item) {
					$data["token"][] = "<a href='/portal/connect_view/".$item->getId()."/'>".$item->name."</a>";
				}
				
			} else {
				$data["error"] = 'No accounts found for this user.';
			}
		}
		elseif(is_null($property))
		{
			$data['selection'] = 'Property';
			$properties = $analytics->management_webproperties->listManagementWebproperties($account);
			if (count($properties->getItems()) > 0) {
				$items = $properties->getItems();
				foreach($items as $item) {
					$data["token"][] = "<a href='/portal/connect_view/".$account."/".$item->getId()."/'>".$item->name."</a>";
				}
				
			} else {
				$data["error"] = 'No properties found for this account.';
			}
		}
		elseif(is_null($view))
		{
			$data['selection'] = 'View';
			$views = $analytics->management_profiles->listManagementProfiles($account, $property);
			if (count($views->getItems()) > 0) {
				$items = $views->getItems();
				foreach($items as $item) {
					$data["token"][] = "<a href='/portal/connect_view/".$account."/".$property."/".$item->getId()."/'>".$item->name."</a>";
				}
				
			} else {
				$data["error"] = 'No views found for this property.';
			}
		}
		else
		{
			$this->user_company->view_id = $view;
			$this->company->update($this->user_company->company_id, array("view_id" => $view));
			$data['done'] = TRUE;
		}
		
		$data["hasTokens"] = count($data['token']) > 0;
		$this->parser->parse("portal/connect_view", $data);
	}
	
	function oauth2()
	{
		$this->load->library("google_php_client", $this->user_company);
		$client = $this->google_php_client->get_client();
		
		$code = $this->input->get('code');
		if (is_null($code)) {
			$auth_url = $client->createAuthUrl();
			redirect($auth_url);
		} else {
			$client->authenticate($this->input->get('code'));
			$token = $client->getAccessToken();
			$this->company->update($this->user_company->company_id, array("ga_token" => $token));
			
			redirect('/portal/connect/');
		}
	}

	function ga_code()
	{
		$data = array('page_title' => 'GA Code Generation');

		$this->parser->parse("portal/ga_code", $data);
	}
}