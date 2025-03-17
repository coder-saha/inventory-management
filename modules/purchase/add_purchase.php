<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $purchaseProductList = $_SESSION["purchaseProductList"] ?? [];
  if (!isset($_SESSION["purchase_date"])) {
    $_SESSION["purchase_date"] = date('Y-m-d');
  }
  if ($_SERVER["REQUEST_METHOD"] === "POST" && count($purchaseProductList) > 0) {
    header("Location: confirm_purchase");
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
          <a class="btn btn-primary" href="manage_purchases">Back to Purchases</a>
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
        <h3>Add Purchase</h3>
        <br />
        <div class="edit-form-container">
          <form action="">
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                <label for="supplier" class="form-label">Supplier Name</label>
                <br />
                <select name="supplier"
                  class="form-select supplier-drop-down<?php echo isset($errors["supplier"]) ? " is-invalid" : ""; ?>"
                  id="supplier">
                  <option value="0">Select</option>
                  <?php
                  while ($row = $suppliers->fetch_assoc()) {
                    ?>
                    <option value="<?php echo $row['supplier_id']; ?>" <?php echo $row['supplier_id'] === $_SESSION["supplier_id"] ? 'selected' : ''; ?>>
                      <?php echo $row['supplier_name']; ?>
                    </option>
                  <?php } ?>
                </select>
                <?php if (isset($errors["supplier"])) { ?>
                  <div class="invalid-feedback">
                    <?php echo $errors["supplier"]; ?>
                  </div>
                <?php } ?>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                <label for="purchase_invoice_no"
                  class="form-label<?= isset($errors["purchase_invoice_no"]) ? " is-invalid" : ""; ?>">Purchase
                  Invoice
                  Number</label>
                <input type="text" name="purchase_invoice_no"
                  class="form-control<?= isset($errors["purchase_invoice_no"]) ? " is-invalid" : ""; ?>"
                  id="purchase_invoice_no" value="<?= $_SESSION["purchase_invoice_no"] ?? "" ?>" onblur="setPurchaseInvoiceNumber()" required />
                <?php if (isset($errors["purchase_invoice_no"])) { ?>
                  <div class="invalid-feedback">
                    <?= $errors["purchase_invoice_no"] ?>
                  </div>
                <?php } ?>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                <label for="purchase_date"
                  class="form-label<?= isset($errors["purchase_date"]) ? " is-invalid" : ""; ?>">Purchase Date</label>
                <input type="date" name="purchase_date"
                  class="form-control<?= isset($errors["purchase_date"]) ? " is-invalid" : ""; ?>" id="purchase_date"
                  value="<?= $_SESSION["purchase_date"] ?? "" ?>" onblur="setPurchaseDate()" required />
                <?php if (isset($errors["purchase_date"])) { ?>
                  <div class="invalid-feedback">
                    <?= $errors["purchase_date"] ?>
                  </div>
                <?php } ?>
              </div>
            </div>
          </form>

          <div>
            <div class="search-product">
              <div class="search-bar">
                <div class="input-group">
                  <input type="text" class="form-control" name="searchbox" id="searchbox" value=""
                    placeholder="Search Products" />
                  <span class="input-group-text btn-search" name="search" title="Search Products"
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

        <?php if (count($purchaseProductList) > 0) { ?>
          <div class="product-list mb-3">
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th width="30%">Product Name</th>
                    <th width="10%">MRP</th>
                    <th width="15%">Purchase Price</th>
                    <th width="10%">Quantity</th>
                    <th width="10%">Subtotal</th>
                    <th width="10%">Action</th>
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
                        <input type="text" name="purchase_price" class="form-control price-input"
                          value="<?php echo $row['purchase_price']; ?>" />
                        <button class="btn btn-success" onclick="updatePurchasePrice(<?= $row['product_id'] ?>)">Save</button>
                      </td>
                      <td>
                        <input type="text" name="purchase_qty" class="form-control quantity-input"
                          value="<?php echo $row['purchase_qty']; ?>" />
                        <button class="btn btn-success"
                          onclick="updatePurchaseQuantity(<?= $row['product_id'] ?>)">Save</button>
                      </td>
                      <td>
                        <?= $row['sub_total'] ?>
                      </td>
                      <td>
                        <button class="btn" title="Remove" onclick="removeProductFromPurchase(<?= $row['product_id'] ?>)"><i
                            class="bi bi-trash-fill"></i></button>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php } ?>

        <form method="post" action="add_purchase" enctype="multipart/form-data" novalidate>

          <div class="row">
            <div class="mb-3">
              <button type="submit" class="btn btn-success btn-update <?= count($purchaseProductList) > 0 ? "" : "disabled" ?>">Proceed</button>
              <a class="btn btn-danger btn-cancel" href="manage_purchases">Cancel</a>
            </div>
          </div>
        </form>

      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const supplierDropDown = document.getElementById("supplier");
      const purchaseInvoiceNumberBox = document.getElementById("purchase_invoice_no");
      const purchaseDateBox = document.getElementById("purchase_date");
      const searchBox = document.getElementById("searchbox");
      const priceBoxes = document.querySelectorAll(".price-input");
      const quantityBoxes = document.querySelectorAll(".quantity-input");
      let price = "";
      let quantity = "";

      priceBoxes.forEach(priceInputBox => {
        priceInputBox.addEventListener("keyup", function (event) {
          price = priceInputBox.value;
        });
      });

      quantityBoxes.forEach(quantityInputBox => {
        quantityInputBox.addEventListener("keyup", function (event) {
          quantity = quantityInputBox.value;
        });
      });

      searchBox.addEventListener("keyup", function (event) {
        searchProducts();
      });

      supplierDropDown.addEventListener("change", () => {
        const supplier_id = supplierDropDown.value;
        const supplier_name = supplierDropDown.options[supplierDropDown.selectedIndex].text;

        $.post("../functions/purchase",
          {
            "operation": "setSupplier",
            "supplierId": supplier_id,
            "supplierName": supplier_name
          },
          function (data) {
            // handle response
          }
        );
      });

      function setPurchaseInvoiceNumber() {
        const invoiceNo = purchaseInvoiceNumberBox.value;

        $.post("../functions/purchase",
          {
            "operation": "setPurchaseInvoiceNumber",
            "invoiceNo": invoiceNo
          },
          function (data) {
            // handle response
          }
        );
      }

      function setPurchaseDate() {
        const date = purchaseDateBox.value;

        $.post("../functions/purchase",
          {
            "operation": "setPurchaseDate",
            "purchaseDate": date
          },
          function (data) {
            // handle response
          }
        );
      }

      function searchProducts() {
        const id = searchbox.value.trim();
        if (id !== "" && id !== null) {
          $.post("../product/search_products",
            {
              "type": "id",
              "productId": id
            },
            function (data) {
              $(".search-list").html(data);
            }
          );
        } else {
          $(".search-list").html("");
        }
      }

      function addProductToPurchase(id) {
        $.post("../functions/purchase",
          {
            "operation": "addProductToPurchase",
            "productId": id
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      function removeProductFromPurchase(id) {
        $.post("../functions/purchase",
          {
            "operation": "removeProductFromPurchase",
            "productId": id
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      function updatePurchasePrice(id) {
        $.post("../functions/purchase",
          {
            "operation": "updatePurchasePrice",
            "productId": id,
            "purchasePrice": price
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      function updatePurchaseQuantity(id) {
        $.post("../functions/purchase",
          {
            "operation": "updatePurchaseQuantity",
            "productId": id,
            "purchaseQuantity": quantity
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