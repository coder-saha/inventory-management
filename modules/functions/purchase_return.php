<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    switch ($_POST["operation"]) {

      case 'addProductToPurchaseReturn': {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $query = "SELECT * FROM sku WHERE sku='$sku' AND status='purchased';";
        $returnPurchaseProductList = $_SESSION["returnPurchaseProductList"] ?? [];
        try {
          $result = mysqli_query($conn, $query);
          if (mysqli_num_rows($result) > 0) {
            $item = mysqli_fetch_assoc($result);
            $product_id = $item["product_id"];
            $purchase_invoice_no = $item["purchase_invoice_no"];
            $query = "SELECT * FROM products where product_id='$product_id';";
            $entryPresent = false;
            for ($i = 0; $i < count($returnPurchaseProductList); $i++) {
              if ($returnPurchaseProductList[$i]["sku"] === $sku) {
                $entryPresent = true;
                break;
              }
            }
            if (!$entryPresent) {
              $result = mysqli_query($conn, $query);
              if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $query = "SELECT * FROM purchases where purchase_invoice_no='$purchase_invoice_no' AND product_id='$product_id';";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                  $purchase_price = mysqli_fetch_assoc($result)["purchase_price"];
                  $entry = array(
                    "product_id" => $data["product_id"],
                    "product_name" => $data["product_name"],
                    "mrp" => $data["mrp"],
                    "purchase_price" => $purchase_price,
                    "sku" => $sku,
                    "purchase_invoice_no" => $purchase_invoice_no
                  );
                  array_push($returnPurchaseProductList, $entry);
                  $_SESSION["returnPurchaseProductList"] = $returnPurchaseProductList;
                }
              }
            }
            echo "success";
          } else {
            echo "not found";
          }
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      case 'removeProductFromPurchaseReturn': {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $returnPurchaseProductList = $_SESSION["returnPurchaseProductList"] ?? [];
        try {
          $entryPresent = false;
          for ($i = 0; $i < count($returnPurchaseProductList); $i++) {
            if ($returnPurchaseProductList[$i]["sku"] === $sku) {
              $entryPresent = true;
              break;
            }
          }
          if ($entryPresent) {
            array_splice($returnPurchaseProductList, $i, 1);
            $_SESSION["returnPurchaseProductList"] = $returnPurchaseProductList;
            echo "success";
          }
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      default:
        echo "Invalid Operation";
        break;
    }
  }
}