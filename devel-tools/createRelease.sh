#!/bin/bash
#
# Hack to package OWL-PHP


show_usage ()
{
	ME=`basename $0`
	cat << ___USAGE_NOTES___
Usage: $ME <owl-php location>
___USAGE_NOTES___
	exit
}

if [ "$1" == "" ]
then
	OWLLOCATION=.
else
	OWLLOCATION=$1
fi 

if [ ! -e $OWLLOCATION/src/OWLloader.php ]
then
	show_usage
	exit
fi 

if [ ! -e $OWLLOCATION/../owl-js/src/lib/owl.js ]
then
	echo Cannot find OWL-JS --- please set the correct path in $0
	exit
fi 

CLOC=`pwd`
cd $OWLLOCATION

OWLVERSION=`grep OWL_VERSION src/OWLloader.php | sed -r "s#(^\s*d.*,\s*'|'\s*\)\s*;\s*$)##g"`

if [ -e __OWLPHPdist.$OWLVERSION.tmp ]
then
	rm -rf __OWLPHPdist.$OWLVERSION.tmp
fi

if [ -e owlphp_$OWLVERSION.zip ]
then
	rm owlphp_$OWLVERSION.zip
fi


mkdir __OWLPHPdist.$OWLVERSION.tmp
cd __OWLPHPdist.$OWLVERSION.tmp

# Kernel sources
mkdir owl-php
cp -Rupv ../src/* ./owl-php
cp -p ../db/sql/owl.tables.sql ./owl-php

# OWL-JS
mkdir owl-js
cp -Rupv ../../owl-js/src/* ./owl-js

# OWL Admin app
mkdir owladmin
cp -Rupv ../admin/* ./owladmin

../devel-tools/createIndexes.pl ./owl-php
../devel-tools/createIndexes.pl ./owl-js
../devel-tools/createIndexes.pl ./owlAdmin

zip -r ../owlphp_$OWLVERSION.zip *

cd ..
rm -rf __OWLPHPdist.$OWLVERSION.tmp
cd $CLOC

echo Succesfully created owlphp_$OWLVERSION.zip

