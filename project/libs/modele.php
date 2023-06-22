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

function getUsers(){
	$SQL = "SELECT id,pseudo FROM users";
	return parcoursRs(SQLSelect($SQL));
}

function getUser($idUser){
	$SQL = "SELECT id,pseudo FROM users WHERE id='$idUser'";
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

// Articles ///////////////////////////////////////////////////

function getArticles(){
	$SQL = "SELECT a.id, a.titre, u.pseudo FROM articles a INNER JOIN users u ON a.idUser = a.id"; 
	return parcoursRs(SQLSelect($SQL));
}

function getArticle($id,$idUser=false){
	$SQL = "SELECT id,titre FROM articles WHERE id='$id'"; 
	if ($idUser)
		$SQL .= " AND idUser='$idUser'"; 
	$rs = parcoursRs(SQLSelect($SQL));
	if (count($rs)) return $rs[0]; 
	else return array();
}

function isUserOwnerOfArticle($idUser,$idArticle) {
	$SQL = "SELECT id FROM articles WHERE id='$idArticle'"; 
	$SQL .= " AND idUser='$idUser'"; 
	return SQLGetChamp($SQL); 
}

function getArticlesUser($idUser){
	$SQL = "SELECT id,titre FROM articles WHERE idUser='$idUser'"; 
	return parcoursRs(SQLSelect($SQL));
}

function mkArticle($idUser, $titre){
	$SQL = "INSERT INTO articles(idUser,titre) VALUES('$idUser', '$titre')";
	return SQLInsert($SQL);
}

function rmArticle($id, $idUser=false) {
	$SQL = "DELETE FROM articles WHERE id='$id'";
	if ($idUser) $SQL .= " AND idUser='$idUser'"; 
	return SQLDelete($SQL);
}

function chgTitreArticle($id,$titre, $idUser=false) {
	$SQL = "UPDATE articles SET titre='$titre' WHERE id='$id'";
	if ($idUser) $SQL .= " AND idUser='$idUser'";
	SQLUpdate($SQL);
	return 1; 
	// return SQLUpdate() pose souci si il n'y a pas modif de titre
	// SQLUpdate renvoie alors 0 ! 
}

// Paragraphes ///////////////////////////////////////////////////

function getParagraphes($id) {
	$SQL = "SELECT id,contenu,ordre FROM paragraphes WHERE idArticle='$id'"; 
	return parcoursRs(SQLSelect($SQL));
}

function getParagraphe($id, $idArticle=false) {
	$SQL = "SELECT id,contenu,ordre FROM paragraphes WHERE id='$id'"; 
	if ($idArticle) $SQL .= " AND idArticle='$idArticle'";

	$rs = parcoursRs(SQLSelect($SQL));
	if (count($rs)) return $rs[0]; 
	else return array();
}


function rmParagraphe($id,$idArticle) {
	$SQL = "DELETE FROM paragraphes WHERE id='$id' AND idArticle='$idArticle'";
	return SQLDelete($SQL);
}

function mkParagraphe($idArticle, $contenu,$ordre=false){
	if (! $ordre)
		$SQL = "INSERT INTO paragraphes(idArticle,contenu) VALUES('$idArticle', '$contenu')";
	else 
		$SQL = "INSERT INTO paragraphes(idArticle,contenu,ordre) VALUES('$idArticle', '$contenu','$ordre')";
	return SQLInsert($SQL);
}

function chgParagrapheContenu($id,$contenu,$idArticle=false) {
	$SQL = "UPDATE paragraphes SET contenu='$contenu' WHERE id='$id'";
	if ($idArticle) $SQL .=  " AND idArticle='$idArticle'"; 
	SQLUpdate($SQL);
	return 1; 
}


function updateOrdreParagraphe($ordre,$id,$idArticle){
	$SQL = "SELECT id FROM paragraphes WHERE ordre = '$ordre' AND idArticle='$idArticle'"; 
	if (SQLGetChamp($SQL)) {
		// Il peut s'agir d'un numéro d'ordre qui est déjà utilisé
		// On va décaler les ordres des paragraphes existants après
		// TODO: SEULEMENT si c'est le CAS (doit être inutile ?)
		$SQL = "UPDATE paragraphes SET ordre = ordre+1 
					WHERE ordre >= '$ordre' AND idArticle='$idArticle'"; 
		SQLUpdate($SQL);
	}

	// avant de changer 
	// l'ordre du paragraphe concerné 
	$SQL = "UPDATE paragraphes SET ordre = '$ordre' WHERE id='$id' AND idArticle='$idArticle'";
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
	$SQL = "SELECT e.id,e.title,u.pseudo FROM exercices e INNER JOIN users u ON u.id = e.creator";
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

function mkExercice($title, $descr, $duration, $theme, $creatorid){
	$SQL = "INSERT INTO exercices(title,descr,duration,theme,creator) VALUES('$title','$descr', '$duration', '$theme', '$creatorid')";
	return SQLInsert($SQL);
}

function upExercice($id, $title, $descr, $duration, $theme){
	$SQL = "UPDATE exercices SET title='$title', descr='$descr', duration='$duration', theme='$theme' WHERE id='$id'";;
	return SQLUpdate($SQL);
}

function rmExercice($id, $idUser=false) {
	$SQL = "DELETE FROM exercices WHERE id='$id'";
	if ($idUser) $SQL .= " AND creator='$idUser'"; 
	return SQLDelete($SQL);
}

?>
