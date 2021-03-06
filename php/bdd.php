<?php


    // cette fonction vous connecte à la base de données et retourne
    // un objet grâce auquel vous allez effectuer des requêtes SQL
    function connexionbd() {

        // A MODIFIER : spécifiez votre login et votre mot de passe ici
        $host = "localhost";
        $username = "gonche";  
        $password = "Ghormeh_Sabzi14"; 
        $dbname = "Annonces";

        // chaîne de connexion pour PDO (ne pas modifier)
//        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8;unix_socket=/tmp/mysql.sock"; // POUR MAC 
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";


        // connexion au serveur de bases de données
        $bd = new PDO($dsn, $username, $password);

        return $bd;
    }

    // cette fonction effectue une requête SQL. On doit lui fournir
    // l'objet base de données et la requête
    function requete($bd, $req) {

        // appel de la méthode query() sur l'objet base de données :
        // la requête est traitée par le serveur et retourne un pointeur de resultat
        $resultat = $bd->query($req);

        // on demande à ce pointeur d'aller chercher toutes les données de résultat
        // d'un coup - on obtient un tableau de tableaux associatifs (un par ligne de la table)
        // Note : dans le cas d'une insertion, on ne récupère pas le resultat
        if ($resultat) {
            $tableau = $resultat->fetchAll(PDO::FETCH_ASSOC);	
            // on retourne ce tableau
            return $tableau;
        }
    }

    // cree la table qui stockera les petites annonces
    // - appeler cette fonction une seule fois au début de votre projet
    function creation_table() {
        $maBd = connexionbd();
        $maRequeteCreation = "CREATE TABLE annonces (id int AUTO_INCREMENT PRIMARY KEY, titre varchar(100), description text, categorie varchar(40), nom_vendeur varchar(60), prix int, photo varchar(255), rdv_lat float, rdv_lon float, date_ajout timestamp DEFAULT CURRENT_TIMESTAMP) CHARACTER SET UTF8";
        requete($maBd, $maRequeteCreation);
    }

    function creation_table_membre(){
        $maBd = connexionbd();
        $maRequeteCreation = "CREATE TABLE Membre (email varchar(50) PRIMARY KEY, nom varchar(50), password varchar(50))CHARACTER SET UTF8";
        requete($maBd, $maRequeteCreation);
    }

    // insère des données d'exemple dans la base
    // - appeler cette fonction une seule fois au début de votre projet
    function insertion_exemples() {
        $maBd = connexionbd();
        $maRequeteInsertion = "INSERT INTO annonces VALUES "
            . "(DEFAULT, 'Cafetière en porcelaine, bon état général', 'Vends cafetière en procelaine italienne des années 60, en bon état.<br>Peu servi, contenance 60cl.<br>15€ ferme, à venir retirer chez moi.', 'cuisine', 'bogoss_34', 15, 'http://www.villeroy-boch.com/fileadmin/picdb/produkte/tk/large/23950100-preview.jpg', 43.632841, 3.8637333, DEFAULT),"
            . "(DEFAULT, 'DVD TimeCop avec Jean-Claude Van Damme', 'Vends 1€ symbolique ce film culte en DVD, encore sous blister.', 'DVD / Films', 'Martine Dubois', 1, 'https://fanart.tv/fanart/movies/8831/moviedisc/timecop-53e23655c9ebd.png', 43.617857, 3.8573201, DEFAULT),"
            . "(DEFAULT, 'Cheap Rolex watches', 'Rolex watch, incredible prices !<br><br>SPAM SPAM SPAM SPAM SPAM SPAM SPAM, SPAM SPAM SPAM SPAM SPAM SPAM<br>SPAM SPAM<br><br>SPAM !', 'bijoux', 'spam spam', 40, 'http://www.boutique-vintage.com/1139-3740-large/petite-montre-mickey-bleue-et-grise-80-s.jpg', 43.5465071, 3.8287231, DEFAULT),"
            . "(DEFAULT, 'Villa 220m² avec piscine et jardin', 'Vends villa années 2000, de plain pied, beaux quartiers proche Montpellier.<br>Toiture rénovée en 2014, ampoules changées à la main avant-hier.<br>Petits travaux à prévoir suite à l''explosion de la citerne de gaz naturel.<br>340 000 euros à débattre. Contacter Jean au 06 66 66 66 66 entre 19h31 et 19h32.', 'immobilier', 'Jean Talus', 340000, 'http://www.photomaison.net/wp-content/uploads/2015/11/image-maison-lego-5.jpg', 43.5732676, 3.903428, DEFAULT);"
        ;
        requete($maBd, $maRequeteInsertion);
    }

    // vide la table de toutes ses donnees
    // - appeler uniquement si besoin de faire le ménage
    function vidage_table() {
        $maBd = connexionbd();
        $maRequeteVidage = "TRUNCATE TABLE annonces";
        requete($maBd, $maRequeteVidage);
    }


    /*******************************************************
    ******** Récupère soit ALL soit certains messages ******
    *******************************************************/
    function get_all_messages() {

        // Requête par défaut 
        $requete = "SELECT * FROM annonces"; 

        /*******************************************************
        ******** Partie gestion des paramètres de sélection ****
        *******************************************************/
        
        // Si il y a une chaine de caractère dans la zone de texte 
        // alors isset($_REQUEST['filter']) est définie et retourne true 
        if (isset($_REQUEST['filter']) and $_REQUEST['filter'] != "" ) {
            $ret = $_REQUEST['filter'];
            // Tableau qui va contenir un mot clé par case 
            $array_filter = Array();
            
            // AND ou OU ? Selon si utilisateur a rentré espace ou ; 
            $split_and = true; 

            // Explode permettrait de verifier les IF 

           //  3 cas : ET, OU, un seul mot 
            if (strpos($ret, ";") != false){
                $array_filter = preg_split("/;/",$ret);
            }
            else if (strpos($ret, " ") != false){
                $split_and = false; 
                $array_filter = preg_split("/\s+/",$ret);
            } 
            else {
                $array_filter[] = $ret;
            }

            
            $requete= $requete." WHERE";

            $list_param = Array(); 

            // 2 cas : 
            // 1er cas : sélection de "Tous", donc recherche dans n'importe quel champ 
            if ($_REQUEST['column'] == "all") {
                
                
                // Cette boucle refait la seconde boucle pour chaque paramètre 
                // On a donc à la fin de cette grande boucle
                // $list_param = [ (column1 like %param1% OR column2 like %param1% OR column3...etc) , (column1 like %param2% OR column2 like %param2% OR column3...etc) ]
               foreach($array_filter as $val){
                    
                    $list_column = Array();
                    
                    // Pour un paramètre donné : (column1 like %param% OR column2 like %param% OR column3...etc)
                    foreach (array("id", "titre", "description", "categorie", "nom_Vendeur", "prix", "photo", "rdv_lat", "rdv_lon", "date_ajout") as $column){
                        $list_column[] = " ".$column." like '%".$val."%' "; 
                    }
                   //$list_or = $join_and = join(" OR ", $list_column);
                   $list_or = join(" OR ", $list_column);
                   $list_param[] = " ( ".$list_or." ) ";             
                } 
                
                
            } // 2eme cas : sélection d'une column/catégorie en particulier de la BDD
            else {
                // On a à la fin de cette  boucle
                // $list_param = [ column1 like %param1% , column1 like %param2% ]
                foreach($array_filter as $val){
                    // [] pour ajouter un element a la fin
                    $list_param[] = " ".$_REQUEST['column']." like '%".$val."%' ";  
                }
            }
            
                // Rajoute condition ET entre les cases de list_param et crée une string.
                if($split_and){
                    $join_and = join(" AND ", $list_param);   
                } // Ou bien condition OU 
                else {
                     $join_and = join(" OR ", $list_param);  
                }
            
                // Requête finale en rajoutant les caractéristiques
                $requete.=$join_and;
     
        }
        
        /*******************************************************
        ******** Envoie requête et mise en forme données *******
        *******************************************************/
        
        $maBd = connexionbd();

        $donnees = requete($maBd, $requete); 
        $data = Array('annonces' => Array());

        foreach ($donnees as $val) {
            $data['annonces'][] = Array ('id' => $val['id'], 
                                        'titre' => $val['titre'], 
                                        'description' => $val['description'], 
                                        'categorie' => $val['categorie'],
                                        'nom_vendeur' => $val['nom_vendeur'],
                                        'prix' => $val['prix'],
                                        'photo' => $val['photo'],
                                        'rdv_lat' => $val['rdv_lat'],
                                        'rdv_lon' => $val['rdv_lon'],
                                        'date_ajout' => $val['date_ajout']); 
        }

        return $data;
    }


   function check_user(){
       
        $requete = 'SELECT * FROM Membre WHERE email = "'.$_REQUEST['email'].'" and password= "'.sha1($_REQUEST['password']).'"'; 
        $maBd = connexionbd();

        $donnees = requete($maBd, $requete); 
       
        if(sizeof($donnees)) {
            
            session_start();
            $_SESSION["nom"] = $donnees[0]["nom"];
        
        }
    }

    function check_session(){        
        session_start();
        if (isset($_SESSION["nom"]) and $_SESSION["nom"] != ""){
            return $_SESSION["nom"];
        }
        return false;
    }

    function ajout() {

        $bd = connexionbd();
        
        if($_REQUEST["rdv_lon"]=="") {
            $_REQUEST["rdv_lon"]=0;
        }
        if($_REQUEST["rdv_lat"]=="") {
            $_REQUEST["rdv_lat"]=0;
        }
        //requete pour ajouter une annonce dans la BDD
        $req = 	"INSERT INTO annonces VALUES "
            . "( DEFAULT, '" 
            . $_REQUEST[ "titre" ] . "', '" 
            . $_REQUEST[ "description" ] . "', '" 
            . $_REQUEST[ "categorie" ] . "', '" 
            . $_REQUEST[ "nom_vendeur" ] . "', '"  
            . $_REQUEST[ "prix" ] . "', '" 
            . $_REQUEST[ "photo" ] . "', '" 
            . $_REQUEST[ "rdv_lat" ] . "', '" 
            . $_REQUEST[ "rdv_lon" ] . "', " 
            . "DEFAULT" . " );";
        
        requete( $bd, $req );
    }

    function supprimer() {
        if (check_session()) {
            $bd = connexionbd();
            $req = 'DELETE FROM annonces WHERE id="'.$_REQUEST["id"].'";';
            requete($bd,$req);
            return "true";
        }
        return "false";
        
    }

    function deconnexion(){
        session_start();
        session_destroy();
    }

    function get_latest_message() {
        
        $requete = "SELECT * FROM annonces ORDER by date_ajout DESC LIMIT 1"; 
        
        $maBd = connexionbd();

        $donnees = requete($maBd, $requete); 
        $data = Array('annonces' => Array());

        foreach ($donnees as $val) {
            $data['annonces'][] = Array ('id' => $val['id'], 
                                        'titre' => $val['titre'], 
                                        'description' => $val['description'], 
                                        'categorie' => $val['categorie'],
                                        'nom_vendeur' => $val['nom_vendeur'],
                                        'prix' => $val['prix'],
                                        'photo' => $val['photo'],
                                        'rdv_lat' => $val['rdv_lat'],
                                        'rdv_lon' => $val['rdv_lon'],
                                        'date_ajout' => $val['date_ajout']); 
        }

        return $data;
    }

    function inscription() {
        
        $bd = connexionbd();
        $req = 	"INSERT INTO Membre VALUES ('" 
            . $_REQUEST[ "email" ] . "', '" 
            . $_REQUEST[ "nom" ] . "', '" 
            . sha1($_REQUEST[ "password" ])
            . "' );";
        
       $ret = requete( $bd, $req );
        
        if (!isset($ret)){
            return false;
        }
        return true;
    }


?>
