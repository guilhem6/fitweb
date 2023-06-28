// Variables
var connected = false;

function init() {
    load();
}

function checkOption(){
    //This will fire on changing of the value of "requests"
    var dropDown = document.getElementById("requestDropDown");
    var textBox = document.getElementById("otherBox");

    if(dropDown.value == "other"){
        textBox.style.visibility = "visible";
    }
}