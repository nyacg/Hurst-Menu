//declare variables
var $switches, displacement, weeksAttendanceJSON, jqXHR;

//wait for page to be loaded before executing the following code
$(document).ready(function(){
	//load the Google Visualization API, when it is loaded execute the drawCharts() function
	google.load("visualization", "1", {packages:["corechart"], "callback" : drawCharts});

	$switches = $('.switch');	//define the switches
	displacement = 0;	//initalise the variable to hold the number of weeks displacement of the data to show

	//place an event handler when a switch is switched 
	$switches.on('switch-change', function (e, data) {
		//change the data shown on the scatter graph
	    drawConfirmedAttendanceChart();
	});

	//when one of the quick set buttons is clicked
	$('.quick-set-graph').click(function(){
		$button = $(this);	
		var id = $button.attr("id");	//get the id of the button clicked
		//console.log(id);	//for testing
		//if the 'Weekdays' button was clicked
		if(id == "weekdays"){
			//set just the switches that refer to a weekday switched on
			$('.switch').each(function(index){
				if(index < 5){
					$(this).bootstrapSwitch('setState', true);
				} else {
					$(this).bootstrapSwitch('setState', false);
				}
			});
		} else if(id == "weekends"){
			//if it is the 'Weekends' button 
			//set just the switches that refer to Saturday and Sunday to ON
			$('.switch').each(function(index){
				if(index >= 5){
					$(this).bootstrapSwitch('setState', true);
				} else {
					$(this).bootstrapSwitch('setState', false);
				}
			});
		} else {
			//otherwise, it will be a button for a specific day
			//and the index of that button in the dropdown list will be the same 
			//as the index of the switch for the day in the set of switches

			//get the index of the button in the list
			var index = $('#days-dropdown li').index(($button).parent()); 
			//console.log(index);	//for testing

			//switch the switch with that index on
			$('.switch').eq(index).bootstrapSwitch('setState', true);
			//switch all other switches off
			$('.switch').not($('.switch').eq(index)).bootstrapSwitch('setState', false);
		}
		drawConfirmedAttendanceChart();		//draw the scatter graph
	});

	//handlers for the clicking of the previous and next week buttons
	//once clicked the bar graph should be redrawn with the data for 
	//the previous and next weeks respectively
	$('#previous-week').click(function(){
		jqXHR.abort();	//cancel the cuttent AJAX request (more efficient)
		displacement--;
		getWeeksAttendanceData(displacement);
	});

	$('#next-week').click(function(){
		jqXHR.abort();	//cancel the cuttent AJAX request (more efficient)
		displacement++;
		getWeeksAttendanceData(displacement);
	});

	//handles the changing of the bar graph's data when the arrow keys are pressed
	$(document).keydown(function(e){
		var code = e.keyCode || e.which;
		//when the left arrow key is pressed the graph should be redrawn 
		//with the data for the previous week
		if(code == 37){ 
			jqXHR.abort();
			displacement--;
		} else if(code == 39){ 
			//when the right arrow key is pressed the graph should be redrawn 
			//with the data for the next week
			jqXHR.abort();
			displacement++;
		}
		getWeeksAttendanceData(displacement);	//draw the bar graph
	});

});

//function getData(){
//	$.get("./res/php/getAttendanceData.php", /*{'from-date': getUrlVars["from-date"], 'to-date': getUrlVars["to-date"]},*/ function(data){

// 	}).done(function(data){
// 		json = $.parseJSON(data);
// 		//console.log(json);
// 		drawConfirmedAttendanceChart();
// 	});
// }

//google.load("visualization", "1", {packages:["corechart"]});
//google.setOnLoadCallback(getData());

//function to draw both the charts by calling the functions required to initiate each process
//used so a single function can be placed in the callback of the API load
function drawCharts(){
	drawConfirmedAttendanceChart();
	getWeeksAttendanceData();
}

//function to handle the drawing of the scatter graph
function drawConfirmedAttendanceChart() {
	var days = [];	//initalise array to hold days that have been selected
	//console.log(loaded);
	//if the number of switches loaded is less than 7 then the page is probably still loading
	//so assume all the weekdays are swiched on
	if(loaded < 7) {
		days = [0, 1, 2, 3, 4];
	} else {
		//otherwise discover which switches are on
		$switches.each(function(index){
			//console.log($(this).attr("name") + " " + index);
			if($(this).bootstrapSwitch('state')){
				days.push(index);	//add the index of the switch to the array of switched on switches (days)	
			}
		});
	}
	//console.log(days);

	//get the data required for the days selected in the correct format for the API
	jsonData = getConfirmedAttendanceFormatted(days);
	//console.log(jsonData);

	//if there is some data for the selected parameters
	if(jsonData.rows.length > 0){
		//convert the corectly formatted data into a Data Table 
		console.log(jsonData);
		var data = new google.visualization.DataTable(jsonData);

		//set the options for the table
		var options = {
			title: 'Confirmed Attendance vs. Actual Attendance for Supper', //title to be shown
			hAxis: {title: 'Confirmed Attendance'},	//horizontal axis label
			vAxis: {title: 'Actual Attendance'},	//vertical axis label
			legend: 'none',	//no legend 
			trendlines: { 0: {} },		//draw a trendline for data series 0 (the only one in our case)
			annotation: {
				//index here is the index of the DataTable column providing the annotation
				//this should show a vertical line if the point being plotted is todays attendance
				2: {
					style: 'line'
				}
			}
		};

		//get the API to create a scatter chart at the placeholder DIV 
		var confirmedAttendanceChart = new google.visualization.ScatterChart(document.getElementById('confirmed-attendance-chart'));
		//draw the chart (using the API) with the data and options set
		confirmedAttendanceChart.draw(data, options);
	} else {
		//show a message that there is no data for selected days and dates
		$('#confirmed-attendance-chart').html("<div class='alert alert-info' style='margin: 100px 0 0 50px; max-width: 600px'><strong>Heads up!</strong> No data for selected days and dates</div>");
	}
}

//function to format the scatter graph data as required by the API
function getConfirmedAttendanceFormatted(days){
	//define the columns for the Data Table with their types and roles
	var cols = [{'label': "Confirmed Attendance", 'type': "number"}, {'label': "Actual Attendance", 'type': "number"}, {'role': "annotation", 'type': "string"}];
	var rows = [];	//initalise the array of rows

	//if there is at least one day selected
	if(days.length > 0){
		//for each value in the days array
		for(i=0; i<days.length; i++){
			var day = confirmedAttendanceJSON[days[i]];	//get that days data from all the data
			//console.log(day);
			var length = day.length;	//for efficiency
			//for each piece of attendance data for that day
			for(j=0; j<length; j++){
				//create the objects required by the API for each cell
				var confirmed = {'v': day[j][0]};
				//console.log(confirmed);
				var actual = {'v': day[j][1]};
				var line = {'v': day[j][2]};
				var row = {'c': [confirmed, actual, line]};
				
				rows.push(row);	//add the row object to the array of rows
			}
		}
	} 
	//group the rows and columns into one object
	var data = {'cols': cols, 'rows': rows};
	//console.log(data);
	return data;	//return the data to the function call
}

//function to get the data required for the bar chart and to draw it
function getWeeksAttendanceData(displacement){
	//console.log(averageAttendanceJSON);

	//Place call to PHP script to recieve the weeks attendance as a JSON encoded string.
	//We pass the average attendance data into the script as it is easier to form all the data 
	//in one go into the format required for the API data table. This is done by converting the 
	//data back into a sting using the JSON.stringify function
	jqXHR = $.get("./res/php/getWeeksAttendance.php", {'displacement': displacement, 'average': JSON.stringify(averageAttendanceJSON)}, function(data){

	}).done(function(data){
		//when completed get the JSON sting as a JavaScript object
		weeksAttendanceJSON = $.parseJSON(data);
		//console.log(json);
		//call the function to draw the bar chart with the data just recieved
		drawWeeksAttendanceChart(weeksAttendanceJSON);
	});
}

//function to draw the bar chart using the Google Visulization API
function drawWeeksAttendanceChart(json){
	//console.log(json);
	//check that there is some data in the object
	if(json.tableData.rows.length > 0){
		//create the Data Table required
		var data = new google.visualization.DataTable(json.tableData);

		//define the options (just the title with the nicely formatted date)
		var options = {
			title: 'Attendance for Week Starting ' + json.date,	
		};

		//define the bar chart holder DIV as as column chart for the API
		var confirmedAttendanceChart = new google.visualization.ColumnChart(document.getElementById('weeks-attendance-chart'));
		confirmedAttendanceChart.draw(data, options);	//draw the bar chart with the data requested and the title
	} else {
		//if there is no data then a message should be shown
		$('#weeks-attendance-chart').html("<div class='alert alert-info' style='margin: 100px 0 0 50px; max-width: 600px'><strong>Heads up!</strong> No data for selected days and dates</div>");
	}
}