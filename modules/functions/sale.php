<?php
session_start();
require_once("../config/db_config.php");
require_once("../config/logger.php");
require_once("../utils/validations.php");

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    switch ($_POST["operation"]) {

      case 'setCustomerType': {
        $customer_type = $_POST["customerType"];
        if ($customer_type !== "0") {
          if ($customer_type === "walkin") {
            $_SESSION["customer_id"] = "1";
            $_SESSION["customer_name"] = "Walk-In";
          }
          $_SESSION["customer_type"] = $customer_type;
          echo "success";
        } else {
          unset($_SESSION["customer_type"]);
          unset($_SESSION["customer_id"]);
          unset($_SESSION["customer_name"]);
          echo "removed";
        }
        break;
      }

      case 'addCustomer': {
        $errors = validateCustomer($_POST);
        if (count($errors) === 0) {
          $customer_name = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["customer_name"])));
          $customer_mobile = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["customer_mobile"])));
          $billing_address = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["billing_address"])));
          $shipping_address = mysqli_real_escape_string($conn, htmlspecialchars(stripslashes($_POST["shipping_address"])));
          $current_date = date('Y-m-d H:i:s');

          $validationQuery = "SELECT * FROM customers WHERE deleted='0' AND (customer_name='$customer_name' AND customer_mobile='$customer_mobile');";
          $query1 = "INSERT INTO customers (customer_id, customer_name, customer_mobile, billing_address, shipping_address, deleted, date_added, date_updated) 
                              VALUES (NULL, '$customer_name', '$customer_mobile', '$billing_address', '$shipping_address', '0', '$current_date', '$current_date');";

          try {
            $validationResult = mysqli_query($conn, $validationQuery);
            if (mysqli_num_rows($validationResult) > 0) {
              echo "Customer Name and Mobile Number already exists";
            } else {
              $result1 = mysqli_query($conn, $query1);
              if (mysqli_affected_rows($conn) > 0) {
                $_SESSION["customer_type"] = "existing";
                $_SESSION["customer_id"] = strval(mysqli_insert_id($conn));
                $_SESSION["customer_name"] = $customer_name;
                echo "success";
              }
            }
          } catch (Exception $e) {
            writeLog($e->getMessage());
          } finally {
            mysqli_close($conn);
          }
          break;
        } else {
          if (isset($errors["customer_name"])) {
            echo "Customer Name: " . $errors["customer_name"];
          } else if (isset($errors["customer_mobile"])) {
            echo "Customer Mobile: " . $errors["customer_mobile"];
          } else if (isset($errors["billing_address"])) {
            echo "Billing Address: " . $errors["billing_address"];
          } else if (isset($errors["shipping_address"])) {
            echo "Shipping Address: " . $errors["shipping_address"];
          }
          break;
        }
      }

      case 'setCustomer': {
        $customer_id = $_POST["customerId"];
        $customer_name = $_POST["customerName"];
        if ($customer_id === "0") {
          unset($_SESSION["customer_id"]);
          unset($_SESSION["customer_name"]);
          echo "removed";
        } else {
          $_SESSION["customer_id"] = $customer_id;
          $_SESSION["customer_name"] = $customer_name;
          echo "success";
        }
        break;
      }

      /* case 'setSaleInvoiceNumber': {
        $_SESSION["sale_invoice_no"] = $_POST["invoiceNo"];
        break;
      } */

      case 'setSaleDate': {
        $_SESSION["sale_date"] = $_POST["saleDate"];
        break;
      }

      case 'addProductToSale': {
        $sku = htmlspecialchars(stripslashes($_POST["skuId"]));
        $query = "SELECT * FROM sku WHERE sku='$sku' AND status='purchased';";
        $saleProductList = $_SESSION["saleProductList"] ?? [];
        try {
          $result = mysqli_query($conn, $query);
          if (mysqli_num_rows($result) > 0) {
            $id = mysqli_fetch_assoc($result)["product_id"];
            $query = "SELECT * FROM products where product_id='$id';";
            $entryPresent = false;
            for ($i = 0; $i < count($saleProductList); $i++) {
              if ($saleProductList[$i]["product_id"] === $id) {
                $price = floatval($saleProductList[$i]["selling_price"]);
                $qty = intval($saleProductList[$i]["sale_qty"]) + 1;
                $skuList = $saleProductList[$i]["sku_list"];
                if (!in_array($sku, $skuList)) {
                  array_push($skuList, $sku);
                  $_SESSION["saleProductList"][$i]["sale_qty"] = strval($qty);
                  $_SESSION["saleProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
                  $_SESSION["saleProductList"][$i]["sku_list"] = $skuList;
                }
                $entryPresent = true;
                break;
              }
            }
            if (!$entryPresent) {
              $result = mysqli_query($conn, $query);
              if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $price = floatval($data["selling_price"]);
                $subTotal = number_format($price, 2, '.', '');
                $skuList = array($sku);
                $entry = array(
                  "product_id" => $data["product_id"],
                  "product_name" => $data["product_name"],
                  "sku_prefix" => $data["sku_prefix"],
                  "mrp" => $data["mrp"],
                  "selling_price" => $data["selling_price"],
                  "sale_qty" => "1",
                  "sku_list" => $skuList,
                  "sub_total" => $subTotal
                );
                array_push($saleProductList, $entry);
                $_SESSION["saleProductList"] = $saleProductList;
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

      case 'removeProductFromSale': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $query = "SELECT * FROM products where product_id='$id';";
        $saleProductList = $_SESSION["saleProductList"] ?? [];
        try {
          $entryPresent = false;
          for ($i = 0; $i < count($saleProductList); $i++) {
            if ($saleProductList[$i]["product_id"] === $id) {
              $entryPresent = true;
              break;
            }
          }
          if ($entryPresent) {
            array_splice($saleProductList, $i, 1);
            $_SESSION["saleProductList"] = $saleProductList;
          }
          echo "success";
        } catch (Exception $e) {
          writeLog($e->getMessage());
          echo "error";
        }
        break;
      }

      case 'updateSalePrice': {
        $id = htmlspecialchars(stripslashes($_POST["productId"]));
        $salePrice = htmlspecialchars(stripslashes($_POST["salePrice"]));
        try {
          $saleProductList = $_SESSION["saleProductList"];
          if (preg_match('/^\d{0,8}(\.\d{1,2})?$/', $salePrice)) {
            for ($i = 0; $i < count($saleProductList); $i++) {
              if ($saleProductList[$i]["product_id"] === $id) {
                $price = floatval($salePrice);
                $qty = intval($saleProductList[$i]["sale_qty"]);
                $_SESSION["saleProductList"][$i]["selling_price"] = $salePrice;
                $_SESSION["saleProductList"][$i]["sub_total"] = number_format($price * $qty, 2, '.', '');
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

      default:
        echo "Invalid Operation";
        break;
    }
  }
}