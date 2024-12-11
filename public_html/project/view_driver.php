<?php
require_once(__DIR__ . "/../../partials/nav.php");

$id = (int)se($_GET, "id", -1, false);
$driver = [];
if ($id > 0) {
    $sql = "SELECT firstName, lastName, birthday, code, number, nationality, is_api, created, modified FROM `Drivers` WHERE id = :id";
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

<?php if($driver):?>
    <div>
        <?php render_table($driver); ?>
    </div>
<?php endif;?>
<?php 
require_once(__DIR__ . "/../../partials/flash.php");