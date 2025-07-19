<?php
$motdepasse = 'test123';
$hash = password_hash($motdepasse, PASSWORD_DEFAULT);
echo $hash;
?>
