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
create table core_profile(
 id int primary key auto_increment,
 uri text,
 time_start int,
 time_end int,
 time_seconds int,
 time_useconds float,
 memory_start int,
 memory_end int,
 memory_total int,
 memory_peak int
);
*/

class Phpd_Module_Profiler implements Phpd_Module
{
	public $phpd;
	private $db;	
	private $time_start, $time_end, $time_seconds, $time_useconds;
	private $memory_start, $memory_end, $memory_total, $memory_peak;

        public function init()
        {
		if(!strstr($this->phpd->reg->get('_phpd.moduleLoadOrder'), 'Gc') || !function_exists('gc_enable'))
		{
			$this->phpd->log->write("The Gc module is not loaded or this version of PHP does not have the GC patch.  The profiler memory usage stats will be unreliable.");
		}

		if($this->phpd->reg->exists('_phpd.module.Profiler.database.instance'))
		{
			$this->db = $this->phpd->reg->get('_phpd.module.Profiler.database.instance');
		}
		else
		{
			$this->phpd->log->write('Unable to start profiler as no database instance passed.');
			return FALSE;
		}
		
		return TRUE;
        }

        public function request()
        {
		$this->time_start = $this->microtime_float();
		$this->memory_start = memory_get_usage();
		return TRUE;
        }

	public function response()
	{
		return TRUE;
	}

        public function cleanup()
        {
		$this->memory_end = memory_get_usage();
		$this->memory_peak = memory_get_peak_usage();
		$this->memory_total = $this->memory_end - $this->memory_start;

		$this->time_end = $this->microtime_float();
		$this->time_seconds = $this->time_end - $this->time_start;
		$this->time_useconds = $this->time_end - $this->time_start;

		$query = "insert into core_profile(
				time_start,
				time_end,
				time_seconds,
				time_useconds,
				memory_start,
				memory_end,
				memory_total,
				memory_peak
			) values (
				'".$this->time_start."',
				'".$this->time_end."',
				'".$this->time_seconds."',
				'".$this->time_useconds."',
				'".$this->memory_start."',
				'".$this->memory_end."',
				'".$this->memory_total."',
				'".$this->memory_peak."'
			);";

		$this->db->query($query);

		return TRUE;
        }

        public function deinit()
        {
		return TRUE;
        }

	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}
