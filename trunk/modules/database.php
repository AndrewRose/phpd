<?php

/* this module simply sets up any database connections specified in the ini */

class Phpd_Module_Database implements Phpd_Module
{
	private $dbs = array();

	public function init(Phpd_Child $o)
	{
		if($o->reg->exists('_phpd.module.Database'))
		{
			foreach($o->reg->get('_phpd.module.Database') as $database => $discard)
			{
				$db = new Aplc_Db;
				$db->init($o->reg->getReference('_phpd.module.Database.'.$database));
				$this->dbs[] = $db; // save reference to making deinits life easier
				$o->reg->set('_phpd.module.Database.'.$database.'.instance', $db);
			}
		}
		else
		{
			// log wasted cpu time
		}
	}

	public function request(Phpd_Child $o)
	{
	}

	public function response(Phpd_Child $o)
	{
	}

	public function cleanup(Phpd_Child $o)
	{
	}

	public function deinit(Phpd_Child $o)
	{
		foreach($this->dbs as $db)
		{
			$db->close();
		}
	}
}
