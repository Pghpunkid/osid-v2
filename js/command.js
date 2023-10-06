var imageCount = 0;
var targetCount = 0;
var timer;

$(function() {
	$('#startDuplication').click(function() {
		startDuplication();
	});

	$('#refreshDevices').click(function() {
		refreshDevices();
	});
	
	$('#refreshImages').click(function() {
		refreshImages();
	});

	getDeviceIP();
	
	$('#ip0').html();
	$('#ip1').html();
	
	initialize();
});

function getDeviceIP() {
	$.post('php/core.php',
		{
			cmd: "deviceIP"
		},
		function(data) {
			if (data.Status == "Ok") {
				$('#ip0').html(data.Message);
				$('#ip1').html(data.Message);
			}
			
			getDiskStats();
		},
		'json'
	);
}

function getDiskStats() {
	$.post('php/core.php',
		{
			cmd: "diskStats"
		},
		function(data) {
			$('#diskUsed').html(data.Used);
			$('#diskAvail').html(data.Available);
			$('#diskTotal').html(data.Total);
		},
		'json'
	);
}

function refreshDevices() {

	$.post('php/core.php',
		{
			cmd: 'getTargets'
		},
		function(data) {
			if (data.Status == 'Ok') {
				var html = "";
				
				targetCount = data.Targets.length;
				if (data.Targets.length > 0) {
						html = '';
						for (var t=0; t<data.Targets.length; t++) {
								html += "<input type='checkbox' id='target" + t + "' name='" + data.Targets[t][0] + "' value='" + t + "'> " + data.Targets[t][0] + " (" + data.Targets[t][1] + ")<br/>";
						}
				}
				else
					html = "No Devices Found.";
					
				if (data.JobStatus == 1 || data.JobStatus == 2) {
					$('.overlay').fadeIn();
					updateStatus();
				}
					
				$('#targetList').html(html);
			}
		},
		'json'
	);
}


function refreshImages() {

	$.post('php/core.php',
		{
			cmd: 'getImages'
		},
		function(data) {
			if (data.Status == 'Ok') {
				var html = "";
				
				imageCount = data.Images.length;
				if (data.Images.length > 0) {
						html = '<select id="imageSel" class="form-control">';
						for (var i=0; i<data.Images.length; i++) {
								html += "<option value='" + i + "'>" + data.Images[i] + "</option>";
						}
						html += '</select>';
				}
				else 
					html = "No Images Found.";
					
				$('#imageList').html(html);

			}
		},
		'json'
	);

}

function initialize() {

	$.post('php/core.php',
		{
			cmd: 'initialize'
		},
		function(data) {
			if (data.Status == 'Ok') {
				var html = "";

				imageCount = data.Images.length;
				if (data.Images.length > 0) {
						html = '<select id="imageSel" class="form-control">';
						for (var i=0; i<data.Images.length; i++) {
								html += "<option value='" + i + "'>" + data.Images[i] + "</option>";
						}
						html += '</select>';
				}
				else 
					html = "No Images Found.";
					
				$('#imageList').html(html);

				html = "";

				targetCount = data.Targets.length;
				if (data.Targets.length > 0) {
						html = '';
						for (var t=0; t<data.Targets.length; t++) {
								html += "<input type='checkbox' id='target" + t + "' name='" + data.Targets[t][0] + "' value='" + t + "'> " + data.Targets[t][0] + " (" + data.Targets[t][1] + ")<br/>";
						}
				}
				else
					html = "No Devices Found.";
					
				if (data.JobStatus == 1 || data.JobStatus == 2) {
					$('.overlay').fadeIn();
					updateStatus();
				}
					
				$('#targetList').html(html);
			}
		},
		'json'
	);
}

function startDuplication() {
	var deviceList = "";

	for (d=0; d<targetCount; d++) {
		if ($('input[type="checkbox"]').eq(d).prop('checked') == true) 
			deviceList += (deviceList == ""?"":"|") + $('input[type="checkbox"]').eq(d).attr('name');
	}
	
	var image = $("#imageSel option:selected").text();
	
	console.log("deviceList:" + deviceList + " image:" + image);
	
	$.post('php/core.php',
		{
			cmd: 'startDuplication',
			devices: deviceList,
			image: image
		},
		function(data) {
			if (data.Status == 'Ok') {
				//Were good to go.'
				$('#details').html('Waiting to start..');
				$('#progressbar').css('width','0%');
				$('.overlay').fadeIn();
				updateStatus();
			}
			else {
				//Uh oh.
			}
		},
		'json'
	);
}

function updateStatus() {
	$.post('php/core.php',
		{
			cmd: 'status'
		},
		function(data) {
			if (data.Status == 'Ok') {
				if (data.JobStatus == 1 || data.JobStatus == 2) {
					$('#progressbar').css('width',data.Percentage + '%');
					$('#details').html(data.Message);
					setTimeout('updateStatus()',500);
				}
				else 
				{
					$('#details').html('Duplication Complete.');
					$('.overlay').delay(5000).fadeOut();
				}
			}
		},
		'json'
	);
	
}