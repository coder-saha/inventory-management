<?php
function writeLog($log_msg)
{
  $log_folder = "../../logs";
  if (!file_exists($log_folder)) {
    mkdir($log_folder, 0777, true);
  }
  $log = "[" . date("F j, Y, g:i a") . "] [" . $_SERVER['REMOTE_ADDR'] . "] [" . $_SESSION["username"] . "] " . $log_msg . PHP_EOL;
  $log_file_path = $log_folder . '/log_' . date('d-M-Y') . '.log';
  error_log($log, 3, $log_file_path);
}
