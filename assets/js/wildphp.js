$(document).ready(function() {
	//start ajax request
	$.ajax({
		url: "data/version.json",
		//force to handle it as text
		dataType: "text",
		success: function(version) {
			
			//data downloaded so we call parseJSON function 
			//and pass downloaded data
			var json = $.parseJSON(version);
			//now json variable contains data in json format
			//let's display a few items
			$('#version').html(json.version);
		}
	});
});