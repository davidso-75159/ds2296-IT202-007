<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>
<div class="container-fluid">
<form onsubmit="return validate(this)" method="POST">
    <?php render_input(["type"=>"email", "id"=>"email", "name"=>"email", "label"=>"Email", "rules"=>["required"=>true]]);?>
    <?php render_input(["type"=>"text", "id"=>"username", "name"=>"username", "label"=>"Username", "rules"=>["required"=>true, "maxlength"=>30]]);?>
    <?php render_input(["type"=>"password", "id"=>"password", "name"=>"password", "label"=>"Password", "rules"=>["required"=>true, "minlength"=>8]]);?>
    <?php render_input(["type"=>"password", "id"=>"confirm", "name"=>"confirm", "label"=>"Confirm Password", "rules"=>["required"=>true,"minlength"=>8]]);?>
    <?php render_button(["text"=>"Register", "type"=>"submit"]);?>
</form>
</div>

<script>
function validate(form) {
    let em = form.email.value;
    let un = form.username.value;
    let pw = form.password.value;
    let con = form.confirm.value;
    let isValid = true;

    if (em.length <= 0) {
        flash("Email is required", "warning");
        isValid = false;
    }
    
    if (!is_valid_email(em)) {
        flash("Invalid email format. Must contain '@'.", "warning");
        isValid = false;
    }

    if (un.length <= 0) {
        flash("Username is required", "warning");
        isValid = false;
    }

    if (!is_valid_username(un)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, ., _, or -", "warning");
        isValid = false;
    }

    if (pw.length <= 0) {
        flash("Password must not be empty", "warning");
        isValid = false;
    }

    if (!is_valid_password(pw) || !is_valid_password(con)) {
        flash("Password must be at least 8 characters long.", "warning");
        isValid = false;
    } 
    
    if (pw !== con) {
        flash("Password and Confirm password must match", "warning");
        isValid = false;
    }

    return isValid;
}
</script>

<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (
        strlen($password) > 0 && $password !== $confirm) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>