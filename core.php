<?php
	global $db;
	$db = new mysqli("");
	if (!$db) {
		die("Failed to connect to the database");
	}
?>
