<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_preferences extends CI_Migration {
	public function up()
	{
		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => FALSE
			),
			'value' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => FALSE
			),
			'date_updated' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			)
		));

		$this->dbforge->add_key(array('user_id', 'name'), TRUE);
		$this->dbforge->create_table('preferences');
	}

	public function down()
	{
		$this->dbforge->drop_table('preferences', TRUE);
	}
}

