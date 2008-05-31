/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2007 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  +----------------------------------------------------------------------+
*/

/* $Id: header,v 1.16.2.1.2.1 2007/01/01 19:32:09 iliaa Exp $ */

#ifndef PHP_PHPD_TRANSPORT_H
#define PHP_PHPD_TRANSPORT_H

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/wait.h>
#include <signal.h>

#include <openssl/ssl.h>
#include <openssl/err.h>

#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <sys/mman.h>

extern zend_module_entry phpd_transport_module_entry;
#define phpext_phpd_transport_ptr &phpd_transport_module_entry

#ifdef PHP_WIN32
#define PHP_PHPD_TRANSPORT_API __declspec(dllexport)
#else
#define PHP_PHPD_TRANSPORT_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(phpd_transport);
PHP_MSHUTDOWN_FUNCTION(phpd_transport);
PHP_RINIT_FUNCTION(phpd_transport);
PHP_RSHUTDOWN_FUNCTION(phpd_transport);
PHP_MINFO_FUNCTION(phpd_transport);

PHP_FUNCTION(confirm_phpd_transport_compiled);	/* For testing, remove later. */
PHP_FUNCTION(phpd_transport_server);
PHP_FUNCTION(phpd_transport_set);
PHP_FUNCTION(phpd_transport_accept);
PHP_FUNCTION(phpd_transport_read);
PHP_FUNCTION(phpd_transport_write);
PHP_FUNCTION(phpd_transport_close);
PHP_FUNCTION(phpd_transport_shutdown);
PHP_FUNCTION(phpd_transport_error);
PHP_FUNCTION(phpd_transport_sendfile);

int 			phpd_transport_server_fd;
int			phpd_transport_client_fd;
int			phpd_transport_max_request = 1024000;
char			*phpd_transport_request_buffer = NULL;
int			phpd_transport_max_length_header = 1024;
struct sockaddr_in 	phpd_transport_my_addr;
struct sockaddr_in	phpd_transport_their_addr;
socklen_t 		phpd_transport_sin_size;
int 			phpd_transport_yes = 1;
int			phpd_transport_backlog = 1024;
static char		*phpd_transport_error_string;
static char 		*phpd_transport_passphrase = NULL;
static char 		*phpd_transport_certfile = NULL;
static SSL_CTX 		*phpd_transport_ssl_ctx;
static SSL		*phpd_transport_ssl;
BIO			*phpd_transport_io, *phpd_transport_ssl_bio;

//ZEND_BEGIN_MODULE_GLOBALS(phpd_transport)
//ZEND_END_MODULE_GLOBALS(phpd_transport)

int phpd_transport_pem_passwd_cb(char *buf, int size, int rwflag, void *data)
{
	if(phpd_transport_passphrase != NULL)
	{
		strncpy(buf, phpd_transport_passphrase, sizeof(phpd_transport_passphrase));
		buf[sizeof(phpd_transport_passphrase)] = '\0';
		return(strlen(buf));
	}

	buf = NULL;
	return 0;
}

int phpd_transport_error_set(char *error)
{
	memset(phpd_transport_error_string, '\0', 1024);
	strncpy(phpd_transport_error_string, error, 1024);
}

/* In every utility function you add that needs to use variables 
   in php_phpd_transport_globals, call TSRMLS_FETCH(); after declaring other 
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as phpd_transport_G(variable).  You are 
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define PHPD_TRANSPORT_G(v) TSRMG(phpd_transport_globals_id, zend_phpd_transport_globals *, v)
#else
#define PHPD_TRANSPORT_G(v) (phpd_transport_globals.v)
#endif

#endif	/* PHP_PHPD_TRANSPORT_H */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
