<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ids = json_decode($_POST["customerIds"]);
    $errorItems = [];
    foreach ($ids as $id) {
      try {
        $query = "SELECT * FROM sales WHERE customer_id='$id';";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
          $query = "SELECT * FROM customers WHERE customer_id='$id';";
          $result = mysqli_query($conn, $query);
          $row = mysqli_fetch_assoc($result);
          $customer = $row["customer_name"];
          array_push($errorItems, $customer);
        } else {
          $query1 = "DELETE FROM customers WHERE customer_id='$id';";
          $result1 = mysqli_query($conn, $query1);
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
        Unable to delete below customers as bills have been generated:<br />
        <?php echo implode(", ", $errorItems); ?>
      </div>
    <?php }
    unset($_SESSION["customer_id"]);
    unset($_SESSION["customer_name"]);
    unset($_SESSION["edit_customer_id"]);
    unset($_SESSION["edit_customer_name"]);
  }
} else {
  header("Location: ../login/user_login");
  exit();
}
?>