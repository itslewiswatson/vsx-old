<?php
	require "core.php";
	
	function isAccount($usr, $email) {
		global $db;
		$queryString = "";
		
		// If there is no email (emails are optional, for now)
		if (!$email || $email == "" || $email == NULL) {
			$queryString = "SELECT usr, email FROM accounts WHERE usr = '" . $usr . "'";
		}
		else {
			$queryString = "SELECT usr, email FROM accounts WHERE usr = '" . $usr . "' OR email = '" . $email . "'";
		}
		
		$q = $db->query($queryString);
		$result = $q->fetch_assoc();
		$res_usr = $result["usr"];
		$res_email = $result["email"];
		
		if ($res_usr == NULL) {
			return array(true, "An account with this username already exists");
		}
		elseif ($res_email == NULL) {
			return array(true, "An account with this email already exists");
		}
		
		return array(false, "");
	}
?>
