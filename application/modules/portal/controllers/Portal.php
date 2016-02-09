<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Portal extends CI_Controller {
	private $user = null;
	private $user_company = null;

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

		$this->load->library("google_php_client", $this->user_company);
	}

	function index()
	{
		$this->load->model("Post_cache_model", "post_cache");
		
		$data = array(
			"page_title" => "Welcome!"
		);
		
		$this->user = $this->ion_auth->user()->row();
		if($this->ion_auth->in_group("manager"))
		{
			$data["is_admin"] = TRUE;
		}

		if($this->user_company->ga_token && $this->user_company->view_id)
		{
			$today = new DateTime();
			$rows = $this->google_php_client->get_posts($today->format('Y-m-d'), $today->modify("-6 days")->format('Y-m-d'));
			$urls = array_column($rows, 1);

			$cache_db = $this->post_cache->get_many_by('url', $urls);
			$cache = array();
			foreach($cache_db as $value)
			{
				$cache[$value->url] = $value;
			}

			$rows_prev = $this->google_php_client->get_posts($today->modify('-1 days')->format('Y-m-d'), $today->modify('-6 days')->format('Y-m-d'));
			$cache_prev = array_column($rows_prev, 2, 1);

			$data['rows'] = array();
			foreach($rows as $index => $row)
			{
				if(array_key_exists($row[1], $cache))
				{
					$prev = isset($cache_prev[$row[1]]) ? $cache_prev[$row[1]] : 0;

					$ar = array(
						"n" => $index + 1,
						"image" => $cache[$row[1]]->image,
						"url" => $row[1],
						"title" => $cache[$row[1]]->title,
						"views" => $row[2],
						"date_published" => date('M j, Y', strtotime($cache[$row[1]]->date_published)),
						"up_down_text" => $prev && ($row[2] - $prev) ? round(100*($row[2] - $prev)/$prev, 1)."%" : "",
						'author' => $row[0]
					);

					if($prev && (($row[2] - $prev) > 0))
					{
						$ar["up_arrow"] = TRUE;
					}
					elseif($prev && (($row[2] - $prev) < 0))
					{
						$ar["down_arrow"] = TRUE;
					}
					$data['rows'][] = $ar;
				}
				else
				{
					$data['rows'][] = array(
						"n" => $index + 1,
						"image" => '/images/ajax-loader.gif',
						"url" => $row[1],
						"title" => $row[1],
						"views" => $row[2],
						"class" => "loading",
					);
				}
			}
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

	function connect_view($account = null, $property = null, $view = null)
	{
		$data = array(
			"page_title" => "Connect to Google Analytics!",
			'token' => array()
		);
		
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