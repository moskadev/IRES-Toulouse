<?php

namespace irestoulouse\controllers;

use WP_User;

class EmailSender extends Controller {

    /** @var WP_User */
    private WP_User $toUser;

    public function __construct(WP_User $to) {
        $this->toUser = $to;
    }

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
            home_url("/wp-admin/admin.php?page=profil_ires")
        );
        $email = apply_filters("invited_user_email", $email, $this->toUser, $this->toUser->roles[0], $password);

        return $this->send($email["subject"], $email["message"]);
    }

    public function send(string $subject, string $message) : bool {
        return wp_mail($this->toUser->user_email, $subject, $message);
    }
}