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

class Phpd_Module_Killer implements Phpd_Module
{
	private $calls = 0;
	private $logPointless = FALSE;

	public function init(Phpd_Child $o)
	{
	}

	public function request(Phpd_Child $o)
	{
	}

	public function response(Phpd_Child $o)
	{
	}

	public function cleanup(Phpd_Child $o)
	{
		if($o->reg->exists('_phpd.module.Killer.requests'))
		{
			if($this->calls >= $o->reg->get('_phpd.module.Killer.requests'))
			{
				$o->shutdown = TRUE;
			}
			else
			{
				$this->calls++;
			}
		}
		else if(!$this->logPointless)
		{
			$o->log->write("You have the killer module loaded but do not have '_phpd.module.Killer.requests' set.  This means this module is wasting CPU time."); 
			$this->logPointless = TRUE;
		}
	}

	public function deinit(Phpd_Child $o)
	{
	}
}
