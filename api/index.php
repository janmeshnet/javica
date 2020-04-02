<?php
if (!isset($_GET['action'])) {
		$_GET['action']='null';
	}
chdir('../javica');
require_once ('./index.php');
chdir('../javica-api');
?>
