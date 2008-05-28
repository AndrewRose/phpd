<?php

class Phpd_Transport_Socket implements Phpd_Transport
{
	public $reg;
	private $socket;
	private $client;
	public $log;

	public function init(Aplc_Registry $reg)
	{
		$this->reg = $reg;

		$error = FALSE;

		if(!function_exists('socket_create'))
		{
			$error = "socket module is not loaded";
		}

		if(!($this->socket = @socket_create(AF_INET, SOCK_STREAM, 0)))
		{
			$error = "Failed to create socket!";
		}

		if(!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1))
		{
			$error = socket_last_error($this->socket);
			$error = socket_strerror($error);
		}

		if(!@socket_bind($this->socket, $this->reg->get('address'), $this->reg->get('port')))
		{
			$error = socket_last_error($this->socket);
			$error = socket_strerror($error);
		}

		if(!@socket_listen($this->socket, $this->reg->get('backLog')))
		{
			$error = "Unable to listen on socket!";
		}

		if($error)
		{
			if($this->log)
			{
				$this->log->write($error);
			}

			echo $error."\n";
			return FALSE;
		}

		socket_set_block($this->socket);

		return TRUE;
	}

	public function request()
	{
		if(($this->client = @socket_accept($this->socket)) !== FALSE)
		{
			return TRUE;
		}
		return FALSE;
	}

	public function read(&$data)
	{
		if(!($data = socket_read($this->client, $this->reg->get('requestLimit'))))
		{
			return FALSE;
		}
		return TRUE;
	}

	public function response(&$data)
	{
		if(socket_write($this->client, $data, strlen($data)) === FALSE)
		{
			echo "Failed to write response...\n";
		}
		return TRUE;
	}

	public function close()
	{
		socket_close($this->client);
	}

	public function deinit()
	{
		socket_close($this->socket);
	}
}
