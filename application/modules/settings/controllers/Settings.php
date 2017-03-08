<?php

class Settings extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		if(!$this->ion_auth->logged_in())
			redirect("/auth/");

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

	function save($part = null)
	{
		if(empty($part))
		{
			redirect('/settings/account/');
		}
	}

	function account() {
		$data = array('page_title' => 'Data Settings');

		$errors = "errors";
		if($this->input->method() == 'post')
		{
			if($this->input->post('type') == 'profile')
			{
				$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
				$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
				$errors = 'errors_profile';
			}
			else if($this->input->post('type') == 'account')
			{
				$this->form_validation->set_rules('email', 'Email', 'trim|required');
				$this->form_validation->set_rules('password', 'Password', 'trim');
				$this->form_validation->set_rules('confirmpassword', 'Password Confirmation', 'trim|matches[password]');
				$errors = 'errors_account';
			}
		}

		if($this->form_validation->run() == FALSE)
		{
			$data[$errors] = validation_errors('<li>', '</li>');

			$data['firstname'] = set_value('firstname', $this->user->first_name);
			$data['lastname'] = set_value('lastname', $this->user->last_name);
			$data['position'] = set_value('position', $this->user->position);
			$data['gravatar'] = set_value('gravatar', $this->user->gravatar);

			$data['email'] = set_value('email', $this->user->email);

			$this->parser->parse('settings/account', $data);
		}
		else
		{
			$this->load->library("ion_auth");

			$user = array();
			if($this->input->post('type') == 'profile')
			{
				$user['first_name'] = set_value('firstname');
				$user['last_name'] = set_value('lastname');
				$user['position'] = set_value('position');
				$user['gravatar'] = !empty(set_value('gravatar')) ? 1 : 0;
			}
			else if($this->input->post('type') == 'account')
			{
				$user['email'] = set_value('email');
				$password = set_value('password');
				if(!empty($password))
				{
					$this->ion_auth->reset_password($this->user->email, $password);
				}
			}

			$this->ion_auth->update($this->user->id, $user);

			redirect('/settings/account/?done='.time());
		}
	}

	function preferences()
	{
		$data = array('page_title' => 'Preferences');

		$this->form_validation->set_rules('date_format', 'Date Format', 'required');
		$this->form_validation->set_rules('date_range', 'Date Range', 'required');
		$this->form_validation->set_rules('sorting', 'Sorting', 'required');

		if($this->form_validation->run() == FALSE)
		{
			$data['errors'] = validation_errors('<li>', '</li>');

			$date_format = array_search($this->preferences->date_format, Preferences_model::DATE_FORMAT);
			$date_format = set_value('date_format', $date_format);
			if(!isset(Preferences_model::DATE_FORMAT[$date_format])) $date_format = 0;
			$data['date_format_'.$date_format] = true;

			$date_range = array_search($this->preferences->date_range, Preferences_model::DATE_RANGE);
			$date_range = set_value('date_range', $date_range);
			if(!isset(Preferences_model::DATE_RANGE[$date_range])) $date_range = 0;
			$data['date_range_'.$date_range] = true;

			$sorting = array_search($this->preferences->sorting, Preferences_model::SORTING);
			$sorting = set_value('sorting', $sorting);
			if(!isset(Preferences_model::SORTING[$sorting])) $sorting = 0;
			$data['sorting_'.$sorting] = true;

			$this->parser->parse('settings/preferences', $data);
		}
		else
		{
			$date_format = set_value('date_format', 0);
			if(!isset(Preferences_model::DATE_FORMAT[$date_format])) $date_format = 0;

			$date_range = set_value('date_range', 0);
			if(!isset(Preferences_model::DATE_RANGE[$date_range])) $date_range = 0;

			$sorting = set_value('sorting', 0);
			if(!isset(Preferences_model::SORTING[$sorting])) $sorting = 0;

			$data = array(
				array('user_id' => $this->user->id, 'name' => 'date_format', 'value' => Preferences_model::DATE_FORMAT[$date_format]),
				array('user_id' => $this->user->id, 'name' => 'date_range', 'value' => Preferences_model::DATE_RANGE[$date_range]),
				array('user_id' => $this->user->id, 'name' => 'sorting', 'value' => Preferences_model::SORTING[$sorting])
			);
			foreach($data as $value)
			{
				$this->preferences->replace_by($value);
			}

			redirect('/settings/preferences/?done='.time());
		}
	}
}