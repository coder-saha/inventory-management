<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    switch ($_POST["operation"]) {

      case 'addProductToSaleReturn': {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $query = "SELECT * FROM sku WHERE sku='$sku' AND status='sold';";
        $returnSaleProductList = $_SESSION["returnSaleProductList"] ?? [];
        try {
          $result = mysqli_query($conn, $query);
          if (mysqli_num_rows($result) > 0) {
            $item = mysqli_fetch_assoc($result);
            $product_id = $item["product_id"];
            $sale_invoice_no = $item["sale_invoice_no"];
            $query = "SELECT * FROM products where product_id='$product_id';";
            $entryPresent = false;
            for ($i = 0; $i < count($returnSaleProductList); $i++) {
              if ($returnSaleProductList[$i]["sku"] === $sku) {
                $entryPresent = true;
                break;
              }
            }
            if (!$entryPresent) {
              $result = mysqli_query($conn, $query);
              if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $query = "SELECT * FROM sales where sale_invoice_no='$sale_invoice_no' AND product_id='$product_id';";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                  $selling_price = mysqli_fetch_assoc($result)["selling_price"];
                  $entry = array(
                    "product_id" => $data["product_id"],
                    "product_name" => $data["product_name"],
                    "mrp" => $data["mrp"],
                    "selling_price" => $selling_price,
                    "sku" => $sku,
                    "sale_invoice_no" => $sale_invoice_no
                  );
                  array_push($returnSaleProductList, $entry);
                  $_SESSION["returnSaleProductList"] = $returnSaleProductList;
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

      case 'removeProductFromSaleReturn': {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $returnSaleProductList = $_SESSION["returnSaleProductList"] ?? [];
        try {
          $entryPresent = false;
          for ($i = 0; $i < count($returnSaleProductList); $i++) {
            if ($returnSaleProductList[$i]["sku"] === $sku) {
              $entryPresent = true;
              break;
            }
          }
          if ($entryPresent) {
            array_splice($returnSaleProductList, $i, 1);
            $_SESSION["returnSaleProductList"] = $returnSaleProductList;
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