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

		//$userDatas = json_decode(json_encode($userDatas), true);

		$infos = [
                "first_name",
                "last_name",
                "Si animateur, titre de la formation",
                "Participation à un labo de maths",
                "Animateur formation PAF (année en cours)",
                "Manifestation",
                "Responsable du groupe",
                "Groupe de recherche-action",
                "Mmembre association (autre)",
                "Mmembre société savante",
			    "Mmembre association professeurs",
                "Nom de la CII",
                "Membre CII",
                "Interventions à l'INSPE",
                "Membre de l'INSPE",
                "Nom du chef de l'établissement",
                "Discipline enseignée",
                "Code UAI/RNE de l'établissement scolaire",
                "Situation professionnelle",
                "Ville de l'établissement",
                "Nom de l'établissement",
                "Type d'établissement",
                "Diplomes",
                "E-mail",
                "Téléphone",
                "Prénom",
                "Identifiant",
                "Nom",
                "situation_pro",
                "type_etablissement",
                "nom_etablissement",
                "ville_etablissement",
                "code_uai_rne",
                "nom_chef_etablissement",
                "participation_labo_maths",
                "membre_inspe",
                "email",
                "interventions_inspe",
                "situation_professionnelle",
                "type_detablissement",
                "nom_de_letablissement",
                "ville_de_letablissement",
                "code_uairne_de_letablissement_scolaire",
                "nom_du_chef_de_letablissement",
                "discipline_enseignee",
                "animateur_formation",
                "si_animateur_titre_de_la_formation",
                "participation_a_un_labo_de_maths",
                "membre_de_linspe",
                "interventions_a_linspe",
                "membre_cii",
                "nom_de_la_cii",
                "membre_association_professeurs",
                "membre_societe_savante",
                "membre_association_autre",
                "groupe_de_rechercheaction",
                "responsable_du_groupe",
                "membre_socit_savante",
                "responsable_groupe",
                "groupe_recherche_action",
                "membre_association_prof",
                "nom_cii",
                "interventions_linspe",
                "participation_un_labo_de_maths",
                "discipline_enseigne",
                "nom_du_chef_de_ltablissement",
                "code_uairne_de_ltablissement_scolaire",
                "ville_de_ltablissement",
                "nom_de_ltablissement",
                "type_dtablissement",
                "tlphone"
            ];
?>
            <table>
<?php
		foreach ($userDatas as $data) {
			if (in_array($data['meta_key'], $infos)) {?>

                <tr>
                    <td>
                        Meta key : <?php echo $data['meta_key'] ?>
                    </td>
                    <td>
                        Meta value : <?php echo $data['meta_value'] ?>
                    </td>
                </tr>

    <?php   } // endif
		} ?>
            </table>
	<?php }


}
