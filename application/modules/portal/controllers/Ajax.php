<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

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

	function get_graph_data()
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

		if(isset($_SESSION["token"]) && isset($_SESSION["view_id"]))
		{
			$this->load->library("google_php_client");
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
				'30daysAgo',
				'today',
				'ga:totalEvents',
				array(
					'dimensions' => 'ga:date',
					'sort' => 'ga:date',
					'max-results' => '25',
					'filters' => 'ga:eventCategory==Author'
				));

			$rows = $res->getRows();
			array_unshift($rows, ['x', 'Views']);
			
			$data['chart_data'] = json_encode($rows);
		}
		$this->parser->parse("portal/home", $data);
	}
}
