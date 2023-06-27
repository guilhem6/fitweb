
function load() {
    // Load header.html
    fetch('header.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('header').innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
        });

    // Load footer.html
    fetch('footer.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('footer').innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function connexion(login,password){
    console.log("connexion")
    var settings = {
        "url": "http://project/api/authenticate?user="+login+"&password="+password,
        "method": "POST",
        "timeout": 0,
      };
      
      $.ajax(settings).done(function (response) {
        console.log(response);
      });
}
