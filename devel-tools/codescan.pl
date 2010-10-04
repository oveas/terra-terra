#!/usr/bin/perl
#
# Scan the OWP-PHP source directory for codes that are registered but that
# don't have an entry in owl.messages.php yet.
#
# Version 0.1 -- Initial version (2008-08-26)
# (c) Oscar van Eijk, Oveas Functionality Provider
# $Id: codescan.pl,v 1.2 2010-10-04 17:40:40 oscar Exp $
#

my $location = '/home/oscar/projects/owl-php/src';
if ($ARGC > 0) {
	$location = $ARGV[0];
}
my $msgfile = 'lib/owl.messages.php';
my %Messages = {};

sub loadmessages ($$) {
	my $loc = shift;
	my $mf = shift;

	open (MF, '<', $loc . '/' . $mf) || die 'Fatal error opening message file ' . $loc . '/' . $mf;
	while (my $line = <MF>) {
		if ($line =~ /\s+,?\s+([A-Z_]*)\s+=>\s+'(.*?)'/i) {
			$Messages{$1} = $2;
		}
	}
	close MF;
}

sub checkfiles ($$) {
	my $loc = shift;
	my $dh = shift;
	$dh++;
	if (!opendir ($dh, $loc)) {
		print '* Fatal error reading ' . $loc . "\n";
		return (0);
	} else {
#		print 'Parsing ' . $loc . " ($dh) \n";
	}
	while (my $file = readdir ($dh)) {
		next if ($file eq '.' || $file eq '..');
		$file = $file;
		if (-d $loc . '/' . $file) {
			if (&checkfiles ($loc . '/' . $file, $dh) == 0) {
				closedir $dh;
				return (0);
			}
		} else {
			if ($file =~ /\.php$/) {
				my $i = 0;
				open (FH, '<', $loc . '/' . $file);
#				print '-> ' . $loc . '/' . $file . "\n";
				while (my $line = <FH>) {
					$i++;
					if ($line =~ /^\s*Register::register_code\s*\('([A-Z_]*?)'\)/i) {
						if (!exists ($Messages{$1})) {
							print '* [' . $1 . '] is not registered in file ' . $file . ' on line ' . $i . "\n";
						} elsif ($Messages{$1} eq '') {
							print '* [' . $1 . '] has no text in file ' . $file . ' on line ' . $i . "\n";
						}
					}
				}
				close FH;
			}
		}
	}
	closedir $dh;
	return (1);
}

&loadmessages ($location, $msgfile);
&checkfiles ($location, 0);
