<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

use irestoulouse\elements\UserData;
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

    private int $lastUserId;

    public function __construct() {
        parent::__construct("Modifier les informations de l'utilisateur", // Page title when the menu is selected
            "Renseigner ses informations", // Name of the menu
            0, // Menu access security level
            "dashicons-id-alt", // Menu icon
            3 // Page position in the list
        );
    }

    public function getContent() : void {?>
        <h1>Renseigner ses informations supplémentaires</h1><?php
        if(count(get_users()) > 1){
            $this->lastUserId = (int) ($_POST["users"] ?? Identifier::getLastRegisteredUser());
            if(isset($_POST["action"]) && $_POST["action"] == "modifyuser"){
                try {
                    foreach (UserData::all(false) as $d){
                        // TODO back end verify
                        //if(isset($_POST[$d->getId()]) && $d->matches($_POST[$d->getId()])){
                        //    throw new \Exception();
                        //}
                    }
                    $this->updateAllData() ?>
                    <div id="message" class="updated notice is-dismissible">
                        <p><strong>Modification des informations de l'utilisateur ID: <?php echo $this->lastUserId ?> ont été bien effectuées </strong></p>
                    </div> <?php
                } catch (\Exception $e){?>
                    <div id="message" class="error notice is-dismissible">
                        <p><strong>Une erreur s'est produite lors du renseignement des informations</strong></p>
                    </div>
                <?php }
            }

            if(in_array('administrator',  wp_get_current_user()->roles)){?>
                <form method='post' name='to-modify-user' id='to-modify-user' class='validate' novalidate='novalidate'
                    <table class='form-table' role='presentation'>
                        <tr class="form-field form-required">
                            <th>
                                <label for='users'>
                                    Sélectionner l'utilisateur à modifier <?php
                                    if($this->lastUserId == Identifier::getLastRegisteredUser()){ ?>
                                        <span class='description'>(sélection par défaut de la dernière création)</span>
                                    <?php } ?>
                                </label>
                            </th>
                            <td>
                                <select name="users"><?php
                                    foreach (get_users() as $user){
                                        if($user->ID == get_current_user_id()){
                                            continue;
                                        }
                                        ?>
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
                </form><?php
            }
            ?>

            <form method='post' name='modify-user' id='modify-user' class='verifiy-form validate' novalidate='novalidate'>
                <input name='action' type='hidden' value='modifyuser'>
                <?php
                foreach(UserData::all() as $userData){
                    if($userData->getType() === "label"){
                        echo "<h2>" . $userData->getName() . "</h2>";
                        continue;
                    }?>
                    <table class='form-table' role='presentation'>
                        <tr class="form-field form-required">
                            <th>
                                <!-- Creating the title of input -->
                                <label for='<?php echo $userData->getId() ?>'> <?php
                                    _e($userData->getName());
                                    if($userData->isRequired()){?>
                                        <span class='description'><?php echo _e("(required)") ?></span> <?php
                                    } ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                $type = $userData->getType();
                                $id = $userData->getId();
                                if(in_array($type, ["text", "email", "checkbox"])){?>
                                    <input <?php
                                        if($userData->isDisabled()) echo "disabled class='disabled' "; echo Dataset::allFrom($userData)?>
                                        type='<?php echo htmlspecialchars($type) ?>'
                                        id='<?php echo htmlspecialchars($id) ?>'
                                        name='<?php echo htmlspecialchars($id) ?>'
                                        value='<?php echo htmlspecialchars($this->getInputValue($id));?>'
                                        <?php echo "selected" ?>>
                                <?php
                                } else if(in_array($type, ["dropdown", "checklist"])){
                                    $multiple = $type === "checklist"?>

                                    <select <?php if($multiple) echo "multiple" ?>
                                            name='<?php echo $id ?>[]'
                                            id='<?php echo $userData->getId() ?>'> <?php
                                    /**
                                     * Extra data are check individually and put in the dropdown or checklist
                                     * Multiple items can be selected for checklist, so we check if the user
                                     * has those extra data
                                     */
                                    foreach ($userData->getExtraData() as $data){?>
                                        <!-- value of the option -->
                                        <!-- check if the extra data has been selected by the user -->
                                        <option value='<?php echo $data ?>'
                                            <?php if($this->containsExtraData($userData, $data)) echo "selected" ?>>
                                            <?php echo $data ?> <!-- the option's text -->
                                        </option> <?php
                                    } ?>
                                    </select> <?php
                                    /**
                                     * Add a notice to the user who wants to use checklist
                                     */
                                    if($multiple) {?>
                                        <span class='description'>Enfoncez Ctrl (^) ou Cmd (⌘)
                                            sur Mac pour sélectionner de multiples choix
                                        </span> <?php
                                    }
                                } ?>
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
     * @param UserData $userData the extra user's metadata
     * @param string $dataToAnalyse metadata that should be checked
     * @return bool true if the metadata has been found
     */
    private function containsExtraData(UserData $userData, string $dataToAnalyse) : bool{
        $metadata = get_user_meta($this->lastUserId, $userData->getId(), true);
        return in_array($dataToAnalyse, explode(",", $metadata != false ? $metadata : ""));
    }

    private function getInputValue(string $inputId) : string{
        $userDatas = get_userdata($this->lastUserId);
        $value = get_user_meta($this->lastUserId, $inputId, true);
        if($inputId === "email" && $userDatas !== null){
            $value = $userDatas->data->user_email ?? $value;
        }
        return $value;
    }

    /**
     * It is necessary to update all datas from the extra metadatas
     * which are in another table in the database, but we should not forgot
     * about the main user's metadata
     */
    private function updateAllData(){
        foreach ($_POST as $meta => $data) {
            if (get_user_meta($this->lastUserId, $meta) !== false) {
                /**
                 * Some values can be arrays of mutlple values, so we stick them with a comma
                 * For others, nothing changes
                 */
                $dataValue = implode(",", !is_array($data) ? [$data] : $data);
                update_user_meta($this->lastUserId, $meta, $dataValue);
            }
        }
        /**
         * We update the main metadata
         */
        wp_update_user([
            "ID" => $this->lastUserId,
            "first_name" => get_user_meta($this->lastUserId, "first_name", true),
            "last_name" => get_user_meta($this->lastUserId, "last_name", true),
            "user_email" => get_user_meta($this->lastUserId, "email", true)
        ]);
    }
}