<?php
namespace menus;
//use irestoulouse\menus\CSVExport;
use irestoulouse\menus\IresMenu;
use XLSXWriter;

add_action( 'admin_post_export_csv', function () {
    if(isset($_POST['download_csv'])) {
        $num_users = count(get_users());// Count the number of users
        /**
         * output header so that file is downloaded
         * instead of open for reading.
         */
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/csv");
        header('Content-Disposition: attachment; filename=ires_toulouse_'.date('d-m-Y').'.csv');
        header("Content-Transfer-Encoding: binary");

        /**
         * create a file pointer connected to the output stream
         * @var [type]
         */
        $output = fopen('php://output', 'w');

        /**
         * output the column headings
         */
        fputcsv($output, array('Identifiant', 'Nom', 'Prenom','E-mail','Téléphone',
            'Groupes, manifestations et responsables','Diplomes','Situation professionnele',
            'type d\'établissement','Nom de l\'établissement','Ville de l\'établissement',
            'Code URAI/RNE de l\'établissement', 'Nom du chef de l\'établissement',
            'Discipline enseignée','Animateur formation PAF','Si animateur, titre de la formation',
            'Participation à un labo de maths','Membre de l\'INSPE','Interventions à l\'INSPE',
            'Membre CII','Nom de la CII','Membre association professeurs','Membre société savante',
            'Membre association(autre)'));

        for ($user_id=1;$user_id < $num_users;$user_id++)
        {
            $wp_array = get_user_meta($user_id);
            $user_email= get_user_by('ID',$user_id)->user_email;
            $user_data = array(
                  $wp_array['nickname'][0],
                  $wp_array['last_name'][0],
                  $wp_array['first_name'][0],
                  $user_email,
                  $wp_array['telephone'][0],
                  $wp_array['groupes'][0],
                  $wp_array['diplomes'][0],
                  $wp_array['situation_pro'][0],
                  $wp_array['type_etablissement'][0],
                  $wp_array['nom_etablissement'][0],
                  $wp_array['ville_etablissement'][0],
                  $wp_array['code_uai_rne'][0],
                  $wp_array['nom_chef_etablissement'][0],
                  $wp_array['discipline_enseignee'][0],
                  $wp_array['animateur_formation'][0],
                  $wp_array['si_animateur_titre_de_la_formation'][0],
                  $wp_array['participation_labo_maths'][0],
                  $wp_array['membre_inspe'][0],
                  $wp_array['interventions_inspe'][0],
                  $wp_array['membre_cii'][0],
                  $wp_array['nom_cii'][0],
                  $wp_array['membre_association_prof'][0],
                  $wp_array['membre_societe_savante'][0],
                  $wp_array['membre_association_autre'][0]);

            fputcsv($output,$user_data);
        }
        fclose($output);
        return $output;
    }
});

add_action('admin_post_export_xlsx', function () {
    $num_users = count(get_users());// Count the number of users
    if (!class_exists('XLSXWriter')) {
        include_once('inc/XLSWriter.php');
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Content-Disposition: attachment; filename=ires_toulouse_'.date('d-m-Y').'.xlsx');
    header("Content-Transfer-Encoding: binary");

    // # set the destination file
    $fileLocation = 'output.xlsx';

    // Object who permit to write in the file (need to call 1 time by write)
    $user_data[0] = array('Identifiant', 'Nom', 'Prenom','E-mail','Téléphone',
        'Groupes, manifestations et responsables','Diplomes','Situation professionnele',
        'type d\'établissement','Nom de l\'établissement','Ville de l\'établissement',
        'Code URAI/RNE de l\'établissement', 'Nom du chef de l\'établissement',
        'Discipline enseignée','Animateur formation PAF','Si animateur, titre de la formation',
        'Participation à un labo de maths','Membre de l\'INSPE','Interventions à l\'INSPE',
        'Membre CII','Nom de la CII','Membre association professeurs','Membre société savante',
        'Membre association(autre)');
    $writer = new XLSXWriter();

    // We write data info in the file
    for ($user_id=1;$user_id < $num_users;$user_id++)
    {
        $writer = new XLSXWriter();
        $wp_array = get_user_meta($user_id);
        $user_email= get_user_by('ID',$user_id)->user_email;
        $user_data[$user_id] = array(
            $wp_array['nickname'][0],
            $wp_array['last_name'][0],
            $wp_array['first_name'][0],
            $user_email,
            $wp_array['telephone'][0],
            $wp_array['groupes'][0],
            $wp_array['diplomes'][0],
            $wp_array['situation_pro'][0],
            $wp_array['type_etablissement'][0],
            $wp_array['nom_etablissement'][0],
            $wp_array['ville_etablissement'][0],
            $wp_array['code_uai_rne'][0],
            $wp_array['nom_chef_etablissement'][0],
            $wp_array['discipline_enseignee'][0],
            $wp_array['animateur_formation'][0],
            $wp_array['si_animateur_titre_de_la_formation'][0],
            $wp_array['participation_labo_maths'][0],
            $wp_array['membre_inspe'][0],
            $wp_array['interventions_inspe'][0],
            $wp_array['membre_cii'][0],
            $wp_array['nom_cii'][0],
            $wp_array['membre_association_prof'][0],
            $wp_array['membre_societe_savante'][0],
            $wp_array['membre_association_autre'][0]);
    }
    $writer->writeSheet($user_data);
    $writer->writeToFile($fileLocation);
    ob_clean(); // Clean buffer
    flush(); // Flush write buffers
    readfile($fileLocation);
    unlink($fileLocation);
    exit;
});
class UserListMenu extends IresMenu {
public function __construct() {
    parent::__construct("Liste des profils de l'IRES de Toulouse",
        "Comptes IRES",0,
        "dashicons-id-alt",3);
}
public function getContent() : void {
?>
    <form  method="post" id="download_form" action="<?php echo admin_url( 'admin-post.php');?>">
        <input type="hidden" name="action" value="export_csv" class="form-control"/>
        <input type="submit" name="download_csv" id="btnPopup"  class="button button-primary" value="<?php _e('Download the log (.csv)', $this->localizationDomain); ?>" />
    </form>

    <form  method="post" id="download_form" action="<?php echo admin_url( 'admin-post.php');?>">
        <input type="hidden" name="action" value="export_xlsx" class="form-control"/>
        <input type="submit" name="download_xlsx" id="btnPopup"  class="button button-primary" value="<?php _e('Download the log (.xlsx)', $this->localizationDomain); ?>" />
    </form>
    <?php
    }
}?>