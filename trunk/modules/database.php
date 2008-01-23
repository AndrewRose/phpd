<?php

/* this module simply sets up any database connections specified in the ini */

class Phpd_Module_Database implements Phpd_Module
{
	public function init(Phpd_Child $o)
	{
		if($o->reg->exists('database'))
		{
			foreach($o->reg->get('database') as $database => $discard)
			{
				$db = new Aplc_Db;
				$db->init($o->reg->getReference('database.'.$database));
				$o->reg->set('database.'.$database.'.instance', $db);
			}
		}
	}

	public function request(Phpd_Child $o)
	{
	}

	public function cleanup(Phpd_Child $o)
	{
	}

	public function deinit(Phpd_Child $o)
	{
		if($o->reg->exists('database'))
		{
			foreach($o->reg->get('database') as $database => $discard)
			{
				if($o->reg->exists('database.'.$database.'.instance'))
				{
					$o->reg->get('database.'.$database.'.instance')->close();
				}
			}
		}
	}
}
