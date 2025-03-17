<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = htmlspecialchars(stripslashes($_POST["saleId"]));
  $query = "SELECT * FROM sales WHERE deleted='0' AND sale_invoice_no LIKE '%$id%' GROUP BY sale_invoice_no;";
  try {
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
      ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Sale Invoice No</th>
              <th>Customer Name</th>
              <th>Sale Date</th>
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
                  <?php echo $row['sale_invoice_no']; ?>
                </td>
                <td>
                  <?php echo $row['customer_name']; ?>
                </td>
                <td>
                  <?php echo $row['sale_date']; ?>
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
                  <a href="edit_sale?saleId=<?php echo $row['sale_invoice_no'] ?>" class="action-btn">
                    <button class="btn btn-warning" title="edit"><i class="bi bi-pencil-square"></i></button>
                  </a>
                  <a href="../sale/print_sale?saleId=<?php echo $row['sale_invoice_no'] ?>" target="_blank" class="action-btn">
                    <button class="btn btn-outline-secondary" title="print bill"><i class="bi bi-printer-fill"></i></button>
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