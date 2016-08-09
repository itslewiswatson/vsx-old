function highlighting() {
	var a = window.location.pathname.split("/");
	var curr = a[a.length - 1].split(".php")[0];

	if (window.location.pathname.match(curr)) {
		curr = curr;
	}
	if (!curr || curr == "") {
		curr = "index";
	}
	if (document.getElementById(curr)) {
		document.getElementById(curr).className += " active";
	}
}
//window.onload = highlighting; // For some reason, this does not work (thanks JavaScript)
highlighting();
