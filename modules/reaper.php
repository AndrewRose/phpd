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

class Phpd_Module_Reaper implements Phpd_Module
{
	public $phpd;
	private $calls = 0;
	private $logPointless = FALSE;

	public function init()
	{
		$this->phpd->reg->set('_phpd.module.Reaper.calls', 0);
		return TRUE;
	}

	public function request()
	{
		set_time_limit($this->phpd->reg->get('_phpd.module.Reaper.timeout'));
		return TRUE;
	}

	public function response()
	{
		return TRUE;
	}

	public function cleanup()
	{
		set_time_limit(0);

		if($this->phpd->reg->exists('_phpd.module.Reaper.requests'))
		{
			if($this->calls >= $this->phpd->reg->get('_phpd.module.Reaper.requests'))
			{
				$this->phpd->shutdown = TRUE;
				$this->phpd->reg->inc('_phpd.module.Reaper.calls');
			}
			else
			{
				$this->calls++;
				$this->phpd->reg->inc('_phpd.module.Reaper.calls');
			}
		}

		return TRUE;
	}

	public function deinit()
	{
		return TRUE;
	}
}
