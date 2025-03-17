<?php
session_start();
require_once "../config/db_config.php";
require_once "../config/logger.php";

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  $page = isset($_GET["page"]) && $_GET["page"] > 0 ? $_GET["page"] : 1;
  if (isset($_GET["records"])) {
    $records = in_array($_GET["records"], [25, 50, 100]) ? $_GET["records"] : 25;
  } else {
    $records = 25;
  }
  try {
    $result = mysqli_query($conn, "SELECT count(*) FROM suppliers WHERE deleted='0';");
  } catch (Exception $e) {
    writeLog($e->getMessage());
  }
  $record_count = mysqli_fetch_assoc($result)["count(*)"];
  $page_count = max(ceil($record_count / $records), 1);
  $page = $page > $page_count ? $page_count : $page;
  $start_from = ($page - 1) * $records;
  $showPagination = $record_count > $records;
  $showNext = $page < $page_count;
  if (($page * $records) > ($record_count + $records)) {
    header("Location: manage_suppliers?page=" . $page_count . "&records=" . $records);
  } else {
    try {
      $query = "SELECT * FROM suppliers WHERE deleted='0' ORDER BY supplier_id LIMIT $records OFFSET $start_from;";
      $result = mysqli_query($conn, $query);
    } catch (Exception $e) {
      writeLog($e->getMessage());
    }
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
  </head>

  <body>
    <header>
      <div class="header-container">
        <div class="heading-menu">
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
      <h3>Manage Suppliers</h3>
      <br />
      <div class="msg-box"></div>

      <div>
        <div class="button-div">
          <a class="btn btn-primary" href="add_supplier">Add New</a>
          <button class="btn btn-danger btn-delete hidden" onclick="deleteSuppliers()">Delete Selected</button>
        </div>
        <div class="search-div">
          <div class="input-group">
            <input type="text" class="form-control" name="supplierId" id="supplierId" value="" placeholder="Search Suppliers" />
            <span class="input-group-text btn-search" name="search" title="Search Supplier" onclick="searchSupplier()">
              <i class="bi bi-search"></i>
            </span>
          </div>
        </div>
        <a class="btn btn-primary btn-dashboard hidden"
          href="manage_suppliers?page=<?php echo $page . "&records=" . $records; ?>">Back to Suppliers</a>

        <?php
        if (mysqli_num_rows($result) > 0) {
          if ($showPagination) { ?>
            <div class="pagination-container">
              <span>
                <?php echo number_format($record_count); ?> items
              </span>
              <button class="btn-pagination" title="First Page" onclick="firstPage()">
                << </button>
                  <button class="btn-pagination" title="Previous Page" onclick="prevPage()" <?php echo $page == 1 ? "disabled" : ""; ?>>
                    < </button>
                      <input type="text" name="page-no" id="page-no-top" class="form-control pagination-box"
                        value="<?php echo $page; ?>">
                      <span>of
                        <?php echo number_format($page_count); ?>
                      </span>
                      <button class="btn-pagination" title="Next Page" onclick="nextPage()" <?php echo !$showNext ? "disabled" : ""; ?>>></button>
                      <button class="btn-pagination" title="Last Page" onclick="lastPage()">>></button>
                      <span>Items per page:</span>
                      <select class="form-select pagination-box pagination-drop" id="records-limit-top">
                        <option value="25" <?php echo $records == 25 ? "selected" : ""; ?>>25</option>
                        <option value="50" <?php echo $records == 50 ? "selected" : ""; ?>>50</option>
                        <option value="100" <?php echo $records == 100 ? "selected" : ""; ?>>100</option>
                      </select>
                      <button class="btn-pagination btn-page-apply"
                        onclick="applyPagination(this.parentElement)">Apply</button>
            </div>
          <?php } ?>
        </div>
        <div class="supplier-list">
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th><input type="checkbox" id="select-all" class="check-box check-all"
                      onclick="checkAllItems(this.checked)" title="Select All" /></th>
                  <th>Supplier Name</th>
                  <th>Supplier Mobile</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="table-scroll">
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
                        <button class="btn" title="edit"><i class="bi bi-pencil-square"></i></button>
                      </a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php if ($showPagination) { ?>
          <div class="pagination-container">
            <span>
              <?php echo number_format($record_count); ?> items
            </span>
            <button class="btn-pagination" title="First Page" onclick="firstPage()">
              << </button>
                <button class="btn-pagination" title="Previous Page" onclick="prevPage()" <?php echo $page == 1 ? "disabled" : ""; ?>>
                  < </button>
                    <input type="text" name="page-no" id="page-no-bottom" class="form-control pagination-box"
                      value="<?php echo $page; ?>">
                    <span>of
                      <?php echo number_format($page_count); ?>
                    </span>
                    <button class="btn-pagination" title="Next Page" onclick="nextPage()" <?php echo !$showNext ? "disabled" : ""; ?>>></button>
                    <button class="btn-pagination" title="Last Page" onclick="lastPage()">>></button>
                    <span>Items per page:</span>
                    <select class="form-select pagination-box pagination-drop" id="records-limit-bottom">
                      <option value="25" <?php echo $records == 25 ? "selected" : ""; ?>>25</option>
                      <option value="50" <?php echo $records == 50 ? "selected" : ""; ?>>50</option>
                      <option value="100" <?php echo $records == 100 ? "selected" : ""; ?>>100</option>
                    </select>
                    <button class="btn-pagination btn-page-apply" onclick="applyPagination(this.parentElement)">Apply</button>
          </div>
        <?php }
        } else { ?>
        <div class="alert alert-danger alert-box">No records found.</div>
      <?php } ?>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
    <script>
      const recordsLimit = document.getElementById("records-limit");
      if (recordsLimit !== null) {
        recordsLimit.value = "<?php echo $records ?>";
      }
      let checkList = [];
      const deleteBtn = document.querySelector(".btn-delete");
      const dashboardBtn = document.querySelector(".btn-dashboard");
      const paginationSections = document.querySelectorAll(".pagination-container");
      const supplierId = document.getElementById("supplierId");

      supplierId.addEventListener("keyup", function (event) {
        if (event.keyCode == 13) {
          searchSupplier();
        }
      })

      function checkItem(checked, id) {
        if (checked) {
          checkList.push(id);
        } else {
          checkList.splice(checkList.indexOf(id), 1);
        }
        if (checkList.length == 0) {
          deleteBtn.classList.add("hidden");
        } else {
          deleteBtn.classList.remove("hidden");
        }
      }

      function checkAllItems(checked) {
        checkBoxes = document.querySelectorAll(".item-checkbox");
        checkList = [];
        if (checked) {
          checkBoxes.forEach(box => {
            box.checked = true;
            checkItem(true, box.id);
          });
        } else {
          deleteBtn.classList.add("hidden");
          checkBoxes.forEach(box => {
            box.checked = false;
          });
        }
      }

      function deleteSuppliers() {
        if (confirm("Are you sure you want to delete these suppliers ?")) {
          $.post("delete_suppliers",
            { "supplierIds": JSON.stringify(checkList) },
            function (data) {
              if (data === "success") {
                window.location.replace("manage_suppliers?page=" + "<?php echo $page ?>" + "&records=" + "<?php echo $records ?>");
              } else {
                $(".msg-box").html(data);
              }
            }
          )
        }
      }

      function searchSupplier() {
        const id = supplierId.value.trim();
        if (id !== "" && id !== null) {
          checkList = [];
          deleteBtn.classList.add("hidden");
          if (paginationSections.length) {
            paginationSections[0].classList.add("hidden");
            paginationSections[1].classList.add("hidden");
          }
          dashboardBtn.classList.remove("hidden");
          supplierId.value = id;
          if (id !== "") {
            $.post("fetch_supplier",
              {
                "supplierId": id
              },
              function (data) {
                $(".supplier-list").html(data);
              }
            )
          }
        }
      }

      function applyPagination(element) {
        const pageElements = element.children;
        window.location.replace("manage_suppliers?page=" + pageElements[3].value + "&records=" + pageElements[8].value);
      }

      function firstPage() {
        const recordSize = "<?php echo $records ?>";
        const page = 1;
        window.location.replace("manage_suppliers?page=" + page + "&records=" + recordSize);
      }

      function prevPage() {
        const recordSize = "<?php echo $records ?>";
        const page = parseInt("<?php echo $page ?>") - 1;
        window.location.replace("manage_suppliers?page=" + page + "&records=" + recordSize);
      }

      function nextPage() {
        const recordSize = "<?php echo $records ?>";
        const page = parseInt("<?php echo $page ?>") + 1;
        window.location.replace("manage_suppliers?page=" + page + "&records=" + recordSize);
      }

      function lastPage() {
        const recordSize = "<?php echo $records ?>";
        const page = "<?php echo $page_count ?>";
        window.location.replace("manage_suppliers?page=" + page + "&records=" + recordSize);
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