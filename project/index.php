<?php
include_once("libs/maLibUtils.php");
include_once("libs/modele.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

$data = array("version"=>1.3);

// 1.3 : interdiction de modification des users 1 et 2

// Routes : /api/...

$method = $_SERVER["REQUEST_METHOD"];
debug("method",$method);

if ($method == "OPTIONS") die("ok - OPTIONS");

$data["success"] = false;
$data["status"] = 400; 

// Verif autorisation : il faut un hash
// Il peut être dans le header, ou dans la chaîne de requête

$connected = false; 

if (!($hash = valider("hash"))) 
	$hash = valider("HTTP_HASH","SERVER"); 

if($hash) {
	// Il y a un hash, il doit être correct...
	if ($connectedId = hash2id($hash)) $connected = true; 
	else {
		// non connecté - peut-être est-ce POST vers /autenticate...
		$method = "error";
		$data["status"] = 403;
	}
}

if (valider("request")) {
	$requestParts = explode('/',$_REQUEST["request"]);

	debug("rewrite-request" ,$_REQUEST["request"] ); 
	debug("#parts", count($requestParts) ); 

	$entite1 = false;
	$idEntite1 = false;
	$entite2 = false; 
	$idEntite2 = false; 

	if (count($requestParts) >0) {
		$entite1 = $requestParts[0];
		debug("entite1",$entite1); 
	} 

	if (count($requestParts) >1) {	
		if (is_id($requestParts[1])) {
			$idEntite1 = intval($requestParts[1]);
			debug("idEntite1",$idEntite1); 
		} else {
			// erreur !
			$method = "error";
			$data["status"] = 400; 
		}
	}

	if (count($requestParts) >2) {
		$entite2 = $requestParts[2];
		debug("entite2",$entite2); 
	}

	if (count($requestParts) >3) {
		if (is_id($requestParts[3])) {
			$idEntite2 = intval($requestParts[3]);
			debug("idEntite2",$idEntite2); 
		} else {
			// erreur !
			$method = "error";
			$data["status"] = 400;
		}

	}  

// TODO: en cas d'erreur : changer $method pour préparer un case 'erreur'

	$action = $method; 
	if ($entite1) $action .= "_$entite1";
	if ($entite2) $action .= "_$entite2";
 
	debug("action", $action);

	if ($action == "POST_authenticate") {
		if ($user = valider("user"))
		if ($password = valider("password")) {
			if ($hash = validerUser($user, $password)) {
				$data["hash"] = $hash;
				$data["success"] = true;
				$data["status"] = 202;
			} else {
				// connexion échouée
				$data["status"] = 401;
			}
		}
	}
	elseif ($action == "POST_users") {
		if ($pseudo = valider("user"))
				if ($pass = valider("password")) {
					$id = mkUser($pseudo, $pass); 
					$data["user"] = getUser($id);
					$data["success"] = true;
					$data["status"] = 201;
				}
	}
	elseif ($connected)
	{
		// On connaît $connectedId
		switch ($action) {

			case 'GET_users' :			
				if ($idEntite1) {
					// GET /api/users/<id>
					$data["user"] = getUser($idEntite1);
					$data["success"] = true;
					$data["status"] = 200; 
				} 
				else {
					// GET /api/users
					if ($student = valider("student")){
						$data["users"] = getUsers($student);
						$data["success"] = true;
						$data["status"] = 200;
					} else {
						$data["users"] = getUsers();
						$data["success"] = true;
						$data["status"] = 200;
					}
					
				}
			break; 

			case 'GET_users_articles' : 
				if ($idEntite1)
				if ($idEntite2) {
					// GET /api/users/<id>/articles/<id>
					$data["article"] = getArticle($idEntite2, $idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					// GET /api/users/<id>/articles
					$data["articles"] = getArticlesUser($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;

			case 'GET_articles' : 
				if ($idEntite1){
					// GET /api/articles/<id>
					// TODO : vérifier user ?
					$data["article"] = getArticle($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					// GET /api/articles
					// Les listes de l'utilisateur connecté
					$data["articles"] = getArticlesUser($connectedId);
					$data["success"] = true;
					$data["status"] = 200; 
				}
			break;

			case 'GET_articles_paragraphes' : 
				if ($idEntite1)
				if ($idEntite2) {
					// GET /api/articles/<id>/paragraphes/<id>
					$data["paragraphe"] = getParagraphe($idEntite2, $idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					// GET /api/articles/<id>/paragraphes
					$data["paragraphes"] = getParagraphes($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;		 
				}
			break; 

			// case 'POST_users' : 
			// 	// POST /api/users?pseudo=&pass=...
			// 	if ($pseudo = valider("user"))
			// 	if ($pass = valider("password")) {
			// 		$id = mkUser($pseudo, $pass); 
			// 		$data["user"] = getUser($id);
			// 		$data["success"] = true;
			// 		$data["status"] = 201;
			// 	}
			// break; 
			
			// NEW PROJECT
			case 'GET_exercices' :
				if ($idEntite1){
					// GET /api/exercices/<id>
					// si un id est fourni on renvoi l'exo en question
					// sinon tous
					$data["exercice"] = getExercice($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					// GET /api/exercices
					$data["exercices"] = getExercices();
					$data["success"] = true;
					$data["status"] = 200; 
				}
			break;

			case 'GET_users_exercices' :
				if ($idEntite1){
					// GET /api/users/<id>/exercices
					// on renvoi les exos de l'utilisateur
					$data["exercices"] = getExercices($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					// GET /api/articles
					// Les listes de l'utilisateur connecté
					$data["exercices"] = getExercices();
					$data["success"] = true;
					$data["status"] = 200; 
				}
			break;

			case 'POST_users_exercices' : 
				// POST /api/users/<id>/exercices?title=...descr=...duration=...theme=...image=...
				if ($idEntite1)
				if ((($title = valider("title")))
					&& ($descr = valider("descr")) 
					&& ($duration = valider("duration"))
					&& ($theme = valider("theme"))
					&& ($image = valider("image"))) {
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
						$id = mkExercice($title, $descr, $duration, $theme, $idEntite1, $image); 
						$data["exercice"] = getExercice($id);
						$data["success"] = true;
						$data["status"] = 201;
					}
				}
			break;

			case 'PUT_users_exercices' : 
				// DELETE /api/users/<id>/exercices/<id>?title=...descr=...duration=...theme=...
				if ($idEntite1)
				if ($idEntite2)
				if ((($title = valider("title")))
				&& ($descr = valider("descr")) 
				&& ($duration = valider("duration"))
				&& ($theme = valider("theme"))){
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
						$id = upExercice($idEntite2, $title, $descr, $duration, $theme, $image);
						$data["exercice"] = getExercice($id);				
						$data["success"] = true;
						$data["status"] = 201; 
					}
				}
			break;
			
			case 'DELETE_users_exercices' : 
				// DELETE /api/users/<id>/exercices/<id>
				if ($idEntite1)
				if ($idEntite2) {
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
						if (rmExercice($idEntite2, $idEntite1)) {				
							$data["success"] = true;
							$data["status"] = 200; 
						} else {
							// erreur 
						}
					}
				}
			break;

			// GESTION DES CYCLES

			case 'GET_cycles' :
				// GET /api/cycles
				if ($idEntite1){
					// GET /api/cycles/<id>
					// si un id est fourni on renvoi le cycle sélectionné
					// sinon tous
					$data["innercycle"] = getExCycle($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					$data["cycles"] = getCycles();
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;

			case 'GET_users_cycles' :
				// GET /api/users/<id>/cycles
				if ($idEntite1){
					// GET /api/cycles/<id>
					// si un id est fourni on renvoi les cycles de l'utilisateur
					$data["cycles"] = getCycles($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					$data["cycles"] = getCycles();
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;


			case 'POST_users_cycles' :
				// POST /api/users/<id>/cycles?$title...theme...breaktime...repetition...
				if ($idEntite1)
				if (!$idEntite2) {
					if ((($title = valider("title")))
					&& ($theme = valider("theme")) 
					&& ($breaktime = valider("breaktime"))
					&& ($repetition = valider("repetition"))) {
					if ($connectedId != $idEntite1 || !isTrainer($connectedId)) {
						$data["status"] = 403;
					} else {
						$id = mkCycle($title, $theme, $breaktime, $repetition, $idEntite1); 
						$data["cycle"] = getCycle($id);
						$data["success"] = true;
						$data["status"] = 201;
					}
				}
				} else {
					// POST /api/users/<id>/cycles/<id>?$order...duration...exerciceid...
					if ((($order = valider("order")))
					&& ($duration = valider("duration")) 
					&& ($exerciceid = valider("exerciceid"))) {
						if ($connectedId != $idEntite1 || !isTrainer($connectedId)) {
							$data["status"] = 403;
						} else {
							$id = insertExCycle($order, $duration, $idEntite2, $exerciceid); 
							$data["innercycle"] = getExCycle($id);
							$data["success"] = true;
							$data["status"] = 201;
						}
					}
				}
			break;

			case 'PUT_users_cycles' :
				// PUT /api/users/<id>/cycles/<id>?old_order...new_order...exerciceid...
				// echange l'ordre de deux exercices dans un cycle
				if ($idEntite1)
				if ($idEntite2) {
					if ($connectedId != $idEntite1 && (!isAdmin($connectedId))) {
						$data["status"] = 403;
					} else {
						if ((($old_order = valider("old_order")))
						&& ($new_order = valider("new_order")) 
						&& ($exerciceid = valider("exerciceid"))) {
							if ($id = moveExCycle($old_order, $new_order, $idEntite2, $exerciceid)) {	
								$data["innercycle"] = getExCycle($id);		
								$data["success"] = true;
								$data["status"] = 200; 
							} else {
								// erreur 
							}
						}
					}
				}

			break;

			case 'DELETE_users_cycles' :
				// DELETE /api/users/<id>/cycles/<id>
				if ($idEntite1)
				if ($idEntite2) {
					if ($connectedId != $idEntite1 && (!isAdmin($connectedId))) {
						$data["status"] = 403;
					} else {
						if (rmCycle($idEntite2)) {				
							$data["success"] = true;
							$data["status"] = 200; 
						} else {
							// erreur 
						}
					}
				}
			break;

			// GESTION DES GROUPES
			
			case 'GET_groups' :
				// GET /api/groups
				if ($idEntite1){
					// GET /api/groups/<id>
					// si un id est fourni on renvoi le groupe selectionné
					// sinon tous
					$data["members"] = getGroup($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					$data["groupes"] = getGroups();
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;

			case 'GET_users_groups' :
				// GET /api/users/<id>/groups/
				if ($idEntite1){
					$data["groupes"] = getGroups($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;


			case 'POST_users_groups' :
				// POST /api/users/<id>/groups?title...theme...members...
				if ($idEntite1)
				if (!$idEntite2) {
					if ((($title = valider("title")))
					&& ($theme = valider("theme")) 
					&& ($members = valider("members"))) {
					if ($connectedId != $idEntite1 || !isTrainer($connectedId)) {
						$data["status"] = 403;
					} else {
						$id = mkGroup($title, $theme, $idEntite1, $members); 
						$data["members"] = getGroup($id);
						$data["success"] = true;
						$data["status"] = 201;
					}
				}
				} else {
					// POST /api/users/<id>/cycles/<id>?$order...duration...exerciceid...
					if ((($order = valider("order")))
					&& ($duration = valider("duration")) 
					&& ($exerciceid = valider("exerciceid"))) {
						if ($connectedId != $idEntite1 || !isTrainer($connectedId)) {
							$data["status"] = 403;
						} else {
							$id = insertExCycle($order, $duration, $idEntite2, $exerciceid); 
							$data["innercycle"] = getExCycle($id);
							$data["success"] = true;
							$data["status"] = 201;
						}
					}
				}
			break;
			
			case "DELETE_users_groups":
				// DELETE /api/users/<id>/groups/<id>
				if ($idEntite1)
				if ($idEntite2) {
					if ($connectedId != $idEntite1 && (!isAdmin($connectedId))) {
						$data["status"] = 403;
					} else {
						if (rmGroup($idEntite2, $idEntite1)) {				
							$data["success"] = true;
							$data["status"] = 200; 
						} else {
							// erreur 
						}
					}
				}
			break;


			// case 'POST_users_cycles' :
			// 	// POST /api/users/<id>/cycles/<id>?$order...duration...exerciceid...
			// 	if ($idEntite1)
			// 	if ($idEntite2) {
			// 		if ((($order = valider("order")))
			// 		&& ($duration = valider("duration")) 
			// 		&& ($exerciceid = valider("exerciceid"))) {
			// 		if ($connectedId != $idEntite1 || !isTrainer($connectedId)) {
			// 			$data["status"] = 403;
			// 		} else {
			// 			$id = insertExCycle($order, $duration, $idEntite2, $exerciceid); 
			// 			$data["innercycle"] = getExCycle($id);
			// 			$data["success"] = true;
			// 			$data["status"] = 201;
			// 		}
			// 	}
			// 	}
			// break;
				

			case 'POST_users_articles' :
				// POST /api/users/<id>/articles?titre=...
				if ($idEntite1)
				if (($titre = valider("titre")) !== false) {
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
						$id = mkArticle($idEntite1, $titre); 
						$data["article"] = getArticle($id);
						$data["success"] = true;
						$data["status"] = 201;
					}
				}
			break; 

			case 'POST_articles_paragraphes' :
				// POST /api/articles/<id>/paragraphes?contenu=...
				if ($idEntite1)
				if (($contenu = valider("contenu")) !== false) {
					if (!isUserOwnerOfArticle($connectedId,$idEntite1)) {
						$data["status"] = 403;
					} else {
						$id = mkParagraphe($idEntite1, $contenu);					
						$data["paragraphe"] = getParagraphe($id,$idEntite1);
						$data["success"] = true; 
						$data["status"] = 201;
					}
				}
			break; 

			case 'POST_articles' :
				// POST /api/articles?titre=...
				if (($titre = valider("titre")) !== false) {
					$id = mkArticle($connectedId, $titre); 
					$data["article"] = getArticle($id);
					$data["success"] = true; 
					$data["status"] = 201;
				}
			break;

			case 'PUT_authenticate' : 
				// régénère un hash ? 
				$data["hash"] = mkHash($connectedId); 
				$data["success"] = true; 
				$data["status"] = 200;
			break; 

			case 'PUT_users' :
				// PUT  /api/users/?pass=...
				if ($connectedId)
				if ($pass = valider("password")) {
                  	if (($connectedId == 1) || ($connectedId==2)) {
                          $data["status"] = 403;
                        } else if (chgPassword($connectedId,$pass)) {
						$data["user"] = getUser($connectedId);
						$data["success"] = true; 
						$data["status"] = 200;
					} else {
						// erreur 
					}
				}
			break; 

			case 'PUT_users_articles' : 
				// PUT /api/users/<id>/articles/<id>?titre=...
				if ($idEntite1)
				if ($idEntite2)
				if (($titre = valider("titre")) !== false) {
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
						if (chgTitreArticle($idEntite2,$titre,$idEntite1)) {
							$data["article"] = getArticle($idEntite2);
							$data["success"] = true; 
							$data["status"] = 200;
						} else {
							// erreur
						}
					}
				}
			break; 

			case 'PUT_articles' : 
				// PUT /api/articles/<id>?titre=...
				if ($idEntite1)
				if (($titre = valider("titre")) !== false) {
					if (!isUserOwnerOfArticle($connectedId,$idEntite1)) {
						$data["status"] = 403;
					} else {
						if (chgTitreArticle($idEntite1,$titre,$connectedId)) {
							$data["article"] = getArticle($idEntite1);
							$data["success"] = true; 
							$data["status"] = 200;
						} else {
							// erreur
						}
					}
				}
			break; 

			case 'PUT_articles_paragraphes' : 
				// PUT /api/articles/<id>/paragraphes/<id>?contenu=...
				if ($idEntite1)
				if ($idEntite2)
				if (($contenu = valider("contenu")) !== false) {
					if (!isUserOwnerOfArticle($connectedId,$idEntite1)) {
						$data["status"] = 403;
					} else {
						if (chgParagrapheContenu($idEntite2,$contenu,$idEntite1)) {
							$data["paragraphe"] = getParagraphe($idEntite2,$idEntite1);
							$data["success"] = true; 
							$data["status"] = 200;
						} else {
							// erreur
						}
					}
				}

				// PUT /api/articles/<id>/paragraphes/<id>?ordre=...
				if ($idEntite1)
				if ($idEntite2)
				if (($ordre = valider("ordre")) !== false ) {
					if (!isUserOwnerOfArticle($connectedId,$idEntite1)) {
						$data["status"] = 403;
					} else {
						updateOrdreParagraphe($ordre,$idEntite2,$idEntite1);
						$data["paragraphe"] = getParagraphe($idEntite2,$idEntite1);
						$data["success"] = true; 
						$data["status"] = 200;
					}
				}
			break;

			case 'DELETE_users' : 
				// DELETE /api/users/<id> 
				if ($idEntite1) {
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
                      	if (($idEntite1 == 1) || ($idEntite1==2)) {
                          $data["status"] = 403;
                        } else if (rmUser($idEntite1)) {
							$data["success"] = true;
							$data["status"] = 200;
						} else {
							// erreur 
						} 
					}
				}
			break; 

			case 'DELETE_users_articles' : 
				// DELETE /api/users/<id>/articles/<id>
				if ($idEntite1)
				if ($idEntite2) {
					if ($connectedId != $idEntite1) {
						$data["status"] = 403;
					} else {
						if (rmArticle($idEntite2, $idEntite1)) {				
							$data["success"] = true;
							$data["status"] = 200; 
						} else {
							// erreur 
						}
					}
				}
			break; 

			case 'DELETE_articles' : 
				// DELETE /api/articles/<id>
				if ($idEntite1) {
					if (!isUserOwnerOfArticle($connectedId,$idEntite1)) {
						$data["status"] = 403;
					} else {
						if (rmArticle($idEntite1, $connectedId)) {				
							$data["success"] = true;
							$data["status"] = 200; 
						} else {
							// erreur 
						}
					}
				}
			break; 

			case 'DELETE_articles_paragraphes' : 
				// DELETE /api/articles/<id>/paragraphes/<id>
				if ($idEntite1)
				if ($idEntite2) {
					if (!isUserOwnerOfArticle($connectedId,$idEntite1)) {
						$data["status"] = 403;
					} else {
						if (rmParagraphe($idEntite2, $idEntite1)) {				
							$data["success"] = true;
							$data["status"] = 200;  
						} else {
							// erreur 
						}
					}
				}
			break; 
		} // switch(action)
	} //connected
}

switch($data["status"]) {
	case 200: header("HTTP/1.0 200 OK");	break;
	case 201: header("HTTP/1.0 201 Created");	break; 
	case 202: header("HTTP/1.0 202 Accepted");	break;
	case 204: header("HTTP/1.0 204 No Content");	break;
	case 400: header("HTTP/1.0 400 Bad Request");	break; 
	case 401: header("HTTP/1.0 401 Unauthorized");	break; 
	case 403: header("HTTP/1.0 403 Forbidden");		break; 
	case 404: header("HTTP/1.0 404 Not Found");		break;
	default: header("HTTP/1.0 200 OK");
		
}

echo json_encode($data);

?>