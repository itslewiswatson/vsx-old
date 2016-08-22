<?php
    require_once "core.php";
	global $db;

    $fields = array(
		"usr",
		"DATE_FORMAT(registered_on, '%d-%m-%Y') AS registered_on",
		"DATE_FORMAT(last_visited, '%d-%m-%Y') AS last_visited",
		"name",
		"website",
	);
	$default["query_string"] = "SELECT " . implode(", ", $fields) . " FROM users GROUP BY usr";

	$queryString = $default["query_string"];

	if ($_GET && $_GET["q"] && isset($_GET["q"])) {
		$i = $_GET["q"];
		$i = str_clean($i);
		$queryString = "
            SELECT " . implode(", ", $fields) . "
            FROM users
            WHERE usr LIKE '%" . $i . "%'
            OR email LIKE '%" . $i . "%'
            OR name LIKE '%" . $i . "%'
            GROUP BY usr
        ";
	}

	// Move this to a template?
	?>
		<body>
            <br>
			<div class="container text-center">
				<div class="row">
                    <div class="col-md-4 col-md-offset-4 col-sm-4 col-sm-offset-4 col-xs-6 col-xs-offset-3">
                        <nobr><h2 style="display: inline; vertical-align: middle;">Members</h2></nobr>
                    </div>
    				<form class="form-inline" action="members.php" method="get">
                        <div class="form-group pull-right">
                            <div class="input-group">
                    			<label class="sr-only">Search</label>
                                <?php
                                    if (isset($i)) {
                                        ?>
                                            <input type="text" name="q" class="form-control pull-right" placeholder="Search for..." value=<?php echo $i; ?>>
                                        <?php
                                    }
                                    else {
                                        ?>
                                            <input type="text" name="q" class="form-control pull-right" placeholder="Search for...">
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
		</body>
	<?php

	$query = $db->query($queryString);

	if (!$query || $query->num_rows == 0) {
		echo "No users found from search parameters";
		exit;
	}

	if ($queryString === $default["query_string"]) {
		?>
			<title>Members - VSX</title>
		<?php
		$countString = "SELECT COUNT(*) AS row_count FROM users";
	}
	else {
		?>
			<title>Search Results - VSX</title>
		<?php
		$countString = "SELECT COUNT(*) AS row_count FROM users WHERE usr LIKE '%" . $i . "%' OR email LIKE '%" . $i . "%' OR name LIKE '%" . $i . "%'";
	}

    $row_count = $db->query($countString)->fetch_assoc()["row_count"];

	?>
		<html>
			<body>
				<div class="container">
                    <div class="row">
    					<table class="table table-hover">
    						<tr>
    							<th>Username</th>
    							<th>Name</th>
    							<th>Website</th>
    							<th>Last Active</th>
    							<th>Registered On</th>
    						</tr>
    						<?php
    							echo "Showing " . $query->num_rows . " of " . $row_count;
        						while ($row = $query->fetch_assoc()) {
        							$website = "<a href='" . $row["website"] . "' target='_blank'>" . $row["website"] . "</a>";
        							if (!$row["website"] || !isset($row["website"])) {
        								$website = "";
        							}
        							echo "<tr>
        								<td><a href='profile.php?u=" . $row["usr"] ."'>" . $row["usr"] . "</a></td>
        								<td>" . $row["name"] . "</td>
        								<td>" . $website . "</td>
        								<td>" . $row["last_visited"] . "</td>
        								<td>" . $row["registered_on"] . "</td>
        							</tr>";
        						}
    						?>
    					</table>
                    </div>
				</div>
			</body>
		</html>
	<?php

    _footer();
?>
