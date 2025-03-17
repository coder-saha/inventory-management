<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $saleProductList = $_SESSION["saleProductList"] ?? [];
  $customerType = $_SESSION["customer_type"] ?? "";
  if (!isset($_SESSION["sale_date"])) {
    $_SESSION["sale_date"] = date('Y-m-d');
  }
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Location: add_sale2");
  }
  try {
    $query = "SELECT * FROM customers WHERE customer_id!='1' ORDER BY date_added, customer_id;";
    $customers = mysqli_query($conn, $query);
    if (!isset($_SESSION["sale_invoice_no"])) {
      $query = "SELECT * FROM sequences WHERE sku_prefix='INVOICE';";
      $result = mysqli_query($conn, $query);
      $current_date = date('Y-m-d H:i:s');
      $current_year = date('Y');
      $date_pattern = date('dmY');
      if (mysqli_num_rows($result) > 0) {
        $serial = mysqli_fetch_assoc($result)["serial_no"];
        $serialValue = str_pad($serial, 4, '0', STR_PAD_LEFT);
        $_SESSION["sale_invoice_no"] = "MKMINV{$date_pattern}{$serialValue}";
      } else {
        $query = "INSERT INTO sequences (sequence_id, sku_prefix, serial_no, date_updated) VALUES (NULL, 'INVOICE', '1', '$current_date');";
        $result = mysqli_query($conn, $query);
        $serialValue = str_pad('1', 4, '0', STR_PAD_LEFT);
        $_SESSION["sale_invoice_no"] = "MKMINV{$date_pattern}{$serialValue}";
      }
    }
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"
      integrity="sha512-k/KAe4Yff9EUdYI5/IAHlwUswqeipP+Cp5qnrsUjTPCgl51La2/JhyyjNciztD7mWNKLSXci48m7cctATKfLlQ=="
      crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  </head>

  <body>
    <header>
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
    <section class="content">
      <div class="sale-form-container">
        <h3>Add Sale</h3>
        <br />
        <form action="">
          <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="customer_type" class="form-label">Customer Type</label>
              <br />
              <select name="customer_type"
                class="form-select customer-type-drop-down<?php echo isset($errors["customer_type"]) ? " is-invalid" : ""; ?>"
                id="customer_type">
                <option value="walkin" <?= $customerType === "walkin" ? 'selected' : ''; ?>>Walk-In</option>
                <option value="new" <?= $customerType === "new" ? 'selected' : ''; ?>>New Customer</option>
                <option value="existing" <?= $customerType === "existing" ? 'selected' : ''; ?>>Existing Customer</option>
              </select>
              <?php if (isset($errors["customer_type"])) { ?>
                <div class="invalid-feedback">
                  <?php echo $errors["customer_type"]; ?>
                </div>
              <?php } ?>
            </div>
            <div id="selectCustomer" class="col-lg-3 col-md-3 col-sm-12 mb-3 hidden">
              <label for="customer" class="form-label">Customer Name</label>
              <br />
              <select name="customer"
                class="form-select customer-drop-down<?php echo isset($errors["customer"]) ? " is-invalid" : ""; ?>"
                id="customer">
                <option value="0">Select</option>
                <?php
                while ($row = $customers->fetch_assoc()) {
                  ?>
                  <option value="<?php echo $row['customer_id']; ?>" <?php echo $row['customer_id'] === $_SESSION['customer_id'] ? 'selected' : ''; ?>>
                    <?php echo $row['customer_name']; ?>
                  </option>
                <?php } ?>
              </select>
              <?php if (isset($errors["customer"])) { ?>
                <div class="invalid-feedback">
                  <?php echo $errors["customer"]; ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="sale_invoice_no"
                class="form-label<?= isset($errors["sale_invoice_no"]) ? " is-invalid" : ""; ?>">Sale
                Invoice
                Number</label>
              <input type="text" name="sale_invoice_no" class="form-control" id="sale_invoice_no"
                value="<?= $_SESSION["sale_invoice_no"] ?? "" ?>" required disabled />
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="sale_date" class="form-label<?= isset($errors["sale_date"]) ? " is-invalid" : ""; ?>">Sale
                Date</label>
              <input type="date" name="sale_date"
                class="form-control<?= isset($errors["sale_date"]) ? " is-invalid" : ""; ?>" id="sale_date"
                value="<?= $_SESSION["sale_date"] ?? "" ?>" onblur="setSaleDate()" required />
              <?php if (isset($errors["sale_date"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["sale_date"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
        </form>
        <div id="addCustomer" class="hidden">
          <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="customer_name"
                class="form-label<?= isset($errors["customer_name"]) ? " is-invalid" : ""; ?>">Customer Name</label>
              <input type="text" name="customer_name"
                class="form-control<?= isset($errors["customer_name"]) ? " is-invalid" : ""; ?>" id="customer_name"
                value="" />
              <?php if (isset($errors["customer_name"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["customer_name"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="customer_mobile"
                class="form-label<?= isset($errors["customer_mobile"]) ? " is-invalid" : ""; ?>">Customer Mobile</label>
              <input type="text" name="customer_mobile"
                class="form-control<?= isset($errors["customer_mobile"]) ? " is-invalid" : ""; ?>" id="customer_mobile"
                value="" />
              <?php if (isset($errors["customer_mobile"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["customer_mobile"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="billing_address"
                class="form-label<?= isset($errors["billing_address"]) ? " is-invalid" : ""; ?>">Billing Address</label>
              <textarea name="billing_address"
                class="form-control<?= isset($errors["billing_address"]) ? " is-invalid" : ""; ?>" id="billing_address"
                value=""></textarea>
              <?php if (isset($errors["billing_address"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["billing_address"] ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
              <label for="shipping_address"
                class="form-label<?= isset($errors["shipping_address"]) ? " is-invalid" : ""; ?>">Shipping
                Address</label>
              <textarea name="shipping_address"
                class="form-control<?= isset($errors["shipping_address"]) ? " is-invalid" : ""; ?>" id="shipping_address"
                value=""></textarea>
              <?php if (isset($errors["shipping_address"])) { ?>
                <div class="invalid-feedback">
                  <?= $errors["shipping_address"] ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div id="customer_error" class="alert alert-danger hidden"></div>
          <div class="row">
            <div class="mb-3">
              <button class="btn btn-success" onclick="addCustomer()">Add Customer</button>
              <button class="btn btn-danger" onclick="cancelCustomer()">Cancel</button>
            </div>
          </div>
        </div>

        <form method="post" action="add_sale" enctype="multipart/form-data" novalidate>

          <div class="row">
            <div class="mb-3">
              <button type="submit" id="proceed" class="btn btn-success btn-update disabled">Proceed</button>
              <a class="btn btn-danger btn-cancel" href="manage_sales">Cancel</a>
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
      const customerDropDown = document.getElementById("customer");
      const selectCustomerDiv = document.getElementById("selectCustomer");
      const addCustomerDiv = document.getElementById("addCustomer");
      const saleInvoiceNumberBox = document.getElementById("sale_invoice_no");
      const saleDateBox = document.getElementById("sale_date");
      const customerNameBox = document.getElementById("customer_name");
      const customerMobileBox = document.getElementById("customer_mobile");
      const billingAddressBox = document.getElementById("billing_address");
      const shippingAddressBox = document.getElementById("shipping_address");
      const customerErrorDiv = document.getElementById("customer_error");
      const proceedBtn = document.getElementById("proceed");
      let customerType = customerTypeDropDown.value;
      let price = "";

      function validateSelection() {
        const cType = customerTypeDropDown.value;
        const sDate = saleDateBox.value;
        if (cType === "new" || sDate === "") {
          proceedBtn.classList.add("disabled");
        } else if (cType === "existing" && customerDropDown.value === "0") {
          proceedBtn.classList.add("disabled");
        } else {
          proceedBtn.classList.remove("disabled");
        }
      }

      switch (customerType) {
        case 'walkin': {
          selectCustomerDiv.classList.add("hidden");
          addCustomerDiv.classList.add("hidden");
          $.post("../functions/sale",
            {
              "operation": "setCustomerType",
              "customerType": "walkin"
            },
            function (data) {
              // handle response
            }
          );
          break;
        }

        case 'new': {
          selectCustomerDiv.classList.add("hidden");
          addCustomerDiv.classList.remove("hidden");
          break;
        }

        case 'existing': {
          selectCustomerDiv.classList.remove("hidden");
          addCustomerDiv.classList.add("hidden");
          break;
        }

        default: {
          selectCustomerDiv.classList.add("hidden");
          addCustomerDiv.classList.add("hidden");
          break;
        }
      }

      validateSelection();

      customerTypeDropDown.addEventListener("change", () => {
        customerType = customerTypeDropDown.value;

        switch (customerType) {
          case 'walkin': {
            selectCustomerDiv.classList.add("hidden");
            addCustomerDiv.classList.add("hidden");
            $.post("../functions/sale",
              {
                "operation": "setCustomerType",
                "customerType": "walkin"
              },
              function (data) {
                // handle response
                validateSelection();
              }
            );
            break;
          }

          case 'new': {
            selectCustomerDiv.classList.add("hidden");
            addCustomerDiv.classList.remove("hidden");
            $.post("../functions/sale",
              {
                "operation": "setCustomerType",
                "customerType": "new"
              },
              function (data) {
                // handle response
                validateSelection();
              }
            );
            break;
          }

          case 'existing': {
            selectCustomerDiv.classList.remove("hidden");
            addCustomerDiv.classList.add("hidden");
            $.post("../functions/sale",
              {
                "operation": "setCustomerType",
                "customerType": "existing"
              },
              function (data) {
                // handle response
                validateSelection();
              }
            );
            break;
          }

          default: {
            selectCustomerDiv.classList.add("hidden");
            addCustomerDiv.classList.add("hidden");
            $.post("../functions/sale",
              {
                "operation": "setCustomerType",
                "customerType": "0"
              },
              function (data) {
                // handle response
              }
            );
            break;
          }
        }
      });

      customerDropDown.addEventListener("change", () => {
        const customer_id = customerDropDown.value;
        const customer_name = customerDropDown.options[customerDropDown.selectedIndex].text;

        $.post("../functions/sale",
          {
            "operation": "setCustomer",
            "customerId": customer_id,
            "customerName": customer_name
          },
          function (data) {
            // handle response
            validateSelection();
          }
        );
      });

      function setSaleDate() {
        const date = saleDateBox.value;

        $.post("../functions/sale",
          {
            "operation": "setSaleDate",
            "saleDate": date
          },
          function (data) {
            // handle response
            validateSelection();
          }
        );
      }

      function addCustomer() {
        const name = customerNameBox.value;
        const mobile = customerMobileBox.value;
        const billAddress = billingAddressBox.value;
        const shipAddress = shippingAddressBox.value;
        $.post("../functions/sale",
          {
            "operation": "addCustomer",
            "customer_name": name,
            "customer_mobile": mobile,
            "billing_address": billAddress,
            "shipping_address": shipAddress
          },
          function (data) {
            // handle response
            if (data === "success") {
              location.reload();
            } else {
              customerErrorDiv.innerText = data;
              customerErrorDiv.classList.remove("hidden");
              setTimeout(() => {
                customerErrorDiv.classList.add("hidden");
              }, 4000);
            }
          }
        );
      }

      function cancelCustomer() {
        customerTypeDropDown.value = "walkin";
        selectCustomerDiv.classList.add("hidden");
        addCustomerDiv.classList.add("hidden");
        $.post("../functions/sale",
          {
            "operation": "setCustomerType",
            "customerType": "walkin"
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

    </script>
  </body>

  </html>
<?php } else {
  header("Location: ../login/user_login");
  exit();
}
?>