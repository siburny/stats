<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_post_table extends CI_Migration {
	public function up()
	{
		$this->dbforge->add_field(array(
			"post_id" => array(
				'type' => 'int',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			"company_id" => array(
				'type' => 'INT',
				'null' => FALSE
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
			),
			'author' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => TRUE
			),
			'date_published' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			)
		));

		$this->dbforge->add_key('post_id', TRUE);
		$this->dbforge->create_table('posts');

		$this->db->query("ALTER TABLE `posts` ADD UNIQUE KEY (url(100))");
	}

	public function down()
	{
		$this->dbforge->drop_table('posts', TRUE);
	}
}

