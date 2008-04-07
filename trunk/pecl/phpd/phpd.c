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
	PHP_FE(phpd_server,	NULL)
	PHP_FE(phpd_set,	NULL)
	PHP_FE(phpd_accept,	NULL)
	PHP_FE(phpd_read,	NULL)
	PHP_FE(phpd_write,	NULL)
	PHP_FE(phpd_close,	NULL)
	PHP_FE(phpd_shutdown,	NULL)
	PHP_FE(phpd_error,	NULL)
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

//	le_phpd_descriptor = zend_register_list_destructors_ex(NULL, NULL, le_phpd_name, module_number);

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


/* {{{ proto  phpd_server()
    */
PHP_FUNCTION(phpd_server)
{
	char *ip;
	int port, ip_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sl|l", &ip, &ip_len, &port, &phpd_backlog) == FAILURE) {
		RETURN_NULL();
	}

	phpd_error_string = emalloc(1024);

	phpd_server_fd = socket(AF_INET, SOCK_STREAM, 0);
	setsockopt(phpd_server_fd, SOL_SOCKET, SO_REUSEADDR, &phpd_yes, sizeof(int));

	phpd_my_addr.sin_family = AF_INET;
	phpd_my_addr.sin_port = htons(port);
	phpd_my_addr.sin_addr.s_addr = inet_addr(ip);
	memset(phpd_my_addr.sin_zero, '\0', sizeof phpd_my_addr.sin_zero);

	bind(phpd_server_fd, (struct sockaddr *)&phpd_my_addr, sizeof phpd_my_addr);
	listen(phpd_server_fd, phpd_backlog);

	SSL_load_error_strings();
	SSL_library_init();

	phpd_ssl_ctx = SSL_CTX_new( SSLv23_server_method() );

	SSL_CTX_set_default_passwd_cb(phpd_ssl_ctx, pem_passwd_cb);

	if(!SSL_CTX_use_certificate_file(phpd_ssl_ctx, phpd_certfile, SSL_FILETYPE_PEM))
	{
		phpd_error_set("phpd_server(): SSL_CTX_use_certificate_file();");
		RETURN_FALSE;
	}

	if(!SSL_CTX_use_PrivateKey_file(phpd_ssl_ctx, phpd_certfile, SSL_FILETYPE_PEM))
	{
		phpd_error_set("phpd_server(): SSL_CTX_use_PrivateKey_file();");
		RETURN_FALSE;
	}

	if(!SSL_CTX_check_private_key(phpd_ssl_ctx))
	{
		phpd_error_set("phpd_server(): SSL_CTX_check_private_key();");
		RETURN_FALSE;
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto  phpd_set()
    */
PHP_FUNCTION(phpd_set)
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

				phpd_certfile = emalloc(Z_STRLEN(temp));
				strncpy(phpd_certfile, Z_STRVAL(temp), Z_STRLEN(temp));
				phpd_certfile[Z_STRLEN(temp)] = '\0';

			} else if(!strcmp(key, "passphrase")) {

				phpd_passphrase = emalloc(Z_STRLEN(temp));
				strncpy(phpd_passphrase, Z_STRVAL(temp), Z_STRLEN(temp));
				phpd_passphrase[Z_STRLEN(temp)] = '\0';

			} 

			zval_dtor(&temp);
		}
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto  phpd_accept()
    */
PHP_FUNCTION(phpd_accept)
{
	phpd_sin_size = sizeof phpd_their_addr;

	phpd_client_fd = accept(phpd_server_fd, (struct sockaddr *)&phpd_their_addr, &phpd_sin_size);

	phpd_ssl = SSL_new(phpd_ssl_ctx);
	if(phpd_ssl==NULL)
	{
		phpd_error_set("phpd_accept(): SSL_new()");
		RETURN_FALSE;
	}

	if(!SSL_set_fd(phpd_ssl, phpd_client_fd))
	{
		phpd_error_set("phpd_accept(): SSL_set_fd();");
		RETURN_FALSE;
	}

	if(SSL_accept(phpd_ssl)<0)
	{
		phpd_error_set(ERR_error_string(ERR_get_error(), NULL));
		RETURN_FALSE;
	}

	RETURN_TRUE;
}
/* }}} */

/* {{{ proto  phpd_read()
    */
PHP_FUNCTION(phpd_read)
{
	char		*tmpbuf;
	int		retval;
	long		length;

        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &length) == FAILURE) {
                return;
        }

	/* overflow check */
	if ((length + 1) < 2) {
		RETURN_FALSE;
	}

	tmpbuf = emalloc(length + 1);

	retval = SSL_read(phpd_ssl, tmpbuf, length);

	if (retval < 0) {
		phpd_error_set(ERR_error_string(ERR_get_error(), NULL));
                efree(tmpbuf);
                RETURN_FALSE;
        }

	tmpbuf = erealloc(tmpbuf, retval + 1);
	tmpbuf[retval] = '\0' ;

	RETURN_STRINGL(tmpbuf, retval, 0);
}
/* }}} */

/* {{{ proto  phpd_write()
    */
PHP_FUNCTION(phpd_write)
{
	int	retval, str_len;
	long	length;
	char	*str;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|l", &str, &str_len, &length) == FAILURE) {
                return;
        }

        if (ZEND_NUM_ARGS() < 2) {
                length = str_len;
        }

	retval = SSL_write(phpd_ssl, str, length);

        if (retval < 0) {
                RETURN_FALSE;
        }

        RETURN_LONG(retval);
}
/* }}} */

/* {{{ proto  phpd_close()
    */
PHP_FUNCTION(phpd_close)
{
	close(phpd_client_fd);
	SSL_shutdown(phpd_ssl);
}
/* }}} */

/* {{{ proto  phpd_shutdown()
    */
PHP_FUNCTION(phpd_shutdown)
{
	close(phpd_server_fd);
	SSL_CTX_free(phpd_ssl_ctx);
}
/* }}} */

/* {{{ proto  phpd_error()
    */
PHP_FUNCTION(phpd_error)
{
	if(phpd_error_string[0] != '\0')
	{
		char *tmp = emalloc(1024);
		strncpy(tmp, phpd_error_string, 1024);
		phpd_error_string[0] = '\0';
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
