<?php
/**
 * \file
 * This is the entry point for OWL-PHP teststub
 * \version $Id: index.php,v 1.2 2008-08-22 12:02:13 oscar Exp $
 */

define ('OWL_ROOT', '/home/oscar/work/eclipse/owl-php/src');
require_once (OWL_ROOT . '/OWLloader.php');

//echo '<pre>';
//print_r ($GLOBALS['register']);
//echo '</pre>';

// Testcases :-)
?>
<html>
<head>
<title>OWL-PHP</title>
</head>
<body>
Hello World<br />
<?php
if (!array_key_exists('c', $_SESSION)) {
	$_SESSION['c'] = 1;
} else {
	$_SESSION['c']++;
}
?>
You've been here <?php echo $_SESSION['c']; ?> times.<br />
<a href="<?php echo ($PHP_SELF); ?>">Continue</a>
</body>
</html>
<?php

phpinfo();
?>
