<?php

// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php");
	die("");
}

// On envoie l'entête Content-type correcte avec le bon charset
header('Content-Type: text/html;charset=utf-8');

// Pose qq soucis avec certains serveurs...
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- **** H E A D **** -->
<head>	
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
	<title>Webfit</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<!-- **** F I N **** H E A D **** -->


<!-- **** B O D Y **** -->
<body>

<div id="banniere">

<div id="logo">
<img src="ressources/ec-lille-rect.png" />
</div>

<a href="index.php?view=home">Accueil</a>
<a href="index.php?view=login">Connexion</a>
<a href="index.php?view=logout">Déconnexion</a>
<a href="index.php?view=exercises">Exercices</a>
<a href="index.php?view=groups">Mes groupes</a>
<a href="index.php?view=dashboard">Mon tableau de bord</a>
<a href="index.php?view=logout">Entraînement</a>
<a href="index.php?view=cycles">Mes cycles</a>
<a href="index.php?view=exercisesadm">Gestion des exercices</a>
<a href="index.php?view=cyclesadm">Gestion des cycles</a>
<a href="index.php?view=feedbacks">Feedbacks</a>
<a href="index.php?view=accounts">Gestion des comptes</a>
<a href="index.php?view=groupadm">Gestion des groupes</a>


<?php
// Si l'utilisateur n'est pas connecte, on affiche un lien de connexion 
if (!valider("connecte","SESSION"))
	echo "<a href=\"index.php?view=login\">Se connecter</a>";
?>

<h1 id="stitre"> Webfit </h1>

</div>