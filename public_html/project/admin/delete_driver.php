<?php
session_start();
require(__DIR__ . "/../../../lib/functions.php");
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("list_drivers.php")));
}

$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid id passed to delete", "danger");
    die(header("Location: " . get_url("/list_drivers.php")));
}

$db = getDB();
$query = "DELETE FROM `Drivers` WHERE id = :id";
try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    flash("Fired driver with id $id", "success");
} catch (Exception $e) {
    error_log("Error firing driver $id" . var_export($e, true));
    flash("Error firing driver", "danger");
}
die(header("Location: " . get_url("list_drivers.php")));
?>