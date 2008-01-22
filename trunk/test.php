<?php

class test
{
	public static $counter = 0;

	public function main($parent)
	{
		$this->counter++;
		if(!isset($_SESSION['somevar'])) $_SESSION['somevar'] = rand(0, 1000000);
		$ret = "Hello ".session_id()." from child: ".$parent->pid.", the time and date is: ".date('d/m/Y H:i:s')." and I've been called: ".$this->counter." times.  Your random number is: ".$_SESSION['somevar']."\n";
		return $ret;
	}
}
