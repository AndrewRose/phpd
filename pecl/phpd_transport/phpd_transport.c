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
#include "php_phpd_transport.h"

/* If you declare any globals in php_phpd_transport.h uncomment this:
ZEND_DECLARE_MODULE_GLOBALS(phpd_transport)
*/

/* True global resources - no need for thread safety here */
static int le_phpd_transport;

/* {{{ phpd_transport_functions[]
 *
 * Every user visible function must have an entry in phpd_transport_functions[].
 */
zend_function_entry phpd_transport_functions[] = {
	PHP_FE(phpd_transport_server,	NULL)
	PHP_FE(phpd_transport_set,	NULL)
	PHP_FE(phpd_transport_accept,	NULL)
	PHP_FE(phpd_transport_read,	NULL)
	PHP_FE(phpd_transport_write,	NULL)
	PHP_FE(phpd_transport_close,	NULL)
	PHP_FE(phpd_transport_shutdown,	NULL)
	PHP_FE(phpd_transport_error,	NULL)
	{NULL, NULL, NULL}	/* Must be the last line in phpd_transport_functions[] */
};
/* }}} */

/* {{{ phpd_transport_module_entry
 */
zend_module_entry phpd_transport_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"phpd_transport",
	phpd_transport_functions,
	PHP_MINIT(phpd_transport),
	PHP_MSHUTDOWN(phpd_transport),
	PHP_RINIT(phpd_transport),		/* Replace with NULL if there's nothing to do at request start */
	PHP_RSHUTDOWN(phpd_transport),	/* Replace with NULL if there's nothing to do at request end */
	PHP_MINFO(phpd_transport),
#if ZEND_MODULE_API_NO >= 20010901
	"0.1", /* Replace with version number for your extension */
#endif
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_PHPD_TRANSPORT
ZEND_GET_MODULE(phpd_transport)
#endif

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("phpd_transport.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_phpd_transport_globals, phpd_transport_globals)
    STD_PHP_INI_ENTRY("phpd_transport.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_phpd_transport_globals, phpd_transport_globals)
PHP_INI_END()
*/
/* }}} */

/* {{{ php_phpd_transport_init_globals
 */
/* Uncomment this function if you have INI entries
static void php_phpd_transport_init_globals(zend_phpd_transport_globals *phpd_transport_globals)
{
	phpd_transport_globals->global_value = 0;
	phpd_transport_globals->global_string = NULL;
}
*/
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(phpd_transport)
{
	/* If you have INI entries, uncomment these lines 
	REGISTER_INI_ENTRIES();
	*/

//	le_phpd_transport_descriptor = zend_register_list_destructors_ex(NULL, NULL, le_phpd_transport_name, module_number);

	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(phpd_transport)
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
PHP_RINIT_FUNCTION(phpd_transport)
{
	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(phpd_transport)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(phpd_transport)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "phpd_transport support", "enabled");
	php_info_print_table_end();

	/* Remove comments if you have entries in php.ini
	DISPLAY_INI_ENTRIES();
	*/
}
/* }}} */


/* {{{ proto  phpd_transport_server()
    */
PHP_FUNCTION(phpd_transport_server)
{
	char *ip;
	int port, ip_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sl|l", &ip, &ip_len, &port, &phpd_transport_backlog) == FAILURE) {
		RETURN_NULL();
	}

	phpd_transport_error_string = pemalloc(1024, 1);

	phpd_transport_server_fd = socket(AF_INET, SOCK_STREAM, 0);
	setsockopt(phpd_transport_server_fd, SOL_SOCKET, SO_REUSEADDR, &phpd_transport_yes, sizeof(int));

	phpd_transport_my_addr.sin_family = AF_INET;
	phpd_transport_my_addr.sin_port = htons(port);
	phpd_transport_my_addr.sin_addr.s_addr = inet_addr(ip);
	memset(phpd_transport_my_addr.sin_zero, '\0', sizeof phpd_transport_my_addr.sin_zero);

	bind(phpd_transport_server_fd, (struct sockaddr *)&phpd_transport_my_addr, sizeof phpd_transport_my_addr);
	listen(phpd_transport_server_fd, phpd_transport_backlog);

	SSL_load_error_strings();
	SSL_library_init();

	phpd_transport_ssl_ctx = SSL_CTX_new( SSLv23_server_method() );

	SSL_CTX_set_default_passwd_cb(phpd_transport_ssl_ctx, phpd_transport_pem_passwd_cb);

	SSL_CTX_set_mode(phpd_transport_ssl_ctx, SSL_MODE_AUTO_RETRY);

	if(!SSL_CTX_use_certificate_file(phpd_transport_ssl_ctx, phpd_transport_certfile, SSL_FILETYPE_PEM))
	{
		phpd_transport_error_set("phpd_transport_server(): SSL_CTX_use_certificate_file();");
		RETURN_FALSE;
	}

	if(!SSL_CTX_use_PrivateKey_file(phpd_transport_ssl_ctx, phpd_transport_certfile, SSL_FILETYPE_PEM))
	{
		phpd_transport_error_set("phpd_transport_server(): SSL_CTX_use_PrivateKey_file();");
		RETURN_FALSE;
	}

	if(!SSL_CTX_check_private_key(phpd_transport_ssl_ctx))
	{
		phpd_transport_error_set("phpd_transport_server(): SSL_CTX_check_private_key();");
		RETURN_FALSE;
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto  phpd_transport_set()
    */
PHP_FUNCTION(phpd_transport_set)
{
	zval *zarray, **data, temp;
	HashTable *arr_hash;
	HashPosition pointer;
	char *key;
	int key_len;
	long index;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &zarray) == FAILURE) {
		RETURN_NULL();
	}

	arr_hash = Z_ARRVAL_P(zarray);

	for(zend_hash_internal_pointer_reset_ex(arr_hash, &pointer); zend_hash_get_current_data_ex(arr_hash, (void**) &data, &pointer) == SUCCESS; zend_hash_move_forward_ex(arr_hash, &pointer)) {

		if (zend_hash_get_current_key_ex(arr_hash, &key, &key_len, &index, 0, &pointer) == HASH_KEY_IS_STRING) {

			temp = **data;
			zval_copy_ctor(&temp);
			convert_to_string(&temp);

			if(!strcmp(key, "local_cert")) {

				phpd_transport_certfile = pemalloc(Z_STRLEN(temp), 1);
				strncpy(phpd_transport_certfile, Z_STRVAL(temp), Z_STRLEN(temp));
				phpd_transport_certfile[Z_STRLEN(temp)] = '\0';

			} else if(!strcmp(key, "passphrase")) {

				phpd_transport_passphrase = pemalloc(Z_STRLEN(temp), 1);
				strncpy(phpd_transport_passphrase, Z_STRVAL(temp), Z_STRLEN(temp));
				phpd_transport_passphrase[Z_STRLEN(temp)] = '\0';

			} else if(!strcmp(key, "requestLimit")) {

				//phpd_transport_max_length_read = 1024000;

			} 

			zval_dtor(&temp);
		}
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto  phpd_transport_accept()
    */
PHP_FUNCTION(phpd_transport_accept)
{
	BIO *sbio;

	phpd_transport_sin_size = sizeof phpd_transport_their_addr;
	phpd_transport_client_fd = accept(phpd_transport_server_fd, (struct sockaddr *)&phpd_transport_their_addr, &phpd_transport_sin_size);

        sbio = BIO_new_socket(phpd_transport_client_fd, BIO_NOCLOSE);

	phpd_transport_ssl = SSL_new(phpd_transport_ssl_ctx);
	if(phpd_transport_ssl==NULL)
	{
		phpd_transport_error_set(ERR_error_string(ERR_get_error(), NULL));
		RETURN_FALSE;
	}

	SSL_set_bio(phpd_transport_ssl, sbio, sbio);

	if(!SSL_set_fd(phpd_transport_ssl, phpd_transport_client_fd))
	{
		phpd_transport_error_set("phpd_transport_accept(): SSL_set_fd();");
		RETURN_FALSE;
	}

	if(SSL_accept(phpd_transport_ssl)<0)
	{
		phpd_transport_error_set(ERR_error_string(ERR_get_error(), NULL));
		RETURN_FALSE;
	}

	phpd_transport_io = BIO_new(BIO_f_buffer());
	phpd_transport_ssl_bio = BIO_new(BIO_f_ssl());
	BIO_set_ssl(phpd_transport_ssl_bio, phpd_transport_ssl, BIO_CLOSE);
	BIO_push(phpd_transport_io, phpd_transport_ssl_bio);

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto  phpd_transport_read()
    */
PHP_FUNCTION(phpd_transport_read)
{
	int	headers_read = 0, headers_read_total = 0, content_read = 0, content_read_total = 0, content_length = 0, content_read_remaining = 0;
	char	tmpbuf[2048];

	// I would love this to be global so the same malloced memory can be used on each request.. but PHP does not agree with me.
	phpd_transport_request_buffer = emalloc(phpd_transport_max_request);

	while(1)
	{
		// set up an alarm
		headers_read = BIO_gets(phpd_transport_io, tmpbuf, phpd_transport_max_length_header);

		if((headers_read + headers_read_total) > phpd_transport_max_request)
		{
			phpd_transport_error_set("Request exceeded phpd_transport_max_request in header read.");
			RETURN_FALSE;
		}

		switch(SSL_get_error(phpd_transport_ssl, headers_read))
		{
			case SSL_ERROR_NONE:
			{
				memcpy(&phpd_transport_request_buffer[headers_read_total], &tmpbuf, headers_read);
				tmpbuf[headers_read+1] = '\0';
				headers_read_total += headers_read;
				break;
			}
			default:
			{
				phpd_transport_error_set(ERR_error_string(ERR_get_error(), NULL));
				RETURN_FALSE;
			}
		}

		if(!strcmp(tmpbuf, "\r\n") || !strcmp(tmpbuf, "\n"))
		{
			break;
		}

		// maybe check the first character so we don't waste time checking all headers?
		if(!content_length && !strncmp(tmpbuf, "Content-Length: ", 16))
		{
			// I'm really not sure about the -1 bit.  But all clients I've tested with seems to have a real content-length of content-length-1..
			content_length = content_read_remaining = strtol(&tmpbuf[16], NULL, 10)-1;

			if((headers_read_total + content_length) > phpd_transport_max_request)
			{
				phpd_transport_error_set("Request will exceed phpd_transport_max_request on content read.");
				RETURN_FALSE;
			}
		}
	}

	if(content_length>=1)
	{
		int max_read = 0;

		while(content_read_total < content_length)
		{
			if((content_length-content_read_total) > 2048)
			{
				max_read = 2048;
			}
			else
			{
				max_read = (content_length-content_read_total)+1;
			}

			content_read = BIO_gets(phpd_transport_io, tmpbuf, max_read);

//php_printf("content_length: %d, content_read_total: %d, content_read: %d, max_read: %d\n", content_length, content_read_total, content_read, max_read);

			if((headers_read_total + (content_read_total + content_read)) > phpd_transport_max_request)
			{
				phpd_transport_error_set("Request exceeded phpd_transport_max_request in content read.");
				RETURN_FALSE;
			}

			memcpy(&phpd_transport_request_buffer[(headers_read_total+content_read_total)], &tmpbuf, content_read);
			content_read_total += content_read;
		}
	}

	// set response code i.e. 408 - timeout if bad read
	RETURN_STRINGL(phpd_transport_request_buffer, headers_read_total + content_read_total, 0);
}
/* }}} */

/* {{{ proto  phpd_transport_write()
    */
PHP_FUNCTION(phpd_transport_write)
{
	int	retval;
	long	length;
	char	*str;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|l", &str, &length) == FAILURE) {
                return;
        }

	retval = BIO_puts(phpd_transport_io, str);

	BIO_flush(phpd_transport_io);

        if (retval < 0) {
		phpd_transport_error_set(ERR_error_string(ERR_get_error(), NULL));
                RETURN_FALSE;
        }

        RETURN_LONG(retval);
}
/* }}} */

/* {{{ proto  phpd_transport_close()
    */
PHP_FUNCTION(phpd_transport_close)
{
	int r;

	r = SSL_shutdown(phpd_transport_ssl);
	if(!r)
	{
		shutdown(phpd_transport_client_fd,1);
		r = SSL_shutdown(phpd_transport_ssl);
	}

	switch(r)
	{
		case 1:
		{
			break; /* Success */
		}
		case 0:
		case -1:
		{
		}
		default:
		{
			//berr_exit("Shutdown failed");
		}
	}

	SSL_free(phpd_transport_ssl);
	close(phpd_transport_client_fd);
}
/* }}} */

/* {{{ proto  phpd_transport_shutdown()
    */
PHP_FUNCTION(phpd_transport_shutdown)
{
	close(phpd_transport_server_fd);
	SSL_CTX_free(phpd_transport_ssl_ctx);
	//efree(phpd_transport_request_buffer);
}
/* }}} */

/* {{{ proto  phpd_transport_error()
    */
PHP_FUNCTION(phpd_transport_error)
{
	if(phpd_transport_error_string[0] != '\0')
	{
		char *tmp = pemalloc(1024, 1);
		strncpy(tmp, phpd_transport_error_string, 1024);
		phpd_transport_error_string[0] = '\0';
		RETURN_STRING(tmp, sizeof(tmp));
	}
	else
	{
		RETURN_FALSE;
	}
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
