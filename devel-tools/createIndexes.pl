#!/usr/bin/perl

# Quick hack to check if all directories in an TT release environment an index.html.
# If not, one is created.
#
# Usage:
# Just execute this script in the top of the directory tree,
# or give the location as an argument.
#
# v0.1 -- 2012-07-04; Initial version for Joomla componentens
# v0.2 -- 2013-06-03; Embedded in OWL
# (c)2012-2013 Oscar van Eijk, Oveas Functionality Provider
#

sub checkDirs ($$)
{
	my $loc = shift;
	my $dh = shift;
	$dh++;
	if (!opendir ($dh, $loc)) {
		print '* Fatal error reading ' . $loc . "\n";
		return (0);
	} else {
		print 'Checking ' . $loc . " ($dh) \n";
	}
	if (-e $loc . '/index.html') {
		print "-> Found an index.html\n";
	} else {
		open (IDX, ">$loc/index.html");
		print IDX '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'."\n"
			.'<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
			.'<title>Terra-Terra</title></head><body>You do not have direct access to this directory</body></html>';
		close IDX;
		print "-> New index.html created!\n";
	}
	while (my $file = readdir ($dh)) {
		next if ($file eq '.' || $file eq '..');
		$file = $file;
		if (-d $loc . '/' . $file) {
			if (&checkDirs ($loc . '/' . $file, $dh) == 0) {
				closedir $dh;
				return (0);
			}
		}
	}
	closedir $dh;
	return (1);
}

my $location = $ARGV[0] || '.';
&checkDirs ($location, 0);

