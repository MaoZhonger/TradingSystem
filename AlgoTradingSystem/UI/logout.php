<?php
setcookie("auth", "", time() - 3600);
header("Location: auth.php");
exit();
?>