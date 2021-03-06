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
	public $phpd;
	private $dbs = array();

	public function init()
	{
		if($this->phpd->reg->exists('_phpd.module.Database'))
		{
			foreach($this->phpd->reg->get('_phpd.module.Database') as $database => $discard)
			{
				$db = new Aplc_Db;
				$db->log = $this->phpd->log;

				if(!$db->init($this->phpd->reg->getReference('_phpd.module.Database.'.$database)))
				{
					$this->phpd->log->write('Unable to start database connection: '.$database);
					return FALSE;
				}

				$this->dbs[] = $db; // save reference to making deinits life easier
				$this->phpd->reg->set('_phpd.module.Database.'.$database.'.instance', $db);
			}
		}
		else
		{
			// log wasted cpu time
		}

		return TRUE;
	}

	public function request()
	{
		foreach($this->dbs as $db)
		{
			$db->ping();
		}
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
		foreach($this->dbs as $db)
		{
			$db->close();
		}
		return TRUE;
	}
}
