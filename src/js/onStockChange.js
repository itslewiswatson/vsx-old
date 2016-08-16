function onEditBuy() {
    var qty = document.getElementById("buy").value;
    qty = Number(qty);
    if (typeof qty !== "number" || qty < 1) {
        document.getElementById("buy-text").innerHTML = "Please enter a valid number";
        return;
    }
    document.getElementById("buy-text").innerHTML = "This will cost you <strong>$</strong>";
}

function onEditSell() {
    var qty = document.getElementById("sell").value;
    qty = Number(qty);
    if (typeof qty !== "number" || qty < 1) {
        document.getElementById("sell-text").innerHTML = "Please enter a valid number";
        return;
    }
    document.getElementById("sell-text").innerHTML = "You will gain <strong>$</strong>";
}

window.addEventListener("load",
    function () {
        document.getElementById("buy").addEventListener("input", onEditBuy);
        document.getElementById("sell").addEventListener("input", onEditSell);
    }
);
