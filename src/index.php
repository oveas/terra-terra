<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.7 2009-03-23 20:14:59 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/projects/Oveas/owl-php/src');
require_once (OWL_ROOT . '/OWLloader.php');

DBG_dumpval ($GLOBALS['config']);
DBG_dumpval ($GLOBALS['form']);
DBG_dumpval ($GLOBALS['register']);
DBG_dumpval ($_SESSION);

if ($GLOBALS['formdata']->a == 'logout') {
	$GLOBALS['user']->logout();
}

if ($GLOBALS['formdata']->u !== null && $GLOBALS['formdata']->p) {
	if (!$GLOBALS['user']->login($GLOBALS['formdata']->u, $GLOBALS['formdata']->p)) {
		$GLOBALS['user']->signal();
	}
}


// Testcases :-)
?>
<html>
<head>
<title>OWL-PHP</title>
</head>
<body>
Hello <?php echo ($GLOBALS['user']->get_username()); ?> (<?php echo ($GLOBALS['user']->user_data['email']); ?>)<br />
<?php
if (!array_key_exists('c', $_SESSION)) {
	$_SESSION['c'] = 1;
} else {
	$_SESSION['c']++;
}
?>
You've been here <?php echo $_SESSION['c']; ?> times.<br />
<a href="<?php echo ($_SERVER['PHP_SELF']); ?>">Continue</a><br />
<a href="<?php echo ($_SERVER['PHP_SELF']); ?>?a=logout">Logout</a><br />

</body>
</html>
<?php

//phpinfo();
require_once (OWL_ROOT . '/OWLrundown.php');
?>
