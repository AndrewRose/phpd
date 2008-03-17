dnl $Id$
dnl config.m4 for extension phpd

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(phpd, for phpd support,
dnl Make sure that the comment is aligned:
dnl [  --with-phpd             Include phpd support])

dnl Otherwise use enable:

dnl PHP_ARG_ENABLE(phpd, whether to enable phpd support,
dnl Make sure that the comment is aligned:
dnl [  --enable-phpd           Enable phpd support])

if test "$PHP_PHPD" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-phpd -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/phpd.h"  # you most likely want to change this
  dnl if test -r $PHP_PHPD/$SEARCH_FOR; then # path given as parameter
  dnl   PHPD_DIR=$PHP_PHPD
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for phpd files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       PHPD_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$PHPD_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the phpd distribution])
  dnl fi

  dnl # --with-phpd -> add include path
  dnl PHP_ADD_INCLUDE($PHPD_DIR/include)

  dnl # --with-phpd -> check for lib and symbol presence
  dnl LIBNAME=phpd # you may want to change this
  dnl LIBSYMBOL=phpd # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $PHPD_DIR/lib, PHPD_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_PHPDLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong phpd lib version or lib not found])
  dnl ],[
  dnl   -L$PHPD_DIR/lib -lm -ldl
  dnl ])
  dnl
  dnl PHP_SUBST(PHPD_SHARED_LIBADD)

  PHP_NEW_EXTENSION(phpd, phpd.c, $ext_shared)
fi
