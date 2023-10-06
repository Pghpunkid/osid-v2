<?php

	$imageDir = "/etc/osid/www/public_html/imgroot";
	$systemDir = "/etc/osid/www/public_html/system";

	$return = array();

	$cmd = (isset($_POST['cmd'])?$_POST['cmd']:'');
	
	if ($cmd == "initialize") {
		//Get Job Status
		$jobstatus = file_get_contents("/etc/osid/www/public_html/system/status.info");
		
		//Get Images
		$images = scandir($imageDir);
		
		$realImages = array();
		for($i=0; $i<sizeof($images); $i++) {
			if ($images[$i] != "." && $images[$i] != ".." && strpos($images[$i],".img") !== false) {
				array_push($realImages,$images[$i]);
			}
		}

		//Get Targets
		$devices = shell_exec("lsblk -d | awk -F: '{print $1,$2}' | awk '/sd[a-z]/ {print $1,$5}'");
        $devices = explode("\n", $devices);

		$realDevices = array();
		for ($d=0; $d<sizeof($devices); $d++) {
			if ($devices[$d] != "") {				
				$deviceDetails = explode(" ",$devices[$d]);
				array_push($realDevices,$deviceDetails);
			}
		}
       
		//Send it back!
		$return['JobStatus'] = $jobstatus;
		$return['Images'] = $realImages;
		$return['Targets'] = $realDevices;
		$return['Status'] = 'Ok';
		echo json_encode($return);
		exit(0);
	}
	else if ($cmd == "getTargets") {
		//Get Targets
		$devices = shell_exec("lsblk -d | awk -F: '{print $1,$2}' | awk '/sd[a-z]/ {print $1,$5}'");
        	$devices = explode("\n", $devices);

		$realDevices = array();
		for ($d=0; $d<sizeof($devices); $d++) {
			if ($devices[$d] != "") {				
				$deviceDetails = explode(" ",$devices[$d]);
				array_push($realDevices,$deviceDetails);
			}
		}
	
		$return['Targets'] = $realDevices;
		$return['Status'] = 'Ok';
		echo json_encode($return);
		exit(0);
	}
	else if ($cmd == "getImages") {
		//Get Images
		$images = scandir($imageDir);
		
		$realImages = array();
		for($i=0; $i<sizeof($images); $i++) {
			if ($images[$i] != "." && $images[$i] != ".." && strpos($images[$i],".img") !== false) {
				array_push($realImages,$images[$i]);
			}
		}
		
		$return['Images'] = $realImages;
		$return['Status'] = 'Ok';
		echo json_encode($return);
		exit(0);
	}
	else if ($cmd == "startDuplication") {
		$image = (isset($_POST['image'])?$_POST['image']:'');
		$devices = (isset($_POST['devices'])?$_POST['devices']:'');
	
		$devices = explode("|", $devices);
	
		if ($image != "" && $devices != "") {
	
			//make sure imagefile.info is blank
			shell_exec("cat /dev/null > /etc/osid/www/public_html/system/imagefile.info");

			//write selected image to the info file
			shell_exec("echo \"" . $image . "\" > /etc/osid/www/public_html/system/imagefile.info");

			//declare DeviceList and UmountList variables
			$DeviceList = "";
			$UmountList = "";

			//create device list from checkbox array
			foreach ($devices as &$DeviceName) {
				
				//put device into variable for use in dcfldd command
				$DeviceList .= "of=/dev/" . $DeviceName . " ";
				
				//put device into variable for unmounting of drives
				$UmountList .= "/usr/bin/umount /dev/" . $DeviceName . " & ";
				
			} //END create device list from checkbox array

			//trim off trailing space from device list varaible
			$DeviceList = rtrim($DeviceList);

			//trim off trailing space from umount list variable
			$UmountList = substr($UmountList, 0, -3);

			//make sure devicelist.info is blank
			shell_exec("cat /dev/null > /etc/osid/www/public_html/system/devicelist.info");

			//write devices to the info file
			shell_exec("echo \"" . $DeviceList . "\" > /etc/osid/www/public_html/system/devicelist.info");

			//make sure umountlist.info is blank
			shell_exec("cat /dev/null > /etc/osid/www/public_html/system/umountlist.info");

			//write devices to the info file
			shell_exec("echo \"" . $UmountList . "\" > /etc/osid/www/public_html/system/umountlist.info");

			//make sure status.info is blank
			shell_exec("cat /dev/null > /etc/osid/www/public_html/system/status.info");

			//set the status.info to one (start job)
			shell_exec("echo \"1\" > /etc/osid/www/public_html/system/status.info");
			
			$return['Status'] = 'Ok';
			echo json_encode($return);
			exit(0);
		}
		$return['Status'] = 'Bad';
		$return['Message'] = 'Missing Data.';
		echo json_encode($return);
		exit(0);
	}
	else if ($cmd == "status") {
		//Get and parse status!
		$jobstatus = file_get_contents("/etc/osid/www/public_html/system/status.info");
		$status = file_get_contents("http://localhost/php/monitor.php");
		
		$data = explode("|",$status);
		
		$time = explode(":",$data[3]);
		
		$timeRemaining = "";
		$hours = ($time[0] < 10?substr($time[0],1,1):$time[0]);
		$minutes = ($time[1] < 10?substr($time[1],1,1):$time[1]);
		$seconds = ($time[2] < 10?substr($time[2],1,1):$time[2]);
		
		if ($hours > 0)
			$timeRemaining = $hours . " Hour".($hours > 1?"s":"").", ";
		if ($minutes > 0)
			$timeRemaining .= $minutes ." Minute".($minutes > 1?"s":"")." ";
		if ($minutes == 0 && $hours == 0)
			$timeRemaining = $seconds . " Second".($seconds > 1?"s":"")." ";
			
		$timeRemaining.='Remaining';
		
		if ($data[0] < 100)
			$string = $data[0] . "% (" . $data[2] . "MB / " . $data[1] . "MB) ". $timeRemaining; 
		else
			$string = "Duplication Complete.";
			
		$return['JobStatus'] = $jobstatus;
		$return['Percentage'] = $data[0];
		$return['Status'] = 'Ok';
		$return['Message'] = $string;
		echo json_encode($return);
		exit(0);
	}
	else if ($cmd == "deviceIP") {
		$ip = shell_exec("hostname -i");
		
		$return['Status'] = 'Ok';
		$return['Message'] = trim($ip);
		echo json_encode($return);
		exit(0);
	}
	else if ($cmd == "diskStats") {
		$usage = shell_exec("df -h | awk '/\/dev\/root/ {print $2,$3,$4,$5}'");

		$usage = explode(' ',$usage);
		
		$return['Used'] = $usage[1];
		$return['Available'] = $usage[2];
		$return['PercentUsed'] = $usage[3];
		$return['Total'] = $usage[0];
		
		$return['Status'] = 'Ok';
		echo json_encode($return);
		exit(0);
	}
	else {
	
		//Bad
		$return['Status'] = 'Bad';
		$return['Message'] = "Unknown Command";
		echo json_encode($return);
		exit(0);
	}
	

?>
