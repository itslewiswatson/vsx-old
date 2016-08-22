<?php
	ini_set('display_startup_errors', 1);
	ini_set('display_errors', 1);
	error_reporting(-1);

	session_start();

	ob_start();
	include "src/templates/header.html";

	global $db;
	$db = new mysqli("localhost", "root", "", "vsx");
	if (!$db) {
		die("Failed to connect to the database");
	}

	function updateUser() {
		if (isset($_SESSION["usr"])) {
			global $db;
			$usr = $_SESSION["usr"];
			$statement = $db->prepare("UPDATE users SET ip = ?, last_visited = CURRENT_TIMESTAMP() WHERE usr = ?");
			$statement->bind_param("ss", $_SERVER["REMOTE_ADDR"], $usr);
			$statement->execute();
		}
	}
	updateUser();

	function _header() {
		return;
	}
	function _footer() {
		return;
	}

	function buttons() {
		?>
		<script type="text/javascript">
			function footer() {
				document.body.insertAdjacentHTML('beforeend', (<?php echo ("'" . str_replace("<br>", "", str_replace("\r", "", str_replace("\n", "", file_get_contents("src/templates/footer.html", true)))) . "'"); ?>));
			}

			window.onload = function () {
				document.getElementById("right-navbar").innerHTML = "";
				var u = "" + String(<?php isset($_SESSION["usr"]) ? $s = "'" . $_SESSION["usr"] . "'" : $s = "'N/A'"; echo strval($s); ?>) + "";
				var loggedIn = <?php isset($_SESSION["usr"]) ? $s = 1 : $s = 0; echo $s; ?>;
				var list = "";
				if (loggedIn == 0) {
					list += '<li id="register_btn"><a href="register.php">Register</a></li>';
					list += '<li id="login_btn"><a href="login.php">Login</a></li>';
				}
				else if (loggedIn == 1) {
					list += '<li id="logged_in_btn" class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">' + "" + u + "" + '<span class="caret"></span></a><ul class="dropdown-menu" role="menu"><li><a href="profile.php">Profile</a></li><li><a href="#">Settings</a></li><li class="divider"></li><li><a href="logout.php">Logout</a></li></ul></li>';
				}
				document.getElementById("right-navbar").innerHTML = list;
				footer();
			}
		</script>
		<?php
	}
	buttons();

	function str_clean($string) {
		$string = str_replace('  ', ' ', $string);
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}

	function numerise($string) {
		return preg_replace("/[^0-9,.]/", "", $string);
	}

	function isLoggedIn() {
		return isset($_SESSION["usr"]) ? true : false;
	}

	function errorVSX($var, $width = 70) {
		?>
			<link rel="stylesheet" type="text/css" href="src/css/custom.css"/>
			<body>
				<div class="container-fluid">
					<div class="row">
						<div style="text-align: center; width: <?php echo $width; ?>%; margin: 0 auto;">
							<div class="alert alert-danger text-center errorVSX" role="alert"><?php echo $var; ?></div>
						</div>
					</div>
				</div>
			</body>
		<?php
	}

	ob_end_flush();
?>
