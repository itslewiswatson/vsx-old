function highlighting() {
	var a = window.location.pathname.split("/");
	var curr = a[a.length - 1].split(".php")[0];

	// I believe this can be simplified
	/*
	if (window.location.pathname.match("stocks")) {
		document.getElementById("index").className += " active";
		return;
	}
	if (curr == "") {
		document.getElementById("index").className += " active";
		return;
	}
	*/

	//if (window.location.pathname.match("stocks")) {
	//	curr = "stocks";
	if (window.location.pathname.match(curr)) {
		curr = curr;
	}
	if (!curr || curr == "") {
		curr = "index";
	}

	document.getElementById(curr).className += " active";
}
//window.onload = highlighting; // For some reason, this does not work (thanks JavaScript)
highlighting();
