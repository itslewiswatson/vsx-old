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

    function getStockAverage($stock, $period) {
        $periods = array(
            "day" => "(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(timing)) <= (60 * 60 * 24)",
            "week" => "(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(timing)) <= (60 * 60 * 24 * 7)",
            "month" => "(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(timing)) <= (60 * 60 * 24 * 7 * (SELECT DAY(LAST_DAY(CURDATE()))))",
            "year" => "(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(timing)) <= (60 * 60 * 24 * (SELECT DAYOFYEAR(CURDATE())))",
            "all" => "1 = 1" // lol
        );
        $timing = $periods[$period];
        global $db;
        $q = $db->query(
            "SELECT AVG(price) AS average
            FROM stocks__history
            WHERE stock = '" . $stock . "'
            AND " . $timing . "
            GROUP BY stock
            LIMIT 1"
        );
        $average = $q->fetch_assoc()["average"];
        if (!$average) {
            $average = "Not found";
        }
        else {
            $average = "$" . number_format($average, 2);
        }
        return $average;
    }
	
	function getStockExchange($stock) {
		$stock = str_clean($stock);
		global $db;
		$exch = $db->query(
			"SELECT exchange
			FROM stocks__
			WHERE stock = '" . $stock . "'
			LIMIT 1"
		);
		return $exch->fetch_assoc()["exchange"];
	}
	
	function getStockOpenClose($stock) {
		$stock = str_clean($stock);
		global $db;
		$exch = $db->query(
			"SELECT open_time, close_time
			FROM stocks__, exchanges
			WHERE stocks__.exchange = exchanges.exchange
			AND stocks__.stock = '" . $stock . "'
			GROUP BY stocks__.stock
			LIMIT 1"
		);
		$data = $exch->fetch_assoc();
		return array($data["open_time"], $data["close_time"]);
	}
	
	// Needs a can user buy stock function
	function buyUserStock($usr, $stock, $qty) {
		global $db;
		require_once "profile_util.php";
		// Check if they can buy stock in the first place
		$price = $qty * getStockCurrentPrice($stock);
        $credits = getUserCredits($usr);
		if ($price > $credits) {
			// Can't buy stock because the price is too high
			return "Insufficient funds - you need $" . number_format($price - $credits, 2);
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

        $subtrac = $db->prepare("UPDATE users SET credits = credits - ? WHERE usr = ?");
        $subtrac->bind_param("is", $price, $usr);
        $subtrac->execute();

		return true;
	}

	function sellUserStock($usr, $stock, $qty) {
		global $db;

		$check = $db->query("SELECT amount FROM stocks__holders WHERE usr = '" . $usr . "' AND stock = '" . $stock . "' LIMIT 1");
		if ($check->num_rows == 0) {
			return "You do not own any shares in this company";
		}
		$amount = $check->fetch_assoc()["amount"];
		if ($amount - $qty == 0) {
			$db->query("DELETE FROM stocks__holders WHERE usr = '" . $usr . "' AND stock = '" . $stock . "'");
		}
		else {
			$change = $db->prepare("UPDATE stocks__holders SET amount = amount - ? WHERE usr = ? AND stock = ?");
			$change->bind_param("iss", $qty, $usr, $stock);
			$change->execute();
		}

		$price = getStockCurrentPrice($stock);
		$price = $price * $qty;

		$transac = $db->prepare("INSERT INTO stocks__transactions (usr, stock, timing, qty, total_price, action) VALUES (?, ?, CURRENT_TIMESTAMP(), ?, ?, 'S')");
		$transac->bind_param("ssid", $usr, $stock, $qty, $price);
		$transac->execute();

		$subtrac = $db->prepare("UPDATE users SET credits = credits + ? WHERE usr = ?");
        $subtrac->bind_param("is", $price, $usr);
        $subtrac->execute();

		return true;
	}
?>
