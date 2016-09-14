<?php
	require_once "core.php";
	global $db;

	$fields = array(
		"exchange",
		"name",
		"website",
		"DATE_FORMAT(open_time, '%l:%i %p') AS open_time",
		"DATE_FORMAT(close_time, '%l:%i %p') AS close_time",
		"(SELECT
			(
			    SELECT 'Open'
			    FROM stocks__ A, exchanges B
			    WHERE TIME(NOW()) BETWEEN
			    (
			        SELECT open_time
			        FROM exchanges
			        WHERE exchange = B.exchange
			    )
			    AND
			    (
			        SELECT close_time
			        FROM exchanges
			        WHERE exchange = B.exchange
			    )
				LIMIT 1
			)
		) AS state2",
		"(SELECT COALESCE(state2, 'Closed')) AS state"
	);

	$queryString = "
		SELECT " . implode(", ", $fields) . "
		FROM exchanges
	";

	$res = $db->query($queryString);

	if (isset($_GET["exchange"]) && !empty($_GET["exchange"])) {
		$exchange = str_clean($_GET["exchange"]);
		$queryString .= " WHERE exchange = '" . $exchange . "' LIMIT 1";
		$res = $db->query($queryString);

		if (!$res || $res->num_rows == 0) {
			errorVSX(
				"<h2>404 - This exchange cannot be found :(</h2>
				<p>It looks like there is no exchange matching '" . $exchange . "'. Click <a href='exchange.php'>here</a> to view all exchanges.</p>"
			);
			exit;
		}

		$exchData = $res->fetch_assoc();
		$exchange = $exchData["exchange"];

		?>
			<html>
				<title><?php echo $exchange; ?> - VSX</title>
				<body>
					<div class="container">
						<div class="row">
							<h2 class="text-center">
								<?php echo $exchange ?>  &mdash; <?php echo $exchData["name"]; ?>
								<br>
								<small>
									Opening hours: <?php echo $exchData["open_time"]; ?> &mdash; <?php echo $exchData["close_time"]; ?>
									<br>
									Currently: <span style="color: #<?php echo ($exchData["state"] == "Open") ? "15A838" : "FF0000"; ?>"><?php echo $exchData["state"]; ?></span>
								</small>
							</h2>
							<br>
							<h4 class="text-center">Stocks trading under this exchange:</h4>
							<br>
							<div class="col-md-4" style="float: none; margin: 0 auto;">
								<table class="table table-hover">
									<tr>
										<th>Stock</th>
										<th>Company Name</th>
									</tr>
									<?php
										$stocks = $db->query(
											"SELECT A.stock AS stock, company_name
											FROM stocks__ A, exchanges B
											WHERE A.exchange = B.exchange
											AND A.exchange = '" . $exchange . "'
											GROUP BY stock"
										);

										while ($row = $stocks->fetch_assoc()) {
											echo "<tr>
												<td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>" . $row["company_name"] . "</a></td>
											</tr>";
										}
									?>
								</table>
							</div>
						</div>
					</div>
				</body>
			</html>
		<?php
		exit;
	}

	?>
		<html>
			<title>Exchanges - VSX</title>
			<body>
				<div class="container">
					<div class="row">
						<h2 class="text-center">Exchanges</h2>
						<br>
						<table class="table table-hover">
							<tr>
								<th>Exchange</th>
								<th>Name</th>
								<th>Website</th>
								<th>Opening Time</th>
								<th>Closing Time</th>
								<th>Open</th>
							</tr>
							<?php
								while ($row = $res->fetch_assoc()) {
									$q = $db->query("
										SELECT 1 = 1
										FROM stocks__ A, exchanges B
										WHERE TIME(NOW()) BETWEEN
										(
											SELECT open_time
											FROM exchanges
											WHERE exchange = B.exchange
										)
										AND
										(
											SELECT close_time
											FROM exchanges
											WHERE exchange = B.exchange
										)
										LIMIT 1
									");
									$open = ($q->num_rows >= 1) ? true : false;
									echo "<tr>
										<td><a href='exchanges.php?exchange=" . $row["exchange"] . "'>" . $row["exchange"] . "</a></td>
										<td>" . $row["name"] . "</td>
										<td><a href='" . $row["website"] . "'>" . $row["website"] . "</a></td>
										<td>" . $row["open_time"] . "</td>
										<td>" . $row["close_time"] . "</td>
										<td class='" . (($open === true) ? "success" : "danger") . "'>" . (($open === true) ? "Yes" : "No") . "</td>
									</tr>";
								}
							?>
						</table>
					</div>
				</div>
			</body>
		</html>
