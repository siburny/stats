<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_cache_table extends CI_Migration {
	public function up()
	{
		$this->dbforge->add_field(array(
			"cache_id" => array(
				'type' => 'int',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			"url" => array(
				'type' => 'VARCHAR',
				'constraint' => '2000',
				'null' => FALSE
			),
			"title" => array(
				'type' => 'VARCHAR',
				'constraint' => '1000',
				'null' => TRUE
			),
			"image" => array(
				'type' => 'VARCHAR',
				'constraint' => '2100',
				'null' => TRUE
			)
		));

		$this->dbforge->add_key('cache_id', TRUE);
		$this->dbforge->create_table('cache');

		$this->db->query("ALTER TABLE `cache` ADD UNIQUE KEY (url(100))");
	}

	public function down()
	{
		$this->dbforge->drop_table('cache', TRUE);
	}
}

