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
//TODO handle GP fetch
foreach ($_POST as $k => $v) {
    if (!in_array($k, ["officialName", "round", "date", "circuit", "poleman", "winner", "secondPlace", "thirdPlace"])) {
        unset($_POST[$k]);
    }
    $race = $_POST;
    error_log("Cleaned up POST: " . var_export($race, true));
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
    $query = "SELECT officialName, round, date, circuit, poleman, winner, secondPlace, thirdPlace FROM `Drivers` WHERE id = :id";
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
if ($races) {
    $form = [
        ["type" => "text", "name" => "officialName", "placeholder" => "Grand Prix Official Name", "label" => "Grand Prix Official Name", "rules" => ["required" => "required"]],
        ["type" => "number", "name" => "round", "placeholder" => "Round Number", "label" => "Round Number", "rules" => ["required" => "required"]],
        ["type" => "date", "name" => "date", "placeholder" => "Race Date", "label" => "Race Date", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "circuit", "placeholder" => "Circuit Name", "label" => "Circuit Name", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "poleman", "placeholder" => "Pole Position", "label" => "Pole Position", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "winner", "placeholder" => "Race Winner", "label" => "Race Winner", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "secondPlace", "placeholder" => "P2 Finisher", "label" => "P2 Finisher", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "thirdPlace", "placeholder" => "P3 Finisher", "label" => "P3 Finisher", "rules" => ["required" => "required"]],
    ];
    $keys = array_keys($races);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $races[$v["name"]];
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