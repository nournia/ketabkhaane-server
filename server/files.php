<?php 
$filesDir = $_SERVER['DOCUMENT_ROOT'] .'../_files/';
if (isset($_GET['q'])) {
	$filename = $filesDir . $_GET['q'];
	if (file_exists($filename)) {
		header('Content-Type: image/jpg');
		header('Content-Length: ' . filesize($filename));
		readfile($filename);
		exit;
	}
}
?>