<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

//build search form
$form = [
    ["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "include_margin" => false],
    ["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "include_margin" => false],
    ["type" => "date", "name" => "birthday", "placeholder" => "Birthday", "label" => "Birthday", "include_margin" => false],
    ["type" => "text", "name" => "code", "placeholder" => "3-letter code", "label" => "3-letter code", "include_margin" => false],
    ["type" => "number", "name" => "number", "placeholder" => "Number", "label" => "Number", "include_margin" => false],
    ["type" => "text", "name" => "nationality", "placeholder" => "Nationality", "label" => "Nationality", "include_margin" => false],
    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["lastName" => "Surname", "birthday" => "Birthday", "number" => "Number","nationality" => "Nationality"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],
    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],
];
error_log("Form data: " . var_export($form, true));



$query = "SELECT firstName, lastName, birthday, code, number, nationality, is_api FROM `Drivers` WHERE 1=1";
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
    if ($session_data) {
        $_GET = $session_data;
    }
}
if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }

    $firstName = se($_GET, "firstName", "", false);
    if (!empty($firstName)) {
        $query .= " AND firstName like :firstName";
        $params[":firstName"] = "%$firstName%";
    }

    $lastName = se($_GET, "lastName", "", false);
    if (!empty($lastName)) {
        $query .= " AND lastName like :lastName";
        $params[":lastName"] = "%$lastName%";
    }
    
    $birthday = se($_GET, "birthday", "", false);
    if (!empty($birthday) && $birthday != "") {
        $query .= " AND birthday = :birthday";
        $params[":birthday"] = $birthday;
    }

    $code = se($_GET, "code", 0, false);
    if (!empty($code) && $code != "") {
        $query .= " AND code like :code";
        $params[":code"] = $code;
    }

    $number = se($_GET, "number", "", false);
    if (!empty($number) && ($number > 0 && $number < 100)) {
        $query .= " AND number = :number";
        $params[":number"] = $number;
    }

    $nationality = se($_GET, "nationality", "", false);
    if (!empty($nationality) && $nationality != "") {
        $query .= " AND nationality like :nationality";
        $params[":nationality"] = "%$nationality%";
    }
    
    //sort and order
    $sort = se($_GET, "sort", "lastName", false);
    if (!in_array($sort, ["lastName", "birthday", "number", "nationality"])) {
        $sort = "lastName";
    }
    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }
    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
    $query .= " LIMIT $limit";
}

$query = "SELECT id, firstName, lastName, birthday, code, price, per_change, latest, volume, api_id FROM `Drivers` ORDER BY created DESC LIMIT 25";
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
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = [
    "data" => $results, "title" => "F1 Drivers", "ignored_columns" => ["id"],
    "edit_url" => get_url("admin/edit_driver.php"),
    "delete_url" => get_url("admin/delete_driver.php")
];
?>
<div class="container-fluid">
    <h3>List Drivers</h3>
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">

            <?php foreach ($form as $k => $v) : ?>
                <div class="col">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach; ?>

        </div>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
        <a href="?clear" class="btn btn-secondary">Clear</a>
    </form>
    <?php render_table($table); ?>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>