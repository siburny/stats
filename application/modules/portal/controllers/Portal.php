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

		$this->load->library("google_php_client");
	}

	function index()
	{
		$data = array(
			"page_title" => "Welcome!"
		);
		
		$this->user = $this->ion_auth->user()->row();
		if($this->ion_auth->in_group("manager"))
		{
			$data["is_admin"] = TRUE;
		}

		$rows = $this->google_php_client->get_posts($this->user_company);
		//print_r($rows);

		$data['rows'] = array();
		foreach($rows as $index => $row)
		{
			$data['rows'][] = array(
				"n" => $index + 1,
				"picture" => '/images/ajax-loader.gif',
				"title" => $row[1],
				"views" => $row[2],
				"up_down" => 0
			);
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
		$token = $this->user_company->ga_token;
		
		$client = $this->google_php_client->get_client($token);
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