<?php

// connexion à la base de données
$dbh = new PDO('mysql:host=lakartxela.iutbayonne.univ-pau.fr;dbname=abalois_bd', 'abalois_bd','abalois_bd');


if( !isset($_REQUEST["t"] ))
{

$Films=["Erreur" => "Erreur aucun parametre ! ",
"code" => "1"];

}
else{

	$titre_Film=$_REQUEST["t"];



	//verification que le pays est dans la base de donnés
	$resultat = $dbh->prepare('SELECT Titre from Films where Titre=:titre ');
	$resultat->bindParam(':titre',$titre_Film);
	$resultat->execute();
	$result=$resultat->fetchAll();

	if(sizeof($result) == 0 || is_null($result[0])){

	$Films=["Erreur" => "le film n'a aucune vue ou n'est pas dans la base de donnee",
	"code" => "2"];


	}else {
	//requête dans la base pour recup les films
	$resultat = $dbh->prepare('SELECT * from Vue where Titre_Films=:titre');
	$resultat->bindParam(':titre',$titre_Film);
	$resultat->execute();
	$result=$resultat->fetchAll();


	foreach ($result as $Film)
	{
	$ListeFilm[]=$TabFilm=[
		"Nom_Pays"=>$Film['Nom_Pays'],
		"NombreVue"=>$Film['NombreVue']
	];
	}

	$Films=[
		"Titre_Film"=>$titre_Film,
		"Pays"=>$ListeFilm
	];
}
}
echo (json_encode($Films));

?>
