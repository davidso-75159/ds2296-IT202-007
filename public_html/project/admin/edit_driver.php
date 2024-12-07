<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
$id = se($_GET, "id", -1, false);
// ds2296, 12/11/2024
foreach ($_POST as $k => $v) {
    if (!in_array($k, ["firstName", "lastName", "birthday", "code", "number", "nationality"])) {
        unset($_POST[$k]);
    }
    $race = $_POST;
    error_log("Cleaned up POST: " . var_export($driver, true));
}
//insert data
$db = getDB();
$query = "UPDATE `Drivers` SET ";

$params = [];
//per record
foreach ($race as $k => $v) {
    if ($params) {
        $query .= ",";
    }
    //be sure $k is trusted as this is a source of sql injection
    $query .= "$k=:$k";
    $params[":$k"] = $v;
}

$query .= " WHERE id = :id";
$params[":id"] = $id;
error_log("Query: " . $query);
error_log("Params: " . var_export($params, true));
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    flash("Updated record ", "success");
} catch (PDOException $e) {
    error_log("Something broke with the query" . var_export($e, true));
    flash("An error occurred", "danger");
}

$races = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT firstName, lastName, birthday, code, number, nationality FROM `Drivers` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $races = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_drivers.php")));
}
if ($drivers) {
    $form = [
        ["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "rules" => ["required" => "required"]],
        ["type" => "date", "name" => "birthday", "placeholder" => "Birthday", "label" => "Birthday", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "code", "placeholder" => "3-letter code", "label" => "3-letter code", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "nationality", "placeholder" => "Nationality", "label" => "Nationality", "rules" => ["required" => "required"]],
    ];
    $keys = array_keys($drivers);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $drivers[$v["name"]];
        }
    }
}
//TODO handle manual create event
?>
<div class="container-fluid">
    <h3>Edit Driver Details</h3>
    <div>
        <a href="<?php echo get_url("admin/list_drivers.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <form method="POST">
        <?php foreach ($form as $k => $v) {
            render_input($v);
        } ?>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
    </form>

</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>