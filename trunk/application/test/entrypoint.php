<?php

class Phpd_Application_Test implements Phpd_Application
{
	public $phpd;
	public $counter = 0;
	public $db;

        public function init()
	{
		$this->db = $this->phpd->reg->get('_phpd.module.Database.local.instance', TRUE);
// create new database instances here don't change the local instance.
	}

	public function request()
	{

	}

        public function response()
	{
                $this->counter++;
                if(!isset($_SESSION['somevar'])) $_SESSION['somevar'] = rand(0, 1000000);
                $ret = "Hello ".session_id()." from child: ".$this->phpd->pid.", the time and date is: ".date('d/m/Y H:i:s')." and I've been called: ".$this->counter." times.  Your random number is: ".$_SESSION['somevar']."\n";

		//usleep(rand(0, 1000000));

		//$result = $this->db->query("select count(*) as count from iris_students");
		//$row = $result->row();
		//$ret .= 'And the count in iris_students is: '.$row['count'];

		$this->phpd->data = $ret;
		return TRUE;
	}

        public function cleanup()
	{

	}

        public function deinit()
	{
	}
}
