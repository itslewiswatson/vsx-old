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
                            <img class="img-responsive" src=<?php echo $avatar; ?> >
                            <?php
                                if (isUserSelf($profile)) {
                                    ?>
                                    <hr>
                                    <div class="btn-group btn-group-justified" role="group" aria-label="...">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-default">Edit profile</button>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-default">Edit account settings</button>
                                        </div>
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                        <div class="col-md-8">
                            <p>Profile details</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    <?php

    _footer();
?>
