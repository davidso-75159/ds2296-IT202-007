<?php
session_start();
session_unset();
session_destroy();
session_start();
require(__DIR__."/../../lib/functions.php");
flash("You have been logged out.", "success");
header("Location: login.php");
?>