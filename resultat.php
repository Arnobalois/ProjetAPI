<?php
if (!isset($_POST['champ-texte'])) {
  echo "Aucun formulaire n'a été soumis :(";
}
else {
  //ajout de la requête en base de donnée pour notre webservice.

  //connexion
  $host = "mysql:host=lakartxela.iutbayonne.univ-pau.fr;dbname=abalois_bd";
  $user = "abalois_bd";
  $pass = "abalois_bd";
  $dbh = new PDO($host, $user, $pass);

  //vérifier existence du pays dans la BD
  $paysFormulaire = $_POST['pays'];
  $resultat = $dbh->prepare('SELECT Nom FROM Pays WHERE Nom LIKE :nomPays');
  $resultat->bindParam(':nomPays', $paysFormulaire);
  $resultat->execute();

  //ajout du pays en BD s'in n'existe pas.
  if ($resultat->rowCount() == 0) {
    $codePays = $_POST['codePays'];
    $insert = $dbh->prepare('INSERT INTO Pays VALUES (?, ?)');
    $insert->execute(array($paysFormulaire, $codePays));
  }

  //vérifier l'existence du film dans la BD
  $titreFilm = $_POST['champ-texte'];
  $anneeFilm = $_POST['anneeFilm'];
  $resultat = $dbh->prepare('SELECT Titre, Annee FROM Films WHERE Titre LIKE :titreFilm AND Annee LIKE :anneeFilm');
  $resultat->bindParam(':titreFilm', $titreFilm);
  $resultat->bindParam(':anneeFilm', $anneeFilm);
  $resultat->execute();

  //ajout du film en BD s'in n'existe pas.
  if ($resultat->rowCount() == 0) {
    $insert = $dbh->prepare('INSERT INTO Films VALUES (?, ?)');
    $insert->execute(array($titreFilm, $anneeFilm));
  }

  //vérification de si le film a déjà été vu
  $resultat = $dbh->prepare('SELECT * FROM Vue WHERE Nom_Pays LIKE :nomPays AND Titre_Films LIKE :titreFilm AND Annee_Films LIKE :anneeFilm');
  $resultat->bindParam(':nomPays', $paysFormulaire);
  $resultat->bindParam(':titreFilm', $titreFilm);
  $resultat->bindParam(':anneeFilm', $anneeFilm);
  $resultat->execute();

  //ajout si jamais vu
  if ($resultat->rowCount() == 0) {
    $insert = $dbh->prepare('INSERT INTO Vue VALUES (?, ?, ?, ?)');
    $insert->execute(array($paysFormulaire, $titreFilm, $anneeFilm, 1));
  }
  else {
    //récupération du nombre de vues
    $nbVues = $resultat->fetch()['NombreVue'];

    //incrémentation du nombre de vues
    $nbVues++;

    //mise à jour en BD
    $update = $dbh->prepare('UPDATE Vue SET NombreVue=? WHERE Nom_Pays LIKE ? AND Titre_Films LIKE ? AND Annee_Films LIKE ?');
    $update->execute(array($nbVues, $paysFormulaire, $titreFilm, $anneeFilm));
  }



  //récupération du json de la première api
  $json = json_decode(file_get_contents("http://www.omdbapi.com/?apikey=896b8c10&t=".str_replace(' ', '%20', $_POST['champ-texte'])));

  //requête sur la deuxième api
  $apiKey = "64e0bff4d5msh1f396094c89ccfap11c036jsnb215af1b1c6f";
  $json2 = json_decode(file_get_contents("https://imdb8.p.rapidapi.com/title/auto-complete?q=".str_replace(' ', '%20', $_POST['champ-texte'])."&rapidapi-key=".$apiKey));
  $idFilm = $json2->d[0]->id;
  $jsonActeurs = json_decode(file_get_contents("https://imdb8.p.rapidapi.com/title/get-top-cast?tconst=".$idFilm."&rapidapi-key=".$apiKey));
  $idActeur1 = ($jsonActeurs[0] != null) ? $jsonActeurs[0] : null;
  $idActeur2 = ($jsonActeurs[1] != null) ? $jsonActeurs[1] : null;
  $idActeur3 = ($jsonActeurs[2] != null) ? $jsonActeurs[2] : null;
  $jsonActeur1 = ($idActeur1 != null) ? json_decode(file_get_contents("https://imdb8.p.rapidapi.com/actors/get-bio?nconst=".substr($idActeur1, 6, -1)."&rapidapi-key=".$apiKey)) : null;
  $jsonActeur2 = ($idActeur2 != null) ? json_decode(file_get_contents("https://imdb8.p.rapidapi.com/actors/get-bio?nconst=".substr($idActeur2, 6, -1)."&rapidapi-key=".$apiKey)) : null;
  $jsonActeur3 = ($idActeur3 != null) ? json_decode(file_get_contents("https://imdb8.p.rapidapi.com/actors/get-bio?nconst=".substr($idActeur3, 6, -1)."&rapidapi-key=".$apiKey)) : null;
  ?>
  <html>
  <head>
    <meta charset="utf-8">
    <title>Projet webservice - resultat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
  </head>
  <body style="background: rgb(150,180,255)">
    <div class="container-xxl">
      <div class="row">
        <div class="col-md-12">
          <h3 class="text-center">
            <?php
            echo $json->Title;
            ?>
          </h3>
        </div>
      </div>
        <div class="mt-5">
      <div class="row">

            <div class="col-md-5" >

              <ul class="list-group list-group-flush " style="background: rgb(150,180,255)">
                <li class="list-group-item">
                <h3><center>Notes</center> </h3>
                </li>
                <?php
                foreach ($json->Ratings as $note) {
                  echo "<li class=\"list-group-item\">".$note->Source." : ".$note->Value."</li>";
                }
                ?>
              </ul>
            </div>
            <div class="col-md-5"  >
              <ul class="list-group list-group-flush " style="background: rgb(150,180,255)">
                <li class="list-group-item">
                <h3><center>Acteurs principaux</center> </h3>
                </li>
            </ul>
            <center>
              <ul>
                <?php
                  echo "<li class=\"list-item\" style=\"float: left; font-size: 12px\">".
                  (($jsonActeur1 != null) ? $jsonActeur1->name : '').
                  "<br/><img src=\"".(($jsonActeur1 != null && isset($jsonActeur1->image)) ? $jsonActeur1->image->url : "https://image.flaticon.com/icons/png/512/129/129840.png").
                  "\" width=\"100\"></li>";

                  echo "<li class=\"list-item\" style=\"float: left; margin-left: 30px; font-size: 12px\">".
                  (($jsonActeur2 != null) ? $jsonActeur2->name : '').
                  "<br/><img src=\"".(($jsonActeur2 != null && isset($jsonActeur2->image)) ? $jsonActeur2->image->url : "https://image.flaticon.com/icons/png/512/129/129840.png").
                  "\" width=\"100\"></li>";

                  echo "<li class=\"list-item\" style=\"float: left; margin-left: 30px; font-size: 12px\">".
                  (($jsonActeur3 != null) ? $jsonActeur3->name : '').
                  "<br/><img src=\"".(($jsonActeur3 != null && isset($jsonActeur3->image)) ? $jsonActeur3->image->url : "https://image.flaticon.com/icons/png/512/129/129840.png").
                  "\" width=\"100\"></li>";
                ?>
              </ul>
            </center>
            </div>
          </div>
          </div>
      </div>
<center><a class="retour" href="accueil.html">Retour</a></center>









  </body>
  </html>
  <?php
}
?>
