<?php

namespace irestoulouse\menus;

use generators\CsvGenerator;
use irestoulouse\elements\Group;
use irestoulouse\menus\groups\GroupDetailsMenu;
use irestoulouse\menus\groups\GroupListMenu;
use irestoulouse\generators\ExcelGenerator;
use irestoulouse\utils\Identifier;
use menus\UserListMenu;

abstract class IresMenu {

    /** @var string */
    private string $pageTitle;
    /** @var string */
    private string $pageMenu;
    /** @var int */
    private int $lvlAccess;
    /** @var string */
    private string $iconUrl;
    /** @var int */
    private int $position;

    /**
     * Creating the menu
     *
     * @param string $pageTitle
     * @param string $pageMenu
     * @param int $lvlAccess
     * @param string $iconUrl
     * @param int $position
     */
    public function __construct(string $pageTitle, string $pageMenu, int $lvlAccess, string $iconUrl, int $position) {
        $this->pageTitle = $pageTitle;
        $this->pageMenu = $pageMenu;
        $this->lvlAccess = $lvlAccess;
        $this->iconUrl = $iconUrl;
        $this->position = $position;
    }

    /**
     * Initialize all menus
     */
    public static function init() : void {
        $hasAboveRole = current_user_can('responsable') ||
            current_user_can('administrator');

        // auto-complete of search bars
        add_action('wp_ajax_autocompleteSearch', function () {
            check_ajax_referer("autocompleteSearchNonce", "security");
            echo json_encode(strlen($_REQUEST["term"] ?? "") > 0 ?
                array_map(function($u) {
                    return Identifier::generateFullName($u);
                }, array_filter(get_users([
                    "search" => "*{$_REQUEST["term"]}*",
                    "search_columns" => ["user_login", "first_name", "last_name", "user_email"]
                ]), function ($u) {
                    return !user_can($u, "administrator");
                })) : []
            );
            wp_die();
        });

        // exportation of excel/csv file
        add_action("admin_init", function () {
            if(isset($_POST["export_users"]) && isset($_POST["export_type"])) {
                $fileName = "ires_utilisateur";
                $users = [];

                $userIds = explode(",", $_POST["export_users"]);
                if(strlen($userIds[0] ?? "") > 0 && count($userIds) > 0){
                    foreach ($userIds as $id){
                        if(is_numeric($id)){
                            if(($u = get_userdata($id)) !== false){
                                //$fileName .= $u->user_login;
                                $users[] = get_userdata($id);
                            }
                        }
                    }
                } else {
                    $users = get_users();
                }
                if(count($users) > 1){
                    $fileName .= "s";
                }
                ($_POST["export_type"] === "excel" ?
                    new ExcelGenerator($fileName) :
                    new CsvGenerator($fileName))->generate($users);
            }
        });
        $mainMenu = $hasAboveRole ? new UserListMenu() : new UserProfileMenu();
        IresMenu::registerSub("admin_menu", $mainMenu,
            $hasAboveRole ?
                [new UserRegisterMenu(), new GroupListMenu(), new UserProfileMenu()] :
                [new UserRegisterMenu(), new UserListMenu(), new GroupListMenu()]
        );
        IresMenu::register("admin_menu", new GroupDetailsMenu(), true);

        // change some specific menus name
        add_action("admin_menu", function () use ($hasAboveRole, $mainMenu){
            global $menu;
            global $submenu;

            if($hasAboveRole){
                if(isset($submenu[$mainMenu->getId()])) {
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
     * @param string $destSubMenu
     * @param IresMenu $menuDefault it's the menu by default on the panel
     * @param array $menu composed of sub-menus you want to add at your panel
     */
    public static function registerSub(string $destSubMenu, IresMenu $menuDefault, array $menu) : void {
        self::register($destSubMenu, $menuDefault);
        foreach ($menu as $browseMenu) {
            add_action($destSubMenu, function () use ($menuDefault, $browseMenu) {
                add_submenu_page($menuDefault->getId(),
                    $browseMenu->getPageTitle(),
                    $browseMenu->getPageMenu(),
                    $browseMenu->getLvlAccess(),
                    $browseMenu->getId(),
                    function () use ($browseMenu) {
                        $browseMenu->generate();
                    },
                );
            });
        }
    }

    /**
     * Adding the menu in the dashboard of the WordPress administration
     *
     * @param string $destMenu
     * @param IresMenu $menu
     * @param bool $invisible
     */
    public static function register(string $destMenu, IresMenu $menu, bool $invisible = false) : void {
        add_action($destMenu, function () use ($menu, $invisible) {
            if(!$invisible) {
                add_menu_page($menu->getPageTitle(),
                    $menu->getPageMenu(),
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
                    $menu->getPageTitle(),
                    $menu->getPageMenu(),
                    $menu->getLvlAccess(),
                    $menu->getId(),
                    function () use ($menu) {
                        $menu->generate();
                    },
                    $menu->getPosition()
                );
            }
        });
    }

    /**
     * @return string
     */
    public function getPageTitle() : string {
        return $this->pageTitle;
    }

    /**
     * @return string
     */
    public function getPageMenu() : string {
        return $this->pageMenu;
    }

    /**
     * @return int
     */
    public function getLvlAccess() : int {
        return $this->lvlAccess;
    }

    /**
     * @return string
     */
    public function getId() : string {
        return Identifier::fromName($this->pageMenu);
    }

    /**
     * Generate content adapted to the WordPress page
     * and adds the title of the menu
     */
    protected function generate() : void {
        $this->analyzeSentData();
        echo "<div class='wrap'>";
            echo "<h1 class='wp-heading-inline'>" . $this->pageTitle . "</h1>";
            $this->getContent();
        echo "</div>";
    }

    /**
     * Analyze the POST/GET data
     */
    public abstract function analyzeSentData() : void;

    /**
     * Content of the page
     */
    public abstract function getContent() : void;

    /**
     * @return string
     */
    public function getIconUrl() : string {
        return $this->iconUrl;
    }

    /**
     * @return int
     */
    public function getPosition() : int {
        return $this->position;
    }
}