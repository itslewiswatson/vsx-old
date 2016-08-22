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
            GROUP BY stock
			LIMIT 1"
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
            GROUP BY stock
			LIMIT 1"
        );
        $previous_price = $PP->fetch_assoc()["previous_price"];
        return $previous_price;
    }

	// Needs a can user buy stock function
	function buyStock($usr, $stock, $qty) {
		global $db;
		require_once "profile_util.php";
		// Check if they can buy stock in the first place
		$price = $qty * getStockCurrentPrice($stock);
		if ($price > getUserCredits($usr)) {
			// Can't buy stock because the price is too high
			return false;
		}

		// Check if user already downs stock
		$check = $db->query("SELECT * FROM stocks__holders WHERE usr = '" . $usr . "' AND stock = '" . $stock . "' LIMIT 1");
		if ($check->num_rows > 0) {
			$queryString = "UPDATE stocks__holders SET amount = amount + ? WHERE usr = ? AND stock = ?";
		}
		else {
			$queryString = "INSERT INTO stocks__holders (amount, usr, stock) VALUES (?, ?, ?)";
		}
		$q = $db->prepare($queryString);
		$q->bind_param("iss", $qty, $usr, $stock);
		$q->execute();

		$transac = $db->prepare("INSERT INTO stocks__transactions (usr, stock, timing, qty, total_price, action) VALUES (?, ?, CURRENT_TIMESTAMP(), ?, ?, 'B')");
		$transac->bind_param("ssid", $usr, $stock, $qty, $price);
		$transac->execute();

		return true;
	}
?>
