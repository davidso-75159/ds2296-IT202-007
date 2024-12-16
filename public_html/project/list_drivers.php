<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/login.php"));
}

$form = [
    ["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "include_margin" => false],
    ["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "include_margin" => false],
    ["type" => "date", "name" => "birthday", "placeholder" => "YYYY-MM-DD", "label" => "Birthday", "include_margin" => false],
    ["type" => "text", "name" => "code", "placeholder" => "i.e., HAM", "label" => "3-letter code", "include_margin" => false],
    ["type" => "number", "name" => "number", "placeholder" => "Must be between 1-99", "label" => "Driver Number", "include_margin" => false],
    ["type" => "text", "name" => "nationality", "placeholder" => "i.e., British", "label" => "Nationality", "include_margin" => false],
    ["type" => "select", "name" => "sort", "label" => "Sort By", "options" => [["lastName" => "Surname"], ["birthday" => "Birthday"], ["number" => "Number"], ["nationality" => "Nationality"]], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => [["asc" => "Ascending"], ["desc" => "Descending"]], "include_margin" => false],
    ["type" => "number", "name" => "limit", "label" => "Records per Page", "value" => 10],
];

$assoc_check = " (SELECT IFNULL(count(1), 0) FROM DriverAssociation WHERE user_id = :user_id and driver_id = Drivers.id LIMIT 1) as is_liked,";
$params[":user_id"] = get_user_id();

$query = "SELECT $assoc_check (driver_id), firstName, lastName, birthday, code, number, nationality FROM `Drivers`";
$where = " WHERE 1=1";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    die(header("Location: " . $session_key));
} else {
    $session_data = session_load($session_key);
}

if (count($_GET) == 0 && isset($session_data) && count($session_data) > 0) {
    $_GET = $session_data;
}

if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    foreach ($form as $k => $v) {
        if (isset($_GET[$v["name"]])) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }

    $firstName = se($_GET, "firstName", "", false);
    if (!empty($firstName)) {
        $where .= " AND firstName LIKE :firstName";
        $params[":firstName"] = "%$firstName%";
    }

    $lastName = se($_GET, "lastName", "", false);
    if (!empty($lastName)) {
        $where .= " AND lastName LIKE :lastName";
        $params[":lastName"] = "%$lastName%";
    }

    $birthday = se($_GET, "birthday", "", false);
    if (!empty($birthday)) {
        $where .= " AND birthday = :birthday";
        $params[":birthday"] = $birthday;
    }

    $code = se($_GET, "code", "", false);
    if (!empty($code)) {
        $where .= " AND code LIKE :code";
        $params[":code"] = "%$code%";
    }

    $number = se($_GET, "number", "", false);
    if (!empty($number) && ($number > 0 && $number < 100)) {
        $where .= " AND number = :number";
        $params[":number"] = $number;
    }

    $nationality = se($_GET, "nationality", "", false);
    if (!empty($nationality)) {
        $where .= " AND nationality LIKE :nationality";
        $params[":nationality"] = "%$nationality%";
    }

    $sort = se($_GET, "sort", "lastName", false);
    if (!in_array($sort, ["lastName", "number", "birthday", "nationality"])) {
        $sort = "lastName";
    }

    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }
    
    $limit = 10;
    if (isset($_GET["limit"]) && !is_nan($_GET["limit"])) {
        $limit = (int)$_GET["limit"];
        if ($limit < 0 || $limit > 100) {
            $limit = 10;
        }
    }

    $query .= $where;
    $query .= " ORDER BY $sort $order";
    $page = (int)se($_GET, "page", 1, false);
    if ($page < 1) {
        $page = 1;
    }
    $offset = ($page - 1) * $limit;
    $query .= " LIMIT $offset, $limit";
}

$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching drivers " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = [
    "data" => $results, "title" => "Matching Drivers", "ignored_columns" => ["id"],
    "view_url" => get_url("view_driver.php"),
    "edit_url" => get_url("admin/edit_driver.php"),
    "delete_url" => get_url("admin/delete_driver.php")
];

?>
<div class="container-fluid">
    <h3>List Drivers</h3>
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">
            <?php foreach ($form as $v) : ?>
                <div class="col">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php render_button(["text" => "Filter", "type" => "submit"]); ?>
        <a href="?clear" class="btn btn-secondary">Clear</a>
    </form>
    <?php render_table($table); ?>
    <div class="row">
        <?php include(__DIR__ . "/../../partials/pagination_nav.php"); ?>
    </div>
</div>

<?php require_once(__DIR__ . "/../../partials/flash.php"); ?>