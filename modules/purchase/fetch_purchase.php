<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = htmlspecialchars(stripslashes($_POST["purchaseId"]));
  $query = "SELECT * FROM purchases WHERE deleted='0' AND purchase_invoice_no LIKE '%$id%' GROUP BY purchase_invoice_no;";
  try {
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
      ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Purchase Invoice No</th>
              <th>Supplier Name</th>
              <th>Purchase Date</th>
              <th>Grand Total</th>
              <th>Date Added</th>
              <th>Date Updated</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
              ?>
              <tr>
                <td>
                  <?php echo $row['purchase_invoice_no']; ?>
                </td>
                <td>
                  <?php echo $row['supplier_name']; ?>
                </td>
                <td>
                  <?php echo $row['purchase_date']; ?>
                </td>
                <td>
                  <?php echo $row['grand_total']; ?>
                </td>
                <td>
                  <?php echo $row['date_added']; ?>
                </td>
                <td>
                  <?php echo $row['date_updated']; ?>
                </td>
                <td>
                  <a href="edit_purchase?purchaseId=<?php echo $row['purchase_invoice_no'] ?>" class="action-btn">
                    <button class="btn btn-warning" title="edit"><i class="bi bi-pencil-square"></i></button>
                  </a>
                  <a href="../utils/print_barcode?purchaseId=<?php echo $row['purchase_invoice_no'] ?>" target="_blank"
                    class="action-btn">
                    <button class="btn btn-outline-dark" title="print barcodes"><i class="bi bi-printer-fill"></i></button>
                  </a>
                </td>
              </tr>
            </tbody>
          <?php } ?>
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