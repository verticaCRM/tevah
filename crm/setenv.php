<?php
list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
header("PHP_AUTH_USER:".$_SERVER['PHP_AUTH_USER']);
header("PHP_AUTH_PW:".$_SERVER['PHP_AUTH_PW']);
//print_r($_SERVER);
//die();
?>