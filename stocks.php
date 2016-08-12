<?php
	require "core.php";
	global $db;
	$fields = array("stocks__.stock AS stock", "company_name", "company_logo", "DATE_FORMAT(MAX(timing), '%H:%i:%S %d-%m-%Y') AS last_updated");

	if (isset($_GET["stock"])) {
		$stock = $_GET["stock"];
		$stock = str_clean($_GET["stock"]);

		$res = $db->query("SELECT " . implode(", ", $fields) . " FROM stocks__, stocks__history	WHERE stocks__.stock = stocks__history.stock AND stocks__.stock = '" . $stock . "'");
		$stockData = $res->fetch_assoc();

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
			//_footer();
			return;
		}

		?>
		<title><?php echo $stockData["company_name"] . " (" . $stock . ")"; ?> - VSX</title>
		<body>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<h1 class="text-center"><?php echo $stockData["company_name"] . " (" . $stock . ")"; ?></h1>
					</div>
				</div>
				<hr>
			</div>
		</body>
		<?php
		//_footer();
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
											<td>" . "$" . sprintf("%4.2f", $current_price) . "</td>
											<td>" . "$" . sprintf("%4.2f", $previous_price) . "</td>
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
