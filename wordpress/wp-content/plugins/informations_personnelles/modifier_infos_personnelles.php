<?php
/**
 * @package informations_personnelles
 * @version 1.0.0
 */
/*
Plugin Name: Informations Personelles
Description: Modifier ses informations personnelles sans utiliser le panel wordpress
Author: IUT Rodez
Version: 1.0.0
*/
$host = 'localhost';
$db = 'wordpress';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo $e->getMessage() ;
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

/**
 * Création de la page du plugin
 * Cette page permettra de modifier ses informations personelles
 * L'utilisateur devra modifier toutes les informations suivantes :
 *      - NOM (EN MAJUSCULES)
 *      - PRÉNOM (EN MAJUSCULES)	
 *      - COURRIEL (professionnel si possible)	
 *      - TÉLÉPHONE	(J'autorise l'affichage de mon numéro de téléphone sur le site de l'IRES)
 *      - CAPES	
 *      - CAPET	
 *      - AGREGATION
 *      - CRPE
 *      - CAFFA
 *      - CAPLP
 *      - CAPPEI
 *      - THÈSE	
 *      - SITUATION PROFESSIONNELLE	
 *      - DISCIPLINE ENSEIGNÉE	
 *      - TEMPS DE TRAVAIL	
 *      - NOM ÉTABLISSEMENT	
 *      - VILLE ÉTABLISSEMENT
 *      - CODE UAI/RNE ÉTABLISSEMENT SCOLAIRE
 *      - NOM CHEF D'ÉTABLISSEMENT
 *      - ANIMATEUR FORMATION PAF 2018/2019	
 *      - SI OUI TITRE DE LA FORMATION	
 *      - PARTICIPATION A UN LABO DE MATHS	
 *      - Êtes vous membre de l'INSPE ?	
 *      - FAITES-VOUS DE INTERVENTIONS À L'INSPE ?	
 *      - MEMBRE CII
 *      - MEMBRE ASSOCIATION PROFESSEURS (APMEP, ...)
 *      - MEMBRE SOCIÉTÉ SAVANTE (Société Mathématique de France, Société Française de Physique, ...)	
 *      - MEMBRE ASSOCIATION (AUTRE)
 */
function my_page() {
	add_menu_page(
		'Renseigner ses informations',	                // Titre de la page quand le menu est selectionné
        'Modifier informations personnelles',			// Nom du menu
        0,							                    // Niveau de sécurité d'accès au menu
        'modifier_infos_personnelles',				    // Nom de référence du menu
        'page_content', 			                    // Appel de la fonction du contenu de la page
        'dashicons-admin-users', 			            // Icone du menu
        3 							                    // Position de la page dans la liste
    );
}

/** Ajout du menu dans le tableau de bord de l'administration WordPress */
add_action( 'admin_menu', 'my_page' );

/**
 * Contenu du menu "Ajouter un utilisateur"
 * Permet de :
 * 		- Afficher un formulaire pour ajouter un utilisateur (Mail, Prénom, Nom)
 * 		- Créer le login de l'utilisateur sous le format :
 * 				première lettre du prénom concaténé avec le nom le tout en minuscule
 * 		- Ajouter l'utilisateur à la base de données
 */
function page_content() {

    $user = wp_get_current_user();
    $user_id = get_current_user_id();

    if (isset($_POST["nom"])) {
        $nom=$_POST["nom"];
        update_user_meta( $user_id, "last_name", $nom);
    }
    if (isset($_POST["prenom"])) {
        $prenom=$_POST["prenom"];
        update_user_meta( $user_id, "first_name", $prenom);
    }
    if (isset($_POST["email"])) {
        $email=$_POST["email"];
        // update_user( $user_id, "user_email", $email);
    }
    if (isset($_POST["tel"])) {
        $tel=$_POST["tel"];
        update_user_meta( $user_id, "tel", $tel);
    }
    if (isset($_POST["CAPES"])) {
        $capes=$_POST["CAPES"];
        update_user_meta( $user_id, "capes", $capes);
    }
    if (isset($_POST["CAPET"])) {
        $capet=$_POST["CAPET"];
        update_user_meta( $user_id, "capet", $capet);
    }
    if (isset($_POST["agregation"])) {
        $agregation=$_POST["agregation"];
        update_user_meta( $user_id, "agregation", $agregation);
    }
    if (isset($_POST["CRPE"])) {
        $crpe=$_POST["CRPE"];
        update_user_meta( $user_id, "crpe", $crpe);
    }
    if (isset($_POST["CAFFA"])) {
        $caffa=$_POST["CAFFA"];
        update_user_meta( $user_id, "caffa", $caffa);
    }
    if (isset($_POST["CAPLP"])) {
        $caplp=$_POST["CAPLP"];
        update_user_meta( $user_id, "caplp", $caplp);
    }
    if (isset($_POST["CAPPEI"])) {
        $cappei=$_POST["CAPPEI"];
        update_user_meta( $user_id, "cappei", $cappei);
    }
    if (isset($_POST["these"])) {
        $these=$_POST["these"];
        update_user_meta( $user_id, "these", $these);
    }
    if (isset($_POST["situation_professionnel"])) {
        $situation_professionnel=$_POST["situation_professionnel"];
        update_user_meta( $user_id, "situation_professionnel", $situation_professionnel);
    }
    if (isset($_POST["discipline_enseignee"])) {
        $discipline_enseignee=$_POST["discipline_enseignee"];
        update_user_meta( $user_id, "discipline_enseignee", $discipline_enseignee);
    }
    if (isset($_POST["tps_travail"])) {
        $tps_travail=$_POST["tps_travail"];
        update_user_meta( $user_id, "tps_travail", $tps_travail);
    }
    if (isset($_POST["nom_etablissemnt"])) {
        $nom_etablissemnt=$_POST["nom_etablissemnt"];
        update_user_meta( $user_id, "nom_etablissemnt", $nom_etablissemnt);
    }
    if (isset($_POST["ville_etablissemnt"])) {
        $ville_etablissemnt=$_POST["ville_etablissemnt"];
        update_user_meta( $user_id, "ville_etablissemnt", $ville_etablissemnt);
    }
    if (isset($_POST["code_UAI_RNE"])) {
        $code_uai_rne=$_POST["code_UAI_RNE"];
        update_user_meta( $user_id, "code_uai_rne", $code_uai_rne);
    }
    if (isset($_POST["nom_chef_etablissemnt"])) {
        $nom_chef_etablissemnt=$_POST["nom_chef_etablissemnt"];
        update_user_meta( $user_id, "nom_chef_etablissemnt", $nom_chef_etablissemnt);
    }
    if (isset($_POST["animateur_formation"])) {
        $animateur_formation=$_POST["animateur_formation"];
        update_user_meta( $user_id, "animateur_formation", $animateur_formation);
    }
    if (isset($_POST["titre_formation"])) {
        $titre_formation=$_POST["titre_formation"];
        update_user_meta( $user_id, "titre_formation", $titre_formation);
    }
    if (isset($_POST["participation_labo_math"])) {
        $participation_labo_math=$_POST["participation_labo_math"];
        update_user_meta( $user_id, "participation_labo_math", $participation_labo_math);
    }
    if (isset($_POST["membre_INSPE"])) {
        $membre_inspe=$_POST["membre_INSPE"];
        update_user_meta( $user_id, "membre_inspe", $membre_inspe);
    }
    if (isset($_POST["intervention_INSPE"])) {
        $intervention_inspe=$_POST["intervention_INSPE"];
        update_user_meta( $user_id, "intervention_inspe", $intervention_inspe);
    }
    if (isset($_POST["membre_CII"])) {
        $membre_cii=$_POST["membre_CII"];
        update_user_meta( $user_id, "membre_cii", $membre_cii);
    }
    if (isset($_POST["membre_association_prof"])) {
        $membre_association_prof=$_POST["membre_association_prof"];
        update_user_meta( $user_id, "membre_association_prof", $membre_association_prof);
    }
    if (isset($_POST["membre_societe_savante"])) {
        $membre_societe_savante=$_POST["membre_societe_savante"];
        update_user_meta( $user_id, "membre_societe_savante", $membre_societe_savante);
    }
    if (isset($_POST["membre_association"])) {
        $membre_association=$_POST["membre_association"];
        update_user_meta( $user_id, "membre_association", $membre_association);
    }

    ?>
    <h1>
		Modifier vos informations personnelles
    </h1>

    <form method="post" action="">
        Identifiant : <input name="identifiant" type="text" value="<?php echo $user->user_login ; ?>" READONLY> <br/> <br/>
        Nom : <input name="nom" type="text" value="<?php echo $user->last_name ; ?>"> <br/> <br/>
        Prénom : <input name="prenom" type="text" value="<?php echo $user->first_name ; ?>"> <br/> <br/>
        Courriel : <input name="email" type="text" value="<?php echo $user->user_email ; ?>"> <br/> <br/>
        Téléphone : <input name="tel" type="text" value="<?php echo $user->tel ; ?>"> <br/> <br/>
        CAPES : <input name="CAPES" type="text" value="<?php echo $user->capes ; ?>"> <br/> <br/>
        CAPET : <input name="CAPET" type="text" value="<?php echo $user->capet ; ?>"> <br/> <br/>
        Agregation : <input name="agregation" type="text" value="<?php echo $user->agregation ; ?>"> <br/> <br/>
        CRPE : <input name="CRPE" type="text" value="<?php echo $user->crpe ; ?>"> <br/> <br/>
        CAFFA : <input name="CAFFA" type="text" value="<?php echo $user->caffa ; ?>"> <br/> <br/>
        CAPLP : <input name="CAPLP" type="text" value="<?php echo $user->caplp ; ?>"> <br/> <br/>
        CAPPEI : <input name="CAPPEI" type="text" value="<?php echo $user->cappei ; ?>"> <br/> <br/>
        Thèse : <input name="these" type="text" value="<?php echo $user->these ; ?>"> <br/> <br/>
        Situation professionnelle : <input name="situation_professionnel" type="text" value="<?php echo $user->situation_professionnel ; ?>"> <br/> <br/>
        Discipline enseignée : <input name="discipline_enseignee" type="text" value="<?php echo $user->discipline_enseignee ; ?>"> <br/> <br/>
        Temps de travail : <input name="tps_travail" type="text" value="<?php echo $user->tps_travail ; ?>"> <br/> <br/>
        Nom de l'établissement : <input name="nom_etablissemnt" type="text" value="<?php echo $user->nom_etablissemnt ; ?>"> <br/> <br/>
        Ville de l'établissement : <input name="ville_etablissemnt" type="text" value="<?php echo $user->ville_etablissemnt ; ?>"> <br/> <br/>
        code UAI/RNE de l'établissement scolaire : <input name="code_UAI_RNE" type="text" value="<?php echo $user->code_uai_rne ; ?>"> <br/> <br/>
        Nom du chef de l'établissement : <input name="nom_chef_etablissemnt" type="text" value="<?php echo $user->nom_chef_etablissemnt ; ?>"> <br/> <br/>
        Animateur formation PAF 2018/2019 : <input name="animateur_formation" type="text" value="<?php echo $user->animateur_formation ; ?>"> <br/> <br/>
        Si oui, titre de la formation : <input name="titre_formation" type="text" value="<?php echo $user->titre_formation ; ?>"> <br/> <br/>
        Participation à un labo de math : <input name="participation_labo_math" type="text" value="<?php echo $user->participation_labo_math ; ?>"> <br/> <br/>
        Êtes vous membre de l'INSPE ? : <input name="membre_INSPE" type="text" value="<?php echo $user->membre_inspe ; ?>"> <br/> <br/>
        Faites-vous des interventions à l'INSPE ? : <input name="intervention_INSPE" type="text" value="<?php echo $user->intervention_inspe ; ?>"> <br/> <br/>
        Membre CII : <input name="membre_CII" type="text" value="<?php echo $user->membre_cii ; ?>"> <br/> <br/>
        Membre d'association professeurs (APMEP, ...) : <input name="membre_association_prof" type="text" value="<?php echo $user->membre_association_prof ; ?>"> <br/> <br/>
        Membre de société savante (Société Mathématique de France, Société Française de Physique, ...) : <input name="membre_societe_savante" type="text" value="<?php echo $user->membre_societe_savante ; ?>"> <br/> <br/>
        Membre association (autre) : <input name="membre_association" type="text" value="<?php echo $user->membre_association ; ?>"> <br/> <br/>
        <input type="submit" value="Enregistrer les informations">
    </form>

<?php
} ?>