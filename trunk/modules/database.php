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

/* this module simply sets up any database connections specified in the ini: _phpd.module.Database */

class Phpd_Module_Database implements Phpd_Module
{
	private $dbs = array();

	public function init(Phpd_Child $o)
	{
		if($o->reg->exists('_phpd.module.Database'))
		{
			foreach($o->reg->get('_phpd.module.Database') as $database => $discard)
			{
				$db = new Aplc_Db;
				$db->init($o->reg->getReference('_phpd.module.Database.'.$database));
				$this->dbs[] = $db; // save reference to making deinits life easier
				$o->reg->set('_phpd.module.Database.'.$database.'.instance', $db);
			}
		}
		else
		{
			// log wasted cpu time
		}
	}

	public function request(Phpd_Child $o)
	{
	}

	public function response(Phpd_Child $o)
	{
	}

	public function cleanup(Phpd_Child $o)
	{
	}

	public function deinit(Phpd_Child $o)
	{
		foreach($this->dbs as $db)
		{
			$db->close();
		}
	}
}
