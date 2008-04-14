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
        public function init();
	public function request();
        public function response();
        public function cleanup();
        public function deinit();
}

class Phpd_Module_Application implements Phpd_Module
{
	public $phpd;
	private $application = FALSE;
	private $applications = array();

	public function init()
	{
		if($this->phpd->reg->exists('_phpd.module.Application.load'))
		{
			$applications = explode(',',$this->phpd->reg->get('_phpd.module.Application.load'));
			foreach($applications as $application)
			{
				$class = 'Phpd_Application_'.$application;
				$this->applications[$application] = new $class;
				$this->applications[$application]->phpd = $this->phpd;
				$this->applications[$application]->init();
			}
		}

		return TRUE;
	}

	public function request()
	{
		if($this->phpd->reg->exists('_phpd.user.application'))
		{
			$this->application = $this->phpd->reg->get('_phpd.user.application');
		}
		else
		{
			$this->phpd->status = 500;
			return FALSE;
		}

		$this->applications[$this->application]->request();
		return TRUE;
	}

	public function response()
	{
		$this->applications[$this->application]->response();
		return TRUE;
	}

	public function cleanup()
	{
		$this->applications[$this->application]->cleanup();
		$this->application = FALSE;
		return TRUE;
	}

	public function deinit()
	{
		foreach($this->applications as $name => $instance)
		{
			$instance->deinit();
			unset($this->applications[$name]);
		}
		unset($this->applications);
		return TRUE;
	}
}
