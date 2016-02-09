<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Cache extends CI_Cache
{
	public function __construct()
	{
		parent::__construct(array('adapter' => 'apc'));
	}
}
