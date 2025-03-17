<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $type = $_POST["type"];
  $skuId = "";
  $query = "";
  $selling_price = "";
  $purchase_price = "";
  switch ($type) {
    case "id": {
      $id = htmlspecialchars(stripslashes($_POST["productId"]));
      $query = "SELECT * FROM products WHERE deleted='0' AND (product_name LIKE '%$id%' OR sku_prefix LIKE '%$id%' OR description LIKE '%$id%');";
      break;
    }

    case "sku": {
      try {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $query = "SELECT * FROM sku WHERE sku='$sku' AND status='purchased';";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
          $skuId = $sku;
          $product_id = mysqli_fetch_assoc($result)["product_id"];
          $query = "SELECT * FROM products WHERE product_id='$product_id';";
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
      break;
    }

    case "soldSku": {
      try {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $query = "SELECT * FROM sku WHERE sku='$sku' AND status='sold';";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
          $skuId = $sku;
          $item = mysqli_fetch_assoc($result);
          $product_id = $item["product_id"];
          $sale_invoice_no = $item["sale_invoice_no"];
          $query = "SELECT * FROM sales WHERE sale_invoice_no='$sale_invoice_no' AND product_id='$product_id';";
          $result = mysqli_query($conn, $query);
          $selling_price = mysqli_fetch_assoc($result)["selling_price"];
          $query = "SELECT * FROM products WHERE product_id='$product_id';";
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
      break;
    }

    case "purchasedSku": {
      try {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $query = "SELECT * FROM sku WHERE sku='$sku' AND status='purchased';";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
          $skuId = $sku;
          $item = mysqli_fetch_assoc($result);
          $product_id = $item["product_id"];
          $purchase_invoice_no = $item["purchase_invoice_no"];
          $query = "SELECT * FROM purchases WHERE purchase_invoice_no='$purchase_invoice_no' AND product_id='$product_id';";
          $result = mysqli_query($conn, $query);
          $purchase_price = mysqli_fetch_assoc($result)["purchase_price"];
          $query = "SELECT * FROM products WHERE product_id='$product_id';";
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
      break;
    }

    default:
      # code...
      break;
  }
  try {
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
      ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Product Name</th>
              <th>MRP</th>
              <th><?php
              if ($type === "purchasedSku") {
                echo "Purchase Price";
              } else {
                echo "Selling Price";
              } ?>
              </th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
              ?>
              <tr>
                <td>
                  <?php echo $row['product_name']; ?>
                </td>
                <td>
                  <?php echo $row['mrp']; ?>
                </td>
                <td>
                  <?php
                  if ($type === "soldSku") {
                    echo $selling_price;
                  } else if ($type === "purchasedSku") {
                    echo $purchase_price;
                  } else {
                    echo $row['selling_price'];
                  }
                  ?>
                </td>
                <td>
                  <?php if ($type === "sku") { ?>
                    <button id="<?php echo $skuId; ?>" class="btn btn-warning" title="Add Product to Bill"
                      onclick="addProductToSale(this.id)">Add Product <i class="bi bi-plus-square"></i></button>
                  <?php } else if ($type === "soldSku") { ?>
                      <button id="<?php echo $skuId; ?>" class="btn btn-warning" title="Add Product to Return"
                        onclick="addProductToSaleReturn(this.id)">Return Product <i class="bi bi-plus-square"></i></button>
                  <?php } else if ($type === "purchasedSku") { ?>
                        <button id="<?php echo $skuId; ?>" class="btn btn-warning" title="Add Product to Return"
                          onclick="addProductToPurchaseReturn(this.id)">Return Product <i class="bi bi-plus-square"></i></button>
                  <?php } else { ?>
                        <button id="<?php echo $row['product_id']; ?>" class="btn btn-warning" title="Add Product to Bill"
                          onclick="addProductToPurchase(this.id)">Add Product <i class="bi bi-plus-square"></i></button>
                  <?php } ?>
                </td>
              </tr>
            </tbody>
          <?php } ?>
        </table>
      </div>
    <?php } else { ?>
      <div class="alert alert-danger">No such records found.</div>
    <?php }
  } catch (Exception $e) {
    writeLog($e->getMessage()); ?>
    <div class="alert alert-danger alert-box">
      <?php echo $e->getMessage(); ?>
    </div>
  <?php }
}
?>