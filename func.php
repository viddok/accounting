<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 04.01.19
 * Time: 6:26
 */

function __autoload ($classname) {
	$classname = str_replace('\\', '/', $classname);
	require_once (__DIR__ . "/$classname.php");
}