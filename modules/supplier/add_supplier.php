<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = validateSupplier($_POST);
    if (count($errors) === 0) {
      $supplier_name = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["supplier_name"])));
      $supplier_mobile = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["supplier_mobile"])));
      $current_date = date('Y-m-d H:i:s');

      $validationQuery = "SELECT * FROM suppliers WHERE deleted='0' AND (supplier_name='$supplier_name' AND supplier_mobile='$supplier_mobile');";
      $query1 = "INSERT INTO suppliers (supplier_id, supplier_name, supplier_mobile, deleted, date_added, date_updated) 
                              VALUES (NULL, '$supplier_name', '$supplier_mobile', '0', '$current_date', '$current_date');";

      try {
        $validationResult = mysqli_query($conn, $validationQuery);
        if (mysqli_num_rows($validationResult) > 0) {
          $errors["supplier_name"] = "Supplier Name and Mobile Number already exists";
          $errors["supplier_mobile"] = "Supplier Name and Mobile Number already exists";
        } else {
          $result1 = mysqli_query($conn, $query1);
          if (mysqli_affected_rows($conn) > 0) {
            header("Location: manage_suppliers");
            exit();
          }
        }
      } catch (Exception $e) {
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
          <a class="btn btn-primary" href="manage_suppliers">Back to Suppliers</a>
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
        <h3>Add Supplier</h3>
        <br />
        <form method="post" action="add_supplier" enctype="multipart/form-data" novalidate>
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="supplier_name"
                class="form-label<?= isset($errors["supplier_name"]) ? " is-invalid" : ""; ?>">Supplier Name</label>
              <input type="text" name="supplier_name"
                class="form-control<?= isset($errors["supplier_name"]) ? " is-invalid" : ""; ?>" id="supplier_name"
                value="<?= $_POST["supplier_name"] ?>" required />
              <?php if (isset($errors["supplier_name"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["supplier_name"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="supplier_mobile"
                class="form-label<?= isset($errors["supplier_mobile"]) ? " is-invalid" : ""; ?>">Supplier Mobile</label>
              <input type="text" name="supplier_mobile"
                class="form-control<?= isset($errors["supplier_mobile"]) ? " is-invalid" : ""; ?>" id="supplier_mobile"
                value="<?= $_POST["supplier_mobile"] ?>" required />
              <?php if (isset($errors["supplier_mobile"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["supplier_mobile"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <button type="submit" class="btn btn-success btn-update">Add</button>
              <a class="btn btn-danger btn-cancel" href="manage_suppliers">Cancel</a>
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
  header("Location: ../login/user_login");
  exit();
}
?>