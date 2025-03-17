<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = htmlspecialchars(stripslashes($_POST["supplierId"]));
  $query = "SELECT * FROM suppliers WHERE deleted='0' AND (supplier_name LIKE '%$id%' OR supplier_mobile LIKE '%$id%');";
  try {
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
      ?>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th><input type="checkbox" id="select-all" class="check-box check-all" onclick="checkAllItems(this.checked)"
                  title="Select All" /></th>
              <th>Supplier Name</th>
              <th>Supplier Mobile</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
              ?>
              <tr>
                <td>
                  <input type="checkbox" id="<?php echo $row['supplier_id'] ?>" class="check-box item-checkbox"
                    onclick="checkItem(this.checked, this.id)" />
                </td>
                <td>
                  <?php echo $row['supplier_name']; ?>
                </td>
                <td>
                  <?php echo $row['supplier_mobile']; ?>
                </td>
                <td>
                  <a href="edit_supplier?supplierId=<?php echo $row['supplier_id'] ?>">
                    <button class="btn"><i class="bi bi-pencil-square"></i></button>
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