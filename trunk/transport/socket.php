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

class Phpd_Transport_Socket implements Phpd_Transport
{
	private $socket;
	private $client;

	public function init(Phpd $o)
	{

		if(!($this->socket = @socket_create(AF_INET, SOCK_STREAM, 0)))
		{
			exit("Failed to create socket!\n");
		}

		if(!@socket_bind($this->socket, $o->reg->get('_phpd.address'), $o->reg->get('_phpd.port')))
		{
			$error = socket_last_error($this->socket);
			$error = socket_strerror($error);
			exit('Unable to bind to socket: '.$error."\n");
		}

		if(!@socket_listen($this->socket, $o->reg->get('_phpd.backLog')))
		{
			exit("Unable to listen on socket!\n");
		}

		socket_set_block($this->socket);

		return TRUE;
	}

	public function request(Phpd_Child $o)
	{
		if(($this->client = @socket_accept($this->socket)) !== FALSE)
		{
			$o->request = @socket_read($this->client, $o->reg->get('_phpd.requestLimit'));
			socket_shutdown($this->client, 0);
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	public function response(Phpd_Child $o)
	{
		if(socket_write($this->client, $o->response, strlen($o->response)) != strlen($o->response))
		{
			echo "Failed to write response...\n";
		}
		//socket_shutdown($this->client, 1);
		socket_close($this->client);
	}

	public function deinit(Phpd $o)
	{
		socket_close($this->socket);
	}
}
