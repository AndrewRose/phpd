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

interface Phpd_Application
{
        public function init(Phpd_Child $o);
	public function request(Phpd_Child $o);
        public function response(Phpd_Child $o);
        public function cleanup(Phpd_Child $o);
        public function deinit(Phpd_Child $o);
}

class Phpd_Module_Response implements Phpd_Module
{
	private $application=FALSE;

	public function init(Phpd_Child $o)
	{
		if($o->reg->exists('_phpd.module.Response.applicationRoot,_phpd.module.Response.defaultApplication'))
		{
			$application = $o->reg->get('_phpd.module.Response.defaultApplication');
			$entryPoint = $o->reg->get('_phpd.module.Response.applicationRoot').$application.'/entrypoint.php';
			if(is_file($entryPoint))
			{
				require_once($entryPoint);
				$this->application = new $application;
				if($this->application instanceof Phpd_Application)
				{
					$this->application->init($o);
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	public function request(Phpd_Child $o)
	{
		if($this->application)
		{
			$this->application->request($o);
			return TRUE;
		}
		return FALSE;
	}

	public function response(Phpd_Child $o)
	{
		if($this->application)
		{
			$this->application->response($o);
			return TRUE;
		}
		return FALSE;
	}

	public function cleanup(Phpd_Child $o)
	{
		if($this->application)
		{
			$this->application->cleanup($o);
			return TRUE;
		}
		return FALSE;
	}

	public function deinit(Phpd_Child $o)
	{
		if($this->application)
		{
			$this->application->deinit($o);
			unset($this->application);
			return TRUE;
		}
		return FALSE;
	}
}
