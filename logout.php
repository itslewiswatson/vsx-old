<?php
	require_once "core.php";
	ob_start();

	if ($_SESSION && isset($_SESSION["usr"])) {
		unset($_SESSION["usr"]);
		header("Location: index.php");
	}
	else {
		header("Location: index.php");
	}
	buttons();
	ob_end_flush();
?>
