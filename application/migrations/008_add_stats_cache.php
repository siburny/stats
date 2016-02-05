<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_stats_cache extends CI_Migration
{
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
			"stats" => array(
				'type' => 'VARCHAR',
				'constraint' => '10000',
				'null' => TRUE
			),
			"cached_time" => array(
				'type' => 'DATETIME',
				'null' => FALSE
			)
		));

		$this->dbforge->add_key('cache_id', TRUE);
		$this->dbforge->create_table('cache_stats');

		$this->db->query("ALTER TABLE `cache_stats` ADD UNIQUE KEY (url(100))");
	}

	public function down()
	{
		$this->dbforge->drop_table('cache_stats', TRUE);
	}
}
