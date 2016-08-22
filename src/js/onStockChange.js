Number.prototype.formatMoney = function(c, d, t) {
	var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

/*
function setCaretPosition(elemId, caretPos) {
    var elem = document.getElementById(elemId);

    if(elem != null) {
        if(elem.createTextRange) {
            var range = elem.createTextRange();
            range.move('character', caretPos);
            range.select();
        }
        else {
            if(elem.selectionStart) {
                elem.focus();
                elem.setSelectionRange(caretPos, caretPos);
            }
            else
                elem.focus();
        }
    }
}
*/

/*
function tocomma(num) {
	return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
*/

function tocomma(n) {
    var parts=n.toString().split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
}

function isValidNumber(num) {
	num = Number(num);
	if (typeof num !== "number" || num < 1) {
        return false;
    }
	return true;
}

function onEditBuy() {
	if (!stockPrice) {
		document.getElementById("buy-text").innerHTML = "Live stock price could not be fetched";
		return;
	}
    var qty = document.getElementById("buy").value;
	if (qty.replace(" ", "") == "") {
		document.getElementById("buy-text").innerHTML = "";
		return;
	}
    qty = Number(qty);
    if (!isValidNumber(qty)) {
        document.getElementById("buy-text").innerHTML = "Please enter a valid number";
        return;
    }
	var price = qty * stockPrice;
	price = (price).formatMoney(2);
    document.getElementById("buy-text").innerHTML = "This will cost you <strong>$" + price + "</strong>";
}

function onEditSell() {
	if (!stockPrice) {
		document.getElementById("sell-text").innerHTML = "Live stock price could not be fetched";
		return;
	}
	
    var qty = document.getElementById("sell").value;
	if (qty == "" || qty.replace(" ", "") == "" || qty.length == 0) {
		document.getElementById("sell-text").innerHTML = "";
		return;
	}
	
	qty = qty.replace(".", "").replace(",", "");
    qty = Number(qty);
    if (!isValidNumber(qty) || qty.NaN || qty == "Infinity") {
        document.getElementById("sell-text").innerHTML = "Please enter a valid number";
		document.getElementById("sell").value = "";
        return;
    }
	
	var val = document.getElementById("sell").value; // Not casted to a number
	while ((val.match(/,/g) || []).length) {
		val = val.replace(",", "")
	}
	
	var val2 = Number(val); // val2 because we will use val for caret index
	if (!val2 || isNaN(val2)) {
		document.getElementById("sell-text").innerHTML = "Please enter a valid number";
		document.getElementById("sell").value = "";
		return;
	}
	
	if (document.getElementById("sell").value.replace(" ", "") == "") {
		document.getElementById("sell-text").innerHTML = "";
	}
	
	var price = val2 * stockPrice;
	price = (price).formatMoney(2);
    document.getElementById("sell-text").innerHTML = "You will gain <strong>$" + price + "</strong>";
	document.getElementById("sell").value = tocomma(val2);
}

window.addEventListener("load",
    function () {
        document.getElementById("buy").addEventListener("input", onEditBuy);
        document.getElementById("sell").addEventListener("input", onEditSell);
		if (!stockPrice) {
			document.getElementById("buy-text").innerHTML = "Live stock price could not be fetched";
			document.getElementById("sell-text").innerHTML = "Live stock price could not be fetched";
		}
    }
);
