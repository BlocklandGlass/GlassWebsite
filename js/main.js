function toggleMobileMenu() {
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
			$("#navcontent > ul").addClass("mobilemenu").before("<a href='javascript:toggleMobileMenu();' id='mobilemenubutton'>|||</a>");
			$(".navcontent .mobilemenu").removeAttr("style");
		}
	} else {
		$("#navcontent > ul").show();
	}
}

$(document).ready(function () {
	checkNavbar()
});
$(window).resize(checkNavbar);
