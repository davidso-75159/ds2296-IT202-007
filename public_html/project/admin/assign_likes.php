<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

if (isset($_POST["users"]) && isset($_POST["drivers"])) {
    $user_ids = $_POST["users"];
    $driver_ids = $_POST["drivers"];
    if (empty($user_ids) || empty($driver_ids)) {
        flash("Both users and drivers need to be selected", "warning");
    } else {
        $db = getDB();
        $ins = $db->prepare("INSERT INTO DriverAssociation (user_id, driver_id) VALUES (:uid, :did)");
        $del = $db->prepare("DELETE FROM DriverAssociation WHERE driver_id = :driver_id AND user_id = :user_id");
        foreach ($user_ids as $uid) {
            foreach ($driver_ids as $did) {
                try {
                    $ins->execute([":uid" => $uid, ":did" => $did]);
                    flash("Added driver likes", "success");
                } catch (PDOException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        try {
                            $del->execute([":uid" => $uid, ":did" => $did]);
                            flash("Removed driver likes", "success");
                        } catch (PDOException $e) {
                            flash("Error unliking driver", "danger");
                            error_log(var_export($e, true));
                        }
                    } else {
                        flash("Error liking driver", "danger");
                        error_log(var_export($e, true));
                    }
                }
            }
        }
    }
}

$drivers = [];
$db = getDB();
$stmt = $db->prepare("SELECT id, CONCAT(firstName, ' ', lastName) AS name FROM Drivers");
try {
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $drivers = $results;
    }
} catch (PDOException $e) {
    error_log(var_export($e->errorInfo, true));
}

$users = [];
$username = "";
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, (SELECT GROUP_CONCAT(Drivers.firstName, ' ', Drivers.lastName) 
                            FROM DriverAssociation JOIN Drivers ON DriverAssociation.driver_id = Drivers.id WHERE DriverAssociation.user_id = Users.id) as drivers
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
} ?>

<div class="container-fluid">
    <h1>Assign Likes</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>Drivers to Assign</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <?php render_input(["type" => "checkbox", "id" => "user_" . se($user, 'id', "", false), "name" => "users[]", "label" => se($user, "username", "", false), "value" => se($user, 'id', "", false)]); ?>
                                    </td>
                                    <td><?php se($user, "drivers", "No Drivers"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php foreach ($drivers as $driver) : ?>
                            <div>
                                <?php render_input(["type" => "checkbox", "id" => "driver_" . se($driver, 'id', "", false), "name" => "drivers[]", "label" => se($driver, "name", "", false), "value" => se($driver, 'id', "", false)]); ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Driver Likes", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>