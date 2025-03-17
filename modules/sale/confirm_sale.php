<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $saleProductList = $_SESSION["saleProductList"] ?? [];
  $grand_total = 0.00;
  foreach ($saleProductList as $row) {
    $grand_total = $grand_total + floatval($row["sub_total"]);
  }
  $grand_total = number_format($grand_total, 2, '.', '');
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = validateSale($_POST);
    if (count($errors) === 0) {
      $sale_invoice_no = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_SESSION["sale_invoice_no"])));
      $customer_id = $_SESSION["customer_id"];
      $customer_name = $_SESSION["customer_name"];
      $sale_date = $_SESSION["sale_date"];
      $current_date = date('Y-m-d H:i:s');
      $current_year = date('Y');

      try {
        foreach ($saleProductList as $product) {
          $product_id = $product["product_id"];
          $sale_qty = $product["sale_qty"];
          $selling_price = $product["selling_price"];
          $sku_prefix = $product["sku_prefix"];
          $sku_list = $product["sku_list"];
          $sub_total = $product["sub_total"];

          $query1 = "INSERT INTO sales 
          (sale_id, sale_invoice_no, customer_id, customer_name, sale_date, product_id, sale_qty, selling_price, sub_total, grand_total, deleted, date_added, date_updated) VALUES 
          (NULL, '$sale_invoice_no', '$customer_id', '$customer_name', '$sale_date', '$product_id', '$sale_qty', '$selling_price', '$sub_total', '$grand_total', '0', '$current_date', '$current_date');";
          $result1 = mysqli_query($conn, $query1);

          if (mysqli_affected_rows($conn) > 0) {
            $query = "SELECT * FROM inventory WHERE product_id='$product_id';";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
              $row = mysqli_fetch_assoc($result);
              $qty = intval($row["quantity"]);
              $qty = $qty - intval($sale_qty);
              $query2 = "UPDATE inventory SET quantity='$qty', date_updated='$current_date' WHERE product_id='$product_id';";
              $result2 = mysqli_query($conn, $query2);
              if (mysqli_affected_rows($conn) > 0) {
                $endIndex = count($sku_list);
                for ($i = 0; $i < $endIndex; $i++) {
                  $sku = $sku_list[$i];
                  $query3 = "UPDATE sku SET sale_invoice_no='$sale_invoice_no', sale_date='$sale_date', status='sold', date_updated='$current_date' WHERE sku='$sku';";
                  $result3 = mysqli_query($conn, $query3);
                }
              }
            }
          }
        }
        if (mysqli_affected_rows($conn) > 0) {
          $query = "SELECT * FROM sequences WHERE sku_prefix='INVOICE';";
          $result = mysqli_query($conn, $query);
          if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $serial = floatval($row["serial_no"]) + 1;
            $query = "UPDATE sequences SET serial_no='$serial', date_updated='$current_date' WHERE sku_prefix='INVOICE';";
            $result = mysqli_query($conn, $query);
          }
          unset($_SESSION["sale_date"]);
          unset($_SESSION["sale_invoice_no"]);
          unset($_SESSION["customer_type"]);
          unset($_SESSION["customer_id"]);
          unset($_SESSION["customer_name"]);
          unset($_SESSION["saleProductList"]);
          header("Location: manage_sales");
          exit();
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
    }
  }
  try {
    $query = "SELECT * FROM customers WHERE customer_id!='1' ORDER BY date_added, customer_id;";
    $customers = mysqli_query($conn, $query);
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
          <a class="btn btn-primary" href="add_sale2">Back to Add Sale</a>
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
      <div class="sale-form-container">
        <h3>Confirm Sale</h3>
        <br />
        <form action="">
          <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="customer_type" class="form-label">Customer Type</label>
              <br />
              <select name="customer_type" class="form-select customer-type-drop-down" id="customer_type" disabled>
                <option value="0">Select</option>
                <option value="walkin" <?= $_SESSION["customer_type"] === "walkin" ? 'selected' : ''; ?>>Walk-In</option>
                <option value="new" <?= $_SESSION["customer_type"] === "new" ? 'selected' : ''; ?>>New Customer</option>
                <option value="existing" <?= $_SESSION["customer_type"] === "existing" ? 'selected' : ''; ?>>Existing
                  Customer</option>
              </select>
            </div>
            <div id="selectCustomer" class="col-lg-3 col-md-3 col-sm-12 mb-3 hidden">
              <label for="customer" class="form-label">Customer Name</label>
              <br />
              <select name="customer" class="form-select customer-drop-down" id="customer" disabled>
                <option value="0">Select</option>
                <?php
                while ($row = $customers->fetch_assoc()) {
                  ?>
                  <option value="<?php echo $row['customer_id']; ?>" <?php echo $row['customer_id'] === $_SESSION['customer_id'] ? 'selected' : ''; ?>>
                    <?php echo $row['customer_name']; ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="sale_invoice_no" class="form-label">Sale
                Invoice
                Number</label>
              <input type="text" name="sale_invoice_no" class="form-control" id="sale_invoice_no"
                value="<?= $_SESSION["sale_invoice_no"] ?? "" ?>" required disabled />
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="sale_date" class="form-label">Sale
                Date</label>
              <input type="date" name="sale_date" class="form-control" id="sale_date"
                value="<?= $_SESSION["sale_date"] ?? "" ?>" required disabled />
            </div>
          </div>
        </form>
        <div class="edit-form-container">

        </div>

        <?php if (count($saleProductList) > 0) { ?>
          <div class="product-list mb-3">
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th width="40%">Product Name</th>
                    <th width="10%">MRP</th>
                    <th width="10%">Sale Price</th>
                    <th width="10%">Quantity</th>
                    <th width="10%">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="table-scroll">
                  <?php
                  foreach ($saleProductList as $row) {
                    ?>
                    <tr>
                      <td>
                        <?php echo $row['product_name']; ?>
                      </td>
                      <td>
                        <?php echo $row['mrp']; ?>
                      </td>
                      <td>
                        <?php echo $row['selling_price']; ?>
                      </td>
                      <td>
                        <?php echo $row['sale_qty']; ?>
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
        <form method="post" action="confirm_sale" enctype="multipart/form-data" novalidate>
          <div class="row">
            <div class="mb-3">
              <button type="submit" class="btn btn-success btn-update">Confirm</button>
              <a class="btn btn-danger btn-cancel" href="add_sale2">Back</a>
            </div>
          </div>
        </form>

      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const customerTypeDropDown = document.getElementById("customer_type");
      const selectCustomerDiv = document.getElementById("selectCustomer");
      const customerType = customerTypeDropDown.value;

      switch (customerType) {
        case 'walkin': {
          selectCustomerDiv.classList.add("hidden");
          break;
        }

        case 'new': {
          selectCustomerDiv.classList.add("hidden");
          break;
        }

        case 'existing': {
          selectCustomerDiv.classList.remove("hidden");
          break;
        }

        default: {
          selectCustomerDiv.classList.add("hidden");
          break;
        }
      }
    </script>
  </body>

  </html>
<?php } else {
  header("Location: ../login/user_login");
  exit();
}
?>