<?php 
if (isset($_ENV['OPENSHIFT_DATA_DIR']))
	$filesDir = $_ENV['OPENSHIFT_DATA_DIR'].'files/';
else 
	$filesDir = '../../files/';

if (isset($_GET['q'])) {
	$filename = $filesDir . $_GET['q'];
	if (file_exists($filename)) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext == 'jpg' || $ext == 'png')
			header('Content-Type: image/'.$ext);
		header('Content-Disposition: attachment; filename='.$_GET['q']);
		header('Content-Length: ' . filesize($filename));
		readfile($filename);
		exit;
	}
}
