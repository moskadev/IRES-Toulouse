<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

use irestoulouse\elements\UserData;

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

    public function __construct() {
        parent::__construct("Modifier les informations de l'utilisateur", // Page title when the menu is selected
            "Renseigner ses informations", // Name of the menu
            0, // Menu access security level
            "dashicons-id-alt", // Menu icon
            3 // Page position in the list
        );
    }

    public function getContent(): void {?>
        <h1>Renseigner ses informations supplémentaires</h1>
        <form method='post' name='profile-page' id='profile-page' class='validate' novalidate='novalidate'>
        <?php
        foreach(UserData::all() as $userData){
            if($userData->getType() === "label"){
                echo "<h2>" . $userData->getName() . "</h2>";
                continue;
            }?>
            <table class='form-table' role='presentation'>
                <tr class="form-field form-required">
                    <th>
                        <label for='<?php echo $userData->getId() ?>'>
                            <?php
                            _e($userData->getName());
                            if($userData->isRequired()){?>
                                <span class='description'><?php echo _e("(required)") ?></span>
                            <?php
                            } ?>
                        </label>
                    </th>
                    <td>
                    <?php
                        $htmlData = "";
                        foreach (UserData::DATAS as $d){
                            $functions = get_class_methods($userData);
                            $call = null;
                            foreach ($functions as $f){
                                if(strpos($f, ucwords($d), 1) <= 1){
                                    $call = call_user_func([$userData, $f]);
                                    break;
                                }
                            }

                            if($call !== null && !is_array($call)) {
                                $htmlData .= "data-$d='" . $call . "' ";
                            }
                        }
                        if(in_array($userData->getType(), ["text", "email", "checkbox"])){?>
                            <input <?php
                                if($userData->isDisabled()) echo "disabled class='disabled' "; echo $htmlData?>
                                type='<?php echo $userData->getType() ?>'
                                id='<?php echo $userData->getId() ?>'
                                name='<?php echo $userData->getId() ?>'
                                value='<?php echo wp_unslash($_POST[$userData->getId()] ?? "") ?>'>
                        <?php
                        } else if(in_array($userData->getType(), ["dropdown", "checklist"])){
                        $multiple = $userData->getType() === "checklist" ? "multiple" : "";?>
                        <select id='<?php echo ($userData->getId() . "' " . $multiple); ?>>
                            <?php
                            foreach ($userData->getExtraData() as $data){?>
                                <option value='<?php echo $data ?>'><?php echo $data ?></option>
                            <?php
                            }
                            echo var_dump($multiple);
                            if($multiple) {?>
                                <span class='description'>Enfoncez Ctrl (^) ou Cmd (⌘)
                                sur Mac pour sélectionner de multiples choix</span>
                            <?php }
                        }?>
                        </select
                    </td>
                </tr>
            </table>
            <?php
            }
            submit_button(__("Modifier les informations"), "primary",
                "profile-page", true,
                ["id" => "profile-page-sub", "disabled" => "true"]);
            ?>
        </form>
    <?php
    }
}