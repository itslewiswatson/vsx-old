<?php
	$db = new mysqli("");
	if (!$db) {
		die("No MySQL :(((((");
	}
	
	session_start(); 
	
	//if (!isset($_SESSION["usr"])) {
	// take usr to login screen
	//}
	
	$usr = $_POST["login.usr"];
	$pw = $_POST["login.pw"];
	
	//function  
	
	function logIn($usr, $pw) {
		if ($usr && $pw) {
			// Check if the username is in fact an email
			if (strpos($usr, "@") !== false) {
				$q = $db->query("SELECT usr FROM accounts WHERE email = '" . $usr . "'");
				$usr = $q->fetch_assoc()["usr"];
				
				if (!$usr) {
					// Cannot find a user with that email
					return false;
				}
			}
			
			// The actual queries
			$q = $db->query("SELECT usr, pw FROM accounts WHERE usr = '" . $usr . "' AND pw = '" . $pw ."'");
			$result = $q->fetch_assoc();
			
			if ($result["usr"] && $result["pw"]) {
				if ($result["pw"] == $pw) {
					// Match of password, log in
				}
				else {
					// This password doesn't match
					return false;
				}
			}
			else {
				if (!$result["usr"]) {
					// There is no user with that name
					return false;
				}
				if (!$result["pw"]) {
					// The user didn't put in a password
					return false;
				}
			}
		}
		else {
			return false;
		}
	}
	 
	if (logIn($usr, $pw) == true) {
	// begin session
	}
	
	// assume they are logged in
	 
?>
