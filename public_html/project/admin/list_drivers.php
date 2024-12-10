<?php
require(__DIR__ . "/../../../partials/nav.php");
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
// Pagination Variables
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$limit = isset($_GET['limit']) ? max(min((int)$_GET['limit'], 100), 10) : 10;
$offset = ($page - 1) * $limit;
// Default Query
$query = "SELECT firstName, lastName, birthday, code, number, nationality, is_api FROM `Drivers` WHERE 1=1";
$params = [];
// Handle Filtering
$filters = [
    'firstName' => se($_GET, "firstName", "", false),
    'lastName' => se($_GET, "lastName", "", false),
    'birthday' => se($_GET, "birthday", "", false),
    'code' => se($_GET, "code", "", false),
    'number' => se($_GET, "number", "", false),
    'nationality' => se($_GET, "nationality", "", false)
];
foreach ($filters as $key => $value) {
    if (!empty($value)) {
        if ($key === 'number' && ($value < 1 || $value > 99)) continue;
        $query .= " AND $key LIKE :$key";
        $params[":$key"] = ($key === 'number') ? $value : "%$value%";
    }
}
// Sorting
$valid_sort_columns = ["lastName", "birthday", "number", "nationality"];
$sort = in_array(se($_GET, "sort", "lastName", false), $valid_sort_columns) ? $_GET["sort"] : "lastName";
$valid_orders = ["asc", "desc"];
$order = in_array(se($_GET, "order", "desc", false), $valid_orders) ? $_GET["order"] : "desc";
$query .= " ORDER BY $sort $order";
// Pagination
$query .= " LIMIT :limit OFFSET :offset";
$params[":limit"] = $limit;
$params[":offset"] = $offset;
// Fetch Data
$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching drivers: " . $e->getMessage());
    flash("Error fetching driver data. Please try again later.", "danger");
}
// Pagination Controls
$total_query = "SELECT COUNT(*) as total FROM `Drivers` WHERE 1=1";
$total_stmt = $db->prepare($total_query);
try {
    $total_stmt->execute();
    $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_count / $limit);
} catch (PDOException $e) {
    error_log("Error fetching total drivers: " . $e->getMessage());
    $total_count = 0;
    $total_pages = 1;
}
?>
<div class="container-fluid">
    <h3>List Drivers</h3>
    <form method="GET" id="driverForm" onsubmit="return validateForm();">
        <div class="row mb-3" style="align-items: flex-end;">
            <?php
            $form = [
                ["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name"],
                ["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname"],
                ["type" => "date", "name" => "birthday", "placeholder" => "Birthday", "label" => "Birthday"],
                ["type" => "text", "name" => "code", "placeholder" => "Code (e.g., ALO)", "label" => "Code"],
                ["type" => "number", "name" => "number", "placeholder" => "Driver Number", "label" => "Number"],
                ["type" => "text", "name" => "nationality", "placeholder" => "Nationality", "label" => "Nationality"],
                ["type" => "select", "name" => "sort", "label" => "Sort By", "options" => ["lastName" => "Surname", "birthday" => "Birthday", "number" => "Number", "nationality" => "Nationality"]],
                ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "Ascending", "desc" => "Descending"]],
                ["type" => "number", "name" => "limit", "label" => "Records per Page", "value" => $limit],
            ];
            foreach ($form as $field) {
                render_input($field);
            }
            ?>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="?" class="btn btn-secondary">Clear</a>
    </form>
    <div class="table-responsive">
        <?php render_table(["data" => $results, "title" => "F1 Drivers", "ignored_columns" => ["id"]]); ?>
    </div>
    <!-- Pagination Controls -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i; ?>&<?= http_build_query($_GET); ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
<!-- JS Validation -->
<script>
    function validateForm() {
        const firstName = document.getElementsByName('firstName')[0].value.trim();
        const lastName = document.getElementsByName('lastName')[0].value.trim();
        const birthday = document.getElementsByName('birthday')[0].value;
        const code = document.getElementsByName('code')[0].value.trim();
        const number = document.getElementsByName('number')[0].value;
        const nationality = document.getElementsByName('nationality')[0].value.trim();
        const limit = document.getElementsByName('limit')[0].value;

        // Validate First Name and Last Name (letters only, optional)
        const nameRegex = /^[a-zA-Z\s]*$/;
        if (firstName && !nameRegex.test(firstName)) {
            flash("First Name must contain only letters and spaces.","warning");
            return false;
        }
        if (lastName && !nameRegex.test(lastName)) {
            flash("Last Name must contain only letters and spaces." ,"warning");
            return false;
        }

        // Validate Birthday (optional, but must be a valid date if provided)
        if (birthday) {
            const today = new Date();
            const enteredDate = new Date(birthday);
            if (enteredDate > today) {
                flash("Birthday cannot be in the future.","warning");
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

        // Validate Records Per Page (limit)
        const limitValue = parseInt(limit, 10);
        if (limitValue < 10 || limitValue > 100) {
            flash("Records per page must be between 10 and 100.","warning");
            return false;
        }

        // All validations passed
        return true;
    }
</script>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>