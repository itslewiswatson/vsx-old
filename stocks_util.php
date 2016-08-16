<?php

    // Utility functions
    function getUserStocks($usr, $stock) {
        global $db;
        $usr = str_clean($usr);
        $stock = str_clean($stock);
        $q = $db->query(
            "SELECT amount
            FROM stocks__holders
            WHERE usr = '" . $usr . "'
            AND stock = '" . $stock . "'
            LIMIT 1"
        );
        $amount = $q->fetch_assoc()["amount"];
        if (!$amount || $amount == NULL) {
            $amount = 0;
        }
        return $amount;
    }

    function getStockTotalAmount($stock) {
        global $db;
        $stock = str_clean($stock);
        $q = $db->query(
            "SELECT total_options
            FROM stocks__
            WHERE stock = '" . $stock . "'
            LIMIT 1"
        );
        $amount = $q->fetch_assoc()["total_options"];
        return $amount;
    }

    function getStockCurrentPrice($stock) {
        global $db;
        $stock = str_clean($stock);
        $CP = $db->query(
            "SELECT price AS current_price
            FROM stocks__history
            WHERE stock = '" . $stock . "'
            AND timing =
                (SELECT MAX(timing)
                FROM stocks__history
                WHERE stock = '" . $stock . "'
                )
            GROUP BY stock"
        );
        $current_price = $CP->fetch_assoc()["current_price"];
        return $current_price;
    }

    function getStockPreviousPrice($stock) {
        global $db;
        $stock = str_clean($stock);
        $PP = $db->query(
            "SELECT price AS previous_price
            FROM stocks__history
            WHERE stock = '" . $stock . "'
            AND timing =
                (SELECT MAX(timing)
                FROM stocks__history
                WHERE stock = '" . $stock . "'
                AND timing <
                    (SELECT MAX(timing)
                    FROM stocks__history
                    WHERE stock = '" . $stock . "'
                    )
                )
            GROUP BY stock"
        );
        $previous_price = $PP->fetch_assoc()["previous_price"];
        return $previous_price;
    }
?>
