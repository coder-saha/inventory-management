<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $returnPurchaseProductList = $_SESSION["returnPurchaseProductList"] ?? [];
  if ($_SERVER["REQUEST_METHOD"] === "POST" && count($returnPurchaseProductList) > 0) {
    header("Location: confirm_return_purchase");
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
      <div class="purchase-form-container">
        <h3>Return Purchase</h3>
        <br />
        <div class="edit-form-container">

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

        <?php if (count($returnPurchaseProductList) > 0) { ?>
          <div class="product-list mb-3">
            <div class="table-scroll">
              <table>
                <thead>
                  <tr>
                    <th width="30%">Product Name</th>
                    <th width="10%">MRP</th>
                    <th width="15%">Purchase Price</th>
                    <th width="10%">SKU</th>
                    <th width="15%">Purchase Invoice Number</th>
                    <th width="10%">Action</th>
                  </tr>
                </thead>
                <tbody class="table-scroll">
                  <?php
                  foreach ($returnPurchaseProductList as $row) {
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
                        <?php echo $row['sku']; ?>
                      </td>
                      <td>
                        <?= $row['purchase_invoice_no'] ?>
                      </td>
                      <td>
                        <button class="btn" title="Remove" onclick="removeProductFromPurchaseReturn('<?= $row['sku'] ?>')"><i
                            class="bi bi-trash-fill"></i></button>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php } ?>

        <form method="post" action="return_purchase" enctype="multipart/form-data" novalidate>

          <div class="row">
            <div class="mb-3">
              <button type="submit"
                class="btn btn-success btn-update <?= count($returnPurchaseProductList) > 0 ? "" : "disabled" ?>">Proceed</button>
              <a class="btn btn-danger btn-cancel" href="add_purchase">Back</a>
            </div>
          </div>
        </form>

      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const searchBox = document.getElementById("searchbox");
      const priceBoxes = document.querySelectorAll(".price-input");

      searchBox.addEventListener("keyup", function (event) {
        searchProducts();
      });

      function searchProducts() {
        const id = searchbox.value.trim();
        if (id !== "" && id !== null) {
          $.post("../product/search_products",
            {
              "type": "purchasedSku",
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

      function addProductToPurchaseReturn(id) {
        $.post("../functions/purchase_return",
          {
            "operation": "addProductToPurchaseReturn",
            "skuId": id
          },
          function (data) {
            // handle response
            location.reload();
          }
        );
      }

      function removeProductFromPurchaseReturn(id) {
        $.post("../functions/purchase_return",
          {
            "operation": "removeProductFromPurchaseReturn",
            "skuId": id
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
        addProductToPurchaseReturn(result);


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