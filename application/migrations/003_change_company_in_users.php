<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Change_company_in_users extends CI_Migration {

	public function up()
	{
		$this->dbforge->drop_column('users', 'company');
		$this->dbforge->add_column('users', array(
			'company' => array(
				'type' => 'INT',
				'null' => FALSE,
				'default' => 1
			))
		);
		$this->dbforge->modify_column('users', array(
			'company' => array(
				'type' => 'INT',
				'null' => FALSE
			))
		);
	}

	public function down()
	{
	}
}
