<?php

namespace irestoulouse\menus;

use generators\CsvGenerator;
use irestoulouse\generators\excel\ExcelGenerator;
use irestoulouse\menus\groups\GroupDetailsMenu;
use irestoulouse\menus\groups\GroupListMenu;
use irestoulouse\menus\users\UserListMenu;
use irestoulouse\menus\users\UserProfileMenu;
use irestoulouse\menus\users\UserRegisterMenu;
use irestoulouse\utils\Identifier;

/**
 * Management of menus with multiple methods like
 * their initializing, registration, removing, etc..
 *
 * @version 2.0
 */
class MenuFactory {

    /** @var Menu[] */
    private static array $menus;

    /**
     * Initialize all menus with :
     * - the auto-completed search bars
     * - the file (Excel/CSV) generator and exporter
     * - Menus renamed depending on the role of the user
     */
    public static function init() : void {
        $hasAboveRole = current_user_can('responsable') ||
            current_user_can('administrator');

        // auto-complete of search bars
        add_action('wp_ajax_autocompleteSearch', function () {
            check_ajax_referer("autocompleteSearchNonce", "security");
            echo json_encode(strlen($_REQUEST["term"] ?? "") > 0 ?
                array_map(function ($u) {
                    return Identifier::generateFullName($u);
                }, get_users([
                    "search" => "*{$_REQUEST["term"]}*",
                    "search_columns" => [
                        "user_login",
                        "first_name",
                        "last_name",
                        "user_email"
                    ]
                ])
                ) : []
            );
            wp_die();
        });

        // exportation of excel/csv file
        add_action("admin_init", function () {
            if (isset($_POST["export_users"]) && isset($_POST["export_type"])) {
                $fileName = "ires_utilisateur";
                $users = [];

                $userIds = explode(",", $_POST["export_users"]);
                if (strlen($userIds[0] ?? "") > 0 && count($userIds) > 0) {
                    foreach ($userIds as $id) {
                        if (is_numeric($id)) {
                            if (($u = get_userdata($id)) !== false) {
                                //$fileName .= $u->user_login;
                                $users[] = get_userdata($id);
                            }
                        }
                    }
                } else {
                    $users = get_users();
                }
                if (count($users) > 1) {
                    $fileName .= "s";
                }
                ($_POST["export_type"] === "excel" ?
                    new ExcelGenerator($fileName) :
                    new CsvGenerator($fileName))->generate($users);
            }
        });
        $mainMenu = $hasAboveRole ? new UserListMenu() : new UserProfileMenu();
        self::registerSub("admin_menu", $mainMenu,
            $hasAboveRole ?
                [new UserRegisterMenu(), new GroupListMenu(), new UserProfileMenu()] :
                [new UserRegisterMenu(), new UserListMenu(), new GroupListMenu()]
        );
        self::register("admin_menu", new GroupDetailsMenu(), true);

        // change some specific menus name
        add_action("admin_menu", function () use ($hasAboveRole, $mainMenu) {
            global $menu;
            global $submenu;

            if ($hasAboveRole) {
                if (isset($submenu[$mainMenu->getId()])) {
                    $submenu[$mainMenu->getId()][0][0] = "Tous les comptes IRES";
                }
            } else {
                $menu[$mainMenu->getPosition()][0] = "Profil IRES";
            }
        });
    }

    /**
     * Adding a submenu in the dashboard of the WordPress administration
     *
     * @param string $destSubMenu the menu's id where it will be registered
     * @param Menu $menuDefault it's the menu by default on the panel
     * @param array $menu composed of sub-menus you want to add at your panel
     *
     * @return bool true if it has been correctly registered
     */
    public static function registerSub(string $destSubMenu, Menu $menuDefault, array $menu) : bool {
        if (self::register($destSubMenu, $menuDefault)) {
            foreach ($menu as $browseMenu) {
                if (self::exists($browseMenu->getId())) {
                    return false;
                }
                add_action($destSubMenu, function () use ($menuDefault, $browseMenu) {
                    self::$menus[$browseMenu->getId()] = $browseMenu;
                    add_submenu_page($menuDefault->getId(),
                        $browseMenu->getTitle(),
                        $browseMenu->getName(),
                        $browseMenu->getLvlAccess(),
                        $browseMenu->getId(),
                        function () use ($browseMenu) {
                            $browseMenu->generate();
                        },
                    );
                });
            }

            return true;
        }

        return false;
    }

    /**
     * Adding the menu in the dashboard of the WordPress administration
     *
     * @param string $destMenu the menu's id where it will be registered
     * @param Menu $menu the menu to be registered
     * @param bool $invisible if it should be invisible
     *
     * @return bool true if it has been registered
     */
    public static function register(string $destMenu, Menu $menu, bool $invisible = false) : bool {
        if (!self::exists($menu->getId())) {
            add_action($destMenu, function () use ($menu, $invisible) {
                self::$menus[$menu->getId()] = $menu;
                if (!$invisible) {
                    add_menu_page($menu->getTitle(),
                        $menu->getName(),
                        $menu->getLvlAccess(),
                        $menu->getId(),
                        function () use ($menu) {
                            $menu->generate();
                        },
                        $menu->getIconUrl(),
                        $menu->getPosition()
                    );
                } else {
                    add_submenu_page(
                        null,
                        $menu->getTitle(),
                        $menu->getName(),
                        $menu->getLvlAccess(),
                        $menu->getId(),
                        function () use ($menu) {
                            $menu->generate();
                        },
                        $menu->getPosition()
                    );
                }
            });

            return true;
        }

        return false;
    }

    /**
     * Checking if the menu's identifier already exists
     * and has been already registered
     * @param string $id menu's identifier
     *
     * @return bool
     */
    public static function exists(string $id) : bool {
        return isset(self::$menus[$id]);
    }

    /**
     * Get the menu from its identifier if it has been
     * registered before
     * @param string $id menu's identifier
     *
     * @return Menu|null the menu if it has been found
     */
    public static function fromId(string $id) : ?Menu {
        return self::$menus[$id] ?? null;
    }

    /**
     * @return Menu[] all menus
     */
    public static function all() : array {
        return self::$menus;
    }
}