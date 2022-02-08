<?php

namespace irestoulouse\menus;

use irestoulouse\elements\input\UserData;
use irestoulouse\exceptions\InvalidInputValueException;
use irestoulouse\utils\Identifier;

use irestoulouse\menus\groups\GroupListMenu;
use irestoulouse\menus\groups\GroupDetailsMenu;
use menus\UserListMenu;

abstract class IresMenu {

	/**
	 * Initialize all menus
	 */
	public static function init() : void{
        IresMenu::registerSub("admin_menu", new UserListMenu(),
            [new UserRegisterMenu(), new GroupListMenu(), new UserProfileMenu()]
        );
        IresMenu::registerInvisibleSub("admin_menu", new GroupDetailsMenu());
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
                    $menu->generateContent();
                },
				$menu->getIconUrl(),
				$menu->getPosition());
		});
	}

    public static function registerInvisibleSub(string $destMenu, IresMenu $menu) : void{
        add_action($destMenu, function () use ($menu) {
            add_submenu_page(
                null,
                $menu->getPageTitle(),
                $menu->getPageMenu(),
                $menu->getLvlAccess(),
                $menu->getId(),
                function () use ($menu) {
                    $menu->generateContent();
                },
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
        self::register($destSubMenu, $menuDefault);
		foreach ($menu as $browseMenu) {
			add_action($destSubMenu, function () use ($menuDefault, $browseMenu) {
				add_submenu_page($menuDefault->getId(),
					$browseMenu->getPageTitle(),
					$browseMenu->getPageMenu(),
					$browseMenu->getLvlAccess(),
					$browseMenu->getId(),
                    function () use ($browseMenu) {
                        $browseMenu->generateContent();
                    },
                );
			});
		}
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

    protected function generateContent() : void{
        echo "<div class='wrap'>";
            echo "<h1 class='wp-heading-inline'>" . $this->pageTitle . "</h1>";
            $this->getContent();
        echo "</div>";
    }

	/**
	 * Content of the page
	 */
	public abstract function getContent() : void;
}