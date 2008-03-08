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

class Phpd_Module_Response implements Phpd_Module
{
	public $phpd;
	private $application=FALSE;

	public function init()
	{
		if($this->phpd->reg->exists('_phpd.module.Response.applicationRoot,_phpd.module.Response.defaultApplication'))
		{
			$application = $this->phpd->reg->get('_phpd.module.Response.defaultApplication');
			$entryPoint = $this->phpd->reg->get('_phpd.module.Response.applicationRoot').$application.'/entrypoint.php';
			if(is_file($entryPoint))
			{
				require_once($entryPoint);
				$this->application = new $application;
				$this->application->phpd = $this->phpd;

				if($this->application instanceof Phpd_Application)
				{
					$this->application->init($this->phpd); return TRUE;
				}
				else
				{
					$this->phpd->log->write("Failed to start application as it is not an instance of Phpd_Application!");
				}
			}
			else
			{
				$this->phpd->log->write("Failed to start application as I could not find the entrypoint: ".$entrypoint);
			}
		}

		return TRUE;
	}

	public function request()
	{
		if($this->application)
		{
			$this->application->request($this->phpd);
		}
		return TRUE;
	}

	public function response()
	{
		if($this->application)
		{
			$this->application->response($this->phpd);
		}
		return TRUE;
	}

	public function cleanup()
	{
		if($this->application)
		{
			$this->application->cleanup($this->phpd);
		}
		return TRUE;
	}

	public function deinit()
	{
		if($this->application)
		{
			$this->application->deinit($this->phpd);
			unset($this->application);
		}
		return TRUE;
	}
}
