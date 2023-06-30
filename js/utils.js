
function load() {
    // Load header.html
    fetch("/fitweb/header.html")
        .then(response => response.text())
        .then(data => {
            document.getElementById('header').innerHTML = data;
            if (this.estConnecte()) {
                document.getElementById('anonymous').style.display = "none";
            }
            else {
                document.getElementById('connected').style.display = "none";
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });

    // Load footer.html
    fetch('/fitweb/footer.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('footer').innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function connexion(login,password){
    console.log("connexion", login, password)

    $.ajax({
        type: "POST",
        url: "http://project/api/authenticate",
        data: {"user":login,"password":password},
        dataType: "json",
        success: function(oRep) {

            console.log(oRep);
            localStorage.setItem('username', login);
            localStorage.setItem('hash',oRep.hash);
            $.ajax({
                type: "GET",
                url:"http://project/api/user",
                dataType:"json",
                headers:{hash:oRep.hash},
                success: function(user) {
                    localStorage.setItem('userid',user.id);

                    $.ajax({
                        type: "GET",
                        url:"http://project/api/users/"+user.id,
                        dataType:"json",
                            headers:{hash:oRep.hash},
                            success: function(resp) {
                                var currentUser = resp.user;
                                localStorage.setItem('istrainer',currentUser.trainer);
                                localStorage.setItem('isadmin',currentUser.admin);
                                window.location.href = '../index.html';

                            },
                            error: function() {console.log("Erreur dans la récupération du type de l'utilisateur")}

                    });
                },
                error: function() {console.log("Erreur dans la récupération de l'id")}

            });

            
            
            
        },
        error: function() {console.log("Mauvais login ou mot de passe")}
    });
}

function createAccount(login,password,isTrainer){
    console.log("createAccount")
    //connexion au compte guilhem
    
    $.ajax({
        type: "POST",
        url: "http://project/api/authenticate",
        data: {"user":"guilhem","password":"super"},
        dataType: "json",
        success: function(oRep){

            console.log(oRep);

            //récupération des utilisateurs
            $.ajax({
                type: "GET",
                url: "http://project/api/users",
                dataType: "json",
                headers:{
                    hash:oRep.hash,
                },
                success: function(oRep){
        
                    console.log(oRep);
                    var pseudos = oRep.users;
                    var pseudoExiste = false;
                    for(var i = 0;i<pseudos.length;i++){
                        var pseudo = pseudos[i].pseudo;
                        console.log(pseudo,login);
                        if (pseudo == login){pseudoExiste=true;
                        ;
                        break;}
                    }
                    if(!pseudoExiste){
                        console.log(login,password)
                        $.ajax({
                            type: "POST",
                            url: "http://project/api/users",
                            data: {"user":login,"password":password},
                            dataType: "json",
                            success: function(oRep){
                                console.log(oRep);
                            },
                            error: function(){
                                console.log("Erreur de création de compte...");}})
                    }else{console.log("Identifiant deja utilise")}

                },
                error: function(){
                    console.log("Erreur de récupération des utilisateurs !");
                }
            }

      )
        },
        error: function(){
            console.log("Erreur d'identification au compte guilhem !");}})

    }

function affichageUser(){
    console.log("affichage user ")
    var storedUsername = localStorage.getItem('username');
    if(storedUsername == null){
        return("Vous n'êtes actuellement pas connecté.")
    }
    else{
        return('Compte actif : '+storedUsername)
    }
}

function deconnexion(){
    console.log("deconnexion");
    var storedUsername = localStorage.getItem('username');
    if(storedUsername==null){
        console.log("deja deconnecte");
        return false
    }
    else{
    localStorage.removeItem('username');
    localStorage.removeItem('hash');
    localStorage.removeItem('userid');
    localStorage.removeItem('istrainer');
    localStorage.removeItem('isadmin');
    localStorage.removeItem('trainingId');
    return true;}
}

function estConnecte(){
    console.log("fonction est connecte")
    var username = localStorage.getItem('username')
    if(username == null){
        return false
    }
    else return true
}