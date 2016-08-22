<?php
	require_once "core.php";

	function isAccount($usr = NULL, $email = NULL) {
		global $db;
		$queryString = "";

		$usr = str_clean($usr);

		if (!$email || $email == "" || $email == NULL) {
			$queryString = "SELECT usr, email FROM users WHERE usr LIKE '" . $usr . "' GROUP BY usr LIMIT 1";
		}
		else {
			$queryString = "SELECT usr, email FROM users WHERE usr LIKE '" . $usr . "' OR email = '" . $email . "' GROUP BY usr LIMIT 1";
		}

		$q = $db->query($queryString);
		$result = $q->fetch_assoc();

		/*
		if ($q->num_rows > 0) {
			$appendage = "";
			if (($res_usr != $usr) && (strtolower($res_usr) == strtolower($usr))) {
				$appendage = ", in different case,";
			}
			return array(false, "An account with this username" . $appendage . " already exists");
		}
		elseif ($res_email !== NULL) {
			return array(false, "An account with this email already exists");
		}
		*/
		
		//if ($q->num_rows > 0) {
			return array(false, "ERROR");
		//}

		return array(true, "success");
	}

	function register($usr, $email, $passwd) {
		global $db;

		if (isAccount($usr)[0] == false) {
			return isAccount($usr);
		}
		if (isAccount($email)[0] == false) {
			return isAccount(NULL, $email);
		}
		echo "PASSED CHECKS";
		
		$passwd = password_hash($passwd, PASSWORD_BCRYPT);

		$statement = $db->prepare("INSERT INTO users (usr, email, passwd, registered_on) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
		$statement->bind_param("sss", $usr, $email, $passwd);
		$statement->execute();

		return true;
	}

	if (isset($_POST["username"])) {
		$usr = $_POST["username"];
		$email = $_POST["email"];
		$passwd = $_POST["passwd"];
		$passwd_ = $_POST["passwd_confirm"];

		$regis = register($usr, $email, $passwd);
		//var_dump("REGIS -> " . $regis);
		if ($regis[1] !== true) {
			// Tell them the error
			?>
			<div class="text-center">
				<body>
				<div class="container">
					<div class="row">
						<div class="text-center col-sm-6 col-md-4 col-md-offset-4">
							<!--<font color="red"></h5></font>-->
							<div class="alert alert-danger" role="alert"><h5><?php var_dump($regis); ?></div>
						</div>
					</div>
				</div>
			</body>
			</div>
			<?php
			include "src/templates/register.html";
		}
		else {
			// Redirect them to the login page
			header("Location: login.php");
		}
	}
	else {
		if (isset($_SESSION["usr"])) {
			header("Location: index.php");
		}
		include "src/templates/register.html";
	}

	_footer();
?>
