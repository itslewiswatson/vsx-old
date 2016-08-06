<?php
	//include "/src/templates/header.html";
	require "core.php";
	_header();
		
	function isAccount($usr = NULL, $email = NULL) {
		global $db;
		$queryString = "";
		
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
		
		if ($res_usr !== NULL) {
			return array(true, "An account with this username already exists");
		}
		elseif ($res_email !== NULL) {
			return array(true, "An account with this email already exists");
		}
		
		return array(false, "");
	}
	
	function register($usr, $email, $passwd) {
		global $db;
		
		if (isAccount($usr)[1] === false) {
			return isAccount($usr)[1];
		}
		if (isAccount($email)[1] === false) {
			return isAccount(NULL, $email)[1];
		}
		
		$passwd = password_hash($passwd, PASSWORD_BCRYPT);
		
		$statement = $db->prepare("INSERT INTO accounts (usr, email, passwd) VALUES (?, ?, ?)");
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
			include "/src/templates/register.html";
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
		include "/src/templates/register.html";
	}
	
	_footer();
?>
