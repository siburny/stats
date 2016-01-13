<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Portal extends CI_Controller {
	const CLIENT_ID = "701858312707-oi2kec5fosja28icldg70c77t1qogakd.apps.googleusercontent.com";
	const CLIENT_SECRET = "tCfJLKGakV7ezgEsAYQ7TWvZ";

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language'));

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");
	}

	function index()
	{
		$data = array(
			"page_title" => "Welcome!",
			"is_admin" => FALSE
		);
		
		$user = $this->ion_auth->user()->row();
		if($this->ion_auth->in_group("manager"))
		{
			$data["is_admin"] = TRUE;
		}
			
		$this->parser->parse("portal/home", $data);
	}
	
	function connect($account = null, $property = null, $view = null)
	{
		$this->load->model("Company_model", "company");
		
		$data = array(
			"page_title" => "Connect to Google Analytics!",
			"token" => array()
		);

		$this->load->library("google_api_php_client");
		$client = new Google_Client();
		$client->setClientId(Portal::CLIENT_ID);
		$client->setClientSecret(Portal::CLIENT_SECRET);
		$client->setRedirectUri("http://stats.local.com/portal/oauth2/");
		$client->setAccessType("offline");
		$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
		
		$user = $this->ion_auth->user()->row();
		$company = $this->company->get($user->company);
		if(is_null($company) || !$company->ga_token)
		{
			$data["status"] = "Not connected [<a href='/portal/oauth2/'>CONNECT</a>]";
		}
		elseif(!is_null($company->view_id))
		{
			$data["status"] = "Connected!";
		}
		else
		{
			if(!isset($_SESSION["token"]))
			{
				$_SESSION['token'] = $company->ga_token;
			}
			
			$client->setAccessToken($_SESSION["token"]);
			$analytics = new Google_Service_Analytics($client);

			if(is_null($account))
			{
				$accounts = $analytics->management_accounts->listManagementAccounts();
				if (count($accounts->getItems()) > 0) {
					$items = $accounts->getItems();
					foreach($items as $item) {
						$data["token"][] = "<a href='/portal/connect/".$item->getId()."/'>".$item->name."</a>";
					}
					
				} else {
					$data["error"] = 'No accounts found for this user.';
				}
			}
			elseif(is_null($property))
			{
				$properties = $analytics->management_webproperties->listManagementWebproperties($account);
				if (count($properties->getItems()) > 0) {
					$items = $properties->getItems();
					foreach($items as $item) {
						$data["token"][] = "<a href='/portal/connect/".$account."/".$item->getId()."/'>".$item->name."</a>";
					}
					
				} else {
					$data["error"] = 'No properties found for this account.';
				}
			}
			elseif(is_null($view))
			{
				$views = $analytics->management_profiles->listManagementProfiles($account, $property);
				if (count($views->getItems()) > 0) {
					$items = $views->getItems();
					foreach($items as $item) {
						$data["token"][] = "<a href='/portal/connect/".$account."/".$property."/".$item->getId()."/'>".$item->name."</a>";
					}
					
				} else {
					$data["error"] = 'No views found for this property.';
				}
			}
			else
			{
				$_SESSION['view_id'] = $view;
				$company->view_id = $view;
				$this->company->update($company->company_id, array("view_id" => $view));
				$data['done'] = TRUE;
			}
			
			$data["hasTokens"] = count($data['token']) > 0;
		}
			
		$this->parser->parse("portal/connect", $data);
	}
	
	function oauth2()
	{
		$this->load->library("google_api_php_client");
		$this->load->model("Company_model", "company");
		
		$client = new Google_Client();
		$client->setClientId(Portal::CLIENT_ID);
		$client->setClientSecret(Portal::CLIENT_SECRET);
		$client->setRedirectUri("http://stats.local.com/portal/oauth2/");
		$client->setAccessType("offline");
		$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
		
		$code = $this->input->get('code');
		if (is_null($code)) {
			$auth_url = $client->createAuthUrl();
			redirect($auth_url);
		} else {
			$client->authenticate($this->input->get('code'));
			$_SESSION["token"] = $client->getAccessToken();
			
			$user = $this->ion_auth->user()->row();
			$this->company->update($user->company, array("ga_token" => $_SESSION["token"]));
			
			redirect('/portal/connect/');
		}
	}
	
	function reports()
	{
		if(!isset($_SESSION["token"]) || !isset($_SESSION["view_id"]))
			redirect("/portal/");
			
		$this->load->library("google_api_php_client");
		$client = new Google_Client();
		$client->setClientId(Portal::CLIENT_ID);
		$client->setClientSecret(Portal::CLIENT_SECRET);
		$client->setRedirectUri("http://stats.local.com/portal/oauth2/");
		$client->setAccessType("offline");
		$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
		$client->setAccessToken($_SESSION["token"]);

		$analytics = new Google_Service_Analytics($client);

		$res = $analytics->data_ga->get(
      'ga:' . $_SESSION['view_id'],
      '7daysAgo',
      'today',
      'ga:totalEvents',
			array(
				'dimensions' => 'ga:eventCategory,ga:eventAction',
				'max-results' => '25'
			));

		$rows = $res->getRows();
		print_r($rows);

	}
}