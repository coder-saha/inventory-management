<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"]) && $_SESSION["user_type"] === "admin") {
  $data = [];
  $isError = false;

  if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id = htmlspecialchars(stripslashes($_GET["customerId"]));
    $_SESSION["customerId"] = $id;
    $query = "SELECT * FROM customers where customer_id='$id';";
    try {
      $result = mysqli_query($conn, $query);
      if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
      }
    } catch (Exception $e) {
      writeLog($e->getMessage());
    }
  }
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = validateCustomer($_POST);
    if (count($errors) === 0) {
      $id = $_SESSION["customerId"];
      $customer_name = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["customer_name"])));
      $customer_mobile = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["customer_mobile"])));
      $billing_address = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["billing_address"])));
      $shipping_address = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["shipping_address"])));
      $current_date = date('Y-m-d H:i:s');

      $validationQuery = "SELECT * FROM customers WHERE customer_id!='$id' AND deleted='0' AND 
                          (customer_name='$customer_name' AND customer_mobile='$customer_mobile');";
      $query1 = "UPDATE customers SET customer_name='$customer_name', customer_mobile='$customer_mobile', billing_address='$billing_address', 
                shipping_address='$shipping_address', date_updated='$current_date' WHERE customer_id='$id';";

      try {
        $validationResult = mysqli_query($conn, $validationQuery);
        if (mysqli_num_rows($validationResult) > 0) {
          $errors["customer_name"] = "Customer Name and Mobile Number already exists";
          $errors["customer_mobile"] = "Customer Name and Mobile Number already exists";
        } else {
          $result1 = mysqli_query($conn, $query1);
          if (mysqli_affected_rows($conn) > 0) {
            unset($_SESSION["customerId"]);
            unset($_SESSION["customer_id"]);
            unset($_SESSION["customer_name"]);
            unset($_SESSION["edit_customer_id"]);
            unset($_SESSION["edit_customer_name"]);
            header("Location: manage_customers");
            exit();
          }
        }
      } catch (Exception $e) {
        $isError = true;
        writeLog($e->getMessage());
      } finally {
        mysqli_close($conn);
      }
    }
  } ?>
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
          <a class="btn btn-primary" href="manage_customers">Back to Customers</a>
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
        <h3>Edit Customer</h3>
        <br />
        <form method="post" action="edit_customer" enctype="multipart/form-data" novalidate>
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="customer_name"
                class="form-label<?= isset($errors["customer_name"]) ? " is-invalid" : ""; ?>">Customer Name</label>
              <input type="text" name="customer_name"
                class="form-control<?= isset($errors["customer_name"]) ? " is-invalid" : ""; ?>" id="customer_name"
                value="<?= $data["customer_name"] ?? $_POST["customer_name"] ?>" required />
              <?php if (isset($errors["customer_name"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["customer_name"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="customer_mobile"
                class="form-label<?= isset($errors["customer_mobile"]) ? " is-invalid" : ""; ?>">Customer Name</label>
              <input type="text" name="customer_mobile"
                class="form-control<?= isset($errors["customer_mobile"]) ? " is-invalid" : ""; ?>" id="customer_mobile"
                value="<?= $data["customer_mobile"] ?? $_POST["customer_mobile"] ?>" required />
              <?php if (isset($errors["customer_mobile"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["customer_mobile"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="billing_address"
                class="form-label<?= isset($errors["billing_address"]) ? " is-invalid" : ""; ?>">Billing Address</label>
              <textarea name="billing_address"
                class="form-control<?= isset($errors["billing_address"]) ? " is-invalid" : ""; ?>" id="billing_address"
                required><?= $data["billing_address"] ?? $_POST["billing_address"] ?></textarea>
              <?php if (isset($errors["billing_address"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["billing_address"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="shipping_address"
                class="form-label<?= isset($errors["shipping_address"]) ? " is-invalid" : ""; ?>">Shipping Address</label>
              <textarea name="shipping_address"
                class="form-control<?= isset($errors["shipping_address"]) ? " is-invalid" : ""; ?>" id="shipping_address"
                required><?= $data["shipping_address"] ?? $_POST["shipping_address"] ?></textarea>
              <?php if (isset($errors["shipping_address"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["shipping_address"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <button type="submit" class="btn btn-success btn-update">Update</button>
              <a class="btn btn-danger btn-cancel" href="manage_customers">Cancel</a>
            </div>
          </div>
        </form>
        <?php if ($isError) { ?>
          <div class="alert alert-danger">Unable to update this customer.</div>
        <?php } ?>
      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
  </body>

  </html>
<?php } else {
  header("Location: ../login/user_login");
  exit();
}
?>