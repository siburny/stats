<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_date_field_to_cache extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_column('cache', array(
			'date_published' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			))
		);
	}

	public function down()
	{
		$this->dbforge->drop_column('cache', 'date_published');
	}
}
