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
			redirect("/auth/");

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);
	}

	function get_graph_data()
	{
		$data = "Can't connect to Google";

		if($this->user_company->ga_token && $this->user_company->view_id)
		{
			$this->load->library("google_php_client");
			$client = $this->google_php_client->get_client($this->user_company->ga_token);
			$analytics = new Google_Service_Analytics($client);

			$res = $analytics->data_ga->get(
				'ga:' . $this->user_company->view_id,
				'30daysAgo',
				'today',
				'ga:totalEvents',
				array(
					'dimensions' => 'ga:date',
					'sort' => 'ga:date',
					'max-results' => '25',
					'filters' => 'ga:eventCategory==Author'
				));

			$data = 'x,Views'.PHP_EOL;
			$rows = $res->getRows();
			foreach($rows as $row)
			{
				$data .= implode(",", $row).PHP_EOL;
			}
		}
		
		$this->output
					->set_content_type('application/json')
					->set_output($data);
	}
}
