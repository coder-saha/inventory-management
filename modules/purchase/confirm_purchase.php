<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $purchaseProductList = $_SESSION["purchaseProductList"] ?? [];
  $grand_total = 0.00;
  foreach ($purchaseProductList as $row) {
    $grand_total = $grand_total + floatval($row["sub_total"]);
  }
  $grand_total = number_format($grand_total, 2, '.', '');
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = validatePurchase($_POST);
    if (count($errors) === 0) {
      $purchase_invoice_no = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_SESSION["purchase_invoice_no"])));
      $supplier_id = $_SESSION["supplier_id"];
      $supplier_name = $_SESSION["supplier_name"];
      $purchase_date = $_SESSION["purchase_date"];
      $current_date = date('Y-m-d H:i:s');
      $current_year = date('Y');

      try {
        foreach ($purchaseProductList as $product) {
          $product_id = $product["product_id"];
          $purchase_qty = $product["purchase_qty"];
          $purchase_price = $product["purchase_price"];
          $sku_prefix = $product["sku_prefix"];
          $sub_total = $product["sub_total"];

          $query1 = "INSERT INTO purchases 
          (purchase_id, purchase_invoice_no, supplier_id, supplier_name, purchase_date, product_id, purchase_qty, purchase_price, sub_total, grand_total, deleted, date_added, date_updated) VALUES 
          (NULL, '$purchase_invoice_no', '$supplier_id', '$supplier_name', '$purchase_date', '$product_id', '$purchase_qty', '$purchase_price', '$sub_total', '$grand_total', '0', '$current_date', '$current_date');";
          $result1 = mysqli_query($conn, $query1);

          if (mysqli_affected_rows($conn) > 0) {
            $query2 = "INSERT INTO inventory (inventory_id, product_id, quantity, date_updated) VALUES (NULL, '$product_id', '$purchase_qty', '$current_date');";
            $query = "SELECT * FROM inventory WHERE product_id='$product_id';";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
              $row = mysqli_fetch_assoc($result);
              $qty = intval($row["quantity"]);
              $qty = $qty + intval($purchase_qty);
              $query2 = "UPDATE inventory SET quantity='$qty', date_updated='$current_date' WHERE product_id='$product_id';";
            }
            $result2 = mysqli_query($conn, $query2);

            if (mysqli_affected_rows($conn) > 0) {
              $query = "SELECT * FROM sequences WHERE sku_prefix='$sku_prefix';";
              $result = mysqli_query($conn, $query);
              if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $serial = floatval($row["serial_no"]);
                for ($i = 0; $i < $purchase_qty; $i++) {
                  $serialValue = str_pad($serial, 4, '0', STR_PAD_LEFT);
                  $sku = "MKM{$sku_prefix}{$current_year}{$serialValue}";
                  $query3 = "INSERT INTO sku 
                  (sku_id, product_id, purchase_invoice_no, sale_invoice_no, sku, purchase_date, sale_date, return_date, status, date_added, date_updated) VALUES 
                  (NULL, '$product_id', '$purchase_invoice_no', NULL, '$sku', '$purchase_date', NULL, NULL, 'purchased', '$current_date', '$current_date');";
                  $result3 = mysqli_query($conn, $query3);
                  $serial++;
                }
                $query = "UPDATE sequences SET serial_no='$serial', date_updated='$current_date' WHERE sku_prefix='$sku_prefix';";
                $result = mysqli_query($conn, $query);
              }
            }
          }
        }
        if (mysqli_affected_rows($conn) > 0) {
          unset($_SESSION["purchase_date"]);
          unset($_SESSION["purchase_invoice_no"]);
          unset($_SESSION["supplier_id"]);
          unset($_SESSION["supplier_name"]);
          unset($_SESSION["purchaseProductList"]);
          header("Location: manage_purchases");
          exit();
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
    }
  }
  try {
    $query = "SELECT * FROM suppliers ORDER BY date_added, supplier_id;";
    $suppliers = mysqli_query($conn, $query);
  } catch (Exception $e) {
    writeLog($e->getMessage());
  } ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../styles.css" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
      integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  </head>

  <body>
    <header>
      <div class="header-container">
        <div class="heading-menu">
          <a class="btn btn-primary" href="add_purchase">Back to Add Purchase</a>
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
      <div class="purchase-form-container">
        <h3>Confirm Purchase</h3>
        <br />
        <div class="edit-form-container">
          <form action="">
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                <label for="supplier_name" class="form-label">Supplier Name</label>
                <input type="text" name="supplier_name" class="form-control" id="supplier_name"
                  value="<?= $_SESSION["supplier_name"] ?? "" ?>" required disabled />
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                <label for="purchase_invoice_no" class="form-label">Purchase
                  Invoice
                  Number</label>
                <input type="text" name="purchase_invoice_no" class="form-control" id="purchase_invoice_no"
                  value="<?= $_SESSION["purchase_invoice_no"] ?? "" ?>" required disabled />
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                <label for="purchase_date" class="form-label">Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control" id="purchase_date"
                  value="<?= $_SESSION["purchase_date"] ?? "" ?>" required disabled />
              </div>
            </div>
          </form>
        </div>

        <?php if (count($purchaseProductList) > 0) { ?>
          <div class="product-list mb-3">
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th width="40%">Product Name</th>
                    <th width="10%">MRP</th>
                    <th width="10%">Purchase Price</th>
                    <th width="10%">Quantity</th>
                    <th width="10%">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="table-scroll">
                  <?php
                  foreach ($purchaseProductList as $row) {
                    ?>
                    <tr>
                      <td>
                        <?php echo $row['product_name']; ?>
                      </td>
                      <td>
                        <?php echo $row['mrp']; ?>
                      </td>
                      <td>
                        <?php echo $row['purchase_price']; ?>
                      </td>
                      <td>
                        <?php echo $row['purchase_qty']; ?>
                      </td>
                      <td>
                        <?= $row['sub_total'] ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php } ?>

        <div class="row">
          <div class="mb-3" style="text-align: right;">
            Grand Total: ₹<?= $grand_total ?>
          </div>
        </div>
        <form method="post" action="confirm_purchase" enctype="multipart/form-data" novalidate>
          <div class="row">
            <div class="mb-3">
              <button type="submit" class="btn btn-success btn-update">Confirm</button>
              <a class="btn btn-danger btn-cancel" href="add_purchase">Back</a>
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