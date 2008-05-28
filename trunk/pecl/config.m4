dnl $Id$
dnl config.m4 for extension phpd_transport

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(phpd_transport, for phpd_transport support,
dnl Make sure that the comment is aligned:
dnl [  --with-phpd_transport             Include phpd_transport support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(phpd_transport, whether to enable phpd_transport support,
Make sure that the comment is aligned:
[  --enable-phpd_transport           Enable phpd_transport support])

if test "$PHP_PHPD_TRANSPORT" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-PHPD_TRANSPORT -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/phpd_transport.h"  # you most likely want to change this
  dnl if test -r $PHP_PHPD_TRANSPORT/$SEARCH_FOR; then # path given as parameter
  dnl   PHPD_TRANSPORT_DIR=$PHP_PHPD_TRANSPORT
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for phpd_transport files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       PHPD_TRANSPORT_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$PHPD_TRANSPORT_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the phpd_transport distribution])
  dnl fi

  dnl # --with-phpd_transport -> add include path
  dnl PHP_ADD_INCLUDE($PHPD_TRANSPORT_DIR/include)

  dnl # --with-phpd_transport -> check for lib and symbol presence
  dnl LIBNAME=phpd_transport # you may want to change this
  dnl LIBSYMBOL=phpd_transport # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $PHPD_TRANSPORT_DIR/lib, PHPD_TRANSPORT_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_PHPD_TRANSPORTLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong phpd_transport lib version or lib not found])
  dnl ],[
  dnl   -L$PHPD_TRANSPORT_DIR/lib -lm -ldl
  dnl ])
  dnl
  dnl PHP_SUBST(PHPD_TRANSPORT_SHARED_LIBADD)

  PHP_NEW_EXTENSION(phpd_transport, phpd_transport.c, $ext_shared)
fi
