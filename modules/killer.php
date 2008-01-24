<?php

class Phpd_Module_Killer implements Phpd_Module
{
	private $calls;
	private $logPointless = FALSE;

	public function init(Phpd_Child $o)
	{
		$this->calls = 0;
	}

	public function request(Phpd_Child $o)
	{
		if($o->reg->exists('_phpd.module.Killer.requests'))
		{
			if($this->calls >= $o->reg->get('_phpd.module.Killer.requests'))
			{
				$o->shutdown = TRUE;
			}
			else
			{
				$this->calls++;
			}
		}
		else if(!$this->logPointless)
		{
			$o->log->write("You have the killer module loaded but do not have '_phpd.module.Killer.requests' set.  This means this module is wasting CPU time."); 
			$this->logPointless = TRUE;
		}
	}

	public function cleanup(Phpd_Child $o)
	{
	}

	public function deinit(Phpd_Child $o)
	{
	}
}
