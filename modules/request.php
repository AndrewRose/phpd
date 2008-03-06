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

class Phpd_Module_Request implements Phpd_Module
{
	public function init(Phpd_Child $o)
	{
		return TRUE;
	}

	public function request(Phpd_Child $o)
	{
		if(!$this->dissolveRequest($o->request, $o))
		{
			return FALSE;
		}
		return TRUE;
	}

	public function response(Phpd_Child $o)
	{
		return TRUE;
	}

	public function cleanup(Phpd_Child $o)
	{
		$o->request = FALSE; 
		return TRUE;
	}

	public function deinit(Phpd_Child $o)
	{
		return TRUE;
	}

	/* http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html */
	public function dissolveRequest($request, Phpd_Child $o)
	{
		$_SERVER = array();
                $_COOKIE = array();
		$_POST = array();
		$_GET = array();

		$request = trim($request);
		$headers = explode("\r\n", $request);

		$headers = array_reverse($headers);
		$request = array_pop($headers);
		$parts = explode(' ', $request);

		if( !isset($parts[2]) || (($parts[2] != 'HTTP/1.1') && ($parts[2] != 'HTTP/1.0')) )
		{
			$o->status = 505;
			return FALSE;
		}

		switch($parts[0])
		{
			case 'GET':
			{
				$method = 'GET';
				$_SERVER['REQUEST_URI'] = $parts[1];
			}
			break;
			case 'POST':
			{
				$method = 'POST';
			}
			break;
			default:
				$o->status = 501;
				return FALSE;
			break;
		}

		$uri = $parts[1];
		$version = $parts[2];

                foreach($headers as $header)
                {
                        list($header, $value) = explode(':', $header);
                        switch(trim($header))
			{
				case 'Cookie':
				{
					$vars = explode(';', $value);
					foreach($vars as $var)
					{
						list($k, $v) = explode('=', $var);
						$_COOKIE[trim($k)] = trim($v);
					}
				}
				break;
			}
		}
		return TRUE;
	}

}
