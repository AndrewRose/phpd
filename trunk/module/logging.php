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

class Phpd_Module_Logging implements Phpd_Module
{
	public $phpd;
	
	public function init()
	{
		if($this->phpd->reg->exists('_phpd.module.Logging'))
		{
			$this->log = new Aplc_Log;
			if(!$this->log->init($this->phpd->reg->getReference('_phpd.module.Logging')))
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	public function request()
	{
/*
example of Apache log entry:
212.219.118.20 - - [09/May/2008:15:02:22 +0100] "GET /sys/js/lightbox2/js/effects.js HTTP/1.0" 200 31969 "https://portal.iris.ac/index.php?___sys_op___[]=link&___link___=144" "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; 
SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)"
*/
                $this->log->write(date('d/m/Y'));
		return TRUE;
	}

	public function response()
	{
		return TRUE;
	}

	public function cleanup()
	{
		return TRUE;
	}

	public function deinit()
	{
		return TRUE;
	}
}
