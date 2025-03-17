<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ids = json_decode($_POST["productIds"]);
    $errorItems = [];
    foreach ($ids as $id) {
      try {
        $query = "SELECT * FROM sku WHERE product_id='$id';";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
          $query = "SELECT * FROM products WHERE product_id='$id';";
          $result = mysqli_query($conn, $query);
          $row = mysqli_fetch_assoc($result);
          $product = $row["product_name"];
          array_push($errorItems, $product);
        } else {
          $query1 = "DELETE FROM products WHERE product_id='$id';";
          $result1 = mysqli_query($conn, $query1);
          $query2 = "DELETE FROM sequences WHERE sku_prefix='$sku_prefix';";
          $result2 = mysqli_query($conn, $query2);
        }
      } catch (Exception $e) {
        writeLog($e->getMessage());
      }
    }
    mysqli_close($conn);
    if (count($errorItems) === 0) {
      echo "success";
    } else { ?>
      <div class="alert alert-danger">
        Unable to delete below products as bar codes have been generated:<br />
        <?php echo implode(", ", $errorItems); ?>
      </div>
    <?php }
    unset($_SESSION["purchaseProductList"]);
    unset($_SESSION["saleProductList"]);
  }
} else {
  header("Location: ../login/user_login");
  exit();
}
?>