<?php
namespace menus;
//use irestoulouse\menus\CSVExport;
use irestoulouse\menus\IresMenu;
add_action( 'admin_post_export_csv', function () {
    if(isset($_POST['download_csv'])) {
        global $wpdb;
        $filename = 'lunchbox-orders';
        $generatedDate = date('d-m-Y His');
        /**
         * output header so that file is downloaded
         * instead of open for reading.
         */
        $fileLocation = "teo";
        /*header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header('Content-Disposition: attachment; filename=lunchbox_orders.csv');
        header("Content-Transfer-Encoding: binary");*/

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/csv");
        header('Content-Disposition: attachment; filename=lunchbox_orders.csv');
        header("Content-Transfer-Encoding: binary");

        /**
         * create a file pointer connected to the output stream
         * @var [type]
         */
        $output = fopen('php://output', 'w');
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}users");

        /**
         * output the column headings
         */
        fputcsv($output, array('Order ID', 'Order Title', 'Order Date'));

    /*    foreach ($results as $key => $value) {
            // $array[] = '';
            $modified_values = array(
                $value['ID'],
                $value['post_title'],
                $value['post_date']
            );

            fputcsv($output, $modified_values);
        }*/
        return $output;
    }
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
    <?php
    }
}?>