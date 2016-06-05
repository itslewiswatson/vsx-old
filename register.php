<?php
	require "core.php";
	
	function register($usr, $pw, $email) {
		$q = $db->query("SELECT COUNT(*) FROM accounts WHERE usr = '" . $usr . "' OR email = '" . $email . "');
		$result = $q->num_rows
		if ($result > 0) {
			return "An account with this username or email already exists";
		}
		return true;
	}
?>
