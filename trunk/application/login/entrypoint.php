<?php

class Phpd_Application_Login implements Phpd_Application
{
	public $phpd;
	public $array = array();

        public function init()
	{
		$this->array = array();
	}

	public function request()
	{
		return TRUE;
	}

        public function response()
	{
		if(!$this->phpd->reg->exists('system.user'))
		{
			$this->phpd->data = file_get_contents('test.html');
		}

		print_r($_POST);

		return TRUE;
	}

        public function cleanup()
	{
		unset($this->array);
	}

        public function deinit()
	{
	}
}
