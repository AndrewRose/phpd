<?php

$response = "HTTP/1.1 200 OK\r\nServer: phpd/1.0\r\nContent-Type: text/html\r\nContent-Length: 12\r\nConnection: close\r\n\r\nHello World!\n\0";
phpd_set(array('local_cert' => 'server.pem', 'passphrase' => 'test'));
phpd_server('0.0.0.0', 443);
while(1)
{
	phpd_accept();
phpd_sendfile("data");
	//echo phpd_read(1024)."\n";
	//phpd_write($response);
	phpd_close();
}
phpd_shutdown();
