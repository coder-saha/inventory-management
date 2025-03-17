<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"]) && $_SESSION["user_type"] === "admin") {
  $data = [];
  $isError = false;
  
  if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id = htmlspecialchars(stripslashes($_GET["productId"]));
    $_SESSION["productId"] = $id;
    $_SESSION["isSkuPresent"] = false;
    $query1 = "SELECT * FROM products where product_id='$id';";
    $query2 = "SELECT * FROM sku WHERE product_id='$id';";
    try {
      $result1 = mysqli_query($conn, $query1);
      if (mysqli_num_rows($result1) > 0) {
        $data = mysqli_fetch_assoc($result1);
        $_SESSION["sku_prefix"] = $data["sku_prefix"];
      }
      $result2 = mysqli_query($conn, $query2);
      if (mysqli_num_rows($result2) > 0) {
        $_SESSION["isSkuPresent"] = true;
      }
    } catch (Exception $e) {
      writeLog($e->getMessage());
    }
  }
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_SESSION["isSkuPresent"]) {
      $_POST["sku_prefix"] = $_SESSION["sku_prefix"];
    }
    $errors = validateProduct($_POST);
    if (count($errors) === 0) {
      $id = $_SESSION["productId"];
      $product_name = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["product_name"])));
      $sku_prefix = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["sku_prefix"])));
      $mrp = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["mrp"])));
      $selling_price = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["selling_price"])));
      $description = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["description"])));
      $current_date = date('Y-m-d H:i:s');

      $validationQuery = "SELECT * FROM products WHERE product_id!='$id' AND deleted='0' AND (product_name='$product_name' OR sku_prefix='$sku_prefix');";
      $query1 = "UPDATE products SET product_name='$product_name', sku_prefix='$sku_prefix', mrp='$mrp', selling_price='$selling_price', description='$description', date_updated='$current_date' WHERE product_id='$id';";

      if ($_SESSION["isSkuPresent"]) {
        $validationQuery = "SELECT * FROM products WHERE product_id!='$id' AND deleted='0' AND product_name='$product_name';";
        $query1 = "UPDATE products SET product_name='$product_name', mrp='$mrp', selling_price='$selling_price', description='$description', date_updated='$current_date' WHERE product_id='$id';";
      }

      try {
        $validationResult = mysqli_query($conn, $validationQuery);
        if (mysqli_num_rows($validationResult) > 0) {
          if ($_SESSION["isSkuPresent"]) {
            $errors["product_name"] = "Product Name already exists";
          } else {
            $errors["product_name"] = "Product Name or SKU Prefix already exists";
            $errors["sku_prefix"] = "Product Name or SKU Prefix already exists";
          }
        } else {
          $result1 = mysqli_query($conn, $query1);
          if (mysqli_affected_rows($conn) > 0) {
            if (!$_SESSION["isSkuPresent"]) {
              $old_sku_prefix = $_SESSION["sku_prefix"];
              $query = "SELECT * FROM sequences WHERE sku_prefix='$old_sku_prefix';";
              $result = mysqli_query($conn, $query);
              $row = mysqli_fetch_assoc($result);
              $sequence_id = $row["sequence_id"];
              $query2 = "UPDATE sequences SET sku_prefix='$sku_prefix', date_updated='$current_date' WHERE sequence_id='$sequence_id';";
              $result2 = mysqli_query($conn, $query2);
            }
            unset($_SESSION["productId"]);
            unset($_SESSION["isSkuPresent"]);
            unset($_SESSION["sku_prefix"]);
            unset($_SESSION["purchaseProductList"]);
            unset($_SESSION["editPurchaseProductList"]);
            unset($_SESSION["saleProductList"]);
            unset($_SESSION["editSaleProductList"]);
            header("Location: manage_products");
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
          <a class="btn btn-primary" href="manage_products">Back to Products</a>
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
        <h3>Edit Product</h3>
        <br />
        <form method="post" action="edit_product" enctype="multipart/form-data" novalidate>
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="product_name"
                class="form-label<?= isset($errors["product_name"]) ? " is-invalid" : ""; ?>">Product Name</label>
              <input type="text" name="product_name"
                class="form-control<?= isset($errors["product_name"]) ? " is-invalid" : ""; ?>" id="product_name"
                value="<?= $data["product_name"] ?? $_POST["product_name"] ?>" required />
              <?php if (isset($errors["product_name"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["product_name"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="sku_prefix" class="form-label<?= isset($errors["sku_prefix"]) ? " is-invalid" : ""; ?>">SKU
                Prefix</label>
              <input type="text" name="sku_prefix"
                class="form-control<?= isset($errors["sku_prefix"]) ? " is-invalid" : ""; ?>" id="sku_prefix"
                value="<?= $data["sku_prefix"] ?? $_POST["sku_prefix"] ?>" required <?= $_SESSION["isSkuPresent"] ? "disabled" : "" ?> />
              <?php if (isset($errors["sku_prefix"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["sku_prefix"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="mrp" class="form-label<?= isset($errors["mrp"]) ? " is-invalid" : ""; ?>">MRP</label>
              <input type="text" name="mrp" class="form-control<?= isset($errors["mrp"]) ? " is-invalid" : ""; ?>"
                id="mrp" value="<?= $data["mrp"] ?? $_POST["mrp"] ?>" required />
              <?php if (isset($errors["mrp"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["mrp"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
              <label for="selling_price"
                class="form-label<?= isset($errors["selling_price"]) ? " is-invalid" : ""; ?>">Selling Price</label>
              <input type="text" name="selling_price"
                class="form-control<?= isset($errors["selling_price"]) ? " is-invalid" : ""; ?>" id="selling_price"
                value="<?= $data["selling_price"] ?? $_POST["selling_price"] ?>" required />
              <?php if (isset($errors["selling_price"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["selling_price"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
              <label for="description"
                class="form-label<?= isset($errors["description"]) ? " is-invalid" : ""; ?>">Description</label>
              <input type="text" name="description"
                class="form-control<?= isset($errors["description"]) ? " is-invalid" : ""; ?>" id="description"
                value="<?= $data["description"] ?? $_POST["description"] ?>" required />
              <?php if (isset($errors["description"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["description"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <button type="submit" class="btn btn-success btn-update">Update</button>
              <a class="btn btn-danger btn-cancel" href="manage_products">Cancel</a>
            </div>
          </div>
        </form>
        <?php if ($isError) { ?>
          <div class="alert alert-danger">Unable to update this product.</div>
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