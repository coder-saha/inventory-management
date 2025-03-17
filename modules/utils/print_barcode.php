<?php
session_start();
require_once "../config/db_config.php";
require_once "../config/logger.php";
include 'barcode128.php';

if (isset($_SESSION["user_id"]) && isset($_SESSION["username"])) {
	$mode = $_GET["mode"];
	if ($mode === "productId") {
		$id = $_GET["productId"];
		try {
			$query = "SELECT S.sku_id, S.product_id, P.product_name, S.sku 
					FROM sku S INNER JOIN products P ON S.product_id = P.product_id 
					WHERE S.product_id='$id' AND status='purchased' 
					ORDER BY S.sku_id;";
			$result = mysqli_query($conn, $query);
		} catch (Exception $e) {
			writeLog($e->getMessage());
		}
	} else if ($mode === "purchaseId") {
		$id = $_GET["purchaseId"];
		try {
			$query = "SELECT S.sku_id, S.product_id, P.product_name, S.sku 
					FROM sku S INNER JOIN products P ON S.product_id = P.product_id 
					WHERE S.purchase_invoice_no='$id' AND status='purchased' 
					ORDER BY S.sku_id;";
			$result = mysqli_query($conn, $query);
		} catch (Exception $e) {
			writeLog($e->getMessage());
		}
	}
	?>
	<html>

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Print Barcodes</title>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.2/html2pdf.bundle.min.js"
			integrity="sha512-MpDFIChbcXl2QgipQrt1VcPHMldRILetapBl5MPCA9Y8r7qvlwx1/Mc9hNTzY+kS5kX6PdoDq41ws1HiVNLdZA=="
			crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<style>
			.barcode-item {
				display: inline-block;
				font-family: Verdana, Helvetica, sans-serif;
				padding: 55px 90px 41.5px;
			}

			.barcode-item .barcode-header {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
				max-width: 200px;
				overflow: hidden;
				padding-bottom: 8px;
				text-align: center;
				text-transform: uppercase;
				white-space: nowrap;
			}

			.barcode-item .barcode-footer {
				font-size: 11px;
				letter-spacing: 4px;
				text-align: center;
			}

			.page-break {
				page-break-before: always;
			}
		</style>
	</head>

	<!-- <body onload="window.print();"> -->

	<body>
		<div class="print-section">
			<?php
			$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
			$length = count($rows);
			for ($i = 0; $i < $length; $i += 2) {
				if ($i !== 0 && $i % 12 === 0) { ?>
					<span class="page-break"></span>
				<?php } ?>
				<div>
					<?php if (($length - $i) > 1) {
						// print 2 barcodes
						$item1 = $rows[$i];
						$item2 = $rows[$i + 1];
						$product_name1 = $item1["product_name"];
						$sku1 = $item1["sku"];
						$product_name2 = $item2["product_name"];
						$sku2 = $item2["sku"];
						?>
						<div class="barcode-item">
							<div class="barcode-header"><?= $product_name1 ?></div>
							<div class="barcode-image"><?= bar128($sku1) ?></div>
							<div class="barcode-footer"><?= $sku1 ?></div>
						</div>
						<div class="barcode-item">
							<div class="barcode-header"><?= $product_name2 ?></div>
							<div class="barcode-image"><?= bar128($sku2) ?></div>
							<div class="barcode-footer"><?= $sku2 ?></div>
						</div>
					<?php } else {
						// print 1 barcode
						$item1 = $rows[$i];
						$product_name1 = $item1["product_name"];
						$sku1 = $item1["sku"];
						?>
						<div class="barcode-item">
							<div class="barcode-header">Marble Marble Marble Marble Marble Marble Marble Marble Marble Marble Marble</div>
							<div class="barcode-image"><?= bar128($sku1) ?></div>
							<div class="barcode-footer"><?= $sku1 ?></div>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
		<script>
			const data = document.querySelector(".print-section");
			const name = "print_barcodes";
			var opt = {
				margin: 1,
				filename: name,
				image: { type: 'jpeg', quality: 1 },
				html2canvas: { scale: 2 },
				jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
			};
			html2pdf(data, opt);
		</script>
	</body>

	</html>
	<?php
} else {
	header("Location: ../login/user_login");
	exit();
}
?>