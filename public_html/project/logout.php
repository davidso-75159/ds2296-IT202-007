<?php
session_start();
session_unset();
session_destroy();
require(__DIR__."/../../lib/functions.php");
flash("You have been logged out.");
header("Location: login.php");
?>