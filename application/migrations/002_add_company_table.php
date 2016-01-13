<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_company_table extends CI_Migration {

	public function up()
	{
		$this->dbforge->drop_table('companies', TRUE);

		$this->dbforge->add_field(array(
			"company_id" => array(
				'type' => 'int',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			"name" => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => FALSE
			),
			"ga_token" => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE
			)
		));

		$this->dbforge->add_key('company_id', TRUE);

		$this->dbforge->create_table('companies');
		
		$data = array(
			'Name' => 'Default Admin Company'
		);
		$this->db->insert('companies', $data);
	}

	public function down()
	{
		$this->dbforge->drop_table('companies', TRUE);
	}
}

