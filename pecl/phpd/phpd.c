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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_phpd.h"

/* phpd */

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

#define MYPORT 443
#define BACKLOG 10

FILE *phpd_log;
int phpd_sockfd, phpd_new_fd;  // listen on sock_fd, new connection on phpd_new_fd
struct sockaddr_in phpd_my_addr;        // my address information
struct sockaddr_in phpd_their_addr; // connector's address information
socklen_t phpd_sin_size;
int phpd_yes=1; // wot dis den?

static char* phpd_certfile = "server.pem";
static char* phpd_cipher;
static SSL_CTX* phpd_ssl_ctx;
static SSL* phpd_ssl;
static int phpd_conn_fd;

char phpd_buf[100000];

/* */

/* If you declare any globals in php_phpd.h uncomment this:
ZEND_DECLARE_MODULE_GLOBALS(phpd)
*/

/* True global resources - no need for thread safety here */
static int le_phpd;

/* {{{ phpd_functions[]
 *
 * Every user visible function must have an entry in phpd_functions[].
 */
zend_function_entry phpd_functions[] = {
	PHP_FE(confirm_phpd_compiled,	NULL)		/* For testing, remove later. */
	PHP_FE(phpd_server,	NULL)
	PHP_FE(phpd_set,	NULL)
	PHP_FE(phpd_accept,	NULL)
	PHP_FE(phpd_read,	NULL)
	PHP_FE(phpd_write,	NULL)
	PHP_FE(phpd_close,	NULL)
	{NULL, NULL, NULL}	/* Must be the last line in phpd_functions[] */
};
/* }}} */

/* {{{ phpd_module_entry
 */
zend_module_entry phpd_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"phpd",
	phpd_functions,
	PHP_MINIT(phpd),
	PHP_MSHUTDOWN(phpd),
	PHP_RINIT(phpd),		/* Replace with NULL if there's nothing to do at request start */
	PHP_RSHUTDOWN(phpd),	/* Replace with NULL if there's nothing to do at request end */
	PHP_MINFO(phpd),
#if ZEND_MODULE_API_NO >= 20010901
	"0.1", /* Replace with version number for your extension */
#endif
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_PHPD
ZEND_GET_MODULE(phpd)
#endif

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("phpd.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_phpd_globals, phpd_globals)
    STD_PHP_INI_ENTRY("phpd.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_phpd_globals, phpd_globals)
PHP_INI_END()
*/
/* }}} */

/* {{{ php_phpd_init_globals
 */
/* Uncomment this function if you have INI entries
static void php_phpd_init_globals(zend_phpd_globals *phpd_globals)
{
	phpd_globals->global_value = 0;
	phpd_globals->global_string = NULL;
}
*/
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(phpd)
{
	/* If you have INI entries, uncomment these lines 
	REGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(phpd)
{
	/* uncomment this line if you have INI entries
	UNREGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request start */
/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(phpd)
{
	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(phpd)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(phpd)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "phpd support", "enabled");
	php_info_print_table_end();

	/* Remove comments if you have entries in php.ini
	DISPLAY_INI_ENTRIES();
	*/
}
/* }}} */


/* Remove the following function when you have succesfully modified config.m4
   so that your module can be compiled into PHP, it exists only for testing
   purposes. */

/* Every user-visible function in PHP should document itself in the source */
/* {{{ proto string confirm_phpd_compiled(string arg)
   Return a string to confirm that the module is compiled in */
PHP_FUNCTION(confirm_phpd_compiled)
{
	char *arg = NULL;
	int arg_len, len;
	char *strg;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &arg, &arg_len) == FAILURE) {
		return;
	}

	len = spprintf(&strg, 0, "Congratulations! You have successfully modified ext/%.78s/config.m4. Module %.78s is now compiled into PHP.", "phpd", arg);
	RETURN_STRINGL(strg, len, 0);
}
/* }}} */
/* The previous line is meant for vim and emacs, so it can correctly fold and 
   unfold functions in source code. See the corresponding marks just before 
   function definition, where the functions purpose is also documented. Please 
   follow this convention for the convenience of others editing your code.
*/

/* {{{ proto  phpd_server()
    */
PHP_FUNCTION(phpd_server)
{
	phpd_log = fopen("test.log", "w+");

	phpd_sockfd = socket(AF_INET, SOCK_STREAM, 0);
	setsockopt(phpd_sockfd, SOL_SOCKET, SO_REUSEADDR, &phpd_yes, sizeof(int));

	phpd_my_addr.sin_family = AF_INET;               // host byte order
	phpd_my_addr.sin_port = htons(MYPORT);   // short, network byte order
	phpd_my_addr.sin_addr.s_addr = INADDR_ANY; // automatically fill with my IP
	memset(phpd_my_addr.sin_zero, '\0', sizeof phpd_my_addr.sin_zero);

	bind(phpd_sockfd, (struct sockaddr *)&phpd_my_addr, sizeof phpd_my_addr);
	listen(phpd_sockfd, BACKLOG);

	SSL_load_error_strings();
	SSL_library_init();

	phpd_ssl_ctx = SSL_CTX_new( SSLv23_server_method() );

	if(	!SSL_CTX_use_certificate_file(phpd_ssl_ctx, phpd_certfile, SSL_FILETYPE_PEM) ||
		!SSL_CTX_use_PrivateKey_file(phpd_ssl_ctx, phpd_certfile, SSL_FILETYPE_PEM) ||
		!SSL_CTX_check_private_key(phpd_ssl_ctx)
	){
		//SSL_CTX_set_phpd_cipher_list(phpd_ssl_ctx, phpd_cipher);
		ERR_print_errors_fp(phpd_log);
	}
}
/* }}} */

/* {{{ proto  phpd_set()
    */
PHP_FUNCTION(phpd_set)
{
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	php_error(E_WARNING, "phpd_set: not yet implemented");
}
/* }}} */

/* {{{ proto  phpd_accept()
    */
PHP_FUNCTION(phpd_accept)
{
	phpd_sin_size = sizeof phpd_their_addr;

	phpd_new_fd = accept(phpd_sockfd, (struct sockaddr *)&phpd_their_addr, &phpd_sin_size);

	phpd_ssl = SSL_new(phpd_ssl_ctx);
	if(phpd_ssl==NULL)
	{
		ERR_print_errors_fp(phpd_log);
	}

	if(!SSL_set_fd(phpd_ssl, phpd_new_fd))
	{
		ERR_print_errors_fp(phpd_log);
	}

	if(!SSL_accept(phpd_ssl))
	{
		ERR_print_errors_fp(phpd_log);
	}
}
/* }}} */

/* {{{ proto  phpd_read()
    */
PHP_FUNCTION(phpd_read)
{
	if(!SSL_read(phpd_ssl, phpd_buf, sizeof(phpd_buf)))
	{
		ERR_print_errors_fp(phpd_log);
	}
}
/* }}} */

/* {{{ proto  phpd_write()
    */
PHP_FUNCTION(phpd_write)
{
	if(!SSL_write(phpd_ssl,
		"HTTP/1.1 200 OK\r\nServer: phpd/1.0\r\nContent-Type: text/html\r\nContent-Length: 12\r\nConnection: close\r\n\r\nHello World!\n\0"
		,sizeof(
		"HTTP/1.1 200 OK\r\nServer: phpd/1.0\r\nContent-Type: text/html\r\nContent-Length: 12\r\nConnection: close\r\n\r\nHello World!\n\0"
	)-1))
	{
		ERR_print_errors_fp(phpd_log);
	}
}
/* }}} */

/* {{{ proto  phpd_close()
    */
PHP_FUNCTION(phpd_close)
{
// need to close the accept descriptor.. but not here
// close(phpd_new_fd);
	SSL_shutdown(phpd_ssl);
        fclose(phpd_log);
}
/* }}} */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
