<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php

//TODO handle GP fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $race = [];
    if ($action === "fetch") {
        $fileContent = file_get_contents("GrandsPrix.txt");
        $race = eval('$result = ' . $fileContent . ';');
    } else if ($action === "create") {
        foreach ($_POST as $k => $v) {
            if (!in_array($k, ["officialName", "round", "date", "circuit", "poleman", "winner", "secondPlace", "thirdPlace"])) {
                unset($_POST[$k]);
            }
            $race = $_POST;
            error_log("Cleaned up POST: " . var_export($race, true));
        }
    }
    //map & insert data
    $races = [];
    foreach($race as $data) {
        $races["officialName"] = $data["officialName"];
        $races["round"] = $data["round"];
        $races["date"] = $data["date"];
        $races["circuit"] = $data["circuit"]["circuitName"];
        $races["poleman"] = $data["poleman"]["driverId"];
        $races["winner"] = $data["winner"]["driverId"];
        $races["secondPlace"] = $data["podium"][1]["driverId"];
        $races["thirdPlace"] = $data["podium"][2]["driverId"];
        array_push($races, $race);
    }
    $db = getDB();
    $query = "INSERT INTO `GrandsPrix` ";
    $columns = [];
    $params = [];
    //per record
    foreach ($races as $k => $v) {
        array_push($columns, "`$k`");
        $params[":$k"] = $v;
    }
    $query .= "(" . join(",", $columns) . ")";
    $query .= "VALUES (" . join(",", array_keys($params)) . ")";
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

//TODO handle manual create event
?>
<div class="container-fluid">
    <h3>Create or Fetch Event</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('create')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('fetch')">Create</a>
        </li>
    </ul>
    <div id="fetch" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "search", "name" => "season", "placeholder" => "Year", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">

            <?php render_input(["type" => "text", "name" => "officialName", "placeholder" => "Grand Prix Official Name", "label" => "Grand Prix Official Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "round", "placeholder" => "Round Number", "label" => "Round Number", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "date", "placeholder" => "Race Date", "label" => "Race Date", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "circuit", "placeholder" => "Circuit Name", "label" => "Circuit Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "poleman", "placeholder" => "Pole Position", "label" => "Pole Position", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "winner", "placeholder" => "Race Winner", "label" => "Race Winner", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "secondPlace", "placeholder" => "P2 Finisher", "label" => "P2 Finisher", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "thirdPlace", "placeholder" => "P3 Finisher", "label" => "P3 Finisher", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>
<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>