<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $number =  (int)se($_POST, "number", 0, false);
    $driver = [];
    if ($action === "fetch") {
        $result = fetch_driver($number);
        error_log("Data from API" . var_export($result, true));
        if ($result) {
            $driver = array_map(function ($item) {
                return $item;
            }, $result);
        }
    } elseif ($action === "create") {
        foreach ($_POST as $k => $v) {
            if (!in_array($k, ["firstName", "lastName", "birthday", "code", "number", "nationality"])) {
                unset($_POST[$k]);
            }
            $driver = $_POST;
            error_log("Cleaned up POST: " . var_export($driver, true));
        }
    }

    $db = getDB();
    $query = "INSERT INTO `Drivers` ";
    $columns = [];
    $params = [];
    //per record
    foreach ($driver as $k => $v) {
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
        flash("Inserted driver " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
            error_log("Something broke with the query" . var_export($e, true));
            flash("An error occurred", "danger");
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
            <?php render_input(["type" => "number", "name" => "number", "placeholder" => "Must be between 1-99", "label" => "Number", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "birthday", "placeholder" => "YYYY-MM-DD", "label" => "Birthday", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "code", "placeholder" => "i.e. HAM", "label" => "3-letter code", "rules" => ["required" => "required"]]); ?> 
            <?php render_input(["type" => "number", "name" => "number", "placeholder" => "Must be between 1-99", "label" => "Number", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "nationality", "placeholder" => "i.e. British", "label" => "Nationality", "rules" => ["required" => "required"]]); ?>
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
    // ds2296 12/11/2024
    function validate(form) {
        const firstName = document.getElementsByName('firstName')[0].value.trim();
        const lastName = document.getElementsByName('lastName')[0].value.trim();
        const birthday = document.getElementsByName('birthday')[0].value;
        const code = document.getElementsByName('code')[0].value.trim();
        const number = document.getElementsByName('number')[0].value;
        const nationality = document.getElementsByName('nationality')[0].value.trim();

        const nameRegex = /^[a-zA-Z\s]*$/;
        if (firstName && !nameRegex.test(firstName)) {
            flash("First Name must contain only letters and spaces.","warning");
            return false;
        }
        if (lastName && !nameRegex.test(lastName)) {
            flash("Surname must contain only letters and spaces." ,"warning");
            return false;
        }

        if (birthday) {
            const today = new Date();
            const enteredDate = new Date(birthday);
            if (enteredDate > today) {
                flash("Birthday must be in the past.","warning");
                return false;
            }
        }

        const codeRegex = /^[A-Z]{3}$/;
        if (code && !codeRegex.test(code)) {
            flash("Code must consist of exactly 3 uppercase letters (i.e., HAM).","warning");
            return false;
        }

        if (number) {
            const numValue = parseInt(number, 10);
            if (numValue < 1 || numValue > 99) {
                flash("Driver Number must be between 1 and 99.","warning");
                return false;
            }
        }

        if (nationality && !nameRegex.test(nationality)) {
            flash("Nationality must contain only letters and spaces.","warning");
            return false;
        }

        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>