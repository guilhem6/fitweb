<?php

session_start();

	include_once "libs/maLibUtils.php";
	include_once "libs/maLibSQL.pdo.php";
	include_once "libs/maLibSecurisation.php"; 
	include_once "libs/modele.php"; 

	$qs = "";
	


	if ($action = valider("action"))
	{
		ob_start ();
		echo "Action = '$action' <br />";

		// ATTENTION : le codage des caractères peut poser PB 
		// si on utilise des actions comportant des accents... 
		// A EVITER si on ne maitrise pas ce type de problématiques

		/* TODO: exercice 4
		// Dans tous les cas, il faut etre logue... 
		// Sauf si on veut se connecter (action == Connexion)

		if ($action != "Connexion") 
			securiser("login");
		*/

		// Un paramètre action a été soumis, on fait le boulot...
		switch($action)
		{
			
			// Connexion //////////////////////////////////////////////////

			case 'logout' :
			case 'Logout' :
			case 'deconnexion' :
						session_destroy();			
			break; 
			
			case 'Connexion' :
				// On verifie la presence des champs login et passe
				
				if ($login = valider("login"))
				if ($pass = valider("pass"))
				{
					// On verifie l'utilisateur, et on crée des variables de session si tout est OK
					// Cf. maLibSecurisation
					verifUser($login,$pass); 	
				}

				// On redirigera vers la page index automatiquement
			break;
		
		
		
			// Recu : 
			// idUser:  3
			// action: Interdire
			// NEVER TRUST USER INPUT 
			// risques : sécurité 
			// limites du cahier des charges en termes de permissions 
			
			//http://localhost/TWE2023/tinyMVC/controleur.php
			//.. ?action=Interdire&idUser=3;drop table users;
		
			case 'Interdire' : 
				// attention : ceci est une affectation, pas une comparaison 
				// la valeur (de vérité) d'une affectation, c'est la valeur affectée
				// équivalent à : 
				// 1) $idUser = valider("idUser")
				// => certitude que la variable existe 
				// cette variable peut valoir false 
				// OU un identifiant en bdd (numérique auto-incrémenté : forcément > 0) 
				// 2) if ($idUser)
				if ($idUser = valider("idUser"))
					interdireUtilisateur($idUser);
				// rediriger vers la vue users 
				$qs = "?view=users&idLastUser=$idUser";
				
			break;
			
			case 'Autoriser' : 
				if ($idUser = valider("idUser"))
					autoriserUtilisateur($idUser);
				// rediriger vers la vue users 
				$qs = "?view=users&idLastUser=$idUser";
				
			break;
			
			// NEVER TRUST USER INPUT
			// Sécurité / Cahier des charges ? 
			case 'Activer' : 
				if ($idConv = valider("idConv"))
				if (valider("connecte","SESSION"))
				if ($_SESSION["isAdmin"]) {
					reactiverConversation($idConv);
				}
				$qs = "?view=conversations&idLastConv=$idConv";
			break;
			
			case 'Archiver' : 
				if ($idConv = valider("idConv"))
				if (valider("connecte","SESSION"))
				if ($_SESSION["isAdmin"]) {
					archiverConversation($idConv);
				}
				$qs = "?view=conversations&idLastConv=$idConv";
			break;
			
			case 'Supprimer' : 
				if ($idConv = valider("idConv"))
				if (valider("connecte","SESSION"))
				if ($_SESSION["isAdmin"]) {
					supprimerConversation($idConv);
				}
				$qs = "?view=conversations";
			break;
			
			case 'Créer' : 
				if ($theme = valider("theme"))
				if (valider("connecte","SESSION"))
				if ($_SESSION["isAdmin"]) {
					$idNewConv = creerConversation($theme);
				}
				$qs = "?view=conversations&idLastConv=$idNewConv";
			break;
			
			case 'Poster' : 
				if ($idConv = valider("idConv")) // première condition à tester
				if ($contenu = valider("contenu"))
				if (valider("connecte","SESSION"))
				{
					$idUser = $_SESSION["idUser"];
					// ATTENTION : la conversation ne doit pas etre archivee 
					$dataConv = getConversation($idConv);  
					if ($dataConv["active"])
						enregistrerMessage($idConv, $idUser, $contenu);
				}
				$qs = "?view=chat&idConv=$idConv";
			break;
		}

	}

	// On redirige toujours vers la page index, mais on ne connait pas le répertoire de base
	// On l'extrait donc du chemin du script courant : $_SERVER["PHP_SELF"]
	// Par exemple, si $_SERVER["PHP_SELF"] vaut /chat/data.php, dirname($_SERVER["PHP_SELF"]) contient /chat

	$urlBase = dirname($_SERVER["PHP_SELF"]) . "/index.php";
	// On redirige vers la page index avec les bons arguments

	header("Location:" . $urlBase . $qs);
	//qs doit contenir le symbole '?'

	// On écrit seulement après cette entête
	ob_end_flush();
	
?>










