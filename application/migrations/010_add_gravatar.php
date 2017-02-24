<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_gravatar extends CI_Migration {
	public function up()
	{
		$this->dbforge->add_column('users', 
			array( 'gravatar' => array( 'type' => 'BOOL' )	)
		);
	}

	public function down()
	{
		$this->dbforge->drop_column('users', 'gravatar');
	}
}