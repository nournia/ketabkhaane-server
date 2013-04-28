<?php

if (isset($_ENV['OPENSHIFT_DATA_DIR']))
	$filesDir = $_ENV['OPENSHIFT_DATA_DIR'].'files/';
else 
	$filesDir = '../../files/';

$cache = 24 * 3600;
