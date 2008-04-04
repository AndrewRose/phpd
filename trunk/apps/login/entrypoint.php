<?php

class login implements Phpd_Application
{
	public $phpd;

        public function init()
	{

	}

	public function request()
	{
		if(isset($_GET['username']) && isset($_GET['password']))
		{

		}
		else
		{
			
		}		
	}

        public function response()
	{
		if(!$this->phpd->reg->exists('system.user'))
		{
			$this->phpd->data = '<html><head></head><body><form method="get"><input type="text" name="username" /> <input type="password" name="password" /></form></body></html>';
		}
		return TRUE;
	}

        public function cleanup()
	{
	}

        public function deinit()
	{
	}
}
