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
		//$this->load->library('gravatar');
		//$data['gravatar'] = $this->gravatar->get('siburny@gmail.com');

		$data = array('page_title' => 'Data Settings');

		$this->form_validation->set_rules('firstname', 'First Name', 'required');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required');

		if($this->form_validation->run() == FALSE)
		{
			$data = array('page_title' => 'User Invitation');
			$data['errors'] = validation_errors('<li>', '</li>');

			$data['firstname'] = set_value('firstname');
			$data['lastname'] = set_value('lastname');
			$data['position'] = set_value('position');

			$this->parser->parse('settings/account', $data);
		}
		else
		{
			$this->load->library("ion_auth");
			
			print_r($this->ion_auth_model);
			//$this->ion_auth_model->update();

			//redirect('/settings/account/?done='.time());
		}
	}
}