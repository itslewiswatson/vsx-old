<?php
	include "src/templates/header.html";
	require "core.php";
	
	global $db;
	
	if (isset($_GET["stock"])) {
		$stock = $_GET["stock"];
		
		$res = $db->query("SELECT * FROM stocks__ WHERE abbreviation = '" . $stock . "' LIMIT 1");
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
			include "src/templates/footer.html";
			return;
		}
		
		?>
		<title><?php echo $stockData["stockName"] . " (" . $stock . ")"; ?> - VSX</title>
		<body>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<h1 class="text-center"><?php echo $stockData["stockName"] . " (" . $stock . ")"; ?></h1>
					</div>
				</div>
				<hr>
			</div>
		</body>
		<?php
				
		include "src/templates/footer.html";
		return;
	}
	
	
	// Main page
	$res = $db->query("SELECT * FROM stocks__ LIMIT 9");
	
	function displayStock($row) {
		?>
		<div class="text-center col-md-4">
			<p><?php echo "<b>" . $row["abbreviation"] . "</b><br>"; echo $row["stockName"]; ?></p>
			<a href="stocks.php?stock=<?php echo $row["abbreviation"]; ?>"><img src="<?php echo $row["avatar"]; ?>"></a>
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
	<?php
	
	?>
	<title>Stocks - VSX</title>
	<body>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<h1 class="text-center">Stocks</h1>
				</div>
			</div>
			<hr>
		</div>
		<div class="container">
			<?php
				$i = 0;
				echo "<div class='row'>";
				
				while ($row = $res->fetch_assoc()) {
					displayStock($row);
					$i++;
					
					if (($i % 3) == 0) {
						echo "</div>";
						if ($res->num_rows > $i) {
							echo "<br><div class='row'>";
						}
					}
				}
			?>
		</div>
		<!--
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<h1 class="text-center"></h1>
				</div>
			</div>
		</div>
		-->
	</body>
	<?php
	include "src/templates/footer.html";
?>