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

class Phpd_Module_Gc implements Phpd_Module
{
	public $phpd;
	public $enabled = FALSE;

        public function init()
        {
		/* gc check from: http://code.google.com/p/appserver-in-php/ */
		if(false === function_exists('gc_enabled'))
		{
			echo "WARNING: This version of PHP is compiled without GC-support!  Be aware of possible memory leaks.\n";
			$this->enabled = FALSE;
		}
		else if (gc_enabled() === false)
		{
			$this->enabled = TRUE;
			gc_enable();
		}

		return TRUE;
        }

        public function request()
        {
		return TRUE;
        }

	public function response()
	{
		return TRUE;
	}

        public function cleanup()
        {
		if($this->enabled)
		{
			gc_collect_cycles();
		}
		return TRUE;
        }

        public function deinit()
        {
		return TRUE;
        }
}
