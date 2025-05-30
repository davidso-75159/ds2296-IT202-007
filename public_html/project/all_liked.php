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

$params = [];
$query = "SELECT Drivers.id, firstName, lastName, birthday, code, number, nationality, 1 as is_liked,
(SELECT count(user_id) FROM DriverAssociation WHERE driver_id = Drivers.id) as total_liked FROM Drivers 
JOIN DriverAssociation on driver_id = Drivers.id 
JOIN Users on DriverAssociation.user_id = Users.id";
$where = "  WHERE 1=1";

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

$total = 0;
$sql = "SELECT count(DISTINCT Drivers.id) AS c FROM Drivers 
JOIN DriverAssociation on driver_id = Drivers.id 
JOIN Users on DriverAssociation.user_id = Users.id $where";
try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    if (isset($params[":user_id"])) {
        unset($params[":user_id"]);
    }
    $stmt->execute($params);
    $r = $stmt->fetch();
    if ($r) {
        $total = (int)$r["c"];
    }
} catch (PDOException $e) {
    flash("Error fetching count", "danger");
    error_log("Error fetching count: " . var_export($e, true));
    error_log("Query: $sql");
    error_log("Params: " . var_export($params, true));
}

$total_pages = ceil($total / $limit);
if ($total <= 0) {
    $total_pages = 1;
}

$page = (int)se($_GET, "page", 1, false);
if ($page < 1) {
    $page = 1;
} elseif ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;
$query .= $where;
$query .= " ORDER BY $sort $order";
$query .= " LIMIT $offset, $limit";

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
    flash("Unhandled error occurred", "danger");
    error_log("Error fetching drivers " . var_export($e, true));
    error_log("Query: $query");
    error_log("Params: " . var_export($params, true));
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
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <div class="row">
        <div class="col">
            Results <?php echo count($results) . "/" . $total; ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a class="btn btn-warning" href="api/clear_liked.php">Clear List</a>
        </div>
    </div>
    <?php render_table($table); ?>
    <div class="row">
        <?php include(__DIR__ . "/../../partials/pagination_nav.php"); ?>
    </div>
</div>

<?php require_once(__DIR__ . "/../../partials/flash.php"); ?>