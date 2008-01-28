<?php

class Phpd_Module_Session implements Phpd_Module
{
	private $session;

	public function init(Phpd_Child $o)
	{
		if(!$o->reg->exists('_phpd.module.Session.database') || !$o->reg->exists('_phpd.module.Database.'.$o->reg->get('_phpd.module.Session.database')))
		{
			throw new Aplc_Exception_Session('Unable to start session module to incorrect or missing registry entries');
		}
		$this->session = new Aplc_Session_Db;
		$this->session->init($o->reg->getReference('_phpd.module.Database.'.$o->reg->get('_phpd.module.Session.database')));
	}

	public function request(Phpd_Child $o)
	{
		$this->session->setHandlers();
		if(!$o->reg->exists('_phpd.module.Session.name'))
		{
			$o->reg->set('_phpd.module.Session.name', 'PHPDSESSID');
		}

		$sessionId = $o->reg->get('_phpd.module.Session.name');
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
		@session_start();
	}

	public function cleanup(Phpd_Child $o)
	{
		session_write_close();
		//session_destroy();
		unset($_SESSION);
	}

	public function deinit(Phpd_Child $o)
	{
	}
}
