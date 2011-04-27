#!/usr/bin/perl
# Quick hack to refactor (function) names in large parts of code.
# Just a simple tool since the Eclipse-PDT refactor doesn't seem to work :-(
# V0.1 -- 20110427 -- Initial version for OWL-PHP
# (c) Oscar van Eijk, Oveas Functionality Provider
#

my $location = '.';
if ($ARGC > 0) {
	$location = $ARGV[0];
}

%rewrites = (
	 'additional_clauses'	=> 'additionalClauses'
	,'alter_scheme'			=> 'alterScheme'
	,'alter_table'			=> 'alterTable'
	,'check_hide'			=> 'checkHide'
	,'close_logfile'		=> 'closeLogfile'
	,'compose_message'		=> 'composeMessage'
	,'config_file'			=> 'configFile'
	,'config_table'			=> 'configTable'
	,'create_scheme'		=> 'createScheme'
	,'create_table'			=> 'createTable'
	,'db_status'			=> 'dbStatus'
	,'define_index'			=> 'defineIndex'
	,'define_scheme'		=> 'defineScheme'
	,'escape_string'		=> 'escapeString'
	,'expand_field'			=> 'expandField'
	,'extract_tablelist'	=> 'extractTablelist'
	,'get_status'			=> 'getStatus'
	,'find_field'			=> 'findField'
	,'force_reread'			=> 'forceReread'
	,'get_callback'			=> 'getCallback'
	,'get_caller'			=> 'getCaller'
	,'get_code'				=> 'getCode'
	,'get_form_data'		=> 'getFormData'
	,'get_group_data'		=> 'getGroupData'
	,'get_group_item'		=> 'getGroupItem'
	,'get_instance'			=> 'getInstance'
	,'get_memberships'		=> 'getMemberships'
	,'get_message'			=> 'getMessage'
	,'get_run_id'			=> 'getRunId'
	,'get_session_id'		=> 'getSessionId'
	,'get_session_var'		=> 'getSessionVar'
	,'get_severity'			=> 'getSeverity'
	,'get_severity_level'	=> 'getSeverityLevel'
	,'get_table_columns'	=> 'getTableColumns'
	,'get_table_indexes'	=> 'getTableIndexes'
	,'get_trace'			=> '_getTrace'
	,'get_user_id'			=> 'getUserId'
	,'get_username'			=> 'getUsername'
	,'handle_exception'		=> 'handleException'
	,'hash_password'		=> 'hashPassword'
	,'inserted_id'			=> 'insertedId'
	,'ip_address'			=> 'ipAddress'
	,'is_open'				=> 'isOpen'
	,'last_inserted_id'		=> 'lastInsertedId'
	,'load_driver'			=> 'loadDriver'
	,'log_exception'		=> 'logException'
	,'log_session'			=> 'logSession'
	,'open_logfile'			=> 'openLogfile'
	,'parse_formdata'		=> 'parseFormdata'
	,'parse_item'			=> 'parseItem'
	,'password_strength'	=> 'passwordStrength'
	,'prepare_delete'		=> 'prepareDelete'
	,'prepare_field'		=> 'prepareField'
	,'prepare_insert'		=> 'prepareInsert'
	,'prepare_read'			=> 'prepareRead'
	,'prepare_update'		=> 'prepareUpdate'
	,'read_config'			=> 'readConfig'
	,'read_data'			=> 'readData'
	,'read_line'			=> 'readLine'
	,'read_userdata'		=> 'readUserdata'
	,'register_app'			=> 'registerApp'
	,'register_argument'	=> 'registerArgument'
	,'register_callback'	=> 'registerCallback'
	,'register_class'		=> 'registerClass'
	,'register_code'		=> 'registerCode'
	,'register_labels'		=> 'registerLabels'
	,'register_messages'	=> 'registerMessages'
	,'register_severity'	=> 'registerSeverity'
	,'reset_calltree'		=> 'resetCalltree'
	,'restore_status'		=> 'restoreStatus'
	,'save_status'			=> 'saveStatus'
	,'set_application'		=> 'setApplication'
	,'set_applic_logfile'	=> 'setApplicLogfile'
	,'set_callback'			=> 'setCallback'
	,'set_callback_argument'=> 'setCallbackArgument'
	,'set_class'			=> 'setClass'
	,'set_code'				=> 'setCode'
	,'set_filename'			=> 'setFilename'
	,'set_high_severity'	=> 'setHighSeverity'
	,'set_join'				=> 'setJoin'
	,'set_key'				=> 'setKey'
	,'set_params'			=> 'setParams'
	,'set_prefix'			=> 'setPrefix'
	,'set_query'			=> 'setQuery'
	,'set_rights'			=> 'setRights'
	,'set_session_var'		=> 'setSessionVar'
	,'set_severity'			=> 'setSeverity'
	,'set_status'			=> 'setStatus'
	,'set_tablename'		=> 'setTablename'
	,'set_username'			=> 'setUsername'
	,'stack_dump'			=> 'stackDump'
	,'table_description'	=> 'tableDescription'
	,'table_exists'			=> 'tableExists'
	,'unescape_string'		=> 'unescapeString'
	,'update_list'			=> 'updateList'
	,'username_exists'		=> 'usernameExists'
	,'validate_scheme'		=> 'validateScheme'
	,'where_clause'			=> 'whereClause'
	,'write_logfile'		=> 'writeLogfile'
	,'OWL_APPL_ID'			=> 'OWL_ID'
);

sub refactorFile ($) {
	my $file = shift;
	return if (!($file =~ /\.php$/));
	$c = 0;
	open (INPUT, "<$file");
	open (OUTPUT, ">$file".".NEW");
	while (my $line = <INPUT>) {
		chomp ($line);
		my $change = $line;
		my $mod = 0;
		foreach my $k (keys %rewrites) {
			if ($change =~ s/$k/$rewrites{$k}/g) {
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
#436+49=485
