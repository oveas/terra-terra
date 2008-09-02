<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.4 2008-09-02 05:16:53 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/work/eclipse/owl-php/src');
require_once (OWL_ROOT . '/OWLloader.php');

//echo '<pre>';
//print_r ($GLOBALS['config']);
//echo '</pre>';

//echo '<pre>';
//print_r ($GLOBALS['form']);
//echo '</pre>';

//echo '<pre>';
//print_r ($GLOBALS['register']);
//echo '</pre>';

//echo '<pre>';
//print_r ($_SESSION);
//echo '</pre>';

if ($GLOBALS['formdata']->a == 'logout') {
	$GLOBALS['user']->logout();
}
// Testcases :-)
?>
<html>
<head>
<title>OWL-PHP</title>
</head>
<body>
Hello <?php echo ($GLOBALS['user']->get_username()); ?><br />
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
