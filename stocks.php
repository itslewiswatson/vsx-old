<?php
	require "core.php";
	global $db;

	if (isset($_GET["stock"])) {
		$stock = $_GET["stock"];
		$stock = str_clean($_GET["stock"]);

		$res = $db->query("SELECT * FROM stocks__ WHERE stock = '" . $stock . "' LIMIT 1");
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

	$res = $db->query("SELECT * FROM stocks__ LIMIT 9");

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
			echo "<table>";
			while ($row = $res->fetch_assoc()) {
				echo "<tr><td><a href='stocks.php?stock=" . $row["stock"] ."'>" . $row["company_name"] . "</a></td></tr>";
			}
			echo "</table>";
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
	<?php
//	_footer();
?>
