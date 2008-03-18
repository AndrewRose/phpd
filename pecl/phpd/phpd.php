<?php

phpd_server();
while(1)
{
	phpd_accept();
	phpd_read();
	phpd_write();
}
phpd_close();
