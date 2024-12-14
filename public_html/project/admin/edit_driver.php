<?php
require_once(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("list_drivers.php")));
}

$id = se($_GET, "id", -1, false);
$driver = [];
$hasError = false;

// Handle form submission
if (isset($_POST["save"])) {
    $firstName = se($_POST, "firstName", "", false);
    $lastName = se($_POST, "lastName", "", false);
    $birthday = se($_POST, "birthday", "", false);
    $code = se($_POST, "code", "", false);
    $number = se($_POST, "number", "", false);
    $nationality = se($_POST, "nationality", "", false);

    // Validation
    if (empty($firstName) || empty($lastName) || empty($birthday) || empty($code) || empty($number) || empty($nationality)) {
        flash("All fields are required.", "warning");
        $hasError = true;
    }

    if (!preg_match("/^[a-zA-Z\sÀ-ÖØ-öø-ÿ]*$/", $firstName) || !preg_match("/^[a-zA-Z\sÀ-ÖØ-öø-ÿ]*$/", $lastName)) {
        flash("Names must contain only letters and spaces.", "danger");
        $hasError = true;
    }

    if (!preg_match("/^[A-Z]{3}$/", $code)) {
        flash("Code must consist of exactly 3 uppercase letters (e.g., HAM).", "danger");
        $hasError = true;
    }

    $birthDate = new DateTime($birthday);
    if ($birthDate > new DateTime()) {
        flash("Birthday must be in the past.", "danger");
        $hasError = true;
    }

    if (!is_numeric($number) || $number < 1 || $number > 99) {
        flash("Driver Number must be between 1 and 99.", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        $db = getDB();
        $query = "UPDATE `Drivers` SET firstName = :firstName, lastName = :lastName, birthday = :birthday, code = :code, number = :number, nationality = :nationality WHERE id = :id";
        $params = [
            ":firstName" => $firstName,
            ":lastName" => $lastName,
            ":birthday" => $birthday,
            ":code" => $code,
            ":number" => $number,
            ":nationality" => $nationality,
            ":id" => $id
        ];

        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Driver details updated successfully!", "success");
        } catch (PDOException $e) {
            flash("An error occurred while updating driver details.", "danger");
        }
    }
}

// Fetch current driver data
if ($id > -1) {
    $db = getDB();
    $stmt = $db->prepare("SELECT firstName, lastName, birthday, code, number, nationality FROM `Drivers` WHERE id = :id LIMIT 1");
    try {
        $stmt->execute([":id" => $id]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$driver) {
            flash("Driver not found.", "danger");
            die(header("Location: " . get_url("list_drivers.php")));
        }
    } catch (PDOException $e) {
        flash("An error occurred while fetching driver data.", "danger");
        die(header("Location: " . get_url("list_drivers.php")));
    }
} else {
    flash("Invalid driver ID.", "danger");
    die(header("Location: " . get_url("list_drivers.php")));
}
?>

<div class="container-fluid">
    <h3>Edit Driver Details</h3>
    <a href="<?php echo get_url("list_drivers.php"); ?>" class="btn btn-secondary mb-3">Back to Driver List</a>
    <form method="POST" onsubmit="return validate(this);">
        <?php render_input(["type" => "text", "id" => "firstName", "name" => "firstName", "label" => "First Name", "value" => se($driver, "firstName", "", false), "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "text", "id" => "lastName", "name" => "lastName", "label" => "Last Name", "value" => se($driver, "lastName", "", false), "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "date", "id" => "birthday", "name" => "birthday", "label" => "Birthday", "value" => se($driver, "birthday", "", false), "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "text", "id" => "code", "name" => "code", "label" => "3-Letter Code", "value" => se($driver, "code", "", false), "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "number", "id" => "number", "name" => "number", "label" => "Driver Number", "value" => se($driver, "number", "", false), "rules" => ["required" => true, "min" => 1, "max" => 99]]); ?>
        <?php render_input(["type" => "text", "id" => "nationality", "name" => "nationality", "label" => "Nationality", "value" => se($driver, "nationality", "", false), "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "hidden", "name" => "save"]); ?>
        <?php render_button(["text" => "Update Driver", "type" => "submit"]); ?>
    </form>
</div>

<script>
function validate(form) {
    const nameRegex = /^[a-zA-Z\sÀ-ÖØ-öø-ÿ]*$/;
    const codeRegex = /^[A-Z]{3}$/;
    let isValid = true;

    const firstName = form.firstName.value.trim();
    const lastName = form.lastName.value.trim();
    const birthday = new Date(form.birthday.value);
    const code = form.code.value.trim();
    const number = parseInt(form.number.value, 10);
    const nationality = form.nationality.value.trim();

    if (!nameRegex.test(firstName)) {
        flash("First Name must contain only letters and spaces.", "warning");
        isValid = false;
    }
    if (!nameRegex.test(lastName)) {
        flash("Last Name must contain only letters and spaces.", "warning");
        isValid = false;
    }
    if (birthday > new Date()) {
        flash("Birthday must be in the past.", "warning");
        isValid = false;
    }
    if (!codeRegex.test(code)) {
        flash("Code must consist of exactly 3 uppercase letters (e.g., ALO).", "warning");
        isValid = false;
    }
    if (isNaN(number) || number < 1 || number > 99) {
        flash("Driver Number must be between 1 and 99.", "warning");
        isValid = false;
    }
    if (!nameRegex.test(nationality)) {
        flash("Nationality must contain only letters and spaces.", "warning");
        isValid = false;
    }
    return isValid;
}
</script>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>