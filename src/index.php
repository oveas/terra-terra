<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.11 2010-12-03 12:07:43 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/projects/owl-php/src');
require_once (OWL_ROOT . '/OWLloader.php');

DBG_dumpval ($GLOBALS['config']);
DBG_dumpval ($GLOBALS['register']);
DBG_dumpval ($_SESSION);

if (!OWLloader::getClass('form')) {
	trigger_error('Error loading the Form class');
}
$LoginForm = new Form('applic#include-path#classfile#class#method');

$_form = OWL::factory('FormHandler');
DBG_dumpval ($_form);

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

// Testcases :-)
?>
<html>
<head>
<title>OWL-PHP</title>
</head>
<body>
Hello <?php echo ($_user->get_username()); ?> (<?php echo ($_user->user_data['email']); ?>)<br />
<?php $_user->set_session_var('c', 1, SESSIONVAR_INCR); ?>
You've been here <?php echo $_user->get_session_var('c', '?'); ?> times.<br />
<a href="<?php echo ($_SERVER['PHP_SELF']); ?>">Continue</a><br />
<a href="<?php echo ($_SERVER['PHP_SELF']); ?>?act=logout">Logout</a><br />

<?php 
$LoginForm->openForm();
$LoginForm->addField('text', 'usr', 'Ik', array('size' => 20));
$LoginForm->addField('password', 'pwd', '', array('size' => 15));
?>
<?php echo $LoginForm->openForm(); ?>
<table border=0>
	<tr>
		<td>Username:</td>
		<td><?php echo $LoginForm->showField('usr'); ?></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><?php echo $LoginForm->showField('pwd'); ?></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="login" name="act"></td>
	</tr>
</table>
<?php echo $LoginForm->closeForm(); ?>

<pre>
<?php print_r($_SESSION);?>
</pre>
<hr>
<pre>
<?php 
OWLloader::getClass('schemehandler');
$_scheme = SchemeHandler::get_instance();
//$_data1 = array();
//$_scheme->table_description('test2', $_data1);
//$_data2 = array();
//$_scheme->table_description('test', $_data2);
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
$_scheme->create_scheme('person');
$_scheme->define_scheme($_table);
$_scheme->define_index($_index);
$_scheme->scheme();
$_scheme->reset();
$_scheme->table_description('person', $_data);
//print_r($_data);
?>
$_t = '<?php print serialize($_data['columns'])?>'
$_i = '<?php print serialize($_data['indexes'])?>'
$_scheme->create_scheme('person');
$_scheme->define_scheme($_t);
$_scheme->define_index($_i);
$_scheme->scheme();
$_scheme->reset();
<?php $_t=serialize($_data['columns']); print_r(unserialize($_t));
$_scheme->table_description('test', $_data);print_r($_data);?>

</pre>
</body>
</html>
<?php

//phpinfo();
OWLloader::getClass('OWLrundown.php', OWL_ROOT);
//require_once (OWL_ROOT . '/OWLrundown.php');
?>