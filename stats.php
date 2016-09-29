<?php
    require_once "core.php";
    global $db;

    $q1 = $db->query(
        "SELECT
        (
            SELECT COALESCE(SUM(qty), 0)
            FROM stocks__transactions
            WHERE action = 'B'
            LIMIT 1
        ) AS bought,
        (
            SELECT COALESCE(SUM(qty), 0)
            FROM stocks__transactions
            WHERE action = 'S'
            LIMIT 1
        ) AS sold"
    );
    $total = $q1->fetch_assoc();

    $q2 = $db->query(
        "SELECT COUNT(*) AS count
        FROM users"
    );
    $userCount = $q2->fetch_assoc()["count"];

    $q3 = $db->query(
        "SELECT usr, SUM(total_price) AS total
        FROM stocks__transactions
        WHERE action = 'B'
        GROUP BY usr
        /*
        HAVING SUM(total_price) >=
        ALL(
            SELECT SUM(total_price)
            FROM stocks__transactions
            WHERE action = 'B'
            GROUP BY usr
        )*/
        ORDER BY total DESC
        LIMIT 1"
    );
    $data3 = $q3->fetch_assoc();
    $mostInvested = $data3["usr"];
    $mostInvestedAmount = $data3["total"];

    $q4 = $db->query(
        "SELECT usr, MAX(total_price) AS total
        FROM stocks__transactions
        WHERE action = 'B'
        GROUP BY usr
        ORDER BY total DESC
        LIMIT 1"
    );
    $data4 = $q4->fetch_assoc();
    $largestInvest = $data4["usr"];
    $largestInvestAmount = $data4["total"];

    // avg prices
    // visits (registered & unregistered)

    $q5 = $db->query(
        "SELECT AVG(price) AS price
        FROM (
            SELECT AVG(price) AS price
            FROM stocks__history
            WHERE timing IN
            (
                SELECT MAX(timing)
                FROM stocks__history
                GROUP BY stock
            )
            GROUP BY stock
        ) AS table1"
    );
    $average = $q5->fetch_assoc()["price"];

    $q6 = $db->query(
        "SELECT COUNT(*) AS no_trades
        FROM stocks__transactions
        LIMIT 1"
    );
    $no_trades = $q6->fetch_assoc()["no_trades"];

    $q7 = $db->query(
        "SELECT COUNT(*) AS most_traded, stock
        FROM stocks__transactions
        GROUP BY stock
        ORDER BY most_traded DESC
        LIMIT 1"
    );
    $data7 = $q7->fetch_assoc();
    $most_traded = $data7["most_traded"];
    $most_traded_stock = $data7["stock"];

    $q8 = $db->query(
        "SELECT
        (
            SELECT COUNT(*)
            FROM stocks__transactions
            WHERE action = 'S'
        ) AS sold,
        (
            SELECT COUNT(*)
            FROM stocks__transactions
            WHERE action = 'B'
        ) AS bought"
    );
    $data8 = $q8->fetch_assoc();
    $sold = $data8["sold"];
    $bought = $data8["bought"];

    // users who made most transactions, bought most, sold most etc
    $q9 = $db->query(
        "SELECT usr, COUNT(*) AS transactions
        FROM stocks__transactions
        GROUP BY usr
        ORDER BY transactions DESC
        LIMIT 1"
    );
    $data9 = $q9->fetch_assoc();
    $transacs_usr = $data9["usr"];
    $transacs_no = $data9["transactions"];

    ?>
        <html>
            <title>Statistics - VSX</title>
            <body>
                <div class="container">
                    <div class="row">
                        <h2 class="text-center">Statistics</h2>
                        <div class="col-md-5" style="float: none; margin: 0 auto;">
                            <div class="thumbnail">
                                <h3 style="margin-top: 2px;">Shares</h3>
                                <p><strong>Total Purchased:</strong><span class="pull-right"><?php echo number_format($total["bought"]); ?></span></p>
                                <p><strong>Total Sold:</strong><span class="pull-right"><?php echo number_format($total["sold"]); ?></span></p>
                                <p><strong>Average:</strong><span class="pull-right">$<?php echo number_format($average, 2); ?></span></p>
                                <p><strong>Trades:</strong><span class="pull-right"><?php echo $no_trades; ?></span></p>
                                <p><strong>Most Traded:</strong><span class="pull-right"><a href="stocks.php?stock=<?php echo $most_traded_stock; ?>"><?php echo $most_traded_stock; ?></a> (<?php echo number_format($most_traded); ?>)</span></p>
                                <p><strong>Purchases:</strong><span class="pull-right"><?php echo $bought; ?></span></p>
                                <p><strong>Sellings:</strong><span class="pull-right"><?php echo $sold; ?></span></p>
                                <hr>
                                <h3>Users</h3>
                                <p><strong>Total Users:</strong><span class="pull-right"><?php echo number_format($userCount); ?></span></p>
                                <p><strong>Most Invested:</strong><span class="pull-right"><a href="profile.php?u=<?php echo $mostInvested; ?>"><?php echo $mostInvested; ?></a> ($<?php echo number_format($mostInvestedAmount, 2); ?>)</span></p>
                                <p><strong>Largest Investment:</strong><span class="pull-right"><a href="profile.php?u=<?php echo $largestInvest; ?>"><?php echo $largestInvest; ?></a> ($<?php echo number_format($largestInvestAmount, 2); ?>)</span></p>
                                <p><strong>Most Transactions:</strong><span class="pull-right"><a href="profile.php?u="<?php echo $transacs_usr; ?>><?php echo $transacs_usr; ?></a> (<?php echo $transacs_no; ?>)</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    <?php
?>
