$(document).ready(function(){ 

	console.log("jQuery working!");

	var $sideNav = $('.nav-pane');
	var $content = $('.content');
	var $swiper = $content;

	var jqXHR;

	var mealDisplacement = 0; //holds the displacement of the menu shown from today

	var $lunch = $('.lunch');
	var $supper = $('.supper');
	var $setYearButton = $('#set-year-button');
	var $attendingCheckbox = $("input[value='attending']");

	var lunchItems = ["soup", "meat", "fish", "vegetarian", "potato", "veg1", "veg2", "veg3", "alternative", "sauce1", "sauce2", "dessert"];
	var supperItems = ["soup", "meat", "fish", "vegetarian", "staple", "veg1", "veg2", "sauce1", "sauce2", "dessert"];

	//$('body').bind('orientationchange', adaptToOrientation);
	$('#loading-small').hide();

	resizeStuff(); //set the page for the type of device being used
	showMenu(mealDisplacement); //get the menu

	//$swiper.swipe.pageScroll.VERTICAL;

	if (readCookie("yeargroup") != null){
		$setYearButton.children('.top').text("currently " + readCookie("yeargroup"));
		$('#years').val(readCookie("yeargroup"));
	} else {
		$setYearButton.children('.top').text("currently unset");
	}


	$('.left-arrow').click(function(){
		changePage("left");		
	});

	$('.right-arrow').click(function(){
		changePage("right");
	});

	$swiper.swipe({
		allowPageScroll:"vertical",
		swipeLeft:function(event, direction, distance, fingerCount){
			console.log(direction);
			changePage("right");
		},
		swipeRight:function(event, direction, distance, fingerCount){
			console.log(direction);
			changePage("left");
		},
		swipeStatus:function(event, phase, direction, distance, duration, fingers){
			if (phase!="cancel" && phase!="end" && (direction=="up" || direction=="down")) {
			 	if (duration<5000) {
					if(direction=="up"){
						//log required as the page scrolls as the mouse moves
						$content.scrollTop($content.scrollTop() + Math.log(distance));
					} else {
						$content.scrollTop($content.scrollTop() - Math.log(distance));
					}
				}
			}
		}
	});

	$('#set-year').click(function(){
		//when the Set Year navigation button is clicked
		var $this = $(this);

		//if the controls are already open, close them
		if ($this.css('height') == '140px') {
			$this.animate({height: '45px'}, function(){
				$this.children('.more').hide();
			});
			
		} else {
			//otherwise show the Set Year controls 
			$this.animate({height: '140px'});
			$this.children('.more').show();
		}
	});

	$setYearButton.click(function(){
		var yeargroup = $('#years').val(); 	//get the value of the dropdown box
		
		//create the GTM formatted string (used to store cookies) for the middle of the summer holidays 
		//so the year resets when the users school year changes
		var date = new Date();
		var year = date.getFullYear();
		date = new Date("August 15 " + year);
		var expires = "; expires="+date.toGMTString();

		//create the cookie
		document.cookie = "yeargroup"+"="+yeargroup+expires+"; path=/";
		//set the button to have the value of the year group set
		$(this).children('.top').text("currently " + (readCookie("yeargroup") ? readCookie("yeargroup") : "unset"));
		//hide the no yeargroup error message
		$('#no-yeargroup').hide();
	});

	$('.more').click(function(event){
		event.stopImmediatePropagation();
	});

	$attendingCheckbox.click(function(){
		var name = getDayMonth(mealDisplacement);
		var yeargroup = readCookie("yeargroup");
		var change = 0;

		if (yeargroup != null) {
			//decide whether to increase or decrease attendance
			if (this.checked){
				change = 1;
			} else {
				change = -1;
			}

			//update on server
			$.post("./res/php/updateAttendance.php", { change: change, yeargroup: yeargroup, d: mealDisplacement })
				.done(function(){
					//for testing purposes
					console.log("attendance succesfully changed " + change + " on " + name + " for yeargroup " + yeargroup);
					if (change == 1) {
						//set cookie of attending supper on this date
						createCookie(name, "yes", 5);
					} else {
						//remove cookie of attending supper on this date
						eraseCookie(name);
					}
				}).fail(function(){
					//if server update didn't work, reset the checkbox to its initial value
					if (change == 1) {
						$attendingCheckbox.prop('checked', false);
					} else {
						$attendingCheckbox.prop('checked', true);
					}
				});
		} else {
			$attendingCheckbox.prop('checked', false);
			$('#no-yeargroup').show();
		}
	});

	$(document).on("click", '.vote-icon', function(){
		var $this = $(this);	//improve efficiency
		var id = $this.parent().parent().attr("id");	//get the html id if the item voted on
		var idsplit = id.split("-");
		var item = idsplit[0];
		var meal = idsplit[1];
		//get the cookie name to be stored as
		var cookieName = getDayMonth(mealDisplacement) + "/" + item + "/" + (item == "soup" && $('#soup-lunch').text() == $('#soup-supper').text() ? "" : meal);
		var voted = readCookie(cookieName);		//see if item has already been voted on
		var vote = $this.hasClass("vote-up") ? "up" : "down";
		var dislikeChange = 0;
		var likeChange = 0;

		//console.log(vote + voted);

		$this.siblings(".vote-icon").removeClass("voted");		//remove highlight from other icon
		$this.addClass("voted");	//highlight this icon

		//decide on the like and dislike changes		
		if (vote == "up"){
			if (voted == "up"){
				//console.log("vote up and voted up");
				$this.removeClass("voted");
				eraseCookie(cookieName);
				likeChange = -1;
			} else {
				//console.log("vote up and possubly voted");
				createCookie(cookieName, "up", 1);
				likeChange = 1;
			}

			if (voted == "down"){
				//console.log("vote up and voted down");
				dislikeChange = -1;
			}

		} else if (vote == "down"){
			if (voted == "down"){
				//console.log("vote down and voted down");
				$this.removeClass("voted");
				eraseCookie(cookieName);
				dislikeChange = -1;
			} else {
				createCookie(cookieName, "down", 1);
				dislikeChange = 1;
				//console.log("vote down and possibly voted ");
			}

			if (voted == "up"){
				likeChange = -1;
				//console.log("vote down and voted up");
			}
		}

		//if item is soup, handle this as the soups are almost always the same for lunch and supper
		if(item == "soup" && $('#soup-lunch').text() == $('#soup-supper').text()){
			var voteSelector = ".vote-" + vote;
			console.log(voteSelector);
			var $sameAsThis = $('#soup-lunch').find(voteSelector).add($('#soup-supper').find(voteSelector)).not($this);
			if($this.hasClass("voted")){
				$sameAsThis.addClass("voted");
			} else {
				$sameAsThis.removeClass("voted");
			}
			$sameAsThis.siblings('.vote-icon').removeClass("voted");
		}

		//update the database
		$.post("./res/php/updateLikes.php", { d: mealDisplacement, m: meal, i: item, lc: likeChange, dc: dislikeChange }, function(data){
			console.log("updating likes");
		}).done(function(){
			console.log("like succesfully changed for " + item + "-" + meal + " with likes of " + likeChange + " and dislikes of " + dislikeChange);
		}).fail(function(){
			console.log("voting failed"); 
			eraseCookie(cookieName);
			$this.toggleClass("voted");
		});
	});

	$('.title').not('#set-year').click(function(){
		closeNav();
		jqXHR.abort();
		var id = $(this).attr("id");
		var $view = $("#" + id + "View");
		$('.view').not($view).css("display", "none");
		$view.css("display", "block");
	});

	$('.content').click(function(){
		closeNav();
	});

	$('#view-nav').click(function(){
		/*$swiper.swipe("disable");*/
		toggleNav();
	});

	$(window).resize(function(){
		resizeStuff();
	});

	$('.nav-pane li').not('#set-year').click(function(){
		$('.nav-pane li').removeClass("active");
		$(this).addClass("active");
	});

	$('#submit-feedback-button').click(function(){
		submitForm();
	});

	$('.suggestions-input').not('#message').keydown(function(e){
		var code = e.keyCode || e.which;
		if (code == 13){ 
			submitForm();
		}
	});

	$(document).keydown(function(e){
		if ($('#menu').hasClass("active")){
			var code = e.keyCode || e.which;
			if (code == 37){ 
				changePage("left");
				return false;
			}
			if (code == 39){ 
				changePage("right");
				return false;
			}
		}
	});

	function resizeStuff(){
		//adaptToOrientation();
		var windowWidth = $(window).width();
		var pageWidth = $('.page').width();
		if (windowWidth<900){
			//$sideNav.css("left","-600px");
			$content.css("width", pageWidth + "px");
		} else {
			$sideNav.css("left","0px");
			var width = pageWidth - $sideNav.width();
			//console.log(width);
			$content.css("width", width + "px");
		}

		if ($('#no-data').css("display") == "none") {
			showMenus();
		}
		$('.page').css("height", $(window).height() + "px");
		$('.lower').css("height", $('.page').height()-$('.top-bar').height() + "px");
		$content.css("height", $content.parent().height() + "px");
		$content.css("top", $('.top-bar').height() + $('.top-bar').offset()['top'] + 1 +"px");
		$content.css("right", (windowWidth-pageWidth)/2 + "px");
	}

	function showMenus(){
		if ($(window).width() > 400){
			$lunch.css("display", "block");
			$supper.css("display", "block");			
		} else {
			var date = new Date();
			var time = date.getHours();

			if (time > 14){
				$supper.css("display", "block");
				$lunch.css("display", "none");
			} else {
				$supper.css("display", "none");
				$lunch.css("display", "block");
			}
		}
	}

	function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	function eraseCookie(name) {
		createCookie(name,"",-1);
	}

	function toggleNav(){
		if ($('.page').width()<900) {
			var position = $sideNav.position();
			//console.log(position["left"]);
			if (position["left"] < 0) {
				$sideNav.animate({left: "0px"});
			} else if (position["left"] >= 0) {
				$sideNav.animate({left: "-600px"});
			}
		}
	}

	function closeNav(){
		/*if ($('.active').attr("id") == "menu") {
			$swiper.swipe("enable");
		}*/

		if ($('.page').width()<900) {
			var position = $sideNav.position();
			if (position["left"] >= 0) {
				$sideNav.animate({left: "-600px"});
			}
		}
	}

	function showMenu(displacement){
		//show the AJAX loading animation so the user knows that processing is occouring
		$('.loading').css('visibility', 'visible').show();

		//use the jQuery get function to call the getMenu.php script asynchronously 
		jqXHR = $.get("./res/php/getMenu.php", { d : displacement }, function(data){
			var $lunchMenu = $('#lunch-menu');
			var $supperMenu = $('#supper-menu');
			//console.log(menu);
			//console.log(displacement);
		}).done(function(data){
			//hide the loading animation as script completed successfully
			$('.loading').hide();

			//decide how to show the date
			var shownDate;
			switch (displacement){
				case 0:
					shownDate = "Today";
					break;
				case 1:
					shownDate = "Tomorrow";
					break;
				case -1:
					shownDate = "Yesterday";
					break;
				default:
					//if not today, tomorrow or yesterday, get the weekday or the nicely formed long date
					shownDate = getWeekday(displacement);
					break;
			}
			//make the html for the date, the long class will be added if the date is more than 28
			//characters, this stops it overflowing the space available
			shownDate = "<p class='day " + (shownDate.length > 28 ? 'long' : '') + "'>" + shownDate + "</p>";
			//console.log(shownDate); //used for testing to check 
			shownDate = $.parseHTML(shownDate); //make the string executable html by jQuery
			$(".day-container").empty().html(shownDate); //replace the current value in the day contatiner

			//get the numeric day of week for the displacement
			var dateToday = new Date();
			var dateOfMenu = new Date();
			dateOfMenu.setDate(dateToday.getDate()+displacement);
			var weekdayNumeric = dateOfMenu.getDay();

			//show all headings as they may have been hidden on the previous call
			$('.heading').show(); 

			if (weekdayNumeric == 0) {
				//if the day is a Sunday then set the lunch menu title to Brunch
				$('#lunch-title').text("Brunch");	
			} else {
				//otherwise it should say Lunch
				$('#lunch-title').text("Lunch");	
			}

			//convert the string returned by the PHP into a JavaScript object
			json = $.parseJSON(data);  
			lunch = json.lunch; //get the lunch data as a variable 
			supper = json.supper; //get the supper data as a variable 
			//console.log(json);
			//console.log(lunch);
			//console.log(supper);
			if (lunch != null && supper != null){
				if ($lunch.css("display") == "none" && $supper.css("display") == "none"){
					showMenus(); //show the menus if they are currently hidden
				}
				$('#no-data').hide(); //hide the no data message

				//for each DOM object with a class of menu item
				//i.e. all the menu item holders in the list
				$('.menu-item').each(function(){
					$this = $(this); //for efficiency
					var id = $this.attr("id"); //get the id if the item
					if (id != undefined && id != "with"){
						var idsplit = id.split("-");
						var item = idsplit[0]; //get the item type from the id
						var meal = idsplit[1]; //get the meal from the id

						//get a string of the cookie name that the votes on the item would be stored under
						var cookieName = getDayMonth(displacement) + "/" + item + "/" + (item == "soup" && lunch.soup == supper.soup ? "" : meal);
						var upClass = "";
						var downClass = "";
						var voted = readCookie(cookieName)

						if (voted == "up"){
							upClass = "voted";
						} else if (voted == "down"){
							downClass = "voted";
						}

						//get the for the thumbs up and thumbs down icons
						//the class of voted sets the thumb to hightlighted
						var voteHTML = displacement == 0 ? "<div class='vote'><img class='vote-icon vote-up " + upClass + "' src='./res/img/vote_up.png' alt='vote up'/><img class='vote-icon vote-down " + downClass + "' src='./res/img/vote_down.png' alt='vote down'/></div>" : "";
						
						//console.log(item);
						//console.log(meal);
						var food = json[meal][item];
						if (food != ""){
							//$this.text(food);
							if ($this.hasClass("heading")){
								$this.html(food);
							} else {
								//set the html for the item with the voting arrows
								$this.html(food + voteHTML);
							}
						} else if (item.indexOf("veg") == -1){
							//if the item is not a vegeatable then hide the heading for that item
							$this.text("");
							$this.prev('.heading').hide();
						} else {
							//otherwise show the heading 
							$this.prev('.heading').show();
							$this.text(""); //avoids null being shown
						}
					} 
				});

				if (weekdayNumeric == 6){ //if it is a Saturday 
					$('#lunch-menu').children(".soup-heading").hide(); //hide the soup heading
					$('#lunch-menu').children(".main-heading").text(json.lunch.soup); //make the main item heading the value of the soup
					$('#soup-lunch').text("");
					//console.log("sat");
				} else {
					//otherwise the heading should be returned to mains
					$('#lunch-menu').children(".main-heading").text("Mains");
				}

				//if the displacement is more than 5 days or before today
				if (displacement > 5 || displacement < 0){
					//console.log("hiding attending");
					$('#attending').hide(); //hide the attending checkbox
				} else {
					$('#attending').show(); //show the attending checkbox
					//see if the user has already set thet they will be attending
					if (readCookie(dateOfMenu.getDate() + "/" + dateOfMenu.getMonth()) == "yes"){
						//if so set the textbox as checked
						$attendingCheckbox.prop('checked', true);
					} else {
						//otherwise set the checkbox as unchecked
						$attendingCheckbox.prop('checked', false);
					}
				}
				var d = new Date();
				//if it is after 3 O'clock then hide the attending checkbox
				if (displacement == 0 && d.getHours() >= 15){
					$('#attending').hide();
				}
			} else {
				$('.menu').hide();
				$('#no-data').show();
			}

		});
	}

	function getDayMonth(displacement){
		var dateToday = new Date();
		var dateOfMenu = new Date();
		dateOfMenu.setDate(dateToday.getDate()+displacement);
		//return the day of the month/the month e.g. 13/10 (13th of October)
		return dateOfMenu.getDate() + "/" + dateOfMenu.getMonth();
	}

	function changePage(direction){
		//aborts the current AJAX getMenu.php request as to aid efficiency
		jqXHR.abort(); 

		//using a ternary operator, a boolean is set as whether the
		//lunch and supper menus are hidden
		var lunchHidden = $lunch.css("display") == "none" ? true : false;
		var supperHidden = $supper.css("display") == "none" ? true : false;
		
		//console.log(lunchHidden);
		//console.log(supperHidden);

		//if they are both hidden or both showing, this will always be true on large screen
		//will be true on mobile devices if there was no data for the current day
		if (lunchHidden == supperHidden){
			if (direction == "left"){
				mealDisplacement--;
			} else if (direction == "right"){
				mealDisplacement++;
			}
			showMenu(mealDisplacement);
		} else if (lunchHidden){
			//if lunch is hidden, hide the supper menu and show the lunch menu
			$supper.css("display", "none");
			$lunch.css("display", "block");
			if (direction == "right") {
				//if direction was to the right get and show menu data for next day
				mealDisplacement++;
				showMenu(mealDisplacement);
			}
		} else if (supperHidden){
			//if supper is hidden, hide the lunch menu and show the supper menu
			$lunch.css("display", "none");
			$supper.css("display", "block");
			//if direction was to the left get and show menu data for previous day
			if (direction == "left"){
				mealDisplacement--;
				showMenu(mealDisplacement);
			}
		} 
	}

	function toTitleCase(str){
	    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	}

	function getWeekday(displacement){
		//arrays of the days and months as strings
		var weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
		var months = ["January","February","March","April","May","June","July","August","September","October","November","December"];

		var dateToday = new Date();
		var dateOfMenu = new Date();
		dateOfMenu.setDate(dateToday.getDate()+displacement);
		
		//get numeric then string values for the weekday and month
		var weekdayNumeric = dateOfMenu.getDay();
		var weekday = weekdays[weekdayNumeric];
		var dayOfMonth = dateOfMenu.getDate();
		var month = months[dateOfMenu.getMonth()];
		//initialise the day siffix
		var daySuffix = "";

		//correct weekday from 0-6 (sunday->saturday) as 1-7 (monday->sunday)
		weekdayNumeric = (weekdayNumeric == 0 ? 7 : weekdayNumeric); 

		//if the day is in the current week
		if (weekdayNumeric-displacement >= 0 && weekdayNumeric-displacement <= 7){
			return weekday; //return just the weekday
		} else {
			//get the correct date suffix e.g. 1st, 2nd, 3rd, 4th
			var dayOfMonth = dayOfMonth.toString();
			var length = dayOfMonth.length;
			var lastDigit = dayOfMonth.substring(length-1);
			var firstDigit = dayOfMonth.substring(0,1);
			//console.log(firstDigit);
			//console.log(lastDigit);
			if (length == 2 && firstDigit == 1){
				daySuffix = daySuffix = "<sup>th</sup>";
			} else {
				switch (lastDigit) {
					case "1":
						daySuffix = "<sup>st</sup>";
						break;
					case "2":
						daySuffix = "<sup>nd</sup>";
						break;
					case "3":
						daySuffix = "<sup>rd</sup>";
						break;
					default:
						daySuffix = "<sup>th</sup>";
						break;
				}
			}
			//return a nicely formatted long date
			return	weekday + ", " + dayOfMonth + daySuffix + " " + month;
		}	
	}

	function submitForm(){
		var formok = true;
		var errors = []; 	//array to hold errors
		//JavaScript object of potential errors
		var errorMessages = {
			email: "You have not entered a valid hppc email address",
			minlength: " must be greater than ",
			maxlength: " must be less than ",
			name: "Invalid name",
			fail: "We did not recieve your feedback, check your connection and try again"
		}

		//select the success and error notices
		var $successNotice = $('#success');
		var $errorNotice = $('#errors');

		//validate that it is a valid hppc.co.uk email address
		var email =  $('#email').val().toLowerCase();
		var emailRegEx = /^[A-z]+\.[A-z]+\@hppc.co.uk$/;
		if(!emailRegEx.test(email)){	
			$('#email').focus();
			formok = false;
			errors.push(errorMessages.email);
		}
		//validate the max length of the email
		if(email.length > $('#email').attr('maxlength')){
			formok = false;
			errors.push("Email" + errorMessages.maxlength + $('#email').attr("maxlength"));
		}

		//validate name
		var name = $('#name').val();
		var nameRegEx = /^[A-z]+\ ?[A-z]*$/
			name = toTitleCase(name);
		if(name.length > $('#name').attr('maxlength')){
			formok = false;
			errors.push("Name" + errorMessages.maxlength + $('#name').attr("maxlength"));
		}
		if(!nameRegEx.test(name)){
			$('#name').focus();
			formok = false;
			errors.push(errorMessages.name);
		}

		//validate message
		var message = $('#message').val();
		if(message.length < $('#message').attr("minlength")){
			formok = false;
			errors.push("Message" + errorMessages.minlength + $('#message').attr("minlength") + " characters");
		}
		
		//remove all current errors
		$errorNotice.find("li[id!='info']").remove();
		
		//if the validation failed
		if(!formok){
			$successNotice.hide();
			for(x in errors){
				//list all errors on the error notice
				$errorNotice.append('<li>'+errors[x]+'</li>');	
			}
			$errorNotice.show(); 	//show the error notice to the user
		} else {
			//if validation successful
			$errorNotice.hide();	//hide error message
			$successNotice.hide();	//show success message
			jqXHR.abort();	//abort current AJAX request

			//submit data to submitForm.php
			jqXHR = $.post("./res/php/submitForm.php", { name: name, email: email, message: message }, function(){
				$('#submit-feedback-button').children('a').text("Sending..."); //change button text to sending to notify user of action
				console.log("Sending feedback");
			}).done(function(data){
				console.log("success");	//for testing
				console.log(data);	//for testing
				if(data[0] != "{"){
					//if server script failed
					console.log("fail"); 	
					$errorNotice.append("<li>There was a problem submitting your message, please contact the admin</li>");
					$errorNotice.show();
				} else {
					var json = $.parseJSON(data);
					console.log(json);
					//if server script validation passed
					if(json.form_ok == true){
						$successNotice.show();	//notify user of successful submission
						$('.suggestions-input').val("");	//empty all of the inputs
					} else {
						//if validation failed list errors on error message
						for(x in json.errors){
							$errorNotice.append('<li>'+json.errors[x]+'</li>');	
							$errorNotice.show();
						}
					}
				}
				
			}).fail(function(){
				//if server request failed show error messsage
				console.log("failed");	//used for testing
				$errorNotice.append("<li>There was a problem submitting your message, please try again</li>");
				$errorNotice.show();
			}).always(function(){
				$('#submit-feedback-button').children('a').text("Send");	//change button back to Send 
			});
		}
	}
});