<?php
	include "/src/templates/header.html";
	require "core.php";
	
	function authenticate($usr, $pw) {
		if ($usr && $pw) {
			global $db;
			
			// Check if the username is in fact an email
			// If it is, we find their username first
			if (strpos($usr, "@") !== false) {
				$q = $db->query("SELECT usr FROM accounts WHERE email = '" . $usr . "'");
				$usr = $q->fetch_assoc()["usr"];
				
				if (!$usr) {
					// Cannot find a user with that email
					return array(false, "There are no users with this email");
				}
			}
						
			// The actual queries
			$q = $db->query("SELECT usr, passwd FROM accounts WHERE usr = '" . $usr . "' AND passwd = '" . $pw ."'");
			$result = $q->fetch_assoc();
			
			if ($q->num_rows == 0) {
				return array(false, ":(");
			}
			
			if ($result["usr"] && $result["passwd"]) {
				if ($result["passwd"] == $pw) {
					// Match of password, log in
					return array(true, $usr);
				}
				else {
					// This password doesn't match
					return array(false, "The password you provided is incorrect");
				}
			}
			else {
				if (!$result["usr"]) {
					// There is no user with that name
					return array(false, "There are no users matching that username");
				}
				if (!$result["passwd"]) {
					// The user didn't put in a password
					return array(false, "You must enter a password");
				}
			}
		}
		return array(false, "Please enter your credentials");
	}
	
	function logIn($usr) {
		$_SESSION["usr"] = $usr;
	}
	
	//include "/src/templates/login.html";
	
	if (isset($_POST["email"])) {
		$email = $_POST["email"];
		$passwd = $_POST["passwd"];
		
		$state = authenticate($email, $passwd);
		
		if ($state[0] === true) {
			logIn($state[1]); // Instead of an error message, it returns the user name
			header("Location: index.php");
		}
		else {
			// Forgive me for this
			?>
			<!--<body>
				<div class="row">-->
					<div class="text-center">
						<font color="red"><h5><?php echo $state[1]; ?></h5></font>
						<hr>
					</div>
				<!--</div>
			</body>-->
			<?php
			include "/src/templates/login.html";
		}
	}
	else {
		include "/src/templates/login.html";
	}
	
	include "/src/templates/footer.html";
?>
