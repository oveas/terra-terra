#!/bin/bash

INDIRs="$HOME/projects/owl-js/src/"
OUTDIR="$HOME/projects/owl-php/docs/srcdoc/jsrewrite/"

BS='\\'
FS='\/'
TOPI=`echo $INDIRs|sed "s/\//$BS$FS/g"`;
TOPO=`echo $OUTDIR|sed "s/\//$BS$FS/g"`;

if [ $# -ne 0 ]
then
	DIRs=$@
fi

for DIR in $INDIRs; do

	JSs=`find $DIR -name "*.js"`

	for JS in $JSs; do
		echo "Process $JS"
		DOC=`echo $JS|sed 's/\(.*\)\.js/\1.java/g'`;
		if [ $JS -nt $DOC ]; then
			DOC=`echo $DOC|sed "s/$TOPI/$TOPO/g"`;
			DEST=`echo $DOC|sed "s/\(.*\)$FS[a-zA-Z0-9_-]*\.java/\1/g"`;
		
			if [ ! -e $DEST ]; then
				mkdir $DEST
			fi

			echo "rewrite $DOC"
			grep -e '^\s*\(///\|//\*\|/\*\*\| \* \| \*/\)' $JS | sed 's/^\s*\/\/\*\(.*\)$/\1/g'> $DOC
		fi
	done
done
