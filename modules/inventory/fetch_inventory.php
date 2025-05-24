<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = htmlspecialchars(stripslashes($_POST["productId"]));
  $query = "SELECT P.product_id, P.product_name, I.quantity, I.date_updated 
    FROM inventory I INNER JOIN products P ON I.product_id = P.product_id 
    WHERE P.product_name LIKE '%$id%' 
    ORDER BY I.date_updated DESC;";
  try {
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
      ?>
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
          <tbody>
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
                  <a href="../utils/print_barcode?mode=productId&productId=<?php echo $row['product_id']; ?>" target="_blank"
                    class="action-btn">
                    <button class="btn btn-outline-dark" title="print barcodes"><i class="bi bi-printer-fill"></i></button>
                  </a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } else { ?>
      <div class="alert alert-danger alert-box">No such records found.</div>
    <?php }
  } catch (Exception $e) {
    writeLog($e->getMessage()); ?>
    <div class="alert alert-danger alert-box">
      <?php echo $e->getMessage(); ?>
    </div>
  <?php }
}
?>