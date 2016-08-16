<?php
	require_once "core.php";

	function isAccount($usr = NULL, $email = NULL) {
		global $db;
		$queryString = "";

		$usr = str_clean($usr);

		if (!$email || $email == "" || $email == NULL) {
			$queryString = "SELECT usr, email FROM users WHERE usr LIKE '" . $usr . "' OR LOWER(usr) LIKE '" . $usr . "' GROUP BY usr";
		}
		else {
			$queryString = "SELECT usr, email FROM users WHERE usr LIKE '" . $usr . "' OR email = '" . $email . "' GROUP BY usr";
		}

		$q = $db->query($queryString);
		$result = $q->fetch_assoc();
		$res_usr = $result["usr"];
		$res_email = $result["email"];

		if ($res_usr !== NULL) {
			$appendage = "";
			if ($res_usr != $usr && strtolower($res_usr) == strtolower($usr)) {
				$appendage = ", in different case,";
			}
			return array(false, "An account with this username" . $appendage . " already exists");
		}
		elseif ($res_email !== NULL) {
			return array(false, "An account with this email already exists");
		}

		return array(true, "");
	}

	function register($usr, $email, $passwd) {
		global $db;

		if (isAccount($usr)[1] == false) {
			return isAccount($usr)[1];
		}
		if (isAccount($email)[1] == false) {
			return isAccount(NULL, $email)[1];
		}

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
		if ($regis !== true) {
			// Tell them the error
			?>
			<div class="text-center">
				<font color="red"><h5><?php var_dump($regis); ?></h5></font>
				<hr>
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
