<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/list_driver.php"));
}
?>

<?php
$id = se($_GET, "id", -1, false);
foreach ($_POST as $k => $v) {
    if (!in_array($k, ["firstName", "lastName", "birthday", "code", "number", "nationality"])) {
        unset($_POST[$k]);
    }
    $driver = $_POST;
    error_log("Cleaned up POST: " . var_export($driver, true));
}

if (empty($driver['firstName']) || empty($driver['lastName']) || empty($driver['birthday']) || empty($driver['code']) || empty($driver['number']) || empty($driver['nationality'])) {
    flash("All fields are required.", "warning");
    die(header("Location: edit_driver.php?id=" . $id));
}

if (!preg_match("/^[a-zA-Z\sÀ-ÖØ-öø-ÿ]*$/", $driver['firstName']) || !preg_match("/^[a-zA-Z\sÀ-ÖØ-öø-ÿ]*$/", $driver['lastName'])) {
    flash("First Name and Surname must contain only letters and spaces.", "warning");
    die(header("Location: edit_driver.php?id=" . $id));
}

if (!preg_match("/^[A-Z]{3}$/", $driver['code'])) {
    flash("Code must consist of exactly 3 uppercase letters (e.g., ALO).", "warning");
    die(header("Location: edit_driver.php?id=" . $id));
}

$birthday = new DateTime($driver['birthday']);
if ($birthday > new DateTime()) {
    flash("Birthday must be in the past.", "warning");
    die(header("Location: edit_driver.php?id=" . $id));
}

if (!is_numeric($driver['number']) || $driver['number'] < 1 || $driver['number'] > 99) {
    flash("Driver Number must be between 1 and 99.", "warning");
    die(header("Location: edit_driver.php?id=" . $id));
}

$db = getDB();
$query = "UPDATE `Drivers` SET ";

$params = [];
//per driver
foreach ($driver as $k => $v) {
    if ($params) {
        $query .= ",";
    }
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
    flash("Updated driver ", "success");
} catch (PDOException $e) {
    error_log("Something broke with the query" . var_export($e, true));
    flash("An error occurred", "danger");
}

$drivers = [];
if ($id > -1) {
    $db = getDB();
    $query = "SELECT firstName, lastName, birthday, code, number, nationality FROM `Drivers` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $drivers = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching driver: " . var_export($e, true));
        flash("Error fetching driver", "danger");
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
        ["type" => "number", "name" => "number", "placeholder" => "Number", "label" => "Number", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "nationality", "placeholder" => "Nationality", "label" => "Nationality", "rules" => ["required" => "required"]],
    ];
    $keys = array_keys($drivers);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $drivers[$v["name"]];
        }
    }
}
?>
<div class="container-fluid">
    <h3>Edit Driver Details</h3>
    <div>
        <a href="<?php echo get_url("admin/list_drivers.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <form method="POST" onsubmit="return validate(this);">
        <?php foreach ($form as $k => $v) {
            render_input($v);
        } ?>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
    </form>
</div>
<script>
    // ds2296 12/11/2024
    function validate(form) {
        const firstName = document.getElementsByName('firstName')[0].value.trim();
        const lastName = document.getElementsByName('lastName')[0].value.trim();
        const birthday = document.getElementsByName('birthday')[0].value;
        const code = document.getElementsByName('code')[0].value.trim();
        const number = document.getElementsByName('number')[0].value;
        const nationality = document.getElementsByName('nationality')[0].value.trim();

        const nameRegex = /^[a-zA-Z\sÀ-ÖØ-öø-ÿ]*$/;
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
            flash("Code must consist of exactly 3 uppercase letters (e.g., ALO).","warning");
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