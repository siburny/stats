<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_preferences extends CI_Migration {
	public function up()
	{
		$this->dbforge->add_field(array(
			"pref_id" => array(
				'type' => 'INT',
				'null' => FALSE
			),
			'date' => array(
				'type' => 'DATE',
				'null' => FALSE
			),
			'sessions' => array(
				'type' => 'INT',
				'null' => FALSE
			),
			'pageviews' => array(
				'type' => 'INT',
				'null' => FALSE
			),
			'date_updated' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			)
		));

		$this->dbforge->add_key('post_id', TRUE);
		$this->dbforge->create_table('preferences');
	}

	public function down()
	{
		$this->dbforge->drop_table('preferences', TRUE);
	}
}

