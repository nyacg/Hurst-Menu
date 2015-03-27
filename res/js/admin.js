var loaded = 0;
$(document).ready(function(){
	$('.switch').each(function(){
		$(this).bootstrapSwitch();
		loaded++;
	});
});