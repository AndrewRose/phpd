<?php

class Phpd_Application_Login implements Phpd_Application
{
	public $phpd;
	public $array = array();

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
			ob_start();
			print_r($this->phpd->reg->dump());
			$tmp = ob_get_contents();
			ob_end_clean();
			$this->phpd->data = "+<pre>".$tmp."</pre>";

			$this->array = array();
			$i = rand(1, 100000);
			echo "+++".$i."+++\n";
			for(; $i; $i--)
			{
				$this->array[$i] = 1;
			}

			//$this->phpd->data = '<html><head></head><body><form method="get"><input type="text" name="username" /> <input type="password" name="password" /></form></body></html>';
		}
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
