<?php

namespace irestoulouse\controllers;

use exceptions\FailedUserRegistrationException;
use irestoulouse\elements\input\UserData;
use WP_User;

class UserConnection extends Controller {

    /** @var string */
    private string $firstName;
    /** @var string */
    private string $lastName;
    /** @var string */
    private string $email;

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @throws FailedUserRegistrationException
     */
    public function __construct(string $firstName, string $lastName, string $email) {
        if (empty($firstName) || empty($lastName) || empty($email)) {
            throw new FailedUserRegistrationException($this->getOriginalLogin(), "Donnée incorrecte");
        }
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    /**
     * @return string the original user's login
     */
    public function getOriginalLogin() : string {
        $firstChar = substr($this->firstName, 0, 1);

        return strtolower($firstChar . $this->lastName);
    }

    /**
     * Register the new user
     * @return WP_User the registered user
     * @throws FailedUserRegistrationException if the user couldn't be registered
     */
    public function register() : WP_User {
        $login = $this->getTrueLogin();
        $password = wp_generate_password(20, false);

        $userId = wp_insert_user([
            "user_login" => $login,
            "first_name" => $this->firstName,
            "last_name" => $this->lastName,
            "user_pass" => $password,
            "user_email" => $this->email,
            "user_registered" => current_time("mysql", 1),
            "user_status" => "0", // visitor
            "display_name" => $login
        ]);
        if (is_wp_error($userId)) {
            throw new FailedUserRegistrationException($login, $userId->get_error_message());
        }
        $user = get_user_by("id", $userId);
        if (isset($password) && !empty($password)) {
            (new EmailSender($user))->confirm($password);
        }
        UserData::registerExtraMetas($userId);

        return $user;
    }

    /**
     * We verify if the same nickname/user login, and so we count the quantity of users
     * with the same nickname by deleting the numbers
     * We also reduce it by 1 because the current user is already in the array too,
     * it's useless to count it
     *
     * @return string the new user's login after counting if
     *                multiple users with the same login exists
     */
    public function getTrueLogin() : string {
        return $this->getOriginalLogin() .
            ($this->countSameLogins() > 0 ? $this->countSameLogins() : "");
    }

    /**
     * @return int the highest number + 1 of the different login
     */
    public function countSameLogins() : int {
        return count(get_users([
            "search" => $this->getOriginalLogin() . "*",
            "search_columns" => ["user_login"]
        ])
        );
    }

    /**
     * @param string $subject
     * @param string $message
     *
     * @return bool true if success
     */
    public function sendEmail(string $subject, string $message) : bool {
        //todo
        return true;
    }
}