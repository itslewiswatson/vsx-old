<?php
	require "core.php";
	ob_start();

	function authenticate($usr, $pw) {
		if ($usr && $pw) {
			global $db;

			// Check if the username is in fact an email
			// If it is, we find their username first
			if (strpos($usr, "@") !== false) {
				$q = $db->query("SELECT usr FROM users WHERE email = '" . $usr . "'");
				if (!$q || $q->num_rows == 0) {
					return array(false, "There are no users with this email");
				}
			}

			// The actual queries
			$q = $db->query("SELECT usr, passwd FROM users WHERE usr = '" . $usr . "'");
			$result = $q->fetch_assoc();

			if ($q->num_rows == 0) {
				return array(false, ":(");
			}

			if ($result["usr"] && $result["passwd"]) {
				if (password_verify($pw, $result["passwd"])) {
					// Match of password, log in
					return array(true, $usr);
				}
				// This password doesn't match
				return array(false, "The password you provided is incorrect");
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

	if (isset($_POST["email"])) {
		$email = $_POST["email"];
		$passwd = $_POST["passwd"];

		$state = authenticate($email, $passwd);

		if ($state[0] === true) {
			logIn($state[1]); // Instead of an error message, it returns the user name
			ob_end_flush();
			header("Location: index.php");
			exit;
		}
		else {
			// Forgive me for this
			?>
			<body>
				<div class="container">
					<div class="row">
						<div class="text-center col-sm-6 col-md-4 col-md-offset-4">
							<!--<font color="red"></h5></font>-->
							<div class="alert alert-danger" role="alert"><h5><?php echo $state[1]; ?></div>
						</div>
					</div>
				</div>
			</body>
			<?php
			include "src/templates/login.html";
		}
	}
	else {
		if (isset($_SESSION["usr"])) {
			ob_end_flush();
			header("Location: index.php");
		}
		include "src/templates/login.html";
	}
?>
