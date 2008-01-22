<?php

class Phpd_Module_Session implements Phpd_Module
{
	public function init(Phpd_Child $o)
	{
		if(!$o->reg->exists('session.name'))
		{
			$o->reg->set('session.name', 'PHPDSESSID');
		}

		$sessionId = $o->reg->get('session.name');
		if(isset($_COOKIE[$sessionId]))
		{
			session_id($_COOKIE[$sessionId]);
		}
		else
		{
			$id = md5(rand(1, 1000000));
			$o->header('Set-Cookie: '.$sessionId.'='.$id);
			session_id($id);
		}
		session_start();
	}

	public function deinit(Phpd_Child $o)
	{
		session_write_close();
	}
}
