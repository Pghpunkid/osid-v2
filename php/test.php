<?php

	$images =  scandir('/etc/osid/www/public_html/imgroot'); 
	print_r($images);

	$DeviceList = shell_exec("lsblk -d | awk -F: '{print $1}' | awk '{print $1}'");
	$DeviceArray = explode("\n", $DeviceList);
	print_r($DeviceArray);
?>
