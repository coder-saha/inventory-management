<?php
function validateProfile($postData) {
  $errors = [];
  $name = $postData["name"];
  if ($name !== '') {
    if (!preg_match('/^[\.a-zA-Z ]*$/', $name)) {
      $errors["name"] = "Invalid";
    }
  } else {
    $errors["name"] = "Required";
  }

  $mobile = $postData["mobile"];
  if ($mobile !== '') {
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
      $errors["mobile"] = "Invalid";
    }
  } else {
    $errors["mobile"] = "Required";
  }

  $email = $postData["email"];
  if ($email !== '') {
    if (!preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)) {
      $errors["email"] = "Invalid";
    }
  } else {
    $errors["email"] = "Required";
  }

  $gst_no = $postData["gst_no"];
  if ($gst_no !== '') {
    if (!preg_match('/\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}[Z]{1}[A-Z\d]{1}/', $gst_no)) {
      $errors["gst_no"] = "Invalid";
    }
  } else {
    $errors["gst_no"] = "Required";
  }

  return $errors;
}
function validateProduct($postData) {
  $errors = [];
  $product_name = $postData["product_name"];
  if ($product_name !== '') {
    if (!preg_match('/^[\.a-zA-Z ]*$/', $product_name)) {
      $errors["product_name"] = "Invalid";
    }
  } else {
    $errors["product_name"] = "Required";
  }

  $sku_prefix = $postData["sku_prefix"];
  if ($sku_prefix !== '') {
    if (!preg_match('/^[A-Z]{4}$/', $sku_prefix)) {
      $errors["sku_prefix"] = "Invalid";
    }
  } else {
    $errors["sku_prefix"] = "Required";
  }

  $mrp = $postData["mrp"];
  if ($mrp !== '') {
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $mrp)) {
      $errors["mrp"] = "Invalid";
    }
  } else {
    $errors["mrp"] = "Required";
  }

  $selling_price = $postData["selling_price"];
  if ($selling_price !== '') {
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $selling_price) || floatval($selling_price) > floatval($mrp)) {
      $errors["selling_price"] = "Invalid";
    }
  } else {
    $errors["selling_price"] = "Required";
  }

  $description = $postData["description"];
  if ($description !== '') {
    if (strlen($description) > 200) {
      $errors["description"] = "Too long";
    }
  } else {
    $errors["description"] = "Required";
  }

  return $errors;
}

function validateCustomer($postData) {
  $errors = [];
  $customer_name = $postData["customer_name"];
  if ($customer_name !== '') {
    if (!preg_match('/^[\.a-zA-Z ]*$/', $customer_name)) {
      $errors["customer_name"] = "Invalid";
    }
  } else {
    $errors["customer_name"] = "Required";
  }

  $customer_mobile = $postData["customer_mobile"];
  if ($customer_mobile !== '') {
    if (!preg_match('/^[6-9]\d{9}$/', $customer_mobile)) {
      $errors["customer_mobile"] = "Invalid";
    }
  } else {
    $errors["customer_mobile"] = "Required";
  }

  $billing_address = $postData["billing_address"];
  if ($billing_address !== '') {

  } else {
    $errors["billing_address"] = "Required";
  }

  $shipping_address = $postData["shipping_address"];
  if ($shipping_address !== '') {

  } else {
    $errors["shipping_address"] = "Required";
  }

  return $errors;
}

function validateSupplier($postData) {
  $errors = [];
  $supplier_name = $postData["supplier_name"];
  if ($supplier_name !== '') {
    if (!preg_match('/^[\.a-zA-Z ]*$/', $supplier_name)) {
      $errors["supplier_name"] = "Invalid";
    }
  } else {
    $errors["supplier_name"] = "Required";
  }

  $supplier_mobile = $postData["supplier_mobile"];
  if ($supplier_mobile !== '') {
    if (!preg_match('/^[6-9]\d{9}$/', $supplier_mobile)) {
      $errors["supplier_mobile"] = "Invalid";
    }
  } else {
    $errors["supplier_mobile"] = "Required";
  }

  return $errors;
}

function validatePurchase($postData) {
  $errors = [];
  return $errors;
}

function validateSale($postData) {
  $errors = [];
  return $errors;
}

function validateInput($postData)
{
  $errors = [];
  $name = $postData["name"];
  if ($name !== '') {
    if (!preg_match('/^[\.a-zA-Z ]*$/', $name)) {
      $errors["name"] = "Invalid";
    }
  } else {
    $errors["name"] = "Required";
  }

  $father_name = $postData["father_name"];
  if ($father_name !== '') {
    if (!preg_match('/^[\.a-zA-Z ]*$/', $father_name)) {
      $errors["father_name"] = "Invalid";
    }
  } else {
    $errors["father_name"] = "Required";
  }

  $mobile = $postData["mobile"];
  if ($mobile !== '') {
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
      $errors["mobile"] = "Invalid";
    }
  } else {
    $errors["mobile"] = "Required";
  }

  $email = $postData["email"];
  if ($email !== '') {
    if (!preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)) {
      $errors["email"] = "Invalid";
    }
  } else {
    $errors["email"] = "Required";
  }

  $dob = $postData["dob"];
  // YYYY-MM-DD and year needs to start with 1 or 2
  if ($dob !== '') {
    if (!preg_match('/^([12][0-9][0-9][0-9])-(1[0-2]|0[1-9])-(3[01]|[12][0-9]|0[1-9])$/', $dob)) {
      $errors["dob"] = "Invalid";
    }
  } else {
    $errors["dob"] = "Required";
  }

  $aadhaar = $postData["aadhaar"];
  if ($aadhaar !== '') {
    if (!preg_match('/^[2-9]\d{11}$/', $aadhaar)) {
      $errors["aadhaar"] = "Invalid";
    }
  } else {
    $errors["aadhaar"] = "Required";
  }

  $pan = $postData["pan"];
  if ($pan !== '') {
    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
      $errors["pan"] = "Invalid";
    }
  } else {
    $errors["pan"] = "Required";
  }

  $qualification = $postData["qualification"];
  if ($qualification !== '') {
    if (!preg_match('/^[\w\-,.\'\s]+$/', $qualification)) {
      $errors["qualification"] = "Invalid";
    }
  } else {
    $errors["qualification"] = "Required";
  }

  $session = $postData["session"];
  if ($session === '') {
    $errors["session"] = "Required";
  }

  $center = $postData["center"];
  if ($center === '') {
    $errors["center"] = "Required";
  }

  $project = $postData["project"];
  if ($project === '') {
    $errors["project"] = "Required";
  }

  $sector = $postData["sector"];
  if ($sector === '') {
    $errors["sector"] = "Required";
  }

  $jobrole = $postData["jobrole"];
  if ($jobrole === '') {
    $errors["jobrole"] = "Required";
  }

  $certified = $postData["certified"];
  if ($certified !== '') {
    if (!preg_match('/^[0,1]{1}$/', $certified)) {
      $errors["certified"] = "Invalid";
    }
  } else {
    $errors["certified"] = "Required";
  }

  $certificate_received = $postData["certificate_received"];
  if ($certificate_received !== '') {
    if (!preg_match('/^[0,1]{1}$/', $certificate_received)) {
      $errors["certificate_received"] = "Invalid";
    }
  } else {
    $errors["certificate_received"] = "Required";
  }

  $placed = $postData["placed"];
  if ($placed !== '') {
    if (!preg_match('/^[0,1]{1}$/', $placed)) {
      $errors["placed"] = "Invalid";
    }
  } else {
    $errors["placed"] = "Required";
  }

  $employment_type = $postData["employment_type"];
  if ($employment_type !== '') {
    if (!in_array($employment_type, ['NA', 'Wage', 'Self'])) {
      $errors["employment_type"] = "Invalid";
    }
    if ($placed === "1" && $employment_type === "NA") {
      $errors["employment_type"] = "Required";
    }
  } else {
    // $errors["employment_type"] = "Required";
  }

  $company_name = $postData["company_name"];
  if ($company_name !== '') {
    if (!preg_match('/^[\w\-,.\'"\s]+$/', $company_name)) {
      $errors["company_name"] = "Invalid";
    }
  } else {
    if ($placed === "1") {
      $errors["company_name"] = "Required";
    }
  }

  $employer_name = $postData["employer_name"];
  if ($employer_name !== '') {
    if (!preg_match('/^[\w\-,.\'"\s]+$/', $employer_name)) {
      $errors["employer_name"] = "Invalid";
    }
  } else {
    if ($placed === "1") {
      $errors["employer_name"] = "Required";
    }
  }

  $employer_mobile = $postData["employer_mobile"];
  if ($employer_mobile !== '') {
    if (!preg_match('/^[6-9]\d{9}$/', $employer_mobile)) {
      $errors["employer_mobile"] = "Invalid";
    }
  } else {
    if ($placed === "1") {
      $errors["employer_mobile"] = "Required";
    }
  }

  $post = $postData["post"];
  if ($post !== '') {
    if (!preg_match('/^[\w\-,.\'"\s]+$/', $post)) {
      $errors["post"] = "Invalid";
    }
  } else {
    if ($placed === "1") {
      $errors["post"] = "Required";
    }
  }

  $salary = $postData["salary"];
  if ($salary !== '') {
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $salary)) {
      $errors["salary"] = "Invalid";
    }
    if ($placed === "1" && !floatval($salary) > 0) {
      $errors["salary"] = "Required";
    }
  } else {
    if ($placed === "1") {
      $errors["salary"] = "Required";
    }
  }

  $status = $postData["status"];
  if ($status !== '') {
    if (!preg_match('/^[0,1]{1}$/', $status)) {
      $errors["status"] = "Invalid";
    }
  } else {
    if ($placed === "1") {
      $errors["status"] = "Required";
    }
  }

  return $errors;
}
