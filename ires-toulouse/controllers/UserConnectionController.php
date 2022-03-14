<?php

namespace irestoulouse\controllers;

use exceptions\FailedUserRegistrationException;
use irestoulouse\data\UserCustomDataFactory;
use WP_User;

/**
 * Handles the whole part of creating and authenticating
 * a new user
 *
 * @version 2.0
 */
class UserConnectionController extends Controller {

    /** @var string */
    private string $firstName;
    /** @var string */
    private string $lastName;
    /** @var string */
    private string $email;

    /**
     * The new user is recognized by its first name, last name and e-mail
     * (that should be confirmed from an e-mail). We are checking if all
     * of these arguments aren't empty and if the e-mail is valid
     *
     * @param string $firstName the user's first name
     * @param string $lastName the user's last name
     * @param string $email the user's e-mail
     *
     * @throws FailedUserRegistrationException if the registration has failed
     */
    public function __construct(string $firstName, string $lastName, string $email) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = html_entity_decode($email);

        if (strlen($firstName) === 0 || strlen($lastName) === 0 || strlen($email) === 0 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new FailedUserRegistrationException($firstName, $lastName, "Donnée incorrecte");
        }
    }

    /**
     * Register the new user from its first name, last name and e-mail
     * An password will be automatically generated and an e-mail will be
     * sent to the given address where he can confirm its account
     *
     * @return WP_User the registered user
     *
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
            throw new FailedUserRegistrationException($this->firstName,
                $this->lastName, $userId->get_error_message()
            );
        }
        $user = get_userdata($userId);
        if (isset($password) && strlen($password) > 0) {
            (new EmailController($user))->confirm($password);
        }
        UserCustomDataFactory::registerExtraMetas($userId);

        return $user;
    }

    /**
     * We verify if the same user login, and so we count the quantity of users
     * with the same user's login by deleting the numbers
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
     * @return string the original user's login
     */
    public function getOriginalLogin() : string {
        $firstChar = substr($this->firstName, 0, 1);

        return strtolower($firstChar . $this->lastName);
    }

    /**
     * Counting the users with the same original login
     *
     * @return int the total of users with the same login
     */
    public function countSameLogins() : int {
        return count(get_users([
                "search" => $this->getOriginalLogin() . "*",
                "search_columns" => ["user_login"]
            ])
        );
    }
}