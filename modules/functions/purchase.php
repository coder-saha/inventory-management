<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    switch ($_POST["operation"]) {

      case 'setSupplier': {
        $supplier_id = $_POST["supplierId"];
        $supplier_name = $_POST["supplierName"];
        if ($supplier_id !== "0") {
          $_SESSION["supplier_id"] = $supplier_id;
          $_SESSION["supplier_name"] = $supplier_name;
          echo "success";
        } else {
          echo "error";
        }
        break;
      }

      case 'setPurchaseInvoiceNumber': {
        $_SESSION["purchase_invoice_no"] = $_POST["invoiceNo"];
        break;
      }

      case 'setPurchaseDate': {
        $_SESSION["purchase_date"] = $_POST["purchaseDate"];
        break;
      }

      case 'addProductToPurchase': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $query = "SELECT * FROM products where product_id='$id';";
        $purchaseProductList = $_SESSION["purchaseProductList"] ?? [];
        try {
          $entryPresent = false;
          for ($i = 0; $i < count($purchaseProductList); $i++) {
            if ($purchaseProductList[$i]["product_id"] === $id) {
              $price = floatval($purchaseProductList[$i]["purchase_price"]);
              $qty = intval($purchaseProductList[$i]["purchase_qty"]) + 1;
              $_SESSION["purchaseProductList"][$i]["purchase_qty"] = strval($qty);
              $_SESSION["purchaseProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
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
              array_push($purchaseProductList, $entry);
              $_SESSION["purchaseProductList"] = $purchaseProductList;
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
        $purchaseProductList = $_SESSION["purchaseProductList"] ?? [];
        try {
          $entryPresent = false;
          for ($i = 0; $i < count($purchaseProductList); $i++) {
            if ($purchaseProductList[$i]["product_id"] === $id) {
              $entryPresent = true;
              break;
            }
          }
          if ($entryPresent) {
            array_splice($purchaseProductList, $i, 1);
            $_SESSION["purchaseProductList"] = $purchaseProductList;
          }
          echo "success";
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
          $purchaseProductList = $_SESSION["purchaseProductList"];
          if (preg_match('/^\d{0,8}(\.\d{1,2})?$/', $purchasePrice)) {
            for ($i = 0; $i < count($purchaseProductList); $i++) {
              if ($purchaseProductList[$i]["product_id"] === $id) {
                $price = floatval($purchasePrice);
                $qty = intval($purchaseProductList[$i]["purchase_qty"]);
                $_SESSION["purchaseProductList"][$i]["purchase_price"] = $purchasePrice;
                $_SESSION["purchaseProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
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
          $purchaseProductList = $_SESSION["purchaseProductList"];
          if (preg_match('/^[0-9]{1,4}$/', $quantity)) {
            for ($i = 0; $i < count($purchaseProductList); $i++) {
              if ($purchaseProductList[$i]["product_id"] === $id) {
                $price = floatval($purchaseProductList[$i]["purchase_price"]);
                $qty = intval($quantity);
                $_SESSION["purchaseProductList"][$i]["purchase_qty"] = $quantity;
                $_SESSION["purchaseProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
                break;
              }
            }
            echo "success";
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