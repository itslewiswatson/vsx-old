<?php
	require_once "core.php";
    require_once "stocks_util.php";
    global $db;

    // Trending stock
    $trending = $db->query(
        "SELECT stock
        FROM stocks__
        ORDER BY RAND()
        LIMIT 6"
    );

	// Recent things
	$recent = $db->query(
		"SELECT usr, stock, action, qty
		FROM stocks__transactions
		GROUP BY usr, stock, action
		ORDER BY timing DESC
		LIMIT 6"
	);

	$total = $db->query(
		"SELECT A.stock AS stock, total_options * (
												SELECT price
												FROM stocks__history
												WHERE stock = A.stock
												AND timing = (
													SELECT MAX(timing)
													FROM stocks__history
													WHERE stock = A.stock
												)) AS value
		FROM stocks__history A, stocks__ B
		WHERE A.stock = B.stock
		GROUP BY stock
		ORDER BY value DESC
		LIMIT 6"
	);

	$top = $db->query(
		"SELECT stock,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					LIMIT 2
				)
				LIMIT 2
			) AS curr,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					AND timing < (
						SELECT MAX(timing)
						FROM stocks__history D
						WHERE C.stock = D.stock
					)
				)
			) AS prev,
			(
				SELECT ROUND((CAST(curr AS SIGNED) - CAST(prev  AS SIGNED)) / CAST(curr AS SIGNED)  * 100, 2)
			) AS diff
		FROM stocks__history A
		GROUP BY stock
		ORDER BY curr DESC
		LIMIT 6"
	);

	$low = $db->query(
		"SELECT stock,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					LIMIT 2
				)
				LIMIT 2
			) AS curr,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					AND timing < (
						SELECT MAX(timing)
						FROM stocks__history D
						WHERE C.stock = D.stock
					)
				)
			) AS prev,
			(
				SELECT ROUND((CAST(curr AS SIGNED) - CAST(prev  AS SIGNED)) / CAST(curr AS SIGNED)  * 100, 2)
			) AS diff
		FROM stocks__history A
		GROUP BY stock
		ORDER BY curr ASC
		LIMIT 6"
	);

	$declines = $db->query(
		"SELECT stock,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					LIMIT 2
				)
				LIMIT 2
			) AS curr,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					AND timing < (
						SELECT MAX(timing)
						FROM stocks__history D
						WHERE C.stock = D.stock
					)
				)
			) AS prev,
			(
				SELECT ROUND((CAST(curr AS SIGNED) - CAST(prev  AS SIGNED)) / CAST(curr AS SIGNED)  * 100, 2)
			) AS diff
		FROM stocks__history A
		GROUP BY stock
		ORDER BY diff ASC
		LIMIT 6"
	);

	$gains = $db->query(
		"SELECT stock,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					LIMIT 2
				)
				LIMIT 2
			) AS curr,
			(
				SELECT price
				FROM stocks__history B
				WHERE A.stock = B.stock
				AND timing = (
					SELECT MAX(timing)
					FROM stocks__history C
					WHERE B.stock = C.stock
					AND timing < (
						SELECT MAX(timing)
						FROM stocks__history D
						WHERE C.stock = D.stock
					)
				)
			) AS prev,
			(
				SELECT ROUND((CAST(curr AS SIGNED) - CAST(prev  AS SIGNED)) / CAST(curr AS SIGNED)  * 100, 2)
			) AS diff
		FROM stocks__history A
		GROUP BY stock
		ORDER BY diff DESC
		LIMIT 6"
	);

	$vol = $db->query(
		"SELECT SUM(qty) AS volume, stock
		FROM stocks__transactions
		GROUP BY stock
		ORDER BY volume DESC
		LIMIT 6"
	);

    ?>
        <html>
        	<title>Index - VSX</title>
			<link rel="stylesheet" type="text/css" href="src/css/custom.css"/>
            <body>
                <div class="container-fluid container2">
					<div class="row">
						<div class="text-center">
							<h3>
								Virtual Stock Exchange<br>
								<small>VSX is an online stock exchange that mimics behavior of real-life stock markets in an inconsequential environment.</small>
							</h3>
						</div>
					</div>
					<hr>
					<div class="row">
						<?php
							if (isLoggedIn()) {
								// Show recent activity on their stock
							}
							else {
								?>
									<div class="alert alert-info text-center" role="alert" style="margin: 0;">
										We noticed you aren't logged in. To trade stocks, you need an account. <a href="register.php" class="alert-link">Register</a> if you haven't already, or <a href="login.php" class="alert-link">login</a> if you already have an account.
									</div>
								<?php
							}
						?>
					</div>
					<hr>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="thumbnail">
                                <h4 class="text-center">Trending Stock</h4>
                                <table class="table table-hover">
                                    <tr>
                                        <th>Stock</th>
                                        <th>Price</th>
                                    </tr>
                                    <?php
                                        while ($row = $trending->fetch_assoc()) {
                                            echo "<tr>
                                                <td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
                                                <td>$" . number_format(getStockCurrentPrice($row["stock"]), 2) . "</td>
                                            </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
						<div class="col-md-3">
                            <div class="thumbnail">
								<h4 class="text-center">Total Value</h4>
                                <table class="table table-hover">
                                    <tr>
                                        <th>Stock</th>
										<th>Value</th>
                                    </tr>
                                    <?php
                                        while ($row = $total->fetch_assoc()) {
                                            echo "<tr>
                                                <td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>$" . number_format($row["value"], 2) . "</td>
                                            </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
						<div class="col-md-3">
							<div class="thumbnail">
								<h4 class="text-center">All-Time Volume</h4>
								<table class="table table-hover">
									<tr>
										<th>Stock</th>
										<th>Volume<th>
									</tr>
									<?php
										while ($row = $vol->fetch_assoc()) {
											echo "<tr>
												<td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>" . number_format($row["volume"]) . "</td>
											</tr>";
										}
									?>
								</table>
							</div>
						</div>
                        <div class="col-md-3">
                            <div class="thumbnail">
								<h4 class="text-center">Recent Trades</h4>
                                <table class="table table-hover">
                                    <tr>
                                        <th>User</th>
                                        <th>Stock</th>
										<th>Qty</th>
										<th>B/S</th>
                                    </tr>
                                    <?php
                                        while ($row = $recent->fetch_assoc()) {
                                            echo "<tr>
                                                <td><a href='profile.php?u=" . $row["usr"] . "'>" . $row["usr"] . "</a></td>
                                                <td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>" . $row["qty"] . "</td>
												<td>" . $row["action"] . "</td>
                                            </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
					<div class="row">
						<div class="col-md-3">
                            <div class="thumbnail">
								<h4 class="text-center">Top Gains</h4>
                                <table class="table table-hover">
									<tr>
										<th>Stock</th>
										<th>Prev</th>
										<th>Curr</th>
										<th>Diff</th>
									</tr>
									<?php
										while ($row = $gains->fetch_assoc()) {
											echo "<tr>
												<td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>$" . number_format($row["prev"], 2) . "</td>
												<td>$" . number_format($row["curr"], 2) . "</td>
												<td><img src='" . ($row["diff"] >= 0 ? "src/images/up.gif" : "src/images/down.gif") . "'/> " . $row["diff"] . "%</td>
											</tr>";
										}
                                    ?>
                                </table>
                            </div>
                        </div>
						<div class="col-md-3">
                            <div class="thumbnail">
								<h4 class="text-center">Top Declines</h4>
                                <table class="table table-hover">
                                    <tr>
                                        <th>Stock</th>
										<th>Prev</th>
										<th>Curr</th>
										<th>Diff</th>
                                    </tr>
                                    <?php
                                        while ($row = $declines->fetch_assoc()) {
                                            echo "<tr>
                                                <td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>$" . number_format($row["prev"], 2) . "</td>
												<td>$" . number_format($row["curr"], 2) . "</td>
												<td><img src='" . ($row["diff"] >= 0 ? "src/images/up.gif" : "src/images/down.gif") . "'/> " . $row["diff"] . "%</td>
                                            </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
						<div class="col-md-3">
							<div class="thumbnail">
								<h4 class="text-center">Top Value</h4>
								<table class="table table-hover">
									<tr>
										<th>Stock</th>
										<th>Price</th>
										<th>Change</th>
									</tr>
									<?php
										while ($row = $top->fetch_assoc()) {
											echo "<tr>
												<td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>$" . number_format($row["curr"], 2) . "</td>
												<td><img src='" . ($row["diff"] >= 0 ? "src/images/up.gif" : "src/images/down.gif") . "'/> " . $row["diff"] . "%</td>
											</tr>";
										}
									?>
								</table>
							</div>
						</div>
						<div class="col-md-3">
                            <div class="thumbnail">
								<h4 class="text-center">Lowest Value</h4>
								<table class="table table-hover">
									<tr>
										<th>Stock</th>
										<th>Price</th>
										<th>Change</th>
									</tr>
									<?php
										while ($row = $low->fetch_assoc()) {
											echo "<tr>
												<td><a href='stocks.php?stock=" . $row["stock"] . "'>" . $row["stock"] . "</a></td>
												<td>$" . number_format($row["curr"], 2) . "</td>
												<td><img src='" . ($row["diff"] >= 0 ? "src/images/up.gif" : "src/images/down.gif") . "'/> " . $row["diff"] . "%</td>
											</tr>";
										}
									?>
								</table>
                            </div>
                        </div>
					</div>
                </div>
            </body>
        </html>
    <?php
?>
