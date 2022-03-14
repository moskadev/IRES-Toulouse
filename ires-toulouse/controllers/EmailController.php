<?php

namespace irestoulouse\controllers;

use irestoulouse\menus\MenuFactory;
use irestoulouse\menus\MenuIds;
use irestoulouse\utils\Locker;
use WP_User;

/**
 * Controller of e-mails for confirmation and sending
 *
 * @version 2.0
 */
class EmailController extends Controller {

    /** @var WP_User */
    private WP_User $toUser;

    /**
     * @param WP_User $to the user to whom we will manipulate the e-mail
     */
    public function __construct(WP_User $to) {
        $this->toUser = $to;
    }

    /**
     * When this method is called, an e-mail will be sent to the
     * user to confirm it. The password given as an argument should
     * be a fully generated password from WP
     *
     * @param string $password the user's password
     *
     * @return bool true if the confirmation e-mail has been sent
     */
    public function confirm(string $password) : bool {
        $message = 'Bonjour,
           
 Vous avez été invité à rejoindre le site %2$s avec le role de %3$s.
 
 Identifiant : %4$s
 Mot de passe : %5$s
 
 Nous vous conseillons de modifier votre mot de passe ici :
 %6$s
 
 Vous pouvez modifier vos informations IRES en cliquant sur ce lien :
 %7$s';

        $email["to"] = $this->toUser->user_email;
        $email["subject"] = sprintf(
            __("[%s] Joining Confirmation"),
            wp_specialchars_decode(get_option("blogname"))
        );

        $passwordReset = get_password_reset_key($this->toUser);
        $email["message"] = sprintf(
            $message,
            get_option("blogname"),
            home_url(),
            wp_specialchars_decode(translate_user_role($this->toUser->roles[0])),
            $this->toUser->user_login,
            $password,
            home_url("/wp-login.php?action=rp&key=$passwordReset&login={$this->toUser->user_login}"),
            MenuFactory::fromId(MenuIds::USER_PROFILE_MENU)->getPageUrl($this->toUser->ID, Locker::STATE_UNLOCKED)
        );
        $email = apply_filters("invited_user_email", $email, $this->toUser, $this->toUser->roles[0], $password);

        return $this->send($email["subject"], $email["message"]);
    }

    /**
     * Same as wp_mail from WP or mail() from PHP, we are just
     * directly including the user's e-mail to these methods
     *
     * @param string $subject The e-mail's subject
     * @param string $message the e-mail's message
     *
     * @return bool true if the e-mail has been sent
     */
    public function send(string $subject, string $message) : bool {
        return wp_mail($this->toUser->user_email, $subject, $message);
    }
}