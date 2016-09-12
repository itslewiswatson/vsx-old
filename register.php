<?php
	require_once "core.php";

	function isAccount($usr = NULL, $email = NULL) {
		global $db;
		$queryString = "";

		$usr = str_clean($usr);

		if (!$email || $email == "" || $email == NULL) {
			$queryString = "SELECT usr, email FROM users WHERE usr = '" . $usr . "' OR LOWER(usr) = LOWER('" . $usr . "') GROUP BY usr LIMIT 1";
		}
		else {
			$queryString = "SELECT usr, email FROM users WHERE usr = '" . $usr . "' OR LOWER(usr) = LOWER('" . $usr . "') OR email = '" . $email . "' GROUP BY usr LIMIT 1";
		}

		$q = $db->query($queryString);
		$result = $q->fetch_assoc();

		if ($q->num_rows > 0) {
			if (strtolower($result["usr"]) == strtolower($usr)) {
				$appendage = "";
				if (($result["usr"] != $usr) && (strtolower($result["usr"]) == strtolower($usr))) {
					$appendage = ", in different case,";
				}
				return array(false, "An account with this username" . $appendage . " already exists");
			}
			elseif ($result["email"] == $email) {
				return array(false, "An account with this email already exists");
			}
			else {
				return array(false, "An unknown error occured");
			}
		}

		return array(true, "");
	}

	function register($usr, $email, $passwd) {
		global $db;

		if (isAccount($usr)[0] === false) {
			return isAccount($usr)[1];
		}
		if (isAccount(NULL, $email)[0] === false) {
			return isAccount(NULL, $email)[1];
		}

		$passwd = password_hash($passwd, PASSWORD_BCRYPT);

		$statement = $db->prepare("INSERT INTO users (usr, email, passwd, registered_on, ip) VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)");
		$statement->bind_param("ssss", $usr, $email, $passwd, $_SERVER["REMOTE_ADDR"]);
		$statement->execute();

		return true;
	}

	if (isset($_POST["username"])) {
		$usr = $_POST["username"];
		$usr = str_clean($usr);
		$email = $_POST["email"];
		$passwd = $_POST["passwd"];
		$passwd_ = $_POST["passwd_confirm"];

		$regis = register($usr, $email, $passwd);
		//var_dump("REGIS -> " . $regis);
		if ($regis !== true) {
			// Tell them the error
			?>
			<div class="text-center">
				<body>
				<div class="container">
					<div class="row">
						<div class="text-center col-sm-6 col-md-4 col-md-offset-4">
							<!--<font color="red"></h5></font>-->
							<div class="alert alert-danger" role="alert"><h5><?php echo($regis); ?></div>
						</div>
					</div>
				</div>
			</body>
			</div>
			<?php
			include "src/templates/register.html";
		}
		else {
			// Redirect them to the index page, and log them in
			$_SESSION["usr"] = $usr;
			header("Location: index.php");
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
