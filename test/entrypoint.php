<?php

class test implements Phpd_Application
{
	public static $counter = 0;
	public $dbconn;

        public function init(Phpd_Child $o)
	{
		//$this->dbcon = mysql_connect('localhost', 'root', '');
		//mysql_select_db('iris', $this->dbcon);
	}

        public function request(Phpd_Child $o)
	{
                $this->counter++;
                if(!isset($_SESSION['somevar'])) $_SESSION['somevar'] = rand(0, 1000000);
                $ret = "Hello ".session_id()." from child: ".$o->pid.", the time and date is: ".date('d/m/Y H:i:s')." and I've been called: ".$this->counter." times.  Your random number is: ".$_SESSION['somevar']."\n";

		//$result = mysql_query("select count(*) from iris_students", $this->dbcon);
		//$row = mysql_fetch_array($result);
		//$ret .= 'And the count in iris_students is: '.$row[0];

                return $ret;
	}

        public function cleanup(Phpd_Child $o)
	{

	}

        public function deinit(Phpd_Child $o)
	{
		//mysql_close($this->dbcon);
	}
}
