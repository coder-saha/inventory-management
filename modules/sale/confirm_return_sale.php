<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $returnSaleProductList = $_SESSION["returnSaleProductList"] ?? [];
  $current_date = date('Y-m-d H:i:s');
  $return_date = date('Y-m-d');
  if ($_SERVER["REQUEST_METHOD"] === "POST" && count($returnSaleProductList) > 0) {
    try {
      foreach ($returnSaleProductList as $product) {
        $product_id = $product["product_id"];
        $sku = $product["sku"];
        $sale_invoice_no = $product["sale_invoice_no"];
        // update sku to purchased and remove sale invoice no
        $query1 = "UPDATE sku SET sale_invoice_no=NULL, return_date='$return_date', status='purchased', date_updated='$current_date' WHERE sku='$sku';";
        $result1 = mysqli_query($conn, $query1);
        if (mysqli_affected_rows($conn) > 0) {
          // update inventory qty and date updated
          $query = "SELECT * FROM inventory WHERE product_id='$product_id';";
          $result = mysqli_query($conn, $query);
          $qty = intval(mysqli_fetch_assoc($result)["quantity"]) + 1;
          $query2 = "UPDATE inventory SET quantity='$qty', date_updated='$current_date' WHERE product_id='$product_id';";
          $result2 = mysqli_query($conn, $query2);
          if (mysqli_affected_rows($conn) > 0) {
            // update sale invoice quantity, prices and date updated
            $query = "SELECT * FROM sales WHERE sale_invoice_no='$sale_invoice_no' AND product_id='$product_id';";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $sale_qty = intval($row["sale_qty"]) - 1;
            $selling_price = floatval($row["selling_price"]);
            $sub_total = floatval($row["sub_total"]) - $selling_price;
            $grand_total = floatval($row["grand_total"]) - $selling_price;
            $query3 = "UPDATE sales SET sale_qty='$sale_qty', sub_total='$sub_total' WHERE sale_invoice_no='$sale_invoice_no' AND product_id='$product_id';";
            if ($sale_qty === 0) {
              $query3 = "DELETE FROM sales WHERE sale_invoice_no='$sale_invoice_no' AND product_id='$product_id';";
            }
            $result3 = mysqli_query($conn, $query3);
            $query4 = "UPDATE sales SET grand_total='$grand_total', date_updated='$current_date' WHERE sale_invoice_no='$sale_invoice_no';";
            $result4 = mysqli_query($conn, $query4);
          }
        }
      }
      if (mysqli_affected_rows($conn) > 0) {
        unset($_SESSION["returnSaleProductList"]);
        header("Location: ../main/dashboard");
      }
    } catch (Exception $e) {
      writeLog($e->getMessage());
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../styles.css" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
      integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"
      integrity="sha512-k/KAe4Yff9EUdYI5/IAHlwUswqeipP+Cp5qnrsUjTPCgl51La2/JhyyjNciztD7mWNKLSXci48m7cctATKfLlQ=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  </head>

  <body>
    <header>
      <div class="header-container">
        <div class="heading-menu">
          <a class="btn btn-primary" href="../main/dashboard">Back to Dashboard</a>
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
        <h3>Return Sale</h3>
        <br />

        <?php if (count($returnSaleProductList) > 0) { ?>
          <div class="product-list mb-3">
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th width="30%">Product Name</th>
                    <th width="10%">MRP</th>
                    <th width="15%">Sale Price</th>
                    <th width="10%">SKU</th>
                    <th width="15%">Sale Invoice Number</th>
                  </tr>
                </thead>
                <tbody class="table-scroll">
                  <?php
                  foreach ($returnSaleProductList as $row) {
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
                        <?php echo $row['sku']; ?>
                      </td>
                      <td>
                        <?= $row['sale_invoice_no'] ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php } ?>

        <form method="post" action="confirm_return_sale" enctype="multipart/form-data" novalidate>

          <div class="row">
            <div class="mb-3">
              <button type="submit"
                class="btn btn-success btn-update <?= count($returnSaleProductList) > 0 ? "" : "disabled" ?>">Proceed</button>
              <a class="btn btn-danger btn-cancel" href="return_sale">Back</a>
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