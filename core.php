<?php
	global $db;
	$db = new mysqli("localhost", "root", "", "vsx");
	if (!$db) {
		die("Failed to connect to the database");
	}
?>
