<?php
	session_start();
	
	global $db;
	$db = new mysqli("localhost", "root", "", "vsx");
	if (!$db) {
		die("Failed to connect to the database");
	}
	
	function handleButtons() {
		if ($_SESSION && isset($_SESSION["usr"])) {
			$current_usr = $_SESSION["usr"];
			?>
			<script type="text/javascript">
				function lol() {
					var l = document.getElementById("login_btn");
					var r = document.getElementById("register_btn");
					var x = document.getElementById("logged_in_btn");
					var e = "<?php echo $current_usr; ?>";
					
					r.remove();
					l.remove();
					x.children[0].innerHTML = "Welcome, " + e + "<span class='caret'></span>";
				}
				window.onload = lol;
			</script>
			<?php
		}
		else {
			?>
			<script type="text/javascript">
				function lol() {var l = document.getElementById("logged_in_btn"); l.remove();}
				window.onload = lol;
			</script>
			<?php
		}
	}
	handleButtons();
?>
