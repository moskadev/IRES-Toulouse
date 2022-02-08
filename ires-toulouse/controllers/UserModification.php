<?php

namespace irestoulouse\controllers;

use Exception;
use irestoulouse\elements\input\UserData;
use WP_User;

class UserModification extends Controller {

    /** @var WP_User */
    private WP_User $user;

    public function __construct(WP_User $user) {
        $this->user = $user;
    }

    /**
     * @return WP_User
     */
    public function getUser() : WP_User {
        return $this->user;
    }

    /**
     * @param UserData $inputData the extra user's metadata
     * @param string $dataToAnalyse metadata that should be checked
     *
     * @return bool true if the metadata has been found
     */
    public function containsExtraData(UserData $inputData, string $dataToAnalyse) : bool {
        $metadata = get_user_meta($this->user->ID, $inputData->getId(), true);

        return in_array($dataToAnalyse, explode(",", $metadata != false ? $metadata : ""));
    }

    /**
     * It is necessary to update all data from the extra metadata
     * which are in another table in the database, but we should not forget
     * about the main user's metadata
     *
     * @throws Exception If an error occurred with Wordpress registration
     */
    public function updateAllUserData() : WP_User {
        foreach ($_POST as $meta => $data) {
            if (get_user_meta($this->user->ID, $meta) !== false) {
                /**
                 * Some values can be arrays of multiple values, so we stick them with a comma
                 * For others, nothing changes
                 */
                $dataValue = implode(",", !is_array($data) ? [$data] : $data);
                update_user_meta($this->user->ID, $meta, $dataValue);
            }
        }
        /**
         * We update the main metadata
         */
        $userId = wp_update_user([
            "ID" => $this->user->ID,
            "first_name" => get_user_meta($this->user->ID, "first_name", true),
            "last_name" => get_user_meta($this->user->ID, "last_name", true),
            "user_email" => get_user_meta($this->user->ID, "email", true)
        ]);
        if (is_wp_error($userId)) {
            throw new Exception("ID {$this->user->ID} : Problème lors de l'enregistrement d'une donnée");
        }

        return get_user_by("id", $userId);
    }
}