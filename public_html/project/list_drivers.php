<?php
require(__DIR__ . "/../../partials/nav.php");
if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/login.php"));
}
// ds2296, 12/11/2024
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$limit = isset($_GET['limit']) ? max(min((int)$_GET['limit'], 100), 10) : 10;
$offset = ($page - 1) * $limit;
// Default Query
$query = "SELECT firstName, lastName, birthday, code, number, nationality, is_api FROM `Drivers` WHERE 1=1";
$params = [];
// filter vars
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
        $query .= " AND $key LIKE :$key";
        $params[":$key"] = "%$value%";
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
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">
            <?php
            $form = [
                ["type" => "text", "name" => "firstName", "placeholder" => "First Name", "label" => "First Name"],
                ["type" => "text", "name" => "lastName", "placeholder" => "Surname", "label" => "Surname"],
                ["type" => "date", "name" => "birthday", "placeholder" => "YYYY-MM-DD", "label" => "Birthday"],
                ["type" => "text", "name" => "code", "placeholder" => "i.e., HAM", "label" => "3-letter code"],
                ["type" => "number", "name" => "number", "placeholder" => "Must be between 1-99", "label" => "Driver Number"],
                ["type" => "text", "name" => "nationality", "placeholder" => "i.e., British", "label" => "Nationality"],
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
<?php require_once(__DIR__ . "/../../partials/flash.php"); ?>