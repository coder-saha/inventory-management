<?php
session_start();
require_once "./modules/config/db_config.php";
require_once "./modules/config/logger.php";

if (isset ($_SESSION["user_id"]) && isset ($_SESSION["username"])) {
  header("Location: ./modules/main/dashboard");
} else {
  header("Location: ./modules/login/user_login");
  exit();
}
