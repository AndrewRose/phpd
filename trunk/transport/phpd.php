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
phpd_options_set([associative array]);
phpd_accept();
phpd_read([length]);
phpd_write([buffer], [length])
phpd_close(); // close a client socket / ssl connection
phpd_shutdown(); // shutdown server socket
*/

class Phpd_Transport_Phpd implements Phpd_Transport
{
	public $phpd;

	public function init()
	{
		if(!$this->phpd->reg->true('_phpd.ssl.on'))
		{
			$this->phpd->log->write('The phpd transport only handles HTTPS!');
			return FALSE;
		}

		$options = array(
		'allow_self_signed' => true,
		'verify_peer' => true,
		'local_cert' => $this->phpd->reg->get('_phpd.ssl.local_cert'),
		'passphrase' => $this->phpd->reg->get('_phpd.ssl.passphrase'),
		'blocking' => TRUE
		);

		phpd_set($this->phpd->reg->get('_phpd.ssl'));

		phpd_server($this->phpd->reg->get('_phpd.address'), $this->phpd->reg->get('_phpd.ssl.port'));
		// phpd_set(array('blocking' => TRUE));
		return TRUE;
	}

	public function request()
	{
		if(phpd_accept())
		{
			$this->phpd->request = phpd_read($this->phpd->reg->get('_phpd.requestLimit'));
		}
		else
		{
			return FALSE;
		}

		return TRUE;
	}

	public function response()
	{
		if(!phpd_write($this->phpd->response))
		{
			$this->phpd->log->write('Failed to write response to socket.  This is most likely a problem neogotiating an IE connection in SSL mode.');
		}
		phpd_close();
	}


	public function deinit($parent=FALSE)
	{
		if($parent)
		{
			for($i = $this->phpd->reg->get('_phpd.preFork'); $i; $i--)
			{
				@fsockopen($this->phpd->reg->get('_phpd.address'), $this->phpd->reg->get('_phpd.port'), $errno, $errstr, 2);
			}
			phpd_shutdown();
		}
	}
}
