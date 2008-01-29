<?php

class Phpd_Module_Request implements Phpd_Module
{
	public function init(Phpd_Child $o)
	{
	}

	public function request(Phpd_Child $o)
	{
		$this->dissolveRequest($o->request);
	}

	public function response(Phpd_Child $o)
	{
	}

	public function cleanup(Phpd_Child $o)
	{
		$o->request = FALSE; 
	}

	public function deinit(Phpd_Child $o)
	{
	}

        /* http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html */
        public function dissolveRequest($request)
        {
                $_COOKIE = array();
		$_POST = array();
		$_GET = array();

                $request = trim($request);
                $headers = explode("\r\n", $request);

                $headers = array_reverse($headers);
                $request = array_pop($headers);
                $parts = explode(' ', $request);

                switch($parts[0])
                {
                        case 'GET':
                                $method = 'GET';
                        break;
                        case 'POST':
                                $method = 'POST';
                        break;
                        default:
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
        }

}
