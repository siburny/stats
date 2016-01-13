<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_view_id_to_company extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_column('companies', array(
			'view_id' => array(
				'type' => 'INT',
				'null' => TRUE
			))
		);
	}

	public function down()
	{
	}
}
