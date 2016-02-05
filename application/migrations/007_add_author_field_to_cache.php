<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_author_field_to_cache extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_column('cache', array(
			'author' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE
			)
		));
	}

	public function down()
	{
		$this->dbforge->drop_column('cache', 'date_published');
	}
}
