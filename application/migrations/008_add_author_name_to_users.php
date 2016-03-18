<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_author_name_to_users extends CI_Migration {
	public function up()
	{
		$this->dbforge->modify_column('users',
			array( 'phone' => array( 'name' => 'position', 'type' => 'VARCHAR', 'constraint' => '30' ) ) 
		);

		$this->dbforge->add_column('users', 
			array( 'author_name' => array( 'type' => 'VARCHAR', 'constraint' => '200' )	)
		);
	}

	public function down()
	{
	}
}