<?php
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
    $driver = $_POST;
    error_log("Cleaned up POST: " . var_export($driver, true));
}

// ds2296, 12/11/2024
if (empty($driver['firstName']) || empty($driver['lastName']) || empty($driver['birthday']) || empty($driver['code']) || empty($driver['number']) || empty($driver['nationality'])) {
    flash("All fields are required.", "warning");
    die(header("Location: edit_driver.php?id=" . $id)); // Redirect back to the form with the error
}

// Formatting Checks
if (!preg_match("/^[a-zA-Z\s]*$/", $driver['firstName']) || !preg_match("/^[a-zA-Z\s]*$/", $driver['lastName'])) {
    flash("First Name and Surname must contain only letters and spaces.", "warning");
    die(header("Location: edit_driver.php?id=" . $id)); // Redirect back to the form with the error
}

if (!preg_match("/^[A-Z]{3}$/", $driver['code'])) {
    flash("Code must consist of exactly 3 uppercase letters (e.g., ALO).", "warning");
    die(header("Location: edit_driver.php?id=" . $id)); // Redirect back to the form with the error
}

$birthday = new DateTime($driver['birthday']);
if ($birthday > new DateTime()) {
    flash("Birthday must be in the past.", "warning");
    die(header("Location: edit_driver.php?id=" . $id)); // Redirect back to the form with the error
}

if (!is_numeric($driver['number']) || $driver['number'] < 1 || $driver['number'] > 99) {
    flash("Driver Number must be between 1 and 99.", "warning");
    die(header("Location: edit_driver.php?id=" . $id)); // Redirect back to the form with the error
}

$db = getDB();
$query = "UPDATE `Drivers` SET ";

$params = [];
//per record
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
    flash("Updated record ", "success");
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

        // Validate First Name and Last Name (letters only, optional)
        const nameRegex = /^[a-zA-Z\s]*$/;
        if (firstName && !nameRegex.test(firstName)) {
            flash("First Name must contain only letters and spaces.","warning");
            return false;
        }
        if (lastName && !nameRegex.test(lastName)) {
            flash("Surname must contain only letters and spaces." ,"warning");
            return false;
        }

        // Validate Birthday (optional, but must be a valid date if provided)
        if (birthday) {
            const today = new Date();
            const enteredDate = new Date(birthday);
            if (enteredDate > today) {
                flash("Birthday must be in the past.","warning");
                return false;
            }
        }

        // Validate Code (3 uppercase letters)
        const codeRegex = /^[A-Z]{3}$/;
        if (code && !codeRegex.test(code)) {
            flash("Code must consist of exactly 3 uppercase letters (e.g., ALO).","warning");
            return false;
        }

        // Validate Driver Number (1-99)
        if (number) {
            const numValue = parseInt(number, 10);
            if (numValue < 1 || numValue > 99) {
                flash("Driver Number must be between 1 and 99.","warning");
                return false;
            }
        }

        // Validate Nationality (letters only, optional)
        if (nationality && !nameRegex.test(nationality)) {
            flash("Nationality must contain only letters and spaces.","warning");
            return false;
        }

        // All validations passed
        return true;
    }
</script>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>