#!/bin/bash
#
# Hack to package Terra-Terra


show_usage ()
{
	ME=`basename $0`
	cat << ___USAGE_NOTES___
Usage: $ME <terra-terra location>
___USAGE_NOTES___
	exit
}

if [ "$1" == "" ]
then
	TTLOCATION=.
else
	TTLOCATION=$1
fi 

if [ ! -e $TTLOCATION/src/TTloader.php ]
then
	show_usage
	exit
fi 

CLOC=`pwd`
cd $TTLOCATION

TTVERSION=`grep TT_VERSION src/TTloader.php | sed -r "s#(^\s*d.*,\s*'|'\s*\)\s*;\s*$)##g"`

if [ -e __TTdist.$TTVERSION.tmp ]
then
	rm -rf __TTdist.$TTVERSION.tmp
fi

if [ -e terra-terra_$TTVERSION.zip ]
then
	rm terra-terra_$TTVERSION.zip
fi


mkdir __TTdist.$TTVERSION.tmp
cd __TTdist.$TTVERSION.tmp

# Kernel sources
mkdir terra-terra
cp -Rupv ../src/* ./terra-terra
cp -p ../db/sql/tt.tables.sql ./terra-terra

# TT Admin app
mkdir ttadmin
cp -Rupv ../admin/* ./ttadmin

../devel-tools/createIndexes.pl ./terra-terra
../devel-tools/createIndexes.pl ./ttadmin
rm ./ttadmin/index.html # Remove from the entry level

zip -r ../terra-terra_$TTVERSION.zip *

cd ..
rm -rf __TTdist.$TTVERSION.tmp
cd $CLOC

echo Succesfully created terra-terra_$TTVERSION.zip

