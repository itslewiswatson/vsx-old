<?php
	include "src/templates/header.html";
	require "core.php";
	
	if ($_SESSION && isset($_SESSION["usr"])) {
		unset($_SESSION["usr"]);
		handleButtons();
	}
	else {
		header("Location: index.php");
	}
	
	include "src/templates/footer.html";
?>