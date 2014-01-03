#!/usr/bin/perl
# Quick hack to refactor (function) names in large parts of code.
# Just a simple tool since the Eclipse-PDT refactor doesn't seem to work :-(
# V0.1 -- 20110427 -- Initial version for OWL-PHP
# V0.2 -- 20130103 -- In the move to Terra-Terra: changed rewrites hash to an array to prevent sorting.
# (c) Oscar van Eijk, Oveas Functionality Provider
#

my $location = '.';
if ($ARGC > 0) {
	$location = $ARGV[0];
}
# !!! Change this one if the character appears in any of the values !!!
my $sepString = '#';

@rewrites = (
	 'OWL-PHP'	. $sepString . 'Terra-Terra'
	,'OWL'		. $sepString . 'TT'
	,'owl-php'	. $sepString . 'terra-terra'
	,'owl'		. $sepString . 'tt'
);

sub refactorFile ($) {
	my $file = shift;
	print "Refactoring $file...\n";
#	return if (!($file =~ /\.php$/));
	$c = 0;
	open (INPUT, "<$file");
	open (OUTPUT, ">$file".".NEW");
	while (my $line = <INPUT>) {
		chomp ($line);
		my $change = $line;
		my $mod = 0;
		for (my $i = 0; $i <= $#rewrites; $i++) {
			my ($old, $new) = split(/$sepString/, $rewrites[$i]);
			if ($change =~ s/$old/$new/g) {
				$mod++;
			}
		}
		if ($mod > 0) {
			print 'Old: ' . $line . "\n";
			print 'New: ' . $change . "\n";
			print 'Write change? ([y]/n): ';
			my $conf = <>;
			chomp ($conf);
			if ($conf ne 'n') {
				print OUTPUT $change . "\n";
				$c++;
			} else {
				print OUTPUT $line . "\n";
			}
		} else {
			print OUTPUT $line . "\n";
		}
	}
	close INPUT;
	close OUTPUT;
	if ($c > 0) {
		unlink ($file);
		rename $file . ".NEW", $file;
		print "$file was changed\n";
	} else {
		unlink ($file . ".NEW");
	}
}

sub parsedir ($$) {
	my $loc = shift;
	my $dh = shift;

	$dh++;
	if (!opendir ($dh, $loc)) {
		print '* Fatal error reading ' . $loc . "\n";
		return (0);
	} else {
		print 'Parsing ' . $loc . " ($dh) \n";
	}
	while (my $file = readdir ($dh)) {
		next if ($file eq '.' || $file eq '..' || $file eq 'CVS');
		if (-d $loc . '/' . $file) {
			if (&parsedir ($loc . '/' . $file, $dh) == 0) {
				closedir $dh;
				return (0);
			}
		} else {
			refactorFile ($loc . '/' .$file);
		}
	}
	closedir $dh;
	return (1);
}

&parsedir ($location, 0);
