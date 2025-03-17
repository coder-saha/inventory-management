<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    unset($_SESSION["editPurchaseError"]);
    switch ($_POST["operation"]) {

      case 'setSupplier': {
        $supplier_id = $_POST["supplierId"];
        $supplier_name = $_POST["supplierName"];
        if ($supplier_id !== "0") {
          $_SESSION["edit_supplier_id"] = $supplier_id;
          $_SESSION["edit_supplier_name"] = $supplier_name;
          echo "success";
        } else {
          echo "error";
        }
        break;
      }

      /*case 'setPurchaseInvoiceNumber': {
        $_SESSION["edit_purchase_invoice_no"] = $_POST["invoiceNo"];
        break;
      }*/

      case 'setPurchaseDate': {
        $_SESSION["edit_purchase_date"] = $_POST["purchaseDate"];
        break;
      }

      case 'addProductToPurchase': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $query = "SELECT * FROM products where product_id='$id';";
        $editPurchaseProductList = $_SESSION["editPurchaseProductList"] ?? [];
        try {
          $entryPresent = false;
          for ($i = 0; $i < count($editPurchaseProductList); $i++) {
            if ($editPurchaseProductList[$i]["product_id"] === $id) {
              $price = floatval($editPurchaseProductList[$i]["purchase_price"]);
              $qty = intval($editPurchaseProductList[$i]["purchase_qty"]) + 1;
              $_SESSION["editPurchaseProductList"][$i]["purchase_qty"] = strval($qty);
              $_SESSION["editPurchaseProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
              if (isset($editPurchaseProductList[$i]["edited"])) {
                $_SESSION["editPurchaseProductList"][$i]["edited"] = "Y";
              }
              $entryPresent = true;
              break;
            }
          }
          if (!$entryPresent) {
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
              $data = mysqli_fetch_assoc($result);
              $entry = array(
                "product_id" => $data["product_id"],
                "product_name" => $data["product_name"],
                "sku_prefix" => $data["sku_prefix"],
                "mrp" => $data["mrp"],
                "selling_price" => $data["selling_price"],
                "purchase_price" => "0",
                "purchase_qty" => "1",
                "sub_total" => "0"
              );
              array_push($editPurchaseProductList, $entry);
              $_SESSION["editPurchaseProductList"] = $editPurchaseProductList;
            }
          }
          echo "success";
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      case 'removeProductFromPurchase': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $edit_purchase_invoice_no = $_SESSION["edit_purchase_invoice_no"];
        $query = "SELECT * FROM sku WHERE product_id='$id' AND purchase_invoice_no='$edit_purchase_invoice_no' AND status!='purchased';";
        $editPurchaseProductList = $_SESSION["editPurchaseProductList"] ?? [];
        try {
          $entryPresent = false;
          $result = mysqli_query($conn, $query);
          if (mysqli_num_rows($result) > 0) {
            $_SESSION["editPurchaseError"] = "This product cannot be deleted as it has been sold or returned.";
            echo "error";
          } else {
            for ($i = 0; $i < count($editPurchaseProductList); $i++) {
              if ($editPurchaseProductList[$i]["product_id"] === $id) {
                $entryPresent = true;
                break;
              }
            }
            if ($entryPresent) {
              if (isset($editPurchaseProductList[$i]["deleted"])) {
                $_SESSION["editPurchaseProductList"][$i]["deleted"] = "Y";
              } else {
                array_splice($editPurchaseProductList, $i, 1);
                $_SESSION["editPurchaseProductList"] = $editPurchaseProductList;
              }
            }
            echo "success";
          }
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      case 'updatePurchasePrice': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $purchasePrice = htmlspecialchars(stripslashes($_POST["purchasePrice"]));
        try {
          $editPurchaseProductList = $_SESSION["editPurchaseProductList"];
          if (preg_match('/^\d{0,8}(\.\d{1,2})?$/', $purchasePrice)) {
            for ($i = 0; $i < count($editPurchaseProductList); $i++) {
              if ($editPurchaseProductList[$i]["product_id"] === $id) {
                $price = floatval($purchasePrice);
                $qty = intval($editPurchaseProductList[$i]["purchase_qty"]);
                $_SESSION["editPurchaseProductList"][$i]["purchase_price"] = $purchasePrice;
                $_SESSION["editPurchaseProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
                if (isset($editPurchaseProductList[$i]["edited"])) {
                  $_SESSION["editPurchaseProductList"][$i]["edited"] = "Y";
                }
                break;
              }
            }
            echo "success";
          } else {
            writeLog("invalid price for Product ID: $id");
            echo "invalid price";
          }
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      case 'updatePurchaseQuantity': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $quantity = htmlspecialchars(stripslashes($_POST["purchaseQuantity"]));
        try {
          $editPurchaseProductList = $_SESSION["editPurchaseProductList"];
          $edit_purchase_invoice_no = $_SESSION["edit_purchase_invoice_no"];
          if (preg_match('/^[0-9]{1,4}$/', $quantity)) {
            $query = "SELECT count(*) FROM sku WHERE 
            product_id='$id' AND purchase_invoice_no='$edit_purchase_invoice_no' AND 
            status!='purchased';";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
              $minStock = mysqli_fetch_assoc($result)["count(*)"];
              if ($quantity < $minStock) {
                $_SESSION["editPurchaseError"] = "Minimum quantity for this Product is {$minStock}";
                break;
              } else {
                for ($i = 0; $i < count($editPurchaseProductList); $i++) {
                  if ($editPurchaseProductList[$i]["product_id"] === $id) {
                    $price = floatval($editPurchaseProductList[$i]["purchase_price"]);
                    $qty = intval($quantity);
                    $_SESSION["editPurchaseProductList"][$i]["purchase_qty"] = $quantity;
                    $_SESSION["editPurchaseProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
                    if (isset($editPurchaseProductList[$i]["edited"])) {
                      $_SESSION["editPurchaseProductList"][$i]["edited"] = "Y";
                    }
                    echo "success";
                    break;
                  }
                }
              }
            }

          } else {
            writeLog("invalid quantity for Product ID: $id");
            echo "invalid quantity";
          }
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      default: {
        echo "Invalid Operation";
        break;
      }
    }
  }
}