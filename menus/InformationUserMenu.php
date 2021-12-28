<?php

namespace irestoulouse\menus;

use irestoulouse\Database;
use wpdb;

/**
 * Creation of the plugin page
 * This page will allow you to see your personal information
 * The user can see all the following information:
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
class InformationUserMenu extends IresMenu {


    /**
     * Constructing the menu and link to the admin page
     */
    public function __construct() {
        parent::__construct( "Vos informations", // Page title when the menu is selected
            "Vos informations", // Name of the menu
            0, // Menu access security level
            "dashicons-admin-users", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Content of the page
     */
    public function getContent() : void {
        /*
         * SQL Request
         * TODO move to another file to respect the MVC method
         */
        global $wpdb;
        $userId = get_current_user_id();
        $userDatas = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}usermeta WHERE user_id = %d", $userId),
            ARRAY_A
        );

        // Array with the type of each line
        $type = [
            "si_animateur_titre_de_la_formation" => "text",
            "participation_labo_maths" => "boolean",
            "membre_inspe" => "boolean",
            "interventions_inspe" => "boolean",
            "manifestation" => "text",
            "responsable_groupe" => "text",
            "groupe_recherche_action" => "text",
            "membre_association_autre" => "text",
            "membre_societe_savante" => "text",
            "membre_association_prof" => "text",
            "nom_cii" => "text",
            "membre_cii" => "boolean",
            "discipline_enseignee" => "text",
            "animateur_formation" => "boolean",
            "nom_chef_etablissement" => "text",
            "code_uai_rne" => "text",
            "ville_etablissement" => "text",
            "nom_etablissement" => "text",
            "type_etablissement" => "text",
            "situation_pro" => "text",
            "email" => "text",
            "diplomes" => "text",
            "telephone" => "text",
            "first_name" => "text",
            "last_name" => "text"
        ];
        // Array with the text of each line
        $correspondance = [
            "first_name" => "Prénom",
            "last_name" => "Nom",
            "email" => "Email",
            "telephone" => "Téléphone",
            "diplomes" => "Diplomes",
            "situation_pro" => "Situation professionnelle",
            "type_etablissement" => "Type établissement",
            "nom_etablissement" => "Nom établissement",
            "ville_etablissement" => "Ville établissement",
            "code_uai_rne" => "Code UAI/RNE de l'établissement scolaire",
            "nom_chef_etablissement" => "Nom du chef de l'établissement",
            "discipline_enseignee" => "Discipline enseignée",

            "animateur_formation" => "Animateur formation PAF (année en cours)",
            "si_animateur_titre_de_la_formation" => "Si animateur, titre de la formation",
            "participation_labo_maths" => "Participation labo de mathématiques",
            "membre_inspe" => "Membre INSPE",
            "interventions_inspe" => "Interventions INSPE",

            "membre_cii" => "Membre CII",
            "nom_cii" => "Nom de la CII",

            "membre_association_prof" => "Membre association professeurs",
            "membre_societe_savante" => "Membre société savante",
            "membre_association_autre" => "Membre association (autre)",

            "groupe_recherche_action" => "Groupe de recherche action",
            "responsable_groupe" => "Responsable du groupe",
            "manifestation" => "Manifestation",
        ];
        // Array with title of each part
        $title = [
            0  => "Informations personnelles",
            12 => "Formations",
            17  => "CII IREM",
            19  => "Associations ou sociétés savantes",
            22  => "Activité de l'IRES de Toulouse"
        ];
        ?>
        <table class='form-table'>
            <?php
            $count = 0;
            foreach ($correspondance as $key => $item) {
                if (array_key_exists($count, $title)) { ?>
                    <tr class="form-field">
                        <td colspan="2">
                            <br><br><h2><?php echo $title[$count] ?></h2>
                        </td>
                    </tr>
                <?php   }
                foreach ($userDatas as $data) {
                    if ($data['meta_key'] === $key) {?>
                        <tr class="form-field">
                            <th>
                                <h4>
                                    <?php echo $item ?>
                                </h4>
                            </th>
                            <td>
                                <?php
                                // Affichage des données
                                if ($type[$data['meta_key']] == "boolean") { ?>
                                    <input disabled type="checkbox" <?php if ($data['meta_value'] == "true") echo "checked";?>>
                                <?php } // endif
                                if ($type[$data['meta_key']] == "text") { ?>
                                    <input disabled type="text" value="<?php echo $data['meta_value'] ?>">
                                <?php } //endif ?>
                            </td>
                        </tr>
                    <?php }
                }
                $count++;
            }?>
        </table> <?php
    }
}
