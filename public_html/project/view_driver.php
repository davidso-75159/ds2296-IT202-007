<?php
require_once(__DIR__ . "/../../partials/nav.php");

$id = (int)se($_GET, "id", -1, false);
$driver = [];
if ($id > 0) {
    $sql = "SELECT firstName, lastName, birthday, code, number, nationality, api_id, created, modified FROM `Drivers` WHERE id = :id";
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $driver = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching driver: " . var_export($e, true));
        flash("Error fetching driver", "danger");
    }
} else {
    flash("Invalid driver", "danger");
}
?>

<div class="container-fluid">
    <h3>Driver: <?php se($driver, "firstName", "Name not")?> <?php se($driver, "lastName", "Available"); ?></h3>
    <div>
        <a href="<?php echo get_url("list_drivers.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <div class="card mx-auto" style="width: 18rem;">
        <img src="https://www.fia.com/sites/default/files/styles/panopoly_image_original/public/fia_logo_final_.png" class="card-img-top" alt="...">
        <div class="card-body">
            <h5 class="card-title"><?php se($driver, "firstName", "Name not")?> <?php se($driver, "lastName", "Available");?></h5>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">Date of Birth: <?php se($driver, "birthday", "Unknown"); ?></li>
                    <li class="list-group-item">Number: <?php se($driver, "number", "Unknown"); ?></li>
                    <li class="list-group-item">3-letter code: <?php se($driver, "code", "Unknown"); ?></li>
                    <li class="list-group-item">Nationality: <?php se($driver, "nationality", "Unknown"); ?></li>
                    <li class="list-group-item">Hired: <?php se($driver, "created", "Unknown"); ?></li>
                    <li class="list-group-item">Modified: <?php se($driver, "modified", "Unknown"); ?></li>
                    <li class="list-group-item">API ID: <?php se($driver, "api_id", "Unknown"); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php 
require_once(__DIR__ . "/../../partials/flash.php");