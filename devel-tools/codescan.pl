#!/usr/bin/perl
#
# Scan the Terra-Terra source directory for codes that are registered but that
# don't have an entry in tt.messages.php yet.
#
# Version 0.1 -- Initial version (2008-08-26)
# (c) Oscar van Eijk, Oveas Functionality Provider
#

my $location = '/home/oscar/projects/terra-terra/src';
my $app = 'tt';

if ($#ARGV >= 0) {
	$location = $ARGV[0];
	if ($#ARGV == 0) {
		print "Usage: $0 [location] [app]\n";
		exit();
	} else {
		$app = $ARGV[1];
	}
}
my $msgfile = 'lib/'.$app.'.messages.php';
my %Messages = {};

my $lblfile = 'lib/'.$app.'.labels.php';
my %Labels = {};

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

sub loadlabels ($$) {
	my $loc = shift;
	my $lf = shift;

	open (LF, '<', $loc . '/' . $lf) || die 'Fatal error opening label file ' . $loc . '/' . $lf;
	while (my $line = <LF>) {
		if ($line =~ /\s+,?\s+('|")(.*?)(\1)\s+=>\s+'(.*?)'/i) {
			$Labels{$2} = $4;
		}
	}
	close LF;
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
					if ($line =~ /^\s*Register::registerCode\s*\('([A-Z_]*?)'\)/i) {
						if (!exists ($Messages{$1})) {
							print '* [' . $1 . '] is not registered in file ' . $file . ' on line ' . $i . "\n";
						} elsif ($Messages{$1} eq '') {
							print '* [' . $1 . '] has no text in file ' . $file . ' on line ' . $i . "\n";
						}
					}
					if ($line =~ /(::|->)trn\s*\('(.*?)'\)/i) {
						if (!exists ($Labels{$2})) {
							print '* [' . $2 . '] has no translation in file ' . $file . ' on line ' . $i . "\n";
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
&loadlabels ($location, $lblfile);
&checkfiles ($location, 0);
