<?php
	require_once "core.php";
    require_once "stocks_util.php";
    global $db;

    // Trending stock
    $trending = $db->query(
        "SELECT stock
        FROM stocks__
        ORDER BY RAND()
        LIMIT 10"
    );
    ?>
        <html>
            <title>Index - VSX</title>
            <body>
                <div class="container">
                    <div class="row">
                        <div class="col-md-2 col-xs-4">
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
                                                <td>" . $row["stock"] . "</td>
                                                <td>$" . number_format(getStockCurrentPrice($row["stock"]), 2) . "</td>
                                            </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-2 col-xs-4 pull-right">
                            <div class="thumbnail">
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    <?php
?>
