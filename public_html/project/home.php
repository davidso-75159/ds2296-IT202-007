<?php
require(__DIR__."/../../partials/nav.php");
?>
<h1>Home</h1>
<?php
error_log("SESSION DATA: " . var_export($_SESSION, true));
if(is_logged_in()){
  flash("Welcome, " . get_user_email()); 
}
else{
  flash("You're not logged in");
}
require(__DIR__."/../../partials/flash.php");
?>