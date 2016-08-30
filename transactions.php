<?php
    require_once "core.php";

    if (!isLoggedIn()) {
        errorVSX("You must be logged in to view transactions - <a href=login.php>login</a> or <a href=register.php>sign up</a>!", 40);
        exit;
    }

    $usr = $_SESSION["usr"];

    $queryString = "SELECT * FROM purchases WHERE usr = '" . $usr . "' ";

    /// Checks if any part of the form was posted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["PayPal"]) && $_POST["PayPal"] !== "") {
            $queryString .= "AND PayPal LIKE '" . $_POST["PayPal"] . "' ";
        }
        if (isset($_POST["after"]) || isset($_POST["before"])) {
            if (isset($_POST["after"]) && isset($_POST["before"]) && $_POST["after"] !== "" && $_POST["before"] !== "") {
                $queryString .= "AND UNIX_TIMESTAMP(timing) BETWEEN " . strtotime($_POST["after"]) . " AND " . strtotime($_POST["before"]) . " ";
            }
            elseif ($_POST["after"] === "" && isset($_POST["before"]) && $_POST["before"] !== "") {
                $queryString .= "AND UNIX_TIMESTAMP(timing) < " . strtotime($_POST["before"]) . " ";
            }
            elseif (isset($_POST["after"]) && $_POST["before"] === "" && $_POST["after"] !== "") {
                $queryString .= "AND UNIX_TIMESTAMP(timing) > " . strtotime($_POST["after"]) . " ";
            }
        }
        if (isset($_POST["num"]) && $_POST["num"] !== "") {
            $queryString .= "AND (qty = " . $_POST["num"] . " OR cost = " . $_POST["num"] . ")";
        }
    }

    $query = $db->query($queryString);
    //var_dump($queryString);

    ?>
        <html>
            <title>Transactions - VSX</title>
            <body>
                <div class="container">
                    <div class="row text-center">
                        <div class="col-md-6 col-md-offset-3">
        					<h2 class="text-center">Transactions</h2>
        				</div>
                    </div>
                    <br>
    				<div class="row">
                        <div class="col-md-4">
                            <div class="thumbnail">
                                <div style="margin: 2% 2% 2% 2%;">
                                    <h4 class="text-center">Refine</h3>
                                    <form action="transactions.php" method="post">
                                        PayPal:
                                        <input type="text" name="PayPal" class="form-control" placeholder="PayPal Address">
                                        <br>
                                        Before:
                                        <input type="datetime-local" name="before" class="form-control">
                                        <br>
                                        After:
                                        <input type="datetime-local" name="after" class="form-control">
                                        <br>
                                        Credits or Quantity:
                                        <input type="number" name="num" class="form-control" placeholder="Credits/Quantity">
                                        <br>
                                        <div class="text-center">
                                            <input type="submit" value="Search" class="btn btn-primary centered">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?php
                                if ($query->num_rows == 0) {
                                    errorVSX("No transactions found");
                                    exit;
                                }
                            ?>
                            <table class="table table-hover">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Price</th>
                                    <th>Credits</th>
                                    <th>PayPal</th>
                                </tr>
                                <!--<tr>-->
                                    <?php
                                        while ($row = $query->fetch_assoc()) {
                                            echo "<tr>
                                                <td>" . $row["timing"] . "</td>
                                                <td>$" . number_format($row["cost"], 2) . "</td>
                                                <td>$" . number_format($row["qty"], 2) . "</td>
                                                <td>" . $row["PayPal"] . "</td>
                                            </tr>";
                                        }
                                    ?>
                                <!--<tr>-->
                            </div>
                        </div>
                    </div>
    			</div>
            </body>
        </html>
    <?php
?>
