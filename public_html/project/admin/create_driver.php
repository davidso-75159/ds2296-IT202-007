<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
//ds2296, 12/11/24
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $driver = [];
    if ($action === "fetch") {
        $driver = fetch_driver();
    } else if ($action === "create") {
        foreach ($_POST as $k => $v) {
            if (!in_array($k, ["firstName", "lastName", "birthday", "code", "number", "nationality"])) {
                unset($_POST[$k]);
            }
            $driver = $_POST;
            error_log("Cleaned up POST: " . var_export($driver, true));
        }
    }
    //map & insert drivers
    $drivers = [];
    foreach($driver as $data) {
        $r = [];
        $r["firstName"] = $data["firstName"];
        $r["lastName"] = $data["lastName"];
        $r["birthday"] = $data["birthDate"];
        $r["code"] = $data["code"];
        $r["number"] = $data["number"];
        $r["nationality"] = $data["nationality"];
        array_push($drivers, $r);
    }

    insert("Drivers", $drivers, ["update_duplicate" => true]);
}

//ds2296, 12/11/2024
?>
<div class="container-fluid">
    <h3>Create or Fetch Driver</h3>
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
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "birthday", "placeholder" => "Birthday", "label" => "Birthday", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "number", "placeholder" => "Number", "label" => "Circuit Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "code", "placeholder" => "3-letter code", "label" => "3-letter code", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "nationality", "placeholder" => "Nationality", "label" => "Nationality", "rules" => ["required" => "required"]]); ?>
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