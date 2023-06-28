
var cache = {
    "apiRoot" : false,
    "hash" : false, 
    "pseudo" : false,
    "logged" :false
}; 

function load() {
    // Load header.html
    fetch('header.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('header').innerHTML = data;
            var estConnecte = this.estConnecte();
            document.getElementById('connected').style.display = estConnecte? "inline" : "none";
            document.getElementById('anonymous').style.display = estConnecte? "none" : "inline";
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

    $.ajax({
        type: "POST",
        url: "http://project/api/authenticate",
        data: {"user":login,"password":password},
        dataType: "json",
        success: function(oRep) {

            console.log(oRep);
            cache.hash = oRep.hash;
            cache.pseudo = login;
            cache.logged = true;
            localStorage.setItem('username', login);
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

            cache.hash = oRep.hash;
            console.log(oRep);

            //récupération des utilisateurs
            $.ajax({
                type: "GET",
                url: "http://project/api/users",
                dataType: "json",
                headers:{
                    hash:cache.hash
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
                                cache.hash = oRep.hash;
                                cache.pseudo = login;
                                cache.logged = true
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
    return true;}
}

function estConnecte(){
    console.log("est connecte")
    if(localStorage.getItem('username') == null){
        return false
    }
    else return true
}