<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Portal extends CI_Controller {
	private $user = null;
	private $user_company = null;

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language'));

		$this->lang->load('auth');
		$this->load->model("Company_model", "company");

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);
		if(!isset($_SESSION["token"]) && !is_null($this->user_company->ga_token))
		{
			$_SESSION['token'] = $this->user_company->ga_token;
		}
		if(!isset($_SESSION["view_id"]) && !is_null($this->user_company->ga_token))
		{
			$_SESSION['view_id'] = $this->user_company->view_id;
		}
	}

	function index()
	{
		$data = array(
			"page_title" => "Welcome!",
			"is_admin" => FALSE
		);
		
		$this->user = $this->ion_auth->user()->row();
		if($this->ion_auth->in_group("manager"))
		{
			$data["is_admin"] = TRUE;
		}

		$this->parser->parse("portal/home", $data);
	}
	
	function connect($account = null, $property = null, $view = null)
	{
		$data = array(
			"page_title" => "Connect to Google Analytics!",
			"token" => array()
		);

		$this->load->library("google_php_client");
			
		if(is_null($this->user_company->ga_token))
		{
			$data["status"] = "Not connected [<a href='/portal/oauth2/'>CONNECT</a>]";
		}
		elseif(!is_null($this->user_company->view_id))
		{
			$data["status"] = "Connected!";
		}
		else
		{
			if(!isset($_SESSION["token"]))
			{
				$_SESSION['token'] = $this->user_company->ga_token;
			}
			
			$client = $this->google_php_client->get_client($_SESSION["token"]);
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
				$this->user_company->view_id = $view;
				$this->company->update($this->user_company->company_id, array("view_id" => $view));
				$data['done'] = TRUE;
			}
			
			$data["hasTokens"] = count($data['token']) > 0;
		}
			
		$this->parser->parse("portal/connect", $data);
	}
	
	function oauth2()
	{
		$this->load->library("google_php_client");
		$client = $this->google_php_client->get_client();
		
		$code = $this->input->get('code');
		if (is_null($code)) {
			$auth_url = $client->createAuthUrl();
			redirect($auth_url);
		} else {
			$client->authenticate($this->input->get('code'));
			$_SESSION["token"] = $client->getAccessToken();
			
			$this->company->update($this->user_company->company_id, array("ga_token" => $_SESSION["token"]));
			
			redirect('/portal/connect/');
		}
	}

	function ga_code()
	{
		$data = array('page_title' => 'GA Code Generation');

		$this->parser->parse("portal/ga_code", $data);
	}
}