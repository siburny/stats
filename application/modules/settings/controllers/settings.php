<?php

class Settings extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->lang->load('auth');
		$this->load->model("Company_model", "company");

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");

		$this->user = $this->ion_auth->user()->row();
		$this->user_company = $this->company->get($this->user->company);

		$this->parser->data['active_menu_settings'] = TRUE;
	}

	function index()
	{
		$data = array('page_title' => 'Settings');

		$this->parser->parse('settings/index', $data);
	}

	function data() {
		$data = array('page_title' => 'Data Settings');

		$this->parser->parse('settings/data', $data);
	}

	function account() {
		$this->load->library('gravatar');

		$data = array('page_title' => 'Data Settings');
		$data['gravatar'] = $this->gravatar->get('siburny@gmail.com');

		$this->parser->parse('settings/account', $data);
	}
}