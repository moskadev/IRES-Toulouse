<?php

namespace irestoulouse\menus;

include_once("AddUserMenu.php");
include_once("ModifyUserDataMenu.php");
include_once(__DIR__ . "../../utils/Identifier.php"); // see why this one is tricky

use irestoulouse\utils\Identifier;

abstract class IresMenu {

    /**
     * Initialize all menus
     */
    public static function init() : void{
        IresMenu::register("admin_menu", new AddUserMenu());

        IresMenu::register("admin_menu", new ModifyUserDataMenu());
    }

    /**
     * Adding the menu in the dashboard of the WordPress administration
     *
     * @param string $destMenu
     * @param IresMenu $menu
     */
    public static function register(string $destMenu, IresMenu $menu) : void{
        add_action($destMenu, function () use ($menu) {
            add_menu_page($menu->getPageTitle(),
                $menu->getPageMenu(),
                $menu->getLvlAccess(),
                Identifier::fromName($menu->getPageMenu()),
                function () use ($menu) {
                    echo "<div class='wrap'>";
                    $menu->getContent();
                    echo "</div>";
                },
                $menu->getIconUrl(),
                $menu->getPosition());
        });
    }

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
     * @return string
     */
    public function getPageTitle(): string {
        return $this->pageTitle;
    }

    /**
     * @return string
     */
    public function getPageMenu(): string {
        return $this->pageMenu;
    }

    /**
     * @return int
     */
    public function getLvlAccess(): int {
        return $this->lvlAccess;
    }

    /**
     * @return string
     */
    public function getIconUrl(): string {
        return $this->iconUrl;
    }

    /**
     * @return int
     */
    public function getPosition(): int {
        return $this->position;
    }

    /**
     * Content of the page
     */
    public abstract function getContent() : void;
}