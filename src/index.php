<?php
define ('TT_ROOT', '/home/oscar/projects/terra-terra/src');
require (TT_ROOT . '/TTloader.php');
TTloader::loadApplication('TT');
?>
<html>
<head>
<title>Terra-Terra</title>
</head>
<body>
Welcome at the Terra-Terra mainpage.<br/>
Terra-Terra itself won't do much; this is all there is. For more information refer to the Terra-Terra documentation and to the TT applications installed on this server.
</body>
</html>
<?php
//phpinfo();
TTloader::getClass('TTrundown.php', TT_ROOT);
?>
