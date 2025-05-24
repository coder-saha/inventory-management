<?php
session_start();
require_once "../config/db_config.php";
require_once "../config/logger.php";

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  try {
    $query = "SELECT P.product_id, P.product_name, I.quantity, I.date_updated 
    FROM inventory I INNER JOIN products P ON I.product_id = P.product_id 
    ORDER BY I.date_updated DESC;";
    $result = mysqli_query($conn, $query);
  } catch (Exception $e) {
    writeLog($e->getMessage());
  }
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  </head>

  <body>
    <header>
      <div class="header-container">
        <div class="heading-menu">
          <a class="btn btn-primary" href="../product/manage_products">Go to Products</a>
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
      <a class="btn btn-primary" href="../main/dashboard">Back to Dashboard</a>
      <h3>Manage Inventory</h3>
      <br />
      <div class="search-div">
        <div class="input-group">
          <input type="text" class="form-control" name="productId" id="productId" value=""
            placeholder="Search Products" />
          <span class="input-group-text btn-search" name="search" title="Search Product" onclick="searchProduct()">
            <i class="bi bi-search"></i>
          </span>
        </div>
      </div>
      <a class="btn btn-primary btn-dashboard hidden"
        href="manage_inventory">Back to Inventory</a>
      <?php
      if (mysqli_num_rows($result) > 0) {
        ?>
        </div>
        <div class="inventory-list">
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>Quantity</th>
                  <th>Last Updated</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="table-scroll">
                <?php
                while ($row = $result->fetch_assoc()) {
                  ?>
                  <tr>
                    <td>
                      <?php echo $row['product_name']; ?>
                    </td>
                    <td>
                      <?php echo $row['quantity'] === "0" ? "Out of Stock" : $row['quantity']; ?>
                    </td>
                    <td>
                      <?php echo $row['date_updated']; ?>
                    </td>
                    <td>
                      <a href="../utils/print_barcode?mode=productId&productId=<?php echo $row['product_id']; ?>"
                        target="_blank" class="action-btn">
                        <button class="btn btn-outline-dark" title="print barcodes"><i
                            class="bi bi-printer-fill"></i></button>
                      </a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php } else { ?>
        <div class="alert alert-danger alert-box">No records found.</div>
      <?php } ?>

    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const dashboardBtn = document.querySelector(".btn-dashboard");
      const productId = document.getElementById("productId");

      productId.addEventListener("keyup", function (event) {
        if (event.keyCode == 13) {
          searchProduct();
        }
      });

      function searchProduct() {
        const id = productId.value.trim();
        if (id !== "" && id !== null) {
          dashboardBtn.classList.remove("hidden");
          productId.value = id;
          if (id !== "") {
            $.post("fetch_inventory",
              {
                "productId": id
              },
              function (data) {
                $(".inventory-list").html(data);
              }
            )
          }
        }
      }
    </script>
  </body>

  </html>
  <?php
} else {
  header("Location: ../login/user_login");
  exit();
}
?>