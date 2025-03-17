<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"]) && $_SESSION["user_type"] === "admin") {
  $printSaleProductList = [];
  $customerType = "";
  $company_name = "";
  $company_email = "";
  $company_mobile = "";
  $company_address = "";
  $company_gst_no = "";
  $customer_id = "";
  $customer_name = "";
  $customer_mobile = "";
  $billing_address = "";
  $shipping_address = "";
  $invoice_date = "";
  $grand_total = 0.00;
  if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $sale_invoice_no = htmlspecialchars(stripslashes($_GET["saleId"]));
    try {
      // Get company details
      $query = "SELECT * FROM profiles WHERE deleted='0';";
      $result = mysqli_query($conn, $query);
      if (mysqli_num_rows($result) === 1) {
        $company = mysqli_fetch_assoc($result);
        $company_name = $company["name"];
        $company_email = $company["email"];
        $company_mobile = $company["mobile"];
        $company_address = $company["address"];
        $company_gst_no = $company["gst_no"];
      }
      $query = "SELECT * FROM sales WHERE sale_invoice_no='$sale_invoice_no' ORDER BY sale_id;";
      $result = mysqli_query($conn, $query);
      if (mysqli_num_rows($result) > 0) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Invoice details
        $sale_date = date_create($rows[0]["sale_date"]);
        $invoice_date = date_format($sale_date, "d-m-Y");
        // Get customer details
        $customer_id = $rows[0]["customer_id"];

        if ($customer_id === "1") {
          $customerType = "walkin";
          $customer_name = $rows[0]["customer_name"];
          $customer_mobile = "NA";
          $billing_address = "Customer";
          $shipping_address = $company_address;
        } else {
          $customerType = "existing";
          $query = "SELECT * FROM customers WHERE customer_id='$customer_id';";
          $result = mysqli_query($conn, $query);
          $customer = mysqli_fetch_assoc($result);
          $customer_name = $customer["customer_name"];
          $customer_mobile = $customer["customer_mobile"];
          $billing_address = $customer["billing_address"];
          $shipping_address = $customer["shipping_address"];
        }

        // Get product details
        for ($i = 0; $i < count($rows); $i++) {
          $row = $rows[$i];
          $id = $row["product_id"];
          $skuList = [];
          $query = "SELECT sku FROM sku WHERE product_id='$id' AND sale_invoice_no='$sale_invoice_no' ORDER BY sku_id";
          $result1 = mysqli_query($conn, $query);
          while ($skuRow = $result1->fetch_assoc()) {
            $skuList[] = $skuRow["sku"];
          }
          $query = "SELECT * FROM products where product_id='$id';";
          $result2 = mysqli_query($conn, $query);
          if (mysqli_num_rows($result2) > 0) {
            $data = mysqli_fetch_assoc($result2);
            $entry = array(
              "product_id" => $data["product_id"],
              "product_name" => $data["product_name"],
              "sku_prefix" => $data["sku_prefix"],
              "mrp" => $data["mrp"],
              "selling_price" => $row["selling_price"],
              "sale_qty" => $row["sale_qty"],
              "sku_list" => $skuList,
              "sub_total" => $row["sub_total"]
            );
            array_push($printSaleProductList, $entry);
          }
        }

        foreach ($printSaleProductList as $product) {
          $grand_total = $grand_total + floatval($product["sub_total"]);
        }
        $grand_total = number_format($grand_total, 2, '.', '');

      } else {
        header("Location: manage_sales");
      }
    } catch (Exception $e) {
      writeLog($e->getMessage());
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
    <title><?= $sale_invoice_no ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../styles.css" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
      integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"
      integrity="sha512-k/KAe4Yff9EUdYI5/IAHlwUswqeipP+Cp5qnrsUjTPCgl51La2/JhyyjNciztD7mWNKLSXci48m7cctATKfLlQ=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.2/html2pdf.bundle.min.js"
      integrity="sha512-MpDFIChbcXl2QgipQrt1VcPHMldRILetapBl5MPCA9Y8r7qvlwx1/Mc9hNTzY+kS5kX6PdoDq41ws1HiVNLdZA=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  </head>

  <body>
    <header class="not-printable">
      <div class="header-container">
        <div class="heading-menu">
          <a class="btn btn-primary" href="manage_sales">Back to Sales</a>
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
    <section class="print-section">
      <hr />
      <h2 style="text-align: center;">Invoice</h2>
      <hr />
      <div class="company-header-section">
        <div class="company-name"><?= $company_name ?></div>
        <div><?= $company_address ?></div>
        <div>Email: <?= $company_email ?></div>
        <div>Mobile: <?= $company_mobile ?></div>
        <div>GST: <?= $company_gst_no ?></div>
      </div>
      <hr />
      <div class="row invoice-detail-section">
        <div class="col-lg-6 col-md-6 col-sm-12">Invoice Number: <span class="invoice-no"><?= $sale_invoice_no ?></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">Invoice Date: <span class="invoice-date"><?= $invoice_date ?></span>
        </div>
      </div>
      <hr />
      <div class="row address-section">
        <div class="col-lg-6 col-md-6 col-sm-12">
          <div>Bill To:</div>
          <?php if ($customerType != "walkin") { ?>
            <div><?= $customer_name ?></div>
          <?php } ?>
          <div><?= $billing_address ?></div>
          <div>Mobile: <?= $customer_mobile ?></div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
          <div>Ship To:</div>
          <?php if ($customerType != "walkin") { ?>
            <div><?= $customer_name ?></div>
          <?php } ?>
          <div><?= $shipping_address ?></div>
          <div>Mobile: <?= $customer_mobile ?></div>
        </div>
      </div>
      <hr />
      <?php if (count($printSaleProductList) > 0) { ?>
        <div class="product-list mb-3">
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th width="30%">Product Name</th>
                  <th width="10%">MRP</th>
                  <th width="15%">Sale Price</th>
                  <th width="10%">Quantity</th>
                  <th width="15%">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($printSaleProductList as $row) {
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
        <div class="mb-3 align-right bill-total">
          Grand Total: ₹<?= $grand_total ?>
        </div>
      </div>
    </section>
    <div class="action-buttons not-printable">
      <div class="row">
        <div class="mt-3 mb-3">
          <button class="btn btn-success btn-update" onclick="printBill()">Print</button>
          <button class="btn btn-warning btn-update" onclick="downloadBill()">Download PDF</button>
          <a class="btn btn-danger btn-cancel" href="manage_sales">Cancel</a>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const data = document.querySelector(".print-section");
      function printBill() {
        window.print();
      }
      function downloadBill() {
        // html2pdf(data);
        const name = document.title;
        var opt = {
          margin: 1,
          filename: name,
          image: { type: 'jpeg', quality: 1 },
          html2canvas: { scale: 2 },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        // New Promise-based usage:
        html2pdf().set(opt).from(data).to('pdf').save();
      }
    </script>
  </body>

  </html>
<?php } else {
  header("Location: ../login/user_login");
  exit();
}
?>