<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_cache_table extends CI_Migration
{
	public function up()
	{
		$this->dbforge->rename_table('cache', 'post_cache');

		$this->dbforge->drop_table('cache_stats');
	}

	public function down()
	{
	}
}
