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

class Phpd_Transport_Stream implements Phpd_Transport
{
	public $phpd;
	private $socket;
	private $client;

	public function init()
	{
		$context = stream_context_create();

		if($this->phpd->reg->true('_phpd.ssl.on'))
		{
			stream_context_set_option($context, 'ssl', 'local_cert',$this->phpd->reg->get('_phpd.ssl.local_cert'));
			stream_context_set_option($context, 'ssl', 'passphrase', $this->phpd->reg->get('_phpd.ssl.passphrase'));

			if($this->phpd->reg->true('_phpd.ssl.allow_self_signed'))
			{
				stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
			}
			if($this->phpd->reg->true('_phpd.ssl.verify_peer'))
			{
				stream_context_set_option($context, 'ssl', 'verify_peer', true);
			}

			$this->phpd->reg->set('_phpd.port', $this->phpd->reg->getOrSet('_phpd.ssl.port', '_phpd.port')); 
		}

		if(!($this->socket = stream_socket_server('tcp://'.$this->phpd->reg->get('_phpd.address').':'.$this->phpd->reg->get('_phpd.port'), $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context)))
		{
			return FALSE;
		}

		stream_set_blocking($this->socket, 1);

		return TRUE;
	}

	public function request()
	{
		if(($this->client = @stream_socket_accept($this->socket)) !== FALSE)
		{
			if($this->phpd->reg->true('_phpd.ssl.on'))
			{
				if(stream_socket_enable_crypto($this->client, TRUE, STREAM_CRYPTO_METHOD_SSLv23_SERVER) === FALSE)
				{
					return FALSE;
				}
			}
			$this->phpd->request = fread($this->client, $this->phpd->reg->get('_phpd.requestLimit'));
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	public function response()
	{
		if(fwrite($this->client, $this->phpd->response) == FALSE)
		{
			$this->phpd->log->write('Failed to write response to socket.  This is most likely a problem neogotiating an IE connection in SSL mode.');
		}

		stream_socket_shutdown($this->client, STREAM_SHUT_RDWR);
	}

	public function deinit($parent=FALSE)
	{
		if(!$parent)
		{
			stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
		}
		else
		{
			for($i = $this->phpd->reg->get('_phpd.preFork'); $i; $i--)
			{
				@fsockopen($this->phpd->reg->get('_phpd.address'), $this->phpd->reg->get('_phpd.port'), $errno, $errstr, 2);
			}
		}
	}
}
