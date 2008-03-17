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

#ifndef PHP_PHPD_H
#define PHP_PHPD_H

extern zend_module_entry phpd_module_entry;
#define phpext_phpd_ptr &phpd_module_entry

#ifdef PHP_WIN32
#define PHP_PHPD_API __declspec(dllexport)
#else
#define PHP_PHPD_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(phpd);
PHP_MSHUTDOWN_FUNCTION(phpd);
PHP_RINIT_FUNCTION(phpd);
PHP_RSHUTDOWN_FUNCTION(phpd);
PHP_MINFO_FUNCTION(phpd);

PHP_FUNCTION(confirm_phpd_compiled);	/* For testing, remove later. */
PHP_FUNCTION(phpd_server);
PHP_FUNCTION(phpd_set);
PHP_FUNCTION(phpd_accept);
PHP_FUNCTION(phpd_read);
PHP_FUNCTION(phpd_write);
PHP_FUNCTION(phpd_close);

/* 
  	Declare any global variables you may need between the BEGIN
	and END macros here:     

ZEND_BEGIN_MODULE_GLOBALS(phpd)
	long  global_value;
	char *global_string;
ZEND_END_MODULE_GLOBALS(phpd)
*/

/* In every utility function you add that needs to use variables 
   in php_phpd_globals, call TSRMLS_FETCH(); after declaring other 
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as PHPD_G(variable).  You are 
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define PHPD_G(v) TSRMG(phpd_globals_id, zend_phpd_globals *, v)
#else
#define PHPD_G(v) (phpd_globals.v)
#endif

#endif	/* PHP_PHPD_H */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */