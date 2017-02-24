<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_post_hash_to_posts extends CI_Migration {
	public function up()
	{
		$this->db->query("ALTER TABLE `posts` DROP KEY `url`");

		//$this->dbforge->add_column('posts',
		//	array( 'hash' => array( 'type' => 'VARCHAR', 'constraint' => '32' )	)
		//);
		//$this->db->query('UPDATE posts SET hash = MD5(url)');
		//$this->db->query("ALTER TABLE `posts` ADD UNIQUE KEY (`hash`)");
	}

	public function down()
	{
		$this->dbforge->drop_column('posts', 'hash');
	}
}