<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.9 2010-10-04 17:40:40 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/projects/owl-php/src');
require_once (OWL_ROOT . '/OWLloader.php');

DBG_dumpval ($GLOBALS['config']);
DBG_dumpval ($GLOBALS['register']);
DBG_dumpval ($_SESSION);

$_form = OWL::factory('FormHandler');
DBG_dumpval ($_form);

$_user =& new User();

switch ($_form->act) {
	case 'login':
		if (!$_user->login($_form->usr, $_form->pwd)) {
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

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table border=0>
	<tr>
		<td>Username:</td>
		<td><input type="text" name="usr" id="usr" width="20"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="pwd" id="pwd" width="20"></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="login" name="act"></td>
	</tr>
</table>
</form>
</body>
</html>
<?php

//phpinfo();
require_once (OWL_ROOT . '/OWLrundown.php');
?>
