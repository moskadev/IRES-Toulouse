<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

use irestoulouse\elements\input\UserInputData;
use irestoulouse\utils\Dataset;
use irestoulouse\utils\Identifier;

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
class ModifyUserDataMenu extends IresMenu {

    /** @var int */
    private int $lastUserId;

    /**
     * Constructing the menu and link to the admin page
     */
    public function __construct() {
        parent::__construct("Modifier les informations supplémentaires", // Page title when the menu is selected
            "Renseigner des informations", // Name of the menu
            0, // Menu access security level
            "dashicons-id-alt", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Content of the page
     */
    public function getContent() : void {
        $this->search_bar();

        $current_user = get_user_by('ID', get_current_user_id());
        $isAdmin = current_user_can('administrator'); ?>
        <h1>Renseigner des informations supplémentaires</h1> <?php
        if(count(get_users()) > 1){
            /**
             * If admin, it gets the last created user or chosen user
             * If responsable, verify if he's responsible for the user
             * else chose itself
             */
            if ($isAdmin || in_array($_POST['users'], self::getUsers($current_user->ID))) {
                $this->lastUserId = (int) $_POST["users"];
            } else {
                $this->lastUserId = get_current_user_id();
                if (isset($_POST['users']) && $_POST['users'] != "") {
                    ?>
                    <div id="message" class="error notice is-dismissible">
                        <p><strong>Vous n'avez pas la permission de modifier cet utilisateur.</strong></p>
                    </div>
                    <?php
                }
            }

            if(isset($_POST["action"]) && $_POST["action"] == "modifyuser"){
                try {
                    $this->verifyPostData();
                    $this->updateAllData() ?>
                    <div id="message" class="updated notice is-dismissible">
                        <p><strong>Modification des informations de l'utilisateur ID:
                                <?php echo $this->lastUserId ?> ont été bien effectuées </strong></p>
                    </div> <?php
                } catch (\Exception $e){?>
                    <div id="message" class="error notice is-dismissible">
                        <p><strong>Erreur : <?php echo $e->getMessage() ?></strong></p>
                    </div>
                <?php }
            }

            if($isAdmin){?>
                <form method='post' name='to-modify-user' id='to-modify-user' class='validate' novalidate='novalidate'>
                <table class='form-table' role='presentation'>
                    <tr class="form-field form-required">
                        <th>
                            <label for='users'>
                                Sélectionnez l'utilisateur à modifier <?php
                                if($this->lastUserId == Identifier::getLastRegisteredUser()){ ?>
                                    <span class='description'>(sélection par défaut de la dernière création)</span>
                                <?php } ?>
                            </label>
                        </th>
                        <td>
                            <select name="users"><?php
                                $users = array_filter(get_users(), function ($u){
                                    return $u->ID != get_current_user_id() && !in_array("administrator",  $u->roles);
                                });
                                foreach ($users as $user){?>
                                    <option value='<?php echo $user->ID ?>' <?php if($this->lastUserId == $user->ID) echo "selected" ?>>
                                        <?php echo $user->nickname ?>
                                    </option>
                                <?php }
                                ?></select>
                            </select>
                        </td>
                    </tr>
                </table> <?php
                submit_button(__("Modifier cet utilisateur"), "button action",
                    "to-modify", true,
                    ["id" => "to-modify-user-btn"]);
                ?>
                <span class='description'>Veuillez valider si vous avez sélectionné un nouveau utilisateur</span>
                </form><?php
            }
            ?>

            <form method='post' name='modify-user' id='modify-user' class='verifiy-form validate' novalidate='novalidate'>
                <input name='action' type='hidden' value='modifyuser'>
                <input type="hidden" name="users" value="<?php echo $_POST['users']; ?>"> <?php
                foreach(UserInputData::all() as $inputData){
                    $inputFormType = $inputData->getFormType();
                    $inputId = $inputData->getId();

                    if($inputFormType === "label"){
                        echo "<h2>" . $inputData->getName() . "</h2>";
                        continue;
                    }?>
                    <table class='form-table' role='presentation'>
                        <tr class="form-field form-required">
                            <th>
                                <!-- Creating the title of input -->
                                <label for='<?php echo $inputId ?>'> <?php
                                    _e($inputData->getName());
                                    if($inputData->isRequired()){?>
                                        <span class='description'><?php _e("(required)") ?></span> <?php
                                    } ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                if(in_array($inputFormType, ["text", "email"])){?>
                                    <input <?php echo Dataset::allFrom($inputData)?>
                                        type='<?php echo htmlspecialchars($inputFormType) ?>'
                                        id='<?php echo htmlspecialchars($inputId) ?>'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value='<?php echo htmlspecialchars($this->getInputValue($inputId));?>'>
                                    <?php
                                } else if($inputFormType === "radio"){
                                    $value = filter_var($this->getInputValue($inputId), FILTER_VALIDATE_BOOLEAN);?>
                                    Oui <input <?php echo Dataset::allFrom($inputData) ?>
                                        type="radio"
                                        id='<?php echo htmlspecialchars($inputId) ?>_oui'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value="true"
                                        <?php if($value == true) echo "checked" ?>>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    Non <input <?php echo Dataset::allFrom($inputData) ?>
                                        type="radio"
                                        id='<?php echo htmlspecialchars($inputId) ?>_non'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value="false"
                                        <?php if($value == false) echo "checked" ?>>
                                    <?php
                                } else if(in_array($inputFormType, ["dropdown", "checklist"])){?>
                                    <select <?php if($inputFormType === "checklist") echo "multiple" ?>
                                        name='<?php echo $inputId ?>[]'
                                        id='<?php echo $inputId ?>'> <?php
                                        /**
                                         * Extra data are checked individually and put in the dropdown or checklist
                                         * Multiple items can be selected for checklist, so we check if the user
                                         * has those extra data
                                         */
                                        foreach ($inputData->getExtraData() as $data){?>
                                            <!-- value of the option -->
                                            <option value='<?php echo $data ?>'
                                                <?php if($this->containsExtraData($inputData, $data)) echo "selected" ?>>
                                                <!-- check if the extra data has been selected by the user -->
                                                <?php echo $data ?> <!-- the option's text -->
                                            </option> <?php
                                        } ?>
                                    </select> <?php
                                }
                                if(!empty($inputData->getDescription())){ ?>
                                    <p class="description"><?php _e($inputData->getDescription()) ?></p>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                submit_button(__("Modifier les informations"), "primary",
                    "profile-page", true,
                    ["id" => "profile-page-sub", "disabled" => "true"]);
                ?>
            </form> <?php
        } else { ?>
            <div id="message" class="error notice">
                <p><strong>Aucun utilisateur ne peut être modifié</strong></p>
            </div>
        <?php }
    }

    /**
     * @param UserInputData $inputData the extra user's metadata
     * @param string $dataToAnalyse metadata that should be checked
     * @return bool true if the metadata has been found
     */
    private function containsExtraData(UserInputData $inputData, string $dataToAnalyse) : bool{
        $metadata = get_user_meta($this->lastUserId, $inputData->getId(), true);
        return in_array($dataToAnalyse, explode(",", $metadata != false ? $metadata : ""));
    }

    /**
     * Looking for the value to put in the input
     * Special check for the emails which should be checked in
     * the other table of the user
     *
     * @param string $inputId
     * @return string input's value
     */
    private function getInputValue(string $inputId) : string{
        $inputDatas = get_userdata($this->lastUserId);
        if(get_user_meta($this->lastUserId, $inputId, true) === false){
            add_user_meta($this->lastUserId, $inputId, UserInputData::fromId($inputId)->getDefaultValue(), true);
        }
        $value = get_user_meta($this->lastUserId, $inputId, true);
        if($inputId === "email" && $inputDatas !== null){
            $value = $inputDatas->data->user_email ?? $value;
        }
        return $value;
    }

    /**
     * It is necessary to update all data from the extra metadata
     * which are in another table in the database, but we should not forget
     * about the main user's metadata
     *
     * @throws \Exception If an error occurred with Wordpress registration
     */
    private function updateAllData(){
        foreach ($_POST as $meta => $data) {
            if (get_user_meta($this->lastUserId, $meta) !== false) {
                /**
                 * Some values can be arrays of multiple values, so we stick them with a comma
                 * For others, nothing changes
                 */
                $dataValue = implode(",", !is_array($data) ? [$data] : $data);
                update_user_meta($this->lastUserId, $meta, $dataValue);
            }
        }
        /**
         * We update the main metadata
         */
        $user = wp_update_user([
            "ID" => $this->lastUserId,
            "first_name" => get_user_meta($this->lastUserId, "first_name", true),
            "last_name" => get_user_meta($this->lastUserId, "last_name", true),
            "user_email" => get_user_meta($this->lastUserId, "email", true)
        ]);
        if(is_wp_error($user)){
            throw new \Exception("ID $this->lastUserId : Problème lors de l'enregistrement d'une donnée");
        }
    }


    /**
     * Get all the users for who $user_id is responsible
     * @param int $user_id id of the responsible
     * @return array|null all the id of the users
     */
    private function getUsers(int $user_id) {
        global $wpdb;
        $users = [];
        $groups = [];
        $results = $wpdb->get_results($wpdb->prepare(" SELECT id_group
                                                            FROM {$wpdb->prefix}groups
                                                            JOIN {$wpdb->prefix}groups_users 
                                                            ON id_group = group_id 
                                                                WHERE is_responsable = 1
                                                                AND user_id = %d", $user_id),
                                             ARRAY_A);
        foreach ($results as $result) {
            array_push($groups, $result['id_group']);
        }

        foreach ($groups as $group) {
            // On récupère tous les utilisateur dans les groupes
            $results = $wpdb->get_results($wpdb->prepare("SELECT * from {$wpdb->prefix}groups_users WHERE group_id = %d", $group), ARRAY_A);
            foreach ($results as $result) {
                if (!in_array($result['user_id'], $users)) {
                    array_push($users, $result['user_id']);
                }
            }
        }
        return $users;
    }

    /**
     * Initialise the scripts for ajax search bar
     */
    function search_bar() {
        wp_enqueue_script('autocomplete-search', plugins_url('ires-toulouse/js/ScriptModifyUserDataMenu.js'),
            ['jquery', 'jquery-ui-autocomplete'], null, true);
        wp_localize_script('autocomplete-search', 'AutocompleteSearch', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('autocompleteSearchNonce')
        ]);
        $wp_scripts = wp_scripts();
        wp_enqueue_style('jquery-ui-css',
            '//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-autocomplete']->ver . '/themes/smoothness/jquery-ui.css',
            false, null, false
        );
    }
}
