<?php

class test implements Phpd_Application
{
	public $counter = 0;
	public $db;

        public function init(Phpd_Child $o)
	{
		$this->db = $o->reg->get('_phpd.module.Database.local.instance', TRUE);
		$this->db->database('iris');
	}

	public function request(Phpd_Child $o)
	{

	}

        public function response(Phpd_Child $o)
	{
                $this->counter++;
                if(!isset($_SESSION['somevar'])) $_SESSION['somevar'] = rand(0, 1000000);
                $ret = "Hello ".session_id()." from child: ".$o->pid.", the time and date is: ".date('d/m/Y H:i:s')." and I've been called: ".$this->counter." times.  Your random number is: ".$_SESSION['somevar']."\n";

		//usleep(rand(0, 1000000));

		//$result = $this->db->query("select count(*) as count from iris_students");
		//$row = $result->row();
		//$ret .= 'And the count in iris_students is: '.$row['count'];

		$o->data = $ret;
	}

        public function cleanup(Phpd_Child $o)
	{

	}

        public function deinit(Phpd_Child $o)
	{
	}
}
