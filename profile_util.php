<?php
    function getUserCredits($usr) {
        $q = $db->query(
            "SELECT credits
            FROM users
            WHERE usr = '" . $usr . "'
            LIMIT 1"
        );
        $credits = $q->fetch_assoc()["credits"];
    }

    function isUserSelf($usr) {
        if (!isset($_SESSION["usr"])) {
            return false;
        }
        if ($usr == $_SESSION["usr"]) {
            return true;
        }
        return false;
    }
?>
