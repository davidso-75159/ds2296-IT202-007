<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
//attempt to apply
if (isset($_POST["users"]) && isset($_POST["Drivers"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $driver_ids = $_POST["Drivers"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($driver_ids)) {
        flash("Both users and Drivers need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO DriverAssociation (user_id, driver_id, is_liked) VALUES (:uid, :did, 1) 
        ON DUPLICATE KEY UPDATE is_liked = !is_liked");
        foreach ($user_ids as $uid) {
            foreach ($driver_ids as $did) {
                try {
                    $stmt->execute([":uid" => $uid, ":did" => $did]);
                    flash("Updated role", "success");
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}

//ds2296, 12/16/2024
$users = [];
$username = "";
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, (SELECT GROUP_CONCAT(Drivers.id) from 
        DriverAssociation JOIN Drivers on DriverAssociation.driver_id = Drivers.id WHERE DriverAssociation.user_id = Users.id) as is_liked
        from Users WHERE username like :username");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}


?>
<div class="container-fluid">
    <h1>Assign Drivers</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>Liked Drivers</th>
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
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Drivers", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>