<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_domain_to_company extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_column('companies', array(
			'domain' => array(
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => FALSE
			))
		);
	}

	public function down()
	{
	}
}
