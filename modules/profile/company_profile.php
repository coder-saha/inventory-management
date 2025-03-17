<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once ("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SESSION["user_type"] === "admin") {
    $query = "SELECT * FROM profiles WHERE deleted='0';";
    try {
      $result = mysqli_query($conn, $query);
    } catch (Exception $e) {
      writeLog($e->getMessage());
    }
    
    if (mysqli_num_rows($result) === 1) {
      $row = mysqli_fetch_assoc($result);
      $id = $row["profile_id"];
      $name = $row["name"];
      $email = $row["email"];
      $mobile = $row["mobile"];
      $address = $row["address"];
      $gst_no = $row["gst_no"];
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $errors = validateProfile($_POST);
      if (count($errors) === 0) {
        $name = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["name"])));
        $email = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["email"])));
        $mobile = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["mobile"])));
        $address = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["address"])));
        $gst_no = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["gst_no"])));
        $current_date = date('Y-m-d H:i:s');

        $query = "UPDATE profiles SET name='$name', email='$email', mobile='$mobile', address='$address', gst_no='$gst_no', deleted='0', 
        date_updated='$current_date' WHERE profile_id='1';";

        try {
          $result = mysqli_query($conn, $query);
          if (mysqli_affected_rows($conn) > 0) {
            //TODO: Update session variables for company details
            header("Location: ../main/dashboard");
            exit();
          }
        } catch (Exception $e) {
          writeLog($e->getMessage());
        } finally {
          mysqli_close($conn);
        }
      }
    }
     ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Inventory Management</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
      <link rel="stylesheet" href="../../styles.css" type="text/css" />
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    </head>

    <body>
      <header>
        <div class="header-container">
          <div class="heading-menu">
            <a class="btn btn-primary" href="../main/dashboard">Back to Home</a>
            <a class="btn btn-secondary" href="../login/logout">Logout</a>
          </div>
          <div class="sd-logo-container">
            <a href="https://swarnodigital.in/">
              <img class="sd-logo" src="../../images/sd-logo.webp" />
            </a>
          </div>
          <div class="heading-main">
            <h1>Inventory Management</h1>
          </div>
        </div>
      </header>
      <section class="content">
        <div class="edit-form-container">
          <h3>Company Profile</h3>
          <br />
          <form method="post" action="company_profile" enctype="multipart/form-data" novalidate>
          <div class="row hidden">
              <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <label for="id" class="form-label">ID</label>
                <input type="text" name="id" class="form-control" id="id" value="<?= $id ?>" required />
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" class="form-control" id="name" value="<?= $name ?>" required />
              </div>
              <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" name="email" class="form-control" id="email" value="<?= $email ?>" required />
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <label for="mobile" class="form-label">Mobile</label>
                <input type="text" name="mobile" class="form-control" id="mobile" value="<?= $mobile ?>" required />
              </div>
              <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" class="form-control" id="address" value="<?= $address ?>" required />
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                <label for="gst_no" class="form-label">GST Number</label>
                <input type="text" name="gst_no" class="form-control" id="gst_no" value="<?= $gst_no ?>" required />
              </div>
            </div>
            <div class="row">
              <div class="mb-3">
                <button type="submit" class="btn btn-success btn-update">Update</button>
                <a class="btn btn-danger btn-cancel" href="../main/dashboard">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </section>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    </body>

    </html>
  <?php } else {
    header("Location: ../main/dashboard");
    exit();
  }
} else {
  header("Location: ../login/user_login");
  exit();
}
?>