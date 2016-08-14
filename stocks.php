<?php
	require "core.php";
	global $db;
	$fields = array(
		"stocks__.stock AS stock",
		"company_name",
		"company_logo",
		"company_bio",
		"DATE_FORMAT(MAX(timing), '%H:%i:%S %d-%m-%Y') AS last_updated"
	);

	if (isset($_GET["stock"])) {
		$stock = $_GET["stock"];
		$stock = str_clean($_GET["stock"]);

		$res = $db->query("SELECT " . implode(", ", $fields) . " FROM stocks__, stocks__history	WHERE stocks__.stock = stocks__history.stock AND stocks__.stock = '" . $stock . "'");
		$stockData = $res->fetch_assoc();

		// If we cannot find anything
		if ($res->num_rows == 0) {
			?>
			<title>Not found (404) - VSX</title>
			<body>
				<div class="container-fluid">
					<div class="row">
						<div class="text-center">
							<h1>404 - This stock cannot be found :(</h1>
							<p>It looks like there is no stock matching '<?php echo $stock; ?>'. Click <a href="stocks.php">here</a> to view all stocks.</p>
						</div>
					</div>
				</div>
			</body>
			<?php
			return;
		}

		// Display everything about the company
		?>
		<html>
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
			<script type="text/javascript">
				google.charts.load('current', {packages: ['corechart', 'line']});
				google.charts.setOnLoadCallback(drawLogScales);
				google.charts.setOnLoadCallback(drawCrosshairs);

				function drawLogScales() {
				  	var data = new google.visualization.DataTable();
				  	data.addColumn('datetime', 'X');
				  	data.addColumn('number', 'Price');

					data.addRows([
						<?php
							$rsq = $db->query(
								"SELECT UNIX_TIMESTAMP(timing) AS timing2, price
								FROM stocks__history
								WHERE stock = '" . $stock . "'
								ORDER BY timing2 ASC
								LIMIT 100"
							);
							while ($row = $rsq->fetch_assoc()) {
								// JavaScript works in milliseconds instead of normal seconds.
								echo "[new Date(" . $row["timing2"] * 1000 . "), " . $row["price"] . "],\n";
							}
						?>
						/*
						[new Date(2000, 8, 5), 0],
						[new Date(2000, 8, 6), 45],
						[new Date(2000, 8, 7), 32],
						*/
			      	]);

				  	var options = {
						hAxis: {
					  		title: 'Time',
					  		logScale: false
						},
						vAxis: {
					  		title: 'Price',
					  		logScale: false,
							format: "currency"
						},
						legend: {position: "none"}
				  	};

				  	var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
				  	chart.draw(data, options);
				}
			</script>
			<link rel="stylesheet" type="text/css" href="src/css/custom.css"/>
			<title><?php echo $stockData["company_name"] . " (" . $stock . ")"; ?> - VSX</title>
			<body>
				<!--
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-6 col-md-offset-3">
							<h1 class="text-center"><?php echo $stockData["company_name"] . " (" . $stock . ")"; ?></h1>
						</div>
					</div>
					<hr>
				</div>
				-->
				<div class="container">
					<div class="row">
						<div class="col-md-4 avatar-display">
							<div class="thumbnail profile">
								<img src=<?php echo $stockData["company_logo"]; ?>>
								<hr>
								<div style="padding-right: 10px; padding-left: 10px">
									<h3><?php echo $stockData["stock"]; ?></h3>
									<h5><?php echo $stockData["company_name"]; ?></h3>
									<p><?php echo $stockData["company_bio"]; ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-8">
							<h3 class="text-center">Recent stock prices of <?php echo $stockData["stock"]; ?></h3>
							<div id="chart_div">
								<!-- Blank div for the graph -->
							</div>
							<hr>
							<p class="text-center">You currently own <strong>$shares</strong> (%) of <strong>$stock</strong>, valued at <strong>$amount</strong></p>
							<hr>
							<!-- Hidden form to retain current GET stock parameter -->
							<div class="col-md-6 text-center">
								<form method="post" action="stocks.php?stock=<?php echo $stockData["stock"]; ?>">
									<div class="form-group">
										<div class="input-group col-md-8 col-md-offset-2">
											<!--<div class="input-group-addon">$</div>-->
											<input type="text" class="form-control" placeholder="Quantity">
										</div>
									</div>
									<p id="buy-text">This will cost you <strong>$15,205.23</strong></p>
									<button type="submit" class="btn btn-primary">Buy Shares</button>
								</form>
							</div>
							<div class="col-md-6 text-center">
								<form method="post" action="stocks.php?stock=<?php echo $stockData["stock"]; ?>">
									<div class="form-group">
										<div class="input-group col-md-8 col-md-offset-2">
											<!--<div class="input-group-addon">$</div>-->
											<input type="text" class="form-control" placeholder="Quantity">
										</div>
									</div>
									<p id="sell-text">You will gain <strong>$15,205.23</strong></p>
									<button type="submit" class="btn btn-primary">Sell Shares</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</body>
		</html>
		<?php
		return;
	}

	$view = "list";
	if ($_GET && $_GET["view"] && isset($_GET["view"])) {
		if (strtolower($_GET["view"]) == "grid") {
			$view = "grid";
		}
		// List otherwise
	}

	$res = $db->query("SELECT " . implode(", ", $fields) . " FROM stocks__, stocks__history	WHERE stocks__.stock = stocks__history.stock GROUP BY stocks__.stock");

	function drawStocks($v = "list") {
		global $res;
		if ($v == "grid") {
			$i = 0;
			echo "<div class='row'>";

			while ($row = $res->fetch_assoc()) {
				stockGrid($row);
				$i++;

				if (($i % 3) == 0) {
					echo "</div>";
					echo "<hr>";
					if ($res->num_rows > $i) {
						echo "<br><div class='row'>";
					}
				}
			}
		}
		else {
			?>
			<html>
				<body>
					<div class="container">
						<div class="row">
							<table class="table table-hover">
								<tr>
									<th>Stock</th>
									<th>Company Name</th>
									<th>Current Price</th>
									<th>Previous Price</th>
									<th>Difference</th>
									<th>Last Updated</th>
									<th>Volume</th>
								</tr>
								<?php
									while ($row = $res->fetch_assoc()) {
										global $db;

										// Current price
										$CP = $db->query(
											"SELECT price AS current_price
											FROM stocks__history
											WHERE stock = '" . $row["stock"] . "'
											AND timing =
												(SELECT MAX(timing)
												FROM stocks__history
												WHERE stock = '" . $row["stock"] . "'
												)
											GROUP BY stock"
										);
										$current_price = $CP->fetch_assoc()["current_price"];

										// Previous price
										$PP = $db->query(
											"SELECT price AS previous_price
											FROM stocks__history
											WHERE stock = '" . $row["stock"] . "'
											AND timing =
												(SELECT MAX(timing)
												FROM stocks__history
												WHERE stock = '" . $row["stock"] . "'
												AND timing <
													(SELECT MAX(timing)
													FROM stocks__history
													WHERE stock = '" . $row["stock"] . "'
													)
												)
											GROUP BY stock"
										);
										$previous_price = $PP->fetch_assoc()["previous_price"];

										// Volume traded today
										$V = $db->query(
											"SELECT qty
											FROM stocks__transactions
											WHERE stock = '" . $row["stock"] . "'
											AND DATE(timing) = CURDATE()"
										);
										$vol = $V->fetch_assoc()["qty"];
										$vol = $vol != NULL ? $vol : "0"; // Accounting for possible cases

										$diff = $current_price - $previous_price;
										$percentage = round(($diff / $previous_price) * 100, 2);
										if ($diff > 0) {
											$diff = "+" . $diff;
											$colour = "success";
										}
										elseif ($diff < 0) {
											$diff = "-" . abs($diff);
											$colour = "danger";
										}
										else {
											$diff = "0";
											$colour = "warning";
										}

										echo "<tr>
											<td><a href='stocks.php?stock=" . $row["stock"] ."'>" . $row["stock"] . "</a></td>
											<td>" . $row["company_name"] . "</td>
											<td>" . "$" . number_format($current_price, 2) . "</td>
											<td>" . "$" . number_format($previous_price, 2) . "</td>
											<td class='" . $colour . "'>" . $percentage . "% (" . $diff . ")</td>
											<td>" . $row["last_updated"] . "</td>
											<td>" . $vol . "</td>
										</tr>";
									}
								?>
						</table>
						</div>
					</div>
				</body>
			</html>
			<?php
		}
	}

	function stockGrid($row) {
		?>
		<div class="text-center col-md-4">
			<p><?php echo "<b>" . $row["stock"] . "</b><br>"; echo $row["company_name"]; ?></p>
			<a href="stocks.php?stock=<?php echo $row["stock"]; ?>"><img src="<?php echo $row["company_logo"]; ?>"></a>
		</div>
		<?php
	}

	?>
	<style>
		img {
			max-width: 50%;
			max-height: 50%;
		}
	</style>
	<title>Stocks - VSX</title>
	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<h1 class="text-center">Stocks</h1>
				</div>
				<div class="btn-group" role="group">
			        <button type="button" class="btn btn-default" onclick="document.location = 'stocks.php?view=list';">List</button>
			        <button type="button" class="btn btn-default" onclick="document.location = 'stocks.php?view=grid';">Grid</button>
			    </div>
			</div>
			<hr>
		</div>
		<div class="container">
			<?php
				drawStocks($view);
			?>
		</div>
	</body>
