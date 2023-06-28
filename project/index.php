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

			// NEW PROJECT
			case 'GET_user' :
					// GET l'id de l'utilisateur connecté (= hash en header)
					$data["id"] = $connectedId;
					$data["success"] = true;
					$data["status"] = 200;
			break;

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
				// PUT /api/users/<id>/exercices/<id>?title=...descr=...duration=...theme=...
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

			case 'GET_creator_groups' :
				// GET /api/users/<id>/groups/
				if ($idEntite1){
					$data["groupes"] = getGroups($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;

			case 'GET_users_groups' :
				// GET /api/users/<id>/groups
				if ($idEntite1){
					// GET /api/users/<id>/groups
					// Renvoi les groupes du user 
					$data["groups"] = getUserGroups($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				}

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

			case 'PUT_users_groups' :
				// PUT /api/users/<id>/groups/<id>?members...
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

			// GESTION DES ASSIGNMENTS

			case 'GET_assignments' :
				// GET /api/assignments
				if ($idEntite1){
					// GET /api/assignments/<id>
					// si un id est fourni on renvoi le assignment selectionné
					// sinon tous
					$data["assignments"] = getAssignment($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				} else {
					$data["assignment"] = getAssignments();
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;
			
			case 'GET_groups_assignments' :
				// GET /api/groups/<id>/assignments
				if ($idEntite1){
					// GET /api/groups/<id>/assignments
					// On renvoie tous les assignments d'un groupe
					$data["assignments"] = getAssignments($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;

			case 'GET_users_assignments' :
				// GET /api/users/<id>/assignments
				if ($idEntite1){
					// GET /api/users/<id>/assignments
					// On renvoie tous les assignments d'un utilisateur
					$data["assignments"] = getUserAssignments($idEntite1);
					$data["success"] = true;
					$data["status"] = 200;
				}
			break;

			case 'POST_groups_assignments' :
				// POST /api/groups/<id>/assignments
				// On crée un assignment pour un groupe
				if ($idEntite1)
				if (!$idEntite2) {
					if ((($title = valider("title")))
					&& ($message = valider("message")) 
					&& ($cycle_id = valider("cycle_id")) 
					&& ($due_date = valider("due_date"))) {
					if (!isTrainer($connectedId) && !isAdmin($connectedId)) {
						$data["status"] = 403;
					} else {
						$id = mkAssignment($title, $message, $cycle_id, $due_date, $idEntite1); 
						$data["assignment"] = getAssignment($id);
						$data["success"] = true;
						$data["status"] = 201;
					}
				}}
				break;

				case 'PUT_groups_assignments' : 
					// PUT /api/groups/<id>/assignments/<id>?title...message...cycle_id...due_date...group_id...done...
					if ($idEntite1)
					if ($idEntite2) {
						$done = valider("done");
						if ((($title = valider("title")))
						&& ($message = valider("message"))
						&& ($cycle_id = valider("cycle_id"))
						&& ($due_date = valider("due_date"))
						&& ($done == 0 || $done == 1)){
							if (!isTrainer($connectedId) && !isAdmin($connectedId)) {
								$data["status"] = 403;
							} else {
								$data["mofif"] = upAssignments($title, $message, $cycle_id, $due_date, $idEntite1, $done, $idEntite2);				
								$data["success"] = true;
								$data["status"] = 201; 
							}
						}
					}
					
				break;

				case 'DELETE_assignments' :
					// DELETE /api/assignments/<id>
					if ($idEntite1)
					{
						if ((!isAdmin($connectedId)) || !isTrainer($connectedId)) {
							$data["status"] = 403;
						} else {
							if (rmAssignment($idEntite1)) {				
								$data["success"] = true;
								$data["status"] = 200; 
							} else {
								// erreur 
							}
						}
					}
				break;

				// GESTION DES SCORES

				case 'GET_scores' :
					// GET /api/scores
					// On renvoi tous les scores
					$data["scores"] = getScores();
					$data["success"] = true;
					$data["status"] = 200;
				break;

				case 'GET_users_scores' :
					// GET /api/users/<id>/scores
					if ($idEntite1)
					if (!$idEntite2)	{
						// GET /api/users/<id>/scores
						// On renvoie tous les scores d'un utilisateur
						$data["scores"] = getUserScores($idEntite1);
						$data["success"] = true;
						$data["status"] = 200;
					} else {
						// GET /api/users/<id>/scores/<id>
						// On renvoi les scores de l'utilisateur pour le GROUPE donné
						$data["scores"] = getUserScores($idEntite1, $idEntite2);
						$data["success"] = true;
						$data["status"] = 200;
					}
				break;

				case 'GET_assignments_scores' :
					// GET /api/assignments/<id>/scores
					// Renvoi les scores de certains assignments
					if ($idEntite1)
					if (!$idEntite2){
						// GET /api/users/<id>/scores
						// On renvoie tous les scores d'un utilisateur
						$data["scores"] = getAssiScores($idEntite1);
						$data["success"] = true;
						$data["status"] = 200;
					} else {
						// erreur
					}
				break;
				
				case 'POST_users_assignments' :
					// POST /api/users/<id>/assignments/<id>?score...feedback...
					// On crée un score pour l'utilisateur sur cet assignment
					if ($idEntite1)
					if ($idEntite2) {
						if ((($score = valider("score")))
						&& ($feedback = valider("feedback"))) {
						if (($connectedId != $idEntite1 && (!isAdmin($connectedId))) || (!isAllowedScore($idEntite1,$idEntite2))) {
							$data["status"] = 403;
						} else {
							mkScores($idEntite1, $idEntite2, $score, $feedback); 
							$data["scores"] = getAssiScores($idEntite2);
							$data["success"] = true;
							$data["status"] = 201;
						}
					}}
				break;

				case 'PUT_users_assignments' : 
					// PUT /api/users/<id>/assignments/<id>?score...feedback...
					if ($idEntite1)
					if ($idEntite2) {
						if ((($score = valider("score")))
						&& ($feedback = valider("feedback"))) {
						if (($connectedId != $idEntite1 && (!isAdmin($connectedId))) || (!isAllowedScore($idEntite1,$idEntite2))) {
							$data["status"] = 403;
						} else {
							upScores($idEntite1, $idEntite2, $score, $feedback); 
							$data["scores"] = getAssiScores($idEntite2);
							$data["success"] = true;
							$data["status"] = 201;
						}
					}}
					
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
                  	    if (chgPassword($connectedId,$pass)) {
							$data["user"] = getUser($connectedId);
							$data["success"] = true; 
							$data["status"] = 200;
						}
					else {
						// erreur 
					}
				}
			break; 

			case 'DELETE_users' : 
				// DELETE /api/users/<id> 
				if ($idEntite1) {
					if ($connectedId != $idEntite1 && !isAdmin($connectedId)) {
						$data["status"] = 403;
					} else {
                      	if (rmUser($idEntite1)) {
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