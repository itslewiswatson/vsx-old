<?php
	require_once "core.php";
	global $db;
	
	$fields = array(
		"exchange",
		"DATE_FORMAT(open_time, '%l:%i %p') AS open_time",
		"DATE_FORMAT(close_time, '%l:%i %p') AS close_time"
	);
	
	$queryString = "
		SELECT " . implode(", ", $fields) . "
		FROM exchanges
	";
	
	$res = $db->query($queryString);
	
	if (isset($_GET["exchange"])) {
		$exchange = str_clean($_GET["exchange"]);
		$queryString .= " WHERE exchange = '" . $exchange . "'";
		$res = $db->query($queryString);
		exit;
	}
	
	?>
		<html>
			<title>Exchanges - VSX</title>
			<div class="container">	
				while ($row = $res->fetch_assoc()) {
				}
			</div>
		</html>
