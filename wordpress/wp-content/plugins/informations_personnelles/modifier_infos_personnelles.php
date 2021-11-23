<?php
/**
 * @package informations_personnelles
 * @version 1.0.0
 */
/*
Plugin Name: Informations Personelles
Description: Editing personal information without using the wordpress panel
Author: IUT Rodez
Version: 1.0.0
*/
/**
 * Creation of the plugin page
 * This page will allow you to modify your personal information
 * The user will have to modify all the following information:
 *      - LAST NAME (IN UPPER CASE)
 *      - FIRST NAME (IN UPPERCASE)
 *      - EMAIL (professional if possible)
 *      - PHONE (I authorize the display of my phone number on the IRES website)
 *      - CAPES
 *      - CAPET
 *      - AGREGATION
 *      - CRPE
 *      - CAFFA
 *      - CAPLP
 *      - CAPPEI
 *      - THESIS
 *      - PROFESSIONAL SITUATION
 *      - DISCIPLINE TAUGHT
 *      - WORKING TIME
 *      - NAME OF THE INSTITUTION
 *      - SCHOOL CITY
 *      - SCHOOL"S UAI/RNE CODE
 *      NAME OF THE HEAD OF THE SCHOOL
 *      - PAF TRAINING LEADER 2018/2019
 *      - IF YES, TITLE OF THE TRAINING
 *      - PARTICIPATION IN A MATH LAB
 *      - Are you a member of INSPE?
 *      - DO YOU DO ANY INTERVENTIONS AT THE INSPE?
 *      - CII MEMBER
 *      - MEMBER OF A TEACHER ASSOCIATION (APMEP, ...)
 *      - MEMBER of a learned society (Société Mathématique de France, Société Française de Physique, ...)
 *      - ASSOCIATION MEMBER (OTHER)
 */
function create_information_page() {
    add_menu_page(
        "Renseigner ses informations", // Page title when the menu is selected
        "Modifier informations personnelles", // Name of the menu
        0, // Menu access security level
        "modifier_infos_personnelles", // Menu reference name
        "information_page_content", // Call the page content function
        "dashicons-admin-users", // Menu icon
        3 // Page position in the list
    );
}

/**
 * Adding the menu in the dashboard of the WordPress administration
 */
add_action("admin_menu", "create_information_page");

/**
 * Contents of the "Add a user" menu
 * Allows to :
 *      - Display a form to add a user (Mail, First name, Name)
 *      - Create the user's login in the format :
 *      first letter of the first name concatenated with the last name all in lower case
 *      - Add the user to the database
 */
function information_page_content() {
    $metas = [
        "last_name" => "Nom",
        "first_name" => "Prénom",
        "email" => "Courriel",
        "tel" => "Téléphone",
        "capes" => "CAPES",
        "capet" => "CAPET",
        "agregation" => "Agrégation",
        "crpe" => "CRPE",
        "caffa" => "CAFFA",
        "caplp" => "CAPLP",
        "cappei" => "CAPPEI",
        "these" => "Thèse",
        "situation_professionnelle" => "Situation professionnelle",
        "discipline_enseignee" => "Discipline enseignée",
        "tps_travail" => "Temps de travail",
        "nom_etablissement" => "Nom de l'établissement",
        "ville_etablissement" => "Ville de l'établissement",
        "code_uia_rne" => "Code UAI/RNE de l'établissement scolaire",
        "nom_chef_etablissement" => "Nom du chef de l'établissement",
        "animateur_formation" => "Animateur formation PAF 2018/2019",
        "titre_formation" => "Titre de la formation",
        "participation_labo_math" => "Participation à un labo de math",
        "membre_inspe" => "Membre de l'INSPE",
        "intervention_inspe" => "Interventions à l'INSPE",
        "membre_cii" => "Membre CII",
        "membre_association_prof" => "Membre d'association professeurs (APMEP, ...)",
        "membre_societe_savante" => "Membre de société savante (Société Mathématique de France, Société Française de Physique, ...)",
        "membre_association" => "Membre association (autre)"
    ];
    $user = wp_get_current_user();
    $user_id = get_current_user_id();
    foreach ($metas as $key => $meta) {
        if (isset($_POST[$key])) {
            update_user_meta($user_id, $key, $_POST[$key]);
        }
    }
    ?>
    <h1>Modifier vos informations personnelles</h1>

    <form method="post" action="">
        <label>
            Identifiant :
            <input class="to-fill" name="identifiant" type="text" value="<?php echo $user->user_login; ?>" readonly>
        </label><br><br>
        <?php
        foreach ($metas as $key => $meta) {
            echo "<label>$meta : ";
                echo "<input " . (in_array($key, ["last_name", "first_name", "email"]) ? "class='to-fill'" : "") . " name=$key id=$key type='text' value=" . $user->$key . ">";
            echo "</label><br><br>";
        }
        ?>

    </form>
    <?php
} ?>
