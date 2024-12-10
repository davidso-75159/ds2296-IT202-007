<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $allowed_keys = ["firstName", "lastName", "birthday", "code", "number", "nationality"];
    $cleaned_post = array_intersect_key($_POST, array_flip($allowed_keys));

    if ($action === "fetch") {
        $search_params = $cleaned_post;
        $driver = fetch_driver($search_params);
    } elseif ($action === "create") {
        $driver = $cleaned_post;
    }

    if (!empty($driver)) {
        $drivers = [];
        foreach ($driver as $data) {
            $r = [
                "firstName" => $data["firstName"] ?? null,
                "lastName" => $data["lastName"] ?? null,
                "birthday" => isset($data["birthDate"]) ? substr($data["birthDate"], 0, 10) : null,
                "code" => $data["code"] ?? null,
                "number" => $data["number"] ?? null,
                "nationality" => $data["nationality"] ?? null,
                "api_id" => $data["id"] ?? null,
            ];
            array_push($drivers, $r);
        }
        insert("Drivers", $drivers, ["update_duplicate" => true]);
    }
}
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
            <?php render_input(["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "birthday", "placeholder" => "Birthday", "label" => "Birthday", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "code", "placeholder" => "i.e.'ALO','HAM','VET'", "label" => "3-letter code", "rules" => ["required" => "required"]]); ?> 
            <?php render_input(["type" => "number", "name" => "number", "placeholder" => "Must be between 1-99", "label" => "Number", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "nationality", "placeholder" => "i.e. 'British', 'German'", "label" => "Nationality", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "birthday", "placeholder" => "Birthday", "label" => "Birthday", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "code", "placeholder" => "i.e.'ALO','HAM','VET'", "label" => "3-letter code", "rules" => ["required" => "required"]]); ?> 
            <?php render_input(["type" => "number", "name" => "number", "placeholder" => "Must be between 1-99", "label" => "Number", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "nationality", "placeholder" => "i.e. 'British', 'German'", "label" => "Nationality", "rules" => ["required" => "required"]]); ?>
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