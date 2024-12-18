<?php
session_start();
require(__DIR__ . "/../../../lib/functions.php");

if (isset($_POST["toggleLiked"])) {
    $driverId = se($_POST, "driverId", -1, false);
    $userId = get_user_id();
    if ($userId) {
        $db = getDB();
        $params = [":driver_id" => $driverId, ":user_id" => $userId];
        $needsDelete = false;
        try {
            $stmt = $db->prepare("INSERT INTO DriverAssociation (user_id, driver_id) VALUES (:user_id, :driver_id)");
            $stmt->execute($params);
            flash("Added to liked drivers", "success");
        } catch (PDOException $e) {
            // use duplicate error as a delete trigger
            if ($e->errorInfo[1] == 1062) {
                $needsDelete = true;
            } else {
                flash("Error liking driver", "danger");
                error_log("Error liking driver: " . var_export($e, true));
            }
        }
        if ($needsDelete) {
            try {
                $stmt = $db->prepare("DELETE FROM DriverAssociation WHERE driver_id = :driver_id AND user_id = :user_id");
                $stmt->execute($params);
                flash("Unliked Driver", "success");
            } catch (PDOException $e) {
                flash("Error unliking driver", "danger");
                error_log("Error unliking driver: " . var_export($e, true));
            }
        }
    } else {
        flash("You must be logged in to do this action", "warning");
    }
    $refer = $_SERVER["HTTP_REFERER"];
    if(empty($refer)){
        $refer = get_url("liked_drivers.php");
    }
    die(header("Location: $refer"));
}
flash("Error toggling liked", "danger");
die(header("Location: " . get_url("home.php")));