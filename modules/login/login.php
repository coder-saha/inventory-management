<?php
session_start();
include "../config/db_config.php";

function parseData($field)
{
  $data = htmlspecialchars(stripslashes(trim($field)));
  return $data;
}

$username = parseData($_POST["username"]);
$password = parseData($_POST["password"]);

if (empty ($username)) {
  header("Location: user_login?error=Username is required");
  exit();
} else if (empty ($password)) {
  header("Location: user_login?error=Password is required");
  exit();
}

$password = md5($password);
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
  $row = mysqli_fetch_assoc($result);
  if ($row["username"] === $username && $row["password"] === $password && $row["active"] === "1") {
    $_SESSION["username"] = $row["username"];
    $_SESSION["user_id"] = $row["user_id"];
    $_SESSION["user_type"] = $row["user_type"];
    header("Location: ../../index");
    exit();
  } else {
    header("Location: user_login?error=Incorrect username or password");
    exit();
  }
} else {
  header("Location: user_login?error=Incorrect username or password");
  exit();
}