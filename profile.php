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

    $userData = $db->query("SELECT * FROM users WHERE usr = '" . $profile . "'");
    if ($userData->num_rows == 0) {
        echo "We could not find a user with this name";
        _footer();
        exit;
    }
    $result = $userData->fetch_assoc();

    if (!isset($result["avatar"])) {
        $avatar = "http://i.imgur.com/qIj873X.jpg";
    }
    else {
        $avatar = $result["avatar"];
    }

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
								<img class="img-responsive" src=<?php echo $avatar; ?> >
								<hr>
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
                            <h1><?php echo $profile; ?></h1>
							<h3>Stocks</h3>
							<hr>
							<p>Shareholder of <strong></strong> different stocks</p>
                            <p>Owns <strong></strong>% of all stock listed on VSX</p>
                            <!-- Maybe check if a majority shareholder in one stock, and display it? -->
                            <p>Owns <strong></strong>% of $stock</p>
							<h3>Monetary</h3>
							<hr>
                            <p>Current Returns:</p>
							<p>Total Invested:</p>
							<p>Profit Loss:</p>
                            <h3>Activity</h3>
                            <hr>
                            <p>Registed:</p>
                            <p>Last Active:</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    <?php

    _footer();
?>
