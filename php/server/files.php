<?php
require('config.php');

if (isset($_GET['q'])) {
	$filename = $filesDir . $_GET['q'];
	if (file_exists($filename)) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext == 'jpg' || $ext == 'png')
			header('Content-Type: image/'.$ext);
		header('Content-Disposition: attachment; filename='.$_GET['q']);
		header('Content-Length: ' . filesize($filename));
		header('Cache-Control: max-age='. $cache);
		header('Expires: '. gmdate('D, d M Y H:i:s', time() + $cache) .' GMT');
		readfile($filename);
		exit;
	}
}
