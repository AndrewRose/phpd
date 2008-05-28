<?php

class Phpd_Transport_Phpd implements Phpd_Transport
{
	public $reg;

	public function init(Aplc_Registry $reg)
	{
		$this->reg = $reg;

		if(!function_exists('phpd_transport_server'))
		{
			return FALSE;
		}

		if(!$this->reg->true('ssl.on'))
		{
			$this->log->write('The Aplc transport only handles HTTPS!');
			return FALSE;
		}

		$options = array(
		'allow_self_signed' => true,
		'verify_peer' => true,
		'local_cert' => $this->reg->get('ssl.local_cert'),
		'passphrase' => $this->reg->get('ssl.passphrase'),
		'blocking' => TRUE
		);

		// remeber that local_cert and passphrase must be set before starting the server.
		phpd_transport_set($this->reg->get('ssl'));

		phpd_transport_server($this->reg->get('address'), $this->reg->get('ssl.port'), $this->reg->get('backLog'));
		// phpd_transport_set(array('blocking' => TRUE));
		return TRUE;
	}

	public function request()
	{
		if(phpd_transport_accept())
		{
			return TRUE;
		}

		if($error = phpd_transport_error())
		{
			$this->log->write($error);
		}

		return FALSE;
	}

	public function read(&$data)
	{
		if(!($data = phpd_transport_read()))
		{
			if($error = phpd_transport_error())
			{
				$this->log->write($error);
			}
			return FALSE;
		}

		return TRUE;
	}

	public function response(&$data)
	{
		if(!phpd_transport_write($data))
		{
			$this->log->write('Failed to write response to socket.  This is most likely a problem neogotiating an IE connection in SSL mode.');
		}
		return TRUE;
	}


	public function close()
	{
		phpd_transport_close();
	}

	public function deinit()
	{
		phpd_transport_shutdown();
	}
}
