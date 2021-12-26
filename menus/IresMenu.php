<?php

namespace irestoulouse\menus;

use irestoulouse\elements\input\UserInputData;
use irestoulouse\exceptions\InvalidInputValueException;
use irestoulouse\utils\Identifier;

abstract class IresMenu {

    /** @var IresMenu */
    private static IresMenu $instance;

    /**
     * @return IresMenu
     */
    public static function getInstance(): IresMenu {
        return self::$instance;
    }

    /**
     * Initialize all menus
     */
    public static function init() : void{
        IresMenu::register("admin_menu", new AddUserMenu());
        IresMenu::register("admin_menu", new AffectionRoleMenu());
        IresMenu::register("admin_menu", new ModifyUserDataMenu());
        IresMenu::registerSub("admin_menu", new CreationGroupeMenu(), array(new ModificationGroupeMenu(), new SuppressionGroupMenu()));
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
                $menu->getId(),
                function () use ($menu) {
                    echo "<div class='wrap'>";
                        $menu->getContent();
                    echo "</div>";
                },
                $menu->getIconUrl(),
                $menu->getPosition());
        });
    }

    /**
     * Adding a submenu in the dashboard of the WordPress administration
     *
     * @param string $destSubMenu
     * @param IresMenu $menuDefault it's the menu by default on the panel
     * @param array $menu composed of the number of sub-menus you want to add at your panel
     */
    public static function registerSub(string $destSubMenu, IresMenu $menuDefault, array $menu) : void{
        add_action($destSubMenu, function () use ($menu, $menuDefault) {
            add_menu_page($menuDefault->getPageTitle(),
                $menuDefault->getPageMenu(),
                $menuDefault->getLvlAccess(),
                $menuDefault->getId(),
                function () use ($menuDefault, $menu) {
                    echo "<div class='wrap'>";
                    $menuDefault->getContent();
                    echo "</div>";
                },
                $menuDefault->getIconUrl(),
                $menuDefault->getPosition()
            );
            add_submenu_page($menuDefault->getId(),
                $menuDefault->getPageTitle(),
                $menuDefault->getPageMenu(),
                $menuDefault->getLvlAccess(),
                $menuDefault->getId()
            );
        });
        foreach ($menu as $browseMenu) {
            add_action($destSubMenu, function () use ($menuDefault, $browseMenu) {
                add_submenu_page($menuDefault->getId(),
                    $browseMenu->getPageTitle(),
                    $browseMenu->getPageMenu(),
                    $browseMenu->getLvlAccess(),
                    $browseMenu->getId(),
                    function () use ($browseMenu) {
                        echo "<div class='wrap'>";
                        $browseMenu->getContent();
                        echo "</div>";
                    });
            });
        }
    }

    /**
     * Adding the submenu in the dashboard of the WordPress administration
     *
     * @param string $destSubMenu
     * @param IresMenu $menu
     */
    public static function registerSubSave(string $destSubMenu, IresMenu $menu, IresMenu $menuDefault) : void{
        add_action($destSubMenu, function () use ($menu, $menuDefault) {
            add_menu_page($menu->getPageTitle(),
                $menuDefault->getPageMenu(),
                $menuDefault->getLvlAccess(),
                $menuDefault->getId(),
                function () use ($menuDefault) {
                    echo "<div class='wrap'>";
                    $menuDefault->getContent();
                    echo "</div>";
                },
                $menuDefault->getIconUrl(),
                $menu->getPosition()
            );
            add_submenu_page($menuDefault->getId(),
                $menuDefault->getPageTitle(),
                $menuDefault->getPageMenu(),
                $menuDefault->getLvlAccess(),
                $menuDefault->getId()
            );
        });
        add_action($destSubMenu, function () use ($menu, $menuDefault) {
            add_submenu_page($menuDefault->getId(),
                $menu->getPageTitle(),
                $menu->getPageMenu(),
                $menu->getLvlAccess(),
                $menu->getId(),
                function () use ($menu) {
                    echo "<div class='wrap'>";
                    $menu->getContent();
                    echo "</div>";
                });
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

        self::$instance = $this;
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
    public function getId() : string{
        return Identifier::fromName($this->pageMenu);
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

    /**
     * Check each input data that needs to be verified by its regex
     * @throws InvalidInputValueException if the value doesn't match the regex
     */
    public function verifyPostData() : void{
        foreach ($_POST as $key => $value){
             $data = UserInputData::fromId($key);
             if(!is_array($value) && $data !== null && !$data->matches($value)){
                 throw new InvalidInputValueException($data->getName());
             }
        }
    }
}