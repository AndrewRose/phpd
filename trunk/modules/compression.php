<?php

class Phpd_Module_Compression implements Phpd_Module
{
	public function init(Phpd_Child $o)
	{
	}

	public function request(Phpd_Child $o)
	{
		// need to check client can handle gzip compression..
		$o->header('Content-Encoding: deflate');
		$o->data = gzdeflate($o->data);
	}

	public function cleanup(Phpd_Child $o)
	{
	}

	public function deinit(Phpd_Child $o)
	{
	}
}
