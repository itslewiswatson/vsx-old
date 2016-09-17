<?php
	require_once "core.php";
	require_once "stocks_util.php";
	global $db;
	$fields = array(
		"stocks__.stock AS stock",
		"company_name",
		"company_logo",
		"company_bio",
		"DATE_FORMAT(MAX(timing), '%H:%i:%S %d-%m-%Y') AS last_updated",
		"company_website",
		"exchange"
	);

	if (isset($_GET["stock"]) && !empty($_GET["stock"])) {
		$stock = $_GET["stock"];
		$stock = str_clean($_GET["stock"]);
		$stock = strtoupper($stock);

		$res = $db->query(
			"SELECT " . implode(", ", $fields) . "
			FROM stocks__, stocks__history
			WHERE stocks__.stock = stocks__history.stock
			AND stocks__.stock = '" . $stock . "'"
		);
		$stockData = $res->fetch_assoc();

		// If we cannot find anything
		if (!$res || $res->num_rows == 0 || !$stockData["stock"]) {
			?>
				<title>Not found (404) - VSX</title>
				<body>
					<div class="container-fluid">
						<div class="row">
							<div class="text-center">
								<?php
									errorVSX("
										<h2>404 - This stock cannot be found :(</h2>
										<p>It looks like there is no stock matching '" . $stock . "'. Click <a href='stocks.php'>here</a> to view all stocks.</p>"
									);
								?>
							</div>
						</div>
					</div>
				</body>
			<?php
			exit;
		}

		$oc = getStockOpenClose($stockData["stock"]);
		$open = $oc[0];
		$close = $oc[1];

		// Check for timing stuff
		$timing = $db->query(
			"SELECT open_time, close_time
			FROM stocks__ A, exchanges B
			WHERE A.exchange = B.exchange
			AND TIME(NOW()) BETWEEN
				'" . $open . "'
			AND
				'" . $close . "'
			AND stock = '" . $stockData["stock"] . "'
			LIMIT 1"
		);

		// Display everything about the company
		?>
		<!DOCTYPE html>
		<html>
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
			<script type="text/javascript">
				google.charts.load('current', {packages: ['corechart', 'line']});
				google.charts.setOnLoadCallback(drawLogScales);

				function drawLogScales() {
				  	var data = new google.visualization.DataTable();
				  	data.addColumn('datetime', 'X');
				  	data.addColumn('number', 'Price');

					data.addRows([
						<?php
							$rsq = $db->query(
								"SELECT UNIX_TIMESTAMP(timing) * 1000 AS timing2, price
								FROM stocks__history
								WHERE stock = '" . $stock . "'
								ORDER BY timing2 ASC
								LIMIT 100"
							);
							while ($row = $rsq->fetch_assoc()) {
								// JavaScript works in milliseconds instead of normal seconds.
								echo "[new Date(" . $row["timing2"] . "), {v: " . $row["price"] . ", f: '$" . number_format($row["price"], 2) . "'}],\n";
							}
						?>
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
				<div class="container">
					<div class="row">
						<div class="col-md-4 avatar-display">
							<div class="thumbnail profile">
                                <!-- Company profile -->
								<img src=<?php echo $stockData["company_logo"]; ?>>
								<hr>
								<div style="padding-right: 10px; padding-left: 10px;">
									<h3><?php echo $stockData["stock"]; ?> <small>(<a href="exchanges.php?exchange=<?php echo getStockExchange($stockData["stock"]); ?>"><?php echo getStockExchange($stockData["stock"]); ?></a>)</small></h3>
									<h4><?php echo $stockData["company_name"]; ?></h4>
									<p><a href="<?php echo $stockData["company_website"]; ?>"><?php echo $stockData["company_website"]; ?></a></p>
									<p><?php echo $stockData["company_bio"]; ?></p>
								</div>
                                <hr>
                                <!-- Stock statistics -->
                                <div style="padding-right: 10px; padding-left: 10px;">
                                    <?php
                                        $shareholders = $db->query("SELECT COALESCE(COUNT(*), 0) AS count FROM stocks__holders WHERE amount > 0 AND stock = '" . $stockData["stock"] . "' LIMIT 1")->fetch_assoc()["count"];
    							    ?>
                                    <p><strong>Shareholders:</strong> <?php echo $shareholders; ?></p>
                                    <p><strong>Average Price(s):</strong></p>
                                    <ul>
                                        <li>Daily: <?php echo getStockAverage($stockData["stock"], "day"); ?></li>
                                        <li>Weekly: <?php echo getStockAverage($stockData["stock"], "week"); ?></li>
                                        <li>Monthly: <?php echo getStockAverage($stockData["stock"], "month"); ?></li>
                                        <li>Annually: <?php echo getStockAverage($stockData["stock"], "year"); ?></li>
                                        <li>All-time: <?php echo getStockAverage($stockData["stock"], "all"); ?></li>
                                    </ul>
                                </div>
							</div>
						</div>
						<div class="col-md-8">
							<?php
							// Check if we have any post data about buying
							// Let's also keep this on the main page, thanks to our friend 'goto'
							if (isset($_POST["buy"]) || isset($_POST["sell"])):
								if (!isLoggedIn()) {
									errorVSX("You must be logged in to buy or sell stocks - <a href=login.php>login</a> or <a href=register.php>sign up</a>!");
									goto end;
								}
								if (isset($_POST["buy"])) {
									$amount = $_POST["buy"];
									$amount = numerise($amount);
									if (!$amount || strlen($amount) == 0 || !(int)$amount) {
										errorVSX("<h4>Oops, we don't recognise that :(</h4> Please enter a valid number!");
                                        goto end;
									}
                                    $amount = (int)$amount;
									if ($amount < 1) {
										errorVSX("<h4>Oops, you can't use that number :(</h4> Please enter a valid number (hint: larger than 0)!");
										goto end;
									}
                                    $buy = buyUserStock($_SESSION["usr"], $stockData["stock"], $amount);
									$price = $amount * getStockCurrentPrice($stockData["stock"]);
                                    if ($buy !== true) {
                                        errorVSX($buy);
                                    }
                                    else {
                                        successVSX("You have successfully bought ". $amount . " share(s) of " . $stockData["stock"] . " for $" . number_format($price, 2));
                                    }
								}
								elseif (isset($_POST["sell"])) {
									$amount = $_POST["sell"];
									$amount = numerise($amount);
									if (!$amount || strlen($amount) == 0 || !(int)$amount) {
										errorVSX("<h4>Oops, we don't recognise that :(</h4> Please enter a valid number!");
                                        goto end;
									}
                                    $amount = (int)$amount;
									if ($amount < 1) {
										errorVSX("<h4>Oops, you can't use that number :(</h4> Please enter a valid number (hint: larger than 0)!");
										goto end;
									}
									$sell = sellUserStock($_SESSION["usr"], $stockData["stock"], $amount);
									$price = $amount * getStockCurrentPrice($stockData["stock"]);
									if ($sell !== true) {
										errorVSX($sell);
									}
									else {
										successVSX("You have successfully sold ". $amount . " share(s) of " . $stockData["stock"] . " for $" . number_format($price, 2));
									}
								}
							endif;
							end:

							$state = ($timing->num_rows > 0) ? "Open" : "Closed";
							?>
							<h3 class="text-center">
								Opening hours: <?php echo date_format(date_create($open), "g:ia"); ?> &mdash; <?php echo date_format(date_create($close), "g:ia"); ?>
								<br>
								<small>Currently: <span style="color: #<?php echo ($state == "Open" ? "15A838" : "FF0000"); ?>"><?php echo $state; ?></span></small>
							</h3>
							<hr>
							<h3 class="text-center">Recent stock prices of <?php echo $stockData["stock"]; ?></h3>
							<div id="chart_div">
								<!-- Blank div for the graph -->
							</div>
							<hr>
							<?php
								if (isset($_SESSION["usr"])) {
									$owned_amount = getUserStocks($_SESSION["usr"], $stockData["stock"]);
									$total_amount = getStockTotalAmount($stockData["stock"]);
									if ($total_amount == 0) {
										$division = 0;
									}
									else {
										$division = $owned_amount / $total_amount;
									}
									$current_price = getStockCurrentPrice($stockData["stock"]);
									$value = $current_price * $owned_amount;
									$digits = strlen(substr(strrchr($division * 100, "."), 1));
									?>
										<p class="text-center">You currently own <strong><?php echo $owned_amount; ?></strong> (<?php echo number_format($division * 100, $digits) ?>%) of <strong><?php echo $stockData["stock"]; ?></strong>, valued at <strong>$<?php echo number_format($value, 2); ?></strong></p>
                                        <hr>
									<?php
								}
                            ?>
							<!-- Hidden form to retain current GET stock parameter -->
							<div class="col-md-6 text-center">
								<form method="post" action="stocks.php?stock=<?php echo $stockData["stock"]; ?>">
									<div class="form-group">
										<div class="input-group col-md-8 col-md-offset-2">
											<input type="number" id="buy" name="buy" class="form-control" placeholder="Quantity" required <?php echo ($state == "Closed" ? "disabled" : ""); ?>>
										</div>
									</div>
									<p id="buy-text"></p>
									<button type="submit" class="btn btn-primary" <?php echo ($state == "Closed" ? "disabled" : ""); ?>>Buy Shares</button>
								</form>
							</div>
							<div class="col-md-6 text-center">
								<form method="post" action="stocks.php?stock=<?php echo $stockData["stock"]; ?>">
									<div class="form-group">
										<div class="input-group col-md-8 col-md-offset-2">
											<input type="number" id="sell" name="sell" class="form-control" placeholder="Quantity" required <?php echo ($state == "Closed" ? "disabled" : ""); ?>>
										</div>
									</div>
									<p id="sell-text"></p>
									<button type="submit" class="btn btn-primary" <?php echo ($state == "Closed" ? "disabled" : ""); ?>>Sell Shares</button>
								</form>
							</div>
						</div>
					</div>
				</div>
				<script type="text/javascript">
					window.addEventListener("load",
						function () {
							stockPrice = '<?php echo getStockCurrentPrice($stockData["stock"]); ?>';
						}
					);
				</script>
				<script type="text/javascript" src="src/js/onStockChange.js"></script>
			</body>
		</html>
		<?php
		return;
	}

	$view = "list";
	if (isset($_GET["view"])) {
		if (strtolower($_GET["view"]) == "grid") {
			$view = "grid";
		}
		// List otherwise
	}

	$qStock = "1 = 1";
	if (isset($_GET["q"])  && $_GET["q"] !== "") {
		$qStock = $_GET["q"];
		$qStock = "stocks__.stock LIKE '%" . str_clean($qStock) . "%'";
	}

	$sort = "stock";
	$order = "ASC";
	if (isset($_GET["sort"]) && $_GET["sort"] !== "") {
		if (!empty($_GET["order"])) {
			$order = strtoupper(str_clean($_GET["order"]));
		}
		$sort = strtolower(str_clean($_GET["sort"]));
		if ($sort === "stock") {
			$sort = "stocks__.stock";
		}
	}

	$res = $db->query(
        "SELECT " . implode(", ", $fields) . "
        FROM stocks__, stocks__history
        WHERE stocks__.stock = stocks__history.stock
		AND " . $qStock . "
        GROUP BY stocks__.stock
		ORDER BY " . $sort . " " . $order
    );

	?>
		<style>
			img {
				max-width: 50%;
				max-height: 50%;
			}
		</style>
		<title>Stocks - VSX</title>
		<body>
			<br>
			<div class="container text-center">
				<div class="row">
					<div class="pull-left">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default" onclick="document.location = 'stocks.php?view=list';">List</button>
							<button type="button" class="btn btn-default" onclick="document.location = 'stocks.php?view=grid';">Grid</button>
						</div>
					</div>
					<div class="col-md-4 col-md-offset-3 col-sm-4 col-sm-offset-4 col-xs-6 col-xs-offset-3">
						<nobr><h2 style="display: inline; vertical-align: middle;">Stocks</h2></nobr>
					</div>
					<form class="form-inline" action="stocks.php" method="get">
						<div class="form-group pull-right">
							<div class="input-group">
								<label class="sr-only">Search</label>
								<?php
									if (isset($_GET["q"]) && $_GET["q"] !== "") {
										?>
											<input type="text" name="q" class="form-control pull-right" placeholder="Search" value=<?php echo $_GET["q"]; ?>>
										<?php
									}
									else {
										?>
											<input type="text" name="q" class="form-control pull-right" placeholder="Search">
										<?php
									}
								?>
								<span class="input-group-btn">
									<button class="btn btn-default">
										<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
									</button>
								</span>
							</div>
						</div>
					</form>
				</div>
			</div>
			<br>
			<div class="container">
				<?php
					if ($res->num_rows == 0) {
						if ($qStock == "1 = 1") {
							errorVSX("No stocks found in the database", 100);
						}
						else {
							errorVSX("No stocks can be found from given search parameters. Click <a href='stocks.php'>here</a> to go back.", 100);
						}
						exit;
					}
					drawStocks($view);
				?>
			</div>
		</body>
	<?php

	function drawStocks($v = "list") {
		global $res;
		if ($v == "grid") {
			$i = 0;
			?>
				<div class="row">
					<hr>
			<?php

			while ($row = $res->fetch_assoc()) {
				stockGrid($row);
				$i++;

				if (($i % 3) == 0) {
					?>
						</div>
						<hr>
					<?php
					if ($res->num_rows > $i) {
						?>
							<br>
							<div class="row">
						<?php
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
									<th><a href="stocks.php?sort=stock&order=<?php global $order; echo $order == "ASC" ? "DESC" : "ASC"; ?>">Stock</a></th>
									<th>Company Name</th>
									<th>Exchange</th>
									<th>Current Price</th>
									<th>Previous Price</th>
									<th>Difference</th>
									<th>Last Updated</th>
									<th>Volume</th>
								</tr>
								<?php
									global $db;
									while ($row = $res->fetch_assoc()) {
										// Current price
										$current_price = getStockCurrentPrice($row["stock"]);

										// Previous price
										$previous_price = getStockPreviousPrice($row["stock"]);

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
											<td><a href='" . $row["exchange"] . "'>" . $row["exchange"] . "</a></td>
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
