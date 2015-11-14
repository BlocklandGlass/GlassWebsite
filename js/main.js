//idk just testing stuff
function checkNavbar() {
	if($("#navcontent").height() > $("#navcontainer").height()) {
		console.log("do thing!");
		$("#navcontent > ul").addClass("mobilemenu").before("<div id='mobilemenubutton'>|||</div>");;
	}
}

$(document).ready(function () {
	checkNavbar()
	$("#mobilemenubutton").click(function () {
		$(".mobilemenu").toggle();
	});
});
$(window).resize(checkNavbar);


