<?php
    require "core.php";
    _header();
    global $db;

    if ($_GET && $_GET["profile"] && isset($_GET["profile"])) {
        if (isLoggedIn()) {
            $profile = $_GET["profile"];
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

    

    _footer();
?>
