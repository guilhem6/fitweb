<?php


//C'est la propriété php_self qui nous l'indique : 
// Quand on vient de index : 
// [PHP_SELF] => /chatISIG/index.php 
// Quand on vient directement par le répertoire templates
// [PHP_SELF] => /chatISIG/templates/accueil.php

// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
// Pas de soucis de bufferisation, puisque c'est dans le cas où on appelle directement la page sans son contexte
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=accueil");
	die("");
}

?>

<div id="corps">

<h1>Accueil</h1>

Bienvenue dans notre site de suivi d'entraînements sportifs !
C'est bien ici !!
<div><h2>Exercices</h2>
<p>Consultez votre liste d'exercices, avec des explications pour les réaliser</p></div>
<div><h2>Cycles</h2></div>
<p>Consultez vos cycles d'exercices.</p>
<div><h2>Tableau de bord</h2></div>
<p>Suivez votre progression !</p>
<div><h2>Groupes</h2></div>
<p>Retrouvez ici vos groupes d'entraînement</p>
<div><h2>Mon entraînement</h2></div>
<p>Sélectionnez votre entraînement et lancez-le avec un chronomètre !</p>


</div>