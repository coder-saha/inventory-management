<?php

// Database configuration

const DB_HOST = "localhost";
const DB_USERNAME = "root";
const DB_PASSWORD = "";
const DB_NAME = "inventoryDB";

// Create database connection

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection

if ($conn->connect_error) {
  echo "Error connecting to database.";
  die ("Connection failed: " . $conn->connect_error);
}

// echo "Connected successfully";