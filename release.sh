#!/bin/sh

TARGETDIR=wp-nicodo

mkdir ${TARGETDIR}

cp readme.txt       ${TARGETDIR}
cp screenshot-1.jpg ${TARGETDIR}
cp admin.css        ${TARGETDIR}
cp nicodo.css       ${TARGETDIR}
cp back.gif         ${TARGETDIR}
cp button.gif       ${TARGETDIR}
cp mce.js           ${TARGETDIR}
cp quicktag.js      ${TARGETDIR}
cp wp-nicodo.php    ${TARGETDIR}

find ${TARGETDIR} -name ".DS_Store" -print -exec rm {} ";"
zip -r wp-nicodo-$1.zip ${TARGETDIR}
rm -rf ${TARGETDIR}
