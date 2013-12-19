<?php
define ('OWL_ROOT', '/home/oscar/projects/owl-php/src');
require (OWL_ROOT . '/OWLloader.php');
OWLloader::loadApplication('OWL');
?>
<html>
<head>
<title>OWL-PHP</title>
</head>
<body>
Welcome at the OWL-PHP mainpage.<br/>
OWL-PHP itself won't do much; this is all there is. For more information refer to the OWL-PHP documentation and to the OWL applications installed on this server.
</body>
</html>
<?php
//phpinfo();
OWLloader::getClass('OWLrundown.php', OWL_ROOT);
?>
