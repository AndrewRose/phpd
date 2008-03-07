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

At some point in the future I will put together a PECL package (phpd) to handle the (ssl) sockets.
I don't see why simple file resource requests can't be threaded here also..


phpd_server([ip], [port]);
phpd_options_set([socket], [associative array]);
phpd_blocking_set([socket], [true/false]);
phpd_accept([socket]);
phpd_read([socket], [length]);
phpd_write([socket], [buffer], [length])
phpd_close([socket]);
*/

class Phpd_Transport_Stream implements Phpd_Transport
{
	private $socket;
	private $client;

	public function init(Phpd $o)
	{
		$options = array(
		'allow_self_signed' => true,
		'verify_peer' => true,
		'local_cert' => $o->reg->get('_phpd.ssl.local_cert'),
		'passphrase' => $o->reg->get('_phpd.ssl.passphrase')
		);

		$this->socket = phpd_server($o->reg->get('_phpd.address'), $o->reg->get('_phpd.port'));
		phpd_options_set($this->socket, $this->reg->get('_phpd.ssl'));
		phpd_blocking_set($this->socket, TRUE);

		return TRUE;
	}

	public function request(Phpd_Child $o)
	{
		if(($this->client = phpd_accept($this->socket))
		{
			$o->request =  phpd_read($this->client, $o->reg->get('_phpd.requestLimit'));
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	public function response(Phpd_Child $o)
	{
		if(!phpd_write($this->client, $o->response))
		{
			$o->log->write('Failed to write response to socket.  This is most likely a problem neogotiating an IE connection in SSL mode.');
		}
		phpd_socket_close($this->client);
	}

	public function deinit(Phpd $o)
	{
		phpd_socket_slose($this->socket);
	}
}
