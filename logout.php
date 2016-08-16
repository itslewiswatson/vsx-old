<?php
	require_once "core.php";
	ob_start();
	_header();

	if ($_SESSION && isset($_SESSION["usr"])) {
		unset($_SESSION["usr"]);
	}
	else {
		header("Location: index.php");
		ob_end_flush();
		exit;
	}

	buttons();

	_footer();
	ob_end_flush();
?>
