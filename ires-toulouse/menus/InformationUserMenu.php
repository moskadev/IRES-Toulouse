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
			"dashicons-id-alt", // Menu icon
			3 // Page position in the list
		);
	}

	/**
	 * Content of the page
	 */
	public function getContent() : void {
        global $wpdb;
		?> <h1>Vos informations :</h1><?php

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
        // ARray with the text of each line
		$correspondance = [
			"first_name" => "Prénom",
			"last_name" => "Nom",
			"telephone" => "Téléphone",
			"si_animateur_titre_de_la_formation" => "Titre de la formation",
			"participation_labo_maths" => "Participation labo de mathématiques",
			"membre_inspe" => "Membre INSPE",
			"interventions_inspe" => "Interventions INSPE",
			"manifestation" => "Manifestation",
			"responsable_groupe" => "Responsable du groupe",
			"groupe_recherche_action" => "Groupe recherche action",
			"membre_association_autre" => "Membre association autre",
			"membre_societe_savante" => "Membre societe savante",
			"membre_association_prof" => "Membre association prof",
			"nom_cii" => "Nom CII",
			"membre_cii" => "Membre CII",
			"discipline_enseignee" => "Discipline enseignée",
			"animateur_formation" => "Animateur formation",
			"nom_chef_etablissement" => "Nom chef établissement",
			"code_uai_rne" => "Code UAI RNE",
			"ville_etablissement" => "Ville établissement",
			"nom_etablissement" => "Nom établissement",
			"type_etablissement" => "Type établissement",
			"situation_pro" => "Situation professionnelle",
			"email" => "Email",
			"diplomes" => "Diplomes",
		];
?>
            <table>
<?php
        foreach ($correspondance as $key => $item) {
	        foreach ($userDatas as $data) {
                if ($data['meta_key'] === $key) {?>
                    <tr>
                        <td>
                            <h3>
                                <?php echo $item ?>
                            </h3>
                        </td>
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
        }
    }
}
