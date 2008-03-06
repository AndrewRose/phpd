<?php
/* 
  phpd Copyright (C) 2007, 2008 Andrew Rose
  rose.andrew@gmail.com
  http://andrewrose.co.uk
  http://andrew-rose.blogspot.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
PHP bug #32330 means we have to reset the handles on every request.  However we only have to create the handler object once in init and reset the handlers in request.
*/

class Phpd_Module_Session implements Phpd_Module
{
	private $session;
	private $sessionId = 'PHPDSESSID';

	public function init(Phpd_Child $o)
	{
		if(!$o->reg->exists('_phpd.module.Session.database'))
		{
			throw new Aplc_Exception_Session('Unable to start session module due to incorrect or missing registry entries');
		}

		$this->session = new Aplc_Session_Db;

		if($o->reg->exists('_phpd.module.Session.name'))
		{
			$this->sessionId = $o->reg->get('_phpd.module.Session.name');
		}

		$this->session->init($o->reg->getReference('_phpd.module.Session.database'));
		return TRUE;
	}

	public function request(Phpd_Child $o)
	{
		$this->session->setHandlers();

		if(isset($_COOKIE[$this->sessionId]))
		{
			session_id($_COOKIE[$this->sessionId]);
		}
		else
		{
			if(($id = $this->session->genId()))
			{
				$o->header('Set-Cookie: '.$this->sessionId.'='.$id);
				session_id($id);
			}
			else
			{
				return FALSE;
			}
		}

		session_start();
		return TRUE;
	}

	public function response(Phpd_Child $o)
	{
		return TRUE;
	}

	public function cleanup(Phpd_Child $o)
	{
		session_write_close();
		//session_destroy();
		unset($_SESSION);
		return TRUE;
	}

	public function deinit(Phpd_Child $o)
	{
		return TRUE;
	}
}
