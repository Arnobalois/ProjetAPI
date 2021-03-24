<?php

// connexion à la base de données
$dbh = new PDO('mysql:host=lakartxela.iutbayonne.univ-pau.fr;dbname=abalois_bd', 'abalois_bd','abalois_bd');


if( !isset($_REQUEST["nom"]) && !isset($_REQUEST["code"]))
{

echo ("Erreur pas de Pays choisi !!! ");

}
else{
	if(!isset($_REQUEST["nom"])) {
		$codePays=$_REQUEST["code"];
	}else if(!isset($_REQUEST["code"])) {
		$Nom_Pays=$_REQUEST["nom"];
	}else {
		$Nom_Pays=$_REQUEST["nom"];
		$codePays=$_REQUEST["code"];
	}

	//verification que le pays est dans la base de donnés
	$resultat = $dbh->prepare('SELECT Nom from Pays where Nom=:nom or code=:codePays');
	$resultat->bindParam(':nom',$Nom_Pays);
	$resultat->bindParam(':codePays', $codePays);
	$resultat->execute();
	$result=$resultat->fetchAll();
	if(sizeof($result) == 0){
	echo(" pas de resultat ou le pays n'existe pas dans la base de donnée");


	}else {
	//requête dans la base pour recup les films
	$resultat = $dbh->prepare('SELECT * from Vue where Nom_Pays=:nom or Nom_Pays = (SELECT Nom from Pays where code =:codePays)');
	$resultat->bindParam(':nom',$Nom_Pays);
	$resultat->bindParam(':codePays', $codePays);
	$resultat->execute();
	$result=$resultat->fetchAll();

	foreach ($result as $Film)
	{
	$ListeFilm[]=$TabFilm=[
		"Titre_Films"=>$Film['Titre_Films'],
		"Annee_Films"=>$Film['Annee_Films'],
		"NombreVue"=>$Film['NombreVue'],
	];
	}
	$Films=[
		"Nom_Pays"=>$Film['Nom_Pays'],
		"Films"=>$ListeFilm,
	];

	echo (json_encode($Films,JSON_PRETTY_PRINT));

}

}








?>
