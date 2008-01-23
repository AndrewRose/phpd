<?php

class Phpd_Module_Session implements Phpd_Module
{
	public function init(Phpd_Child $o)
	{
	}

	public function request(Phpd_Child $o)
	{
		/* this block should be in init.. but theres some wierd bug that causes it to fail */
		if($o->reg->exists('session.database') && $o->reg->exists('database.'.$o->reg->get('session.database')))
		{
			$session = new Aplc_Session_Db;
			$session->init($o->reg->getReference('database.'.$o->reg->get('session.database')));
			$session->setHandlers();
		}
		/* */

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

	public function cleanup(Phpd_Child $o)
	{
		session_write_close();
		unset($_SESSION);
	}

	public function deinit(Phpd_Child $o)
	{
	}
}
