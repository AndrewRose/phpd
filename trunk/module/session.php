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
	public $phpd;
	private $session;
	private $sessionId = 'PHPDSESSID';

	public function init()
	{
		if(!$this->phpd->reg->exists('_phpd.module.Session.database'))
		{
			throw new Aplc_Exception_Session('Unable to start session module due to incorrect or missing registry entries');
		}

		$this->session = new Aplc_Session_Db;

		if($this->phpd->reg->exists('_phpd.module.Session.name'))
		{
			$this->sessionId = $this->phpd->reg->get('_phpd.module.Session.name');
		}

		$this->session->init($this->phpd->reg->getReference('_phpd.module.Session.database'));

		$this->phpd->reg->drop('_phpd.user');

		return TRUE;
	}

	public function request()
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
				$this->phpd->header('Set-Cookie: '.$this->sessionId.'='.$id);
				session_id($id);
			}
			else
			{
				return FALSE;
			}
		}

		@session_start();

		// setup the user object for the authentication module which will be called next.
		if(isset($_SESSION['_phpd']['user']))
		{
			$this->phpd->reg->import('_phpd.user', $_SESSION['_phpd']['user']);
		}
		else
		{
			$this->phpd->reg->set('_phpd.user', FALSE);
		}

		return TRUE;
	}

	public function response()
	{
		return TRUE;
	}

	public function cleanup()
	{
		$_SESSION['_phpd']['user'] = $this->phpd->reg->dump('_phpd.user');
		$this->phpd->reg->set('_phpd.user', FALSE);
		session_write_close();
		return TRUE;
	}

	public function deinit()
	{
		return TRUE;
	}
}
