<?php

// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=login");
	die("");
}

?>

<div id="corps">

<h1>Entrez vos identifiants</h1>

<div id="formLogin">
<form action="controleur.php" method="GET">
Pseudo</br><input type="text" name="login" /><br />
Mot de passe</br><input type="password" name="pass" /><br />
<input type="submit" name="action" value="Connexion" />
</form>
</div>

</div>