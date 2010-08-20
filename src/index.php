<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.8 2010-08-20 08:39:54 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/projects/owl-php/src');
require_once (OWL_ROOT . '/OWLloader.php');

DBG_dumpval ($GLOBALS['config']);
DBG_dumpval ($GLOBALS['form']);
DBG_dumpval ($GLOBALS['register']);
DBG_dumpval ($_SESSION);
//		echo $GLOBALS['formdata']->act;
switch ($GLOBALS['formdata']->act) {
	case 'login':
		if (!$GLOBALS['user']->login($GLOBALS['formdata']->usr, $GLOBALS['formdata']->pwd)) {
			$GLOBALS['user']->signal();
		}
		break;
	case 'logout':
		$GLOBALS['user']->logout();
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
Hello <?php echo ($GLOBALS['user']->get_username()); ?> (<?php echo ($GLOBALS['user']->user_data['email']); ?>)<br />
<?php $GLOBALS['user']->set_session_var('c', 1, SESSIONVAR_INCR); ?>
You've been here <?php echo $GLOBALS['user']->get_session_var('c', '?'); ?> times.<br />
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
