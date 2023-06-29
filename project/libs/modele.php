<?php
include_once("maLibSQL.pdo.php"); 
// définit les fonctions SQLSelect, SQLUpdate...


// Users ///////////////////////////////////////////////////

function validerUser($pseudo, $pass){
	$SQL = "SELECT id FROM users WHERE pseudo='$pseudo' AND pass='$pass'";
	if ($id=SQLGetChamp($SQL))
		return getHash($id);
	else return false;
}

function hash2id($hash) {
	$SQL = "SELECT id FROM users WHERE hash='$hash'";
	return SQLGetChamp($SQL); 
}

function hash2pseudo($hash) {
	$SQL = "SELECT pseudo FROM users WHERE hash='$hash'";
	return SQLGetChamp($SQL); 
}

function getUsers($students = false){
	$SQL = "SELECT id,pseudo FROM users";
	if ($students) {
		$SQL .= " WHERE trainer = 0 AND admin = 0";
	}
	return parcoursRs(SQLSelect($SQL));
}

function getUser($idUser){
	$SQL = "SELECT id,pseudo,trainer,admin FROM users WHERE id='$idUser'";
	$rs = parcoursRs(SQLSelect($SQL));
	if (count($rs)) return $rs[0]; 
	else return array();
}

function getHash($idUser){
	$SQL = "SELECT hash FROM users WHERE id='$idUser'";
	return SQLGetChamp($SQL);
}

function mkHash($idUser) {
	// génère un (nouveau) hash pour cet user
	// il faudrait ajouter une date d'expiration
	$dataUser = getUser($idUser);
	if (count($dataUser) == 0) return false;
 
	$payload = $dataUser["pseudo"] . date("H:i:s");
	$hash = md5($payload); 
	$SQL = "UPDATE users SET hash='$hash' WHERE id='$idUser'"; 
	SQLUpdate($SQL); 
	return $hash; 
}

function mkUser($pseudo, $pass){
	$SQL = "INSERT INTO users(pseudo,pass) VALUES('$pseudo', '$pass')";
	$idUser = SQLInsert($SQL);
	mkHash($idUser); 
	return $idUser; 
}


function rmUser($idUser) {
	$SQL = "DELETE FROM users WHERE id='$idUser'";
	return SQLDelete($SQL);
}

function chgPassword($idUser,$pass) {
	$SQL = "UPDATE users SET pass='$pass' WHERE id='$idUser'";
	SQLUpdate($SQL);
	return 1; 
}

// NEW USERS MANAGEMENT /////////////////////////////////////////////////////////////////////////////

function isTrainer($id) {
	$SQL = "SELECT trainer FROM users WHERE id = '$id'";
	return SQLGetChamp($SQL);
}

function isAdmin($id) {
	$SQL = "SELECT admin FROM users WHERE id = '$id'";
	return SQLGetChamp($SQL);
}
 

// NEW CRUD EXERCICE ////////////////////////////////////////////////////////////////////////////////

function getExercices($id = false){
	$SQL = "SELECT e.id,e.title,e.theme,e.image,u.pseudo FROM exercices e INNER JOIN users u ON u.id = e.creator";
	if ($id) {
		$SQL .= " WHERE creator='$id'";
	}
	return parcoursRs(SQLSelect($SQL));
}

function getExercice($id,$idUser=false){
	$SQL = "SELECT * FROM exercices WHERE id='$id'"; 
	if ($idUser)
		$SQL .= " AND creator='$idUser'"; 
	$rs = parcoursRs(SQLSelect($SQL));
	if (count($rs)) return $rs[0]; 
	else return array();
}

function mkExercice($title, $descr, $duration, $theme, $creatorid, $image){
	$SQL = "INSERT INTO exercices(title,descr,duration,theme,creator,image) VALUES('$title','$descr', '$duration', '$theme', '$creatorid', '$image')";
	return SQLInsert($SQL);
}

function upExercice($id, $title, $descr, $duration, $theme, $image){
	$SQL = "UPDATE exercices SET title='$title', descr='$descr', duration='$duration', theme='$theme', image = $image WHERE id='$id'";;
	return SQLUpdate($SQL);
}

function rmExercice($id, $idUser=false) {
	$SQL = "DELETE FROM exercices WHERE id='$id'";
	if ($idUser) $SQL .= " AND creator='$idUser'"; 
	return SQLDelete($SQL);
}

// NEW CRUD CYCLE ////////////////////////////////////////////////////////////////////////////////

function mkCycle($title, $theme, $breaktime, $repetition, $creatorid){
	$SQL = "INSERT INTO cycles(title, theme, breaktime, repetition, creator) VALUES('$title', '$theme', '$breaktime', '$repetition', '$creatorid')";
	return SQLInsert($SQL);
}

function insertExCycle($order, $duration, $cycleid, $exerciceid){
	$SQL = "UPDATE innercycles SET order_ex = order_ex+1 
					WHERE order_ex >= '$order' AND id_cycle ='$cycleid'"; 
	SQLUpdate($SQL);
	$SQL = "INSERT INTO innercycles(order_ex, id_cycle, id_exercice, duration) VALUES('$order', '$cycleid', '$exerciceid', '$duration')";
	SQLInsert($SQL);
	return $cycleid;
}

function removeExCycle($order, $cycleid) {
	$SQL = "DELETE FROM innercycles WHERE order_ex = '$order' AND id_cycle ='$cycleid'"; 
	SQLDelete($SQL);
	$SQL = "UPDATE innercycles SET order_ex = order_ex-1 
					WHERE order_ex > '$order' AND id_cycle ='$cycleid'";
	return SQLUpdate($SQL);
}

function moveExCycle($old_order, $new_order, $cycleid, $exerciceid) {
	if ($old_order < $new_order) {
		$SQL = "UPDATE innercycles SET order_ex = order_ex-1 
					WHERE order_ex <= '$new_order' AND order_ex > '$old_order' AND id_cycle ='$cycleid'";
		SQLUpdate($SQL);
		$SQL = "UPDATE innercycles SET order_ex = $new_order
					WHERE order_ex = '$old_order' AND id_exercice ='$exerciceid' AND id_cycle ='$cycleid'";
		return SQLUpdate($SQL);
	} else {
		$SQL = "UPDATE innercycles SET order_ex = order_ex+1 
					WHERE order_ex >= '$new_order' AND order_ex < '$old_order' AND id_cycle ='$cycleid'";
		SQLUpdate($SQL);
		$SQL = "UPDATE innercycles SET order_ex = $new_order
					WHERE order_ex = '$old_order' AND id_exercice ='$exerciceid' AND id_cycle ='$cycleid'";
		return SQLUpdate($SQL);
	}
}

function rmCycle($cycleid) {
	$SQL = "DELETE FROM innercycles WHERE id_cycle ='$cycleid'"; 
	SQLDelete($SQL);
	$SQL = "DELETE FROM cycles WHERE id ='$cycleid'"; 
	return SQLDelete($SQL);
}

function getCycles($iduser = false) {
	$SQL = "SELECT * FROM cycles";
	if ($iduser) {
		$SQL .= " WHERE creator = '$iduser'";
	}
	return(parcoursRs(SQLSelect($SQL)));
}

function getCycle($cycleid) {
	$SQL = "SELECT * FROM cycles WHERE id='$cycleid'";
	return(parcoursRs(SQLSelect($SQL)));
}

function getExCycle($cycleid) {
	$SQL = "SELECT e.title,e.image,i.id_exercice,i.order_ex,i.duration FROM innercycles i INNER JOIN exercices e ON i.id_exercice = e.id WHERE id_cycle='$cycleid' ORDER BY order_ex ASC";
	return parcoursRs(SQLSelect($SQL));
}

// NEW CRUD GROUP ////////////////////////////////////////////////////////////////////////////////

function getGroup($id) {
	$SQL = "SELECT u.pseudo, u.id FROM users u INNER JOIN members m ON m.id_user = u.id WHERE m.id_group = '$id' 
	ORDER BY u.id ASC";
	return parcoursRs(SQLSelect($SQL));
}

function getGroups($id = false) {
	$SQL = "SELECT * FROM community";
	if ($id){
		$SQL .= " WHERE creator = '$id'";
	}  

	return parcoursRs(SQLSelect($SQL));
}

function getUserGroups($idUser) {
	$SQL = "SELECT c.* FROM community c INNER JOIN members m ON c.id = m.id_group WHERE m.id_user = '$idUser'";
	return parcoursRs(SQLSelect($SQL));
}


function mkGroup($title, $theme, $creatorid, $members) {
	$SQL = "INSERT INTO community(title, theme, creator) VALUES('$title', '$theme', '$creatorid')";
	$created_groupid = SQLInsert($SQL);
	for ($i=0; $i< count($members); $i++) {
		$new_member = $members[$i];
		$SQL = "INSERT INTO members(id_user, id_group) VALUES('$new_member', '$created_groupid')";
		SQLInsert($SQL);
	}
	return $created_groupid;
}

function rmGroup($groupid, $userId){
	$SQL = "SELECT creator FROM community WHERE creator ='$userId'";
	$autorise = SQLGetChamp($SQL);
	if ($autorise) {
		$SQL = "DELETE FROM members WHERE id_group ='$groupid'";
		SQLDelete($SQL);
		$SQL = "DELETE FROM community WHERE id ='$groupid'";
		return SQLDelete($SQL);
	}
	
}

// NEW CRUD ASSIGNMENTS ////////////////////////////////////////////////////////////////////////////////

function getAssignments($idgroup = false) {
	$SQL = "SELECT * FROM assignments";
	if ($idgroup) {
		$SQL .= " WHERE group_id = '$idgroup'";
	}
	return parcoursRs(SQLSelect($SQL));
}

function getAssignment($id) {
	$SQL = "SELECT * FROM assignments WHERE id = '$id'";
	return parcoursRs(SQLSelect($SQL));
}

function mkAssignment($title, $message, $cycle_id, $due_date, $group_id) {
	$SQL = "INSERT INTO assignments(title, message, cycle_id, due_date, group_id, done) VALUES('$title', '$message', '$cycle_id', '$due_date', '$group_id', 0)";
	return SQLInsert($SQL);
}

function getUserAssignments($idUser) {
	$SQL = "SELECT a.* FROM assignments a INNER JOIN community c ON c.id = a.group_id INNER JOIN members m ON m.id_group = a.group_id WHERE m.id_user = '$idUser'";
	return parcoursRs(SQLSelect($SQL));
}

function upAssignments($title, $message, $cycle_id, $due_date, $group_id, $done, $idAssi) {
	$SQL = "UPDATE assignments SET title='$title', message='$message', cycle_id='$cycle_id', due_date='$due_date', group_id = $group_id, done='$done' WHERE id='$idAssi'";
	return SQLUpdate($SQL);
}

function rmAssignment($assignid) {
	$SQL = "DELETE s FROM scores s INNER JOIN assignments a ON a.id = s.id_assignment WHERE a.id ='$assignid'";
	SQLDelete($SQL);
	$SQL = "DELETE FROM assignments WHERE id ='$assignid'";
	return SQLDelete($SQL);
}

// NEW CRUD SCORES

function getScores() {
	$SQL = "SELECT * FROM scores";
	return parcoursRs(SQLSelect($SQL));
}

function getUserScores($id_user, $id_group = false) {
	if ($id_group) {
		$SQL = "SELECT * FROM scores s INNER JOIN assignments a ON s.id_assignment = a.id WHERE s.id_user = '$id_user' AND a.group_id = '$id_group' ORDER BY a.due_date ASC";
		return parcoursRs(SQLSelect($SQL));
	} else {
		$SQL = "SELECT * FROM scores s INNER JOIN assignments a ON s.id_assignment = a.id WHERE id_user = '$id_user' ORDER BY a.due_date ASC";
		return parcoursRs(SQLSelect($SQL));
	}
	
}

function getAssiScores($id_assignment) {
	$SQL = "SELECT * FROM scores WHERE id_assignment = '$id_assignment'";
	return parcoursRs(SQLSelect($SQL));
}

function isAllowedScore($id_user, $id_assignment) {
	$SQL = "SELECT m.id_user FROM members m INNER JOIN assignments a ON a.group_id = m.id_group WHERE a.id = '$id_assignment' AND m.id_user = '$id_user'";
	return SQLGetChamp($SQL);
}

function mkScores($id_user, $id_assignment, $score, $feedback) {
	$SQL = "INSERT INTO scores(id_user, id_assignment, score, feedback) VALUES('$id_user', '$id_assignment', '$score', '$feedback')";
	return SQLInsert($SQL);
}

function upScores($id_user, $id_assignment, $score, $feedback) {
	$SQL = "UPDATE scores SET score='$score', feedback='$feedback' WHERE id_user='$id_user' AND id_assignment='$id_assignment'";
	return SQLUpdate($SQL);
}
?>
