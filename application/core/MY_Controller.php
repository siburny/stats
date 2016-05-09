<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	function __construct()
	{
		parent::__construct();

		if($this->ion_auth->logged_in())
		{
			$this->parser->data['is_logged_in'] = TRUE;
		}
	}
}