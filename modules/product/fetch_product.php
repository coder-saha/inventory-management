<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = htmlspecialchars(stripslashes($_POST["productId"]));
  $query = "SELECT * FROM products WHERE deleted='0' AND (product_name LIKE '%$id%' OR sku_prefix LIKE '%$id%' OR description LIKE '%$id%');";
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
              <th>Product Name</th>
              <th>SKU Prefix</th>
              <th>MRP</th>
              <th>Selling Price</th>
              <th>Description</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
              ?>
              <tr>
                <td>
                  <input type="checkbox" id="<?php echo $row['product_id'] ?>" class="check-box item-checkbox"
                    onclick="checkItem(this.checked, this.id)" />
                </td>
                <td>
                  <?php echo $row['product_name']; ?>
                </td>
                <td>
                  <?php echo $row['sku_prefix']; ?>
                </td>
                <td>
                  <?php echo $row['mrp']; ?>
                </td>
                <td>
                  <?php echo $row['selling_price']; ?>
                </td>
                <td>
                  <?php echo $row['description']; ?>
                </td>
                <td>
                  <a href="edit_product?productId=<?php echo $row['product_id'] ?>">
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