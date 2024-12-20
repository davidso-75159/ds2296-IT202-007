<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

$users = [];
$username = "";
$drivers = [];

if (isset($_POST["users"]) && isset($_POST["drivers"])) {
    $user_ids = $_POST["users"];
    $driver_ids = $_POST["drivers"];
    if (empty($user_ids) || empty($driver_ids)) {
        flash("Both users and drivers need to be selected", "warning");
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO DriverAssociation (user_id, driver_id, is_liked) 
                              VALUES (:uid, :did, 1) ON DUPLICATE KEY UPDATE is_liked = !is_liked");
        foreach ($user_ids as $uid) {
            foreach ($driver_ids as $did) {
                try {
                    $stmt->execute([":uid" => $uid, ":did" => $did]);
                    flash("Updated driver likes", "success");
                } catch (PDOException $e) {
                    error_log(var_export($e, true));
                }
            }
        }
    }
}

if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, (SELECT GROUP_CONCAT(Drivers.firstName, ' ', Drivers.lastName) FROM DriverAssociation JOIN Drivers ON DriverAssociation.driver_id = Drivers.id WHERE DriverAssociation.user_id = Users.id) as is_liked
                              FROM Users WHERE username LIKE :username");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            } else {
                flash("No users found", "warning");
            }
        } catch (PDOException $e) {
            error_log(var_export($e, true));
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}

$db = getDB();
$stmt = $db->prepare("SELECT id, firstName, lastName FROM Drivers");
try {
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    flash(var_export($e, true), "danger");
}

?>
<div class="container-fluid">
    <h1>Assign Driver Likes</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]); ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Users</th>
                    <th>Liked Drivers</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td>
                            <?php render_input(["type" => "checkbox", "id" => "user_" . se($user, 'id', "", false), "name" => "users[]", "label" => se($user, "username", "", false), "value" => se($user, 'id', "", false)]); ?>
                        </td>
                        <td>
                            <?php echo $user['is_liked'] ? $user['is_liked'] : 'No Drivers'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Drivers", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>