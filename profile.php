<?php
    require_once "core.php";
    require_once "profile_util.php";
    global $db;

	// Check for actions
    if ($_GET && $_GET["u"] && isset($_GET["u"])) {
        if (isLoggedIn()) {
            $profile = $_GET["u"];
        }
        else {
            errorVSX("You must be logged in to view user profiles - <a href=login.php>login</a> or <a href=register.php>sign up</a>!", 40);
            exit;
        }
    }
    else {
        if (isLoggedIn()) {
            $profile = $_SESSION["usr"];
        }
        else {
            errorVSX("You must be logged in to view user profiles - <a href=login.php>login</a> or <a href=register.php>sign up</a>!", 40);
            exit;
        }
    }

    $profile = str_clean($profile);

	// Actual SQL queries

	// The main one
	$fields = array(
		"DATE_FORMAT(registered_on, '%d.%m.%Y ─ %l:%i %p') AS registered_on",
		"DATE_FORMAT(last_visited, '%d.%m.%Y ─ %l:%i %p') AS last_visited",
		"name",
		"website",
		"avatar",
		"bio",
        "email",
        "ip"
	);
    $userData = $db->query(
		"SELECT " . implode(", ", $fields) . "
		FROM users
		WHERE usr = '" . $profile . "'"
	);
	// Don't do the extra queries if they don't need to be done
    if (!$userData) {
        errorVSX("VSX has encountered an internal error", 40);
        exit;
    }
	if (!$userData || $userData->num_rows == 0) {
    	errorVSX("We could not find a user with this name", 40);
        exit;
    }
    $result = $userData->fetch_assoc();

	// User's stock count
	$q1 = $db->query(
		"SELECT COUNT(*) AS stock_count
		FROM stocks__holders
		WHERE usr = '" . $profile . "'"
	);
	$stock_count = $q1->fetch_assoc()["stock_count"];

	// User's total shares
	$q2 = $db->query(
		"SELECT SUM(amount) AS amount
		FROM stocks__holders
		WHERE usr = '" . $profile . "'"
	);
	$amount = $q2->fetch_assoc()["amount"];

	// Total shares
	/*
	$q3 = $db->query(
		"SELECT SUM(amount) AS amount
		FROM stocks__holders"
	);
	*/
	$q3 = $db->query(
		"SELECT SUM(total_options) AS amount
		FROM stocks__"
	);
	$total_amount = $q3->fetch_assoc()["amount"];
	if ($total_amount == 0) {
		$total_amount = 1;
	}

	// If they own shares, display a random one with its information
	if ($amount > 0) {
		$q4 = $db->query(
			"SELECT amount, stocks__holders.stock, total_options
			FROM stocks__holders, stocks__
			WHERE usr = '" . $profile . "'
			AND stocks__holders.stock = stocks__.stock
			ORDER BY RAND()
			LIMIT 1"
		);
		$random = $q4->fetch_assoc();
		$random_amount = $random["amount"];
		$random_stock = $random["stock"];
		$random_total = $random["total_options"];

		// Query for the total options available of the specified random stock
		/*
		$q5 = $db->query(
			"SELECT total_options
			FROM stocks__
			WHERE stock = '" . $random_stock . "'"
		);
		$random_total = $q5->fetch_assoc()["total_options"];
		*/
	}

	// q5 is no longer used, use it here
	// Current returns
	$q5 = $db->query("
		SELECT (
			(
				SELECT SUM(total_price)
				FROM stocks__transactions
				WHERE action = 'B'
				AND usr = '" . $profile . "'
			)
			-
			(
				SELECT SUM(total_price)
				FROM stocks__transactions
				WHERE action = 'S'
				AND usr = '" . $profile . "'
			)
		) AS price
	");
	$current_returns = $q5->fetch_assoc()["price"];

	// Total investments
	$q6 = $db->query(
		"SELECT SUM(total_price) AS price
		FROM stocks__transactions
		WHERE usr = '" . $profile . "'
		AND action = 'B'
		LIMIT 1"
	);
	$total_investments = $q6->fetch_assoc()["price"];

    // Recent activity
    // Checks for activity in past 3 days and groups together stocks
    $q7 = $db->query(
        "SELECT stock, SUM(qty) AS qty, action
        FROM stocks__transactions
        WHERE usr = '" . $profile . "'
        AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(DATE(timing))) <= 259200
        GROUP BY stock, action
		ORDER BY timing DESC
        LIMIT 3"
    );

    // Current value of all stocks they own
    $q8 = $db->query(
        "SELECT SUM(qty * price) AS val
        FROM stocks__history, stocks__transactions
        WHERE usr = '" . $profile . "'
        AND stocks__history.stock = stocks__transactions.stock
        AND stocks__history.timing = (
            SELECT MAX(stocks__history.timing)
            FROM stocks__transactions, stocks__history
            WHERE usr = '" . $profile . "'
            AND stocks__transactions.stock = stocks__history.stock
        )
        LIMIT 1"
    );
    $total_value = $q8->fetch_assoc()["val"];

    // Get profile's IP
    @$countryData = file_get_contents("http://freegeoip.net/json/" . $result["ip"]);
    @$countryData = json_decode($countryData, true); // Converts the JSON string to an array
    $countryLong = $countryData["country_name"] != NULL ? $countryData["country_name"] : "N/A";
    $countryShort = $countryData["country_code"] != NULL ? $countryData["country_code"] : "N/A";

    ?>
        <html>
            <link rel="stylesheet" type="text/css" href="src/css/custom.css"/>
            <link rel="stylesheet" type="text/css" href="src/css/flags.css"/>
            <title>Profile of <?php echo $profile; ?> - VSX</title>
            <body>
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 avatar-display">
							<div class="thumbnail profile">
								<?php
									if (isset($result["avatar"])) {
									?>
										<img class="img-responsive" src=<?php echo $result["avatar"]; ?> >
										<hr>
									<?php
									}
								?>
                                <div style="padding-left: 10px; padding-right: 10px;">
    								<h3><?php echo $profile; ?></h3>
    								<?php
    									if (isset($result["name"])) {
    										?>
    											<h5><i><?php echo $result["name"]; ?></i></h5>
    										<?php
    									}
    									if (isset($result["bio"])) {
    										?>
    											<p><?php echo $result["bio"]; ?></p>
    										<?php
    									}
    								?>
                                </div>
							</div>
							<?php
								if (isUserSelf($profile)) {
									?>
									<hr>
									<div class="btn-group btn-group-justified" role="group" aria-label="...">
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-default">Edit profile</button>
										</div>
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-default">Edit account</button>
										</div>
									</div>
									<?php
								}
							?>
                        </div>
                        <div class="col-md-8">
                            <!--<h1><?php //echo $profile; ?></h1>-->
                            <h3>User</h3>
                            <!-- Add option to hide email -->
                            <p><strong>Email:</strong> <a href=<?php echo "mailto:" . $result["email"]; ?> target="_top"><?php echo $result["email"]; ?></a></p>
                            <!-- Add flag and country (current placeholder) -->
                            <p><strong>Location:</strong> <?php echo $countryLong; ?> <img src="src/images/blank.gif" class='<?php echo "flag flag-" . strtolower($countryShort) . ""; ?>'/></p>
                            <p><strong>Registered:</strong> <?php echo $result["registered_on"]; ?></p>
                            <p><strong>Last Active:</strong> <?php echo $result["last_visited"]; ?></p>
                            <hr>
							<h3>Stocks</h3>
							<p>Shareholder of <strong><?php echo $stock_count; ?></strong> different stock(s)</p>
                            <p>Owns <strong><?php echo round(($amount / $total_amount) * 100, 3); ?></strong>% of all stock listed on VSX</p>
                            <!-- Maybe check if a majority shareholder in one stock, and display it? -->
							<?php
								if ($amount > 0) {
									?>
										<p>Owns <strong><?php echo round(($random_amount / $random_total) * 100, 3); ?></strong>% (<?php echo $random_amount; ?>) of <strong><a href=<?php echo "stocks.php?stock=" . $random_stock; ?> target="_blank"><?php echo $random_stock; ?></a></strong></p>
									<?php
								}
							?>
                            <hr>
							<h3>Monetary</h3>
                            <p><strong>Total Invested:</strong> $<?php echo number_format($total_investments, 2); ?></p>
                            <p><strong>Current Value:</strong> $<?php echo number_format($total_value, 2); ?></p>
							<p><strong>Current Returns:</strong> $<?php echo number_format($current_returns, 2); ?></p>
							<!--<p>Profit Loss:</p>-->
                            <hr>
                            <h3>Recent Activity</h3>
                            <!-- If in last week or so (max to display = 3) -->
                            <?php
                                if ($q7->num_rows == 0) {
                                    ?>
                                        <p>No recent activity</p>
                                    <?php
                                }
                                else {
                                    while ($activity = $q7->fetch_assoc()) {
                                        $action = $activity["action"] == "B" ? "purchased" : "sold";
                                        ?>
                                            <p>Recently <?php echo $action; ?> <strong><?php echo $activity["qty"]; ?></strong> share(s) of <strong><a href=<?php echo "stocks.php?stock=" . $activity["stock"]; ?> target="_blank"><?php echo $activity["stock"]; ?></a></strong></p>
                                        <?php
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    <?php
?>
