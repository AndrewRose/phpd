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
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	php_error(E_WARNING, "phpd_server: not yet implemented");
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
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	php_error(E_WARNING, "phpd_accept: not yet implemented");
}
/* }}} */

/* {{{ proto  phpd_read()
    */
PHP_FUNCTION(phpd_read)
{
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	php_error(E_WARNING, "phpd_read: not yet implemented");
}
/* }}} */

/* {{{ proto  phpd_write()
    */
PHP_FUNCTION(phpd_write)
{
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	php_error(E_WARNING, "phpd_write: not yet implemented");
}
/* }}} */

/* {{{ proto  phpd_close()
    */
PHP_FUNCTION(phpd_close)
{
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}
	php_error(E_WARNING, "phpd_close: not yet implemented");
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
