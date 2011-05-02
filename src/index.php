<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \author Oscar van Eijk, Oveas Functionality Provider
 * \version $Id: index.php,v 1.15 2011-05-02 12:56:15 oscar Exp $
 */
define ('APPL_CODE', 'OWL');
define ('OWL_ROOT', '/home/oscar/projects/owl-php/src');
require (OWL_ROOT . '/OWLloader.php');
/*
OWLdbg_add ($GLOBALS['config']);
OWLdbg_add ($GLOBALS['register']);
OWLdbg_add ($_SESSION);

if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class');
}
$LoginForm = new Form('applic#include-path#classfile#class#method');

$_form = OWL::factory('FormHandler');
OWLdbg_add ($_form);

$_user = new User();

switch ($_form->get('act')) {
	case 'login':
		if (!$_user->login($_form->get('usr'), $_form->get('pwd'))) {
			$_user->signal();
		}
		break;
	case 'logout':
		$_user->logout();
		header('location: ' . $_SERVER['PHP_SELF']);
		break;
	default :
		break;
}
*/
// Testcases :-)
?>
<html>
<head>
<title>OWL-PHP</title>
</head>
<body>
<pre>
<?php 
$_scheme = OWL::factory('schemehandler');
//$_data1 = array();
//$_scheme->tableDescription('test2', $_data1);
//$_data2 = array();
//$_scheme->tableDescription('test', $_data2);
//print_r($_scheme->compare($_data1,$_data2));
$_table = array(
	 'id' => array (
			 'type' => 'INT'
			,'length' => 11
			,'auto_inc' => true
			,'null' => false
	)
	,'name' => array (
			 'type' => 'varchar'
			,'length' => 24
			,'auto_inc' => false
			,'null' => false
	)
	,'address' => array (
			 'type' => 'text'
			,'length' => 0
			,'null' => false
	)
	,'phone' => array (
			 'type' => 'varchar'
			,'length' => 16
			,'null' => true
	)
	,'country' => array (
			 'type' => 'enum'
			,'length' => 0
			,'auto_inc' => false
			,'options' => array('NL', 'BE', 'DE', 'FR', 'ES')
			,'default' => 'ES'
			,'null' => false
	)
);
$_index = array (
	 'name' => array(
			 'columns' => array ('name')
			,'primary' => false
			,'unique' => false
			,'type' => null
	)
	,'address' => array(
			 'columns' => array ('address')
			,'primary' => false
			,'unique' => false
			,'type' => 'FULLTEXT'
	)
);
$_scheme->createScheme('person');
$_scheme->defineScheme($_table);
$_scheme->defineIndex($_index);
$_scheme->scheme();
$_scheme->reset();
$_scheme->tableDescription('person', $_data);
echo '<pre>'. print_r($_data, 1) . '</pre>';
?>

</pre>
</body>
</html>
<?php

//phpinfo();
OWLloader::getClass('OWLrundown.php', OWL_ROOT);
//require_once (OWL_ROOT . '/OWLrundown.php');
?>
