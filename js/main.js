//idk just testing stuff
function toggleMobileMenu() {
	console.log("tog");
	$(".mobilemenu").slideToggle();
}

function checkNavbar() {
	if(document.getElementById("mobilemenubutton")) {
		console.log("undo thing");
		$("#navcontent > ul").removeClass("mobilemenu");
		$("#mobilemenubutton").remove();
	}

	if($("#navcontent").height() > $("#navcontainer").height()) {
		if(!document.getElementById("mobilemenubutton")) {
			console.log("do thing!");
			$("#navcontent > ul").addClass("mobilemenu").before("<a href='javascript:toggleMobileMenu();' id='mobilemenubutton'>|||</a>");
		}
	} else {
		$("#navcontent > ul").show();
	}
}

$(document).ready(function () {
	checkNavbar()
	//$("#mobilemenubutton").click(function () {
	//	$(".mobilemenu").slideToggle();
	//});
});
$(window).resize(checkNavbar);


