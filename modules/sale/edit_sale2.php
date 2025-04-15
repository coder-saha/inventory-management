<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"]) && $_SESSION["user_type"] === "admin") {
  $editSaleProductList = $_SESSION["editSaleProductList"] ?? [];
  $dateError = false;
  $customerType = $_SESSION["edit_customer_type"] ?? "";
  if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $edit_sale_invoice_no = htmlspecialchars(stripslashes($_GET["saleId"]));
    if (!isset($_SESSION["edit_sale_invoice_no"]) || $_SESSION["edit_sale_invoice_no"] !== $edit_sale_invoice_no) {
      $_SESSION["edit_sale_invoice_no"] = $edit_sale_invoice_no;
      unset($_SESSION["editSaleError"]);
      unset($_SESSION["editSaleProductList"]);
      $query = "SELECT * FROM sales WHERE sale_invoice_no='$edit_sale_invoice_no' ORDER BY sale_id;";
      try {
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
          $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
          $date1 = date_create($_SESSION["edit_sale_date"]);
          $date2 = date_create(date('Y-m-d'));
          $diff = date_diff($date1, $date2);
          $dateDiff = $diff->days;
          if ($dateDiff >= 30) {
            $dateError = true;
            unset($_SESSION["edit_sale_invoice_no"]);
          } else {
            $editSaleProductList = [];
            for ($i = 0; $i < count($rows); $i++) {
              $row = $rows[$i];
              $id = $row["product_id"];
              $skuList = [];
              $query = "SELECT sku FROM sku WHERE product_id='$id' AND sale_invoice_no='$edit_sale_invoice_no' ORDER BY sku_id";
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
                  "sub_total" => $row["sub_total"],
                  "edited" => "N",
                  "deleted" => "N"
                );
                array_push($editSaleProductList, $entry);
                $_SESSION["editSaleProductList"] = $editSaleProductList;
              }
            }
          }
        } else {
          // header("Location: manage_sales");
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
    }
  }
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Location: confirm_edit_sale");
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
        <h3>Edit Sale</h3>
        <br />
        <?php if ($dateError) { ?>
          <div class="alert alert-danger">You cannot edit this sale as it is older than 30 days.</div>
        <?php } else { ?>
          <form action="">
            <div class="row">
              <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                <label for="customer_type" class="form-label">Customer Type</label>
                <br />
                <select name="customer_type"
                  class="form-select customer-type-drop-down<?php echo isset($errors["customer_type"]) ? " is-invalid" : ""; ?>"
                  id="customer_type" disabled>
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
                  id="customer" disabled>
                  <option value="0">Select</option>
                  <?php
                  while ($row = $customers->fetch_assoc()) {
                    ?>
                    <option value="<?php echo $row['customer_id']; ?>" <?php echo $row['customer_id'] === $_SESSION['edit_customer_id'] ? 'selected' : ''; ?>>
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
                  value="<?= $_SESSION["edit_sale_invoice_no"] ?? "" ?>" required disabled />
              </div>
              <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                <label for="sale_date" class="form-label<?= isset($errors["sale_date"]) ? " is-invalid" : ""; ?>">Sale
                  Date</label>
                <input type="date" name="sale_date"
                  class="form-control<?= isset($errors["sale_date"]) ? " is-invalid" : ""; ?>" id="sale_date"
                  value="<?= $_SESSION["edit_sale_date"] ?? "" ?>" onblur="setSaleDate()" required disabled />
                <?php if (isset($errors["sale_date"])) { ?>
                  <div class="invalid-feedback">
                    <?= $errors["sale_date"] ?>
                  </div>
                <?php } ?>
              </div>
            </div>

          </form>
          <div class="edit-form-container">
            <?php if (isset($_SESSION["editSaleError"])) { ?>
              <div id="editSaleError" class="alert alert-danger"><?= $_SESSION["editSaleError"] ?? "" ?></div>
            <?php } ?>

            <div class="barcode-container mb-3">
              <div id="barcode-reader"></div>
              <div id="barcode-result"></div>
            </div>

            <div>
              <div class="search-product">
                <div class="search-bar mb-3">
                  <div class="input-group">
                    <input type="text" class="form-control" name="searchbox" id="searchbox" value=""
                      placeholder="Search Products" />
                    <span class="input-group-text btn-search" name="search" title="Search Products by SKU"
                      onclick="searchProducts()">
                      <i class="bi bi-search"></i>
                    </span>
                  </div>
                </div>
              </div>
              <div class="search-list mb-3">

              </div>
            </div>

          </div>

          <?php if (count($editSaleProductList) > 0) { ?>
            <div class="product-list mb-3">
              <div class="table-scroll">
                <table>
                  <thead>
                    <tr>
                      <th width="30%">Product Name</th>
                      <th width="10%">MRP</th>
                      <th width="15%">Sale Price</th>
                      <th width="10%">Quantity</th>
                      <th width="10%">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody class="table-scroll">
                    <?php
                    foreach ($editSaleProductList as $row) {
                      ?>
                      <tr>
                        <td>
                          <?php echo $row['product_name']; ?>
                        </td>
                        <td>
                          <?php echo $row['mrp']; ?>
                        </td>
                        <td>
                          <input type="text" name="sale_price" class="form-control price-input"
                            value="<?php echo $row['selling_price']; ?>" />
                          <button class="btn btn-success" onclick="updateSalePrice(<?= $row['product_id'] ?>)">Save</button>
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

          <form method="post" action="edit_sale2" enctype="multipart/form-data" novalidate>

            <div class="row">
              <div class="mb-3">
                <button type="submit" class="btn btn-success btn-update">Proceed</button>
                <a class="btn btn-danger btn-cancel"
                  href="edit_sale?saleId=<?= $_SESSION["edit_sale_invoice_no"] ?>">Back</a>
              </div>
            </div>
          </form>
        <?php } ?>
      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const customerTypeDropDown = document.getElementById("customer_type");
      const selectCustomerDiv = document.getElementById("selectCustomer");
      const searchBox = document.getElementById("searchbox");
      const priceBoxes = document.querySelectorAll(".price-input");
      const editSaleError = document.getElementById("editSaleError");
      let customerType = customerTypeDropDown.value;
      let price = "";

      // Hide error after 5 seconds
      setTimeout(() => {
        if (editSaleError != null) {
          editSaleError.hidden = true;
        }
      }, 5000);

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

      priceBoxes.forEach(priceInputBox => {
        priceInputBox.addEventListener("keyup", function (event) {
          price = priceInputBox.value;
        });
      });

      searchBox.addEventListener("keyup", function (event) {
        searchProducts();
      });

      function searchProducts() {
        const id = searchbox.value.trim();
        if (id !== "" && id !== null) {
          $.post("../product/search_products",
            {
              "type": "sku",
              "skuId": id
            },
            function (data) {
              $(".search-list").html(data);
            }
          );
        } else {
          $(".search-list").html("");
        }
      }

      function addProductToSale(id) {
        $.post("../functions/sale_edit",
          {
            "operation": "addProductToSale",
            "skuId": id
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      function removeProductFromSale(id) {
        $.post("../functions/sale_edit",
          {
            "operation": "removeProductFromSale",
            "productId": id
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      function updateSalePrice(id) {
        $.post("../functions/sale_edit",
          {
            "operation": "updateSalePrice",
            "productId": id,
            "salePrice": price
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      //barcode script
      const screenWidth = window.innerWidth;
      let scannerWidth = 600;
      let scannerHeight = 200;
      if (screenWidth < 600) {
        scannerWidth = screenWidth - 45;
        scannerHeight = 150;
      }
      const scanner = new Html5QrcodeScanner('barcode-reader', {
        // Scanner will be initialized in DOM inside element with id of 'reader'
        qrbox: {
          width: scannerWidth,
          height: scannerHeight,
        },  // Sets dimensions of scanning box (set relative to reader element width)
        fps: 120, // Frames per second to attempt a scan
      });


      scanner.render(success, error);
      // Starts scanner

      function success(result) {
        // document.getElementById('barcode-result').innerHTML = `${result}`;
        addProductToSale(result);


        // Prints result as a link inside result element

        scanner.clear();
        // Clears scanning instance

        // document.getElementById('barcode-reader').remove();
        // Removes reader element from DOM since no longer needed

      }

      function error(err) {
        console.error(err);
        // Prints any errors to the console
      }



    </script>
  </body>

  </html>
<?php } else {
  header("Location: ../login/user_login");
  exit();
}
?>