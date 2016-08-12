<?php
    require "core.php";
    _header();
    global $db;

    function isUserSelf($usr) {
        if (!isset($_SESSION["usr"])) {
            return false;
        }
        if ($usr == $_SESSION["usr"]) {
            return true;
        }
        return false;
    }

	// Check for actions
    if ($_GET && $_GET["u"] && isset($_GET["u"])) {
        if (isLoggedIn()) {
            $profile = $_GET["u"];
        }
        else {
            echo "You must be logged in to view user profiles";
            _footer();
            exit;
        }
    }
    else {
        if (isLoggedIn()) {
            $profile = $_SESSION["usr"];
        }
        else {
            echo "You must be logged in to view user profiles";
            _footer();
            exit;
        }
    }

    $profile = str_clean($profile);
	
	// Actual SQL queries
	
	// The main one
	$fields = array(
		"DATE_FORMAT(registered_on, '%l:%i %p ─ %d.%m.%Y') AS registered_on",
		"DATE_FORMAT(last_visited, '%l:%i %p ─ %d.%m.%Y') AS last_visited",
		"name",
		"website",
		"avatar",
		"bio",
	);
    $userData = $db->query(
		"SELECT " . implode(", ", $fields) . " 
		FROM users
		WHERE usr = '" . $profile . "'"
	);
	// Don't do the extra queries if they don't need to be done
	if ($userData->num_rows == 0) {
        echo "We could not find a user with this name";
        _footer();
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
	
	// Total investments
	$q6 = $db->query(
		"SELECT SUM(total_price) AS price
		FROM stocks__transactions
		WHERE usr = '" . $profile . "'
		AND action = 'B'
		LIMIT 1"
	);
	$total_investments = $q6->fetch_assoc()["price"];
	
    ?>
        <html>
            <style type="text/css">
            #avatar img {
                max-width: 325px;
                width: 150%;
                height: auto;
                margin: 0 auto;
            }
            </style>
            <title>Profile of <?php echo $profile; ?> - VSX</title>
            <body>
                <div class="container">
                    <div class="row">
                        <div class="col-md-4" id="avatar">
							<div class="thumbnail">
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
							<h3>Stocks</h3>
							<hr>
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
							<h3>Monetary</h3>
							<hr>
                            <p>Current Returns:</p>
							<p>Total Invested: $<?php echo sprintf("%4.2f", $total_investments); ?></p>
							<p>Profit Loss:</p>
                            <h3>Activity</h3>
                            <hr>
                            <p>Registered: <?php echo $result["registered_on"]; ?></p>
                            <p>Last Active: <?php echo $result["last_visited"]; ?></p>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    <?php

    _footer();
?>
