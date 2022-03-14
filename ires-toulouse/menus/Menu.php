<?php

namespace irestoulouse\menus;

use irestoulouse\Element;

/**
 * Menus are used to display new pages in Wordpress
 * Each new menu should be extended to this object
 * To create a new menu, use MenuFactory::register
 *
 * @version 2.0
 */
abstract class Menu extends Element {

    /** @var string */
    private string $title;
    /** @var int */
    private int $lvlAccess;
    /** @var string */
    private string $iconUrl;
    /** @var int */
    private int $position;

    /**
     * Initializing the menu with an identifier (should be used with MenuIds), a name
     * that will be displayed in a navigation bar, the title of the page, the level
     * access depending on the WP roles, an icon URL and its position in the nav bar
     *
     * @param string $id menu's identifier
     * @param string $name menu's name in nav bar
     * @param string $title menu's title
     * @param int $lvlAccess menu's level access for roles
     * @param string $iconUrl menu's icon url displayed in nav bar
     * @param int $position menu's position in nav bar
     */
    public function __construct(string $id, string $name, string $title, int $lvlAccess, string $iconUrl, int $position) {
        parent::__construct($id, $name);
        $this->title = $title;
        $this->lvlAccess = $lvlAccess;
        $this->iconUrl = $iconUrl;
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getTitle() : string {
        return $this->title;
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
    public function getIconUrl() : string {
        return $this->iconUrl;
    }

    /**
     * @return int
     */
    public function getPosition() : int {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getPageUrl() : string {
        return self::createPageUrl();
    }

    /**
     * Generate the page's URL and, if given, add
     * parameters to it
     *
     * @param array $params the parameters to add to the URL
     *
     * @return string the full page's url with params
     */
    protected function createPageUrl(array $params = []) : string {
        $gets = "";
        foreach ($params as $param => $value) {
            $gets .= "&" . $param . "=" . $value;
        }

        return home_url("/wp-admin/admin.php?page=" . $this->id . $gets);
    }

    /**
     * Generate content adapted to the WordPress page
     * and adds the title of the menu
     */
    public function generate() : void {
        $this->analyzeParams(array_merge($_GET, $_POST));
        echo "<div class='wrap'>";
            echo "<h1 class='wp-heading-inline'>" . $this->title . "</h1>";
            $this->showContent();
        echo "</div>";
    }

    /**
     * This method is necessary to analyse every parameters sent
     * from the server and check each of them before loading
     * the page itself
     *
     * @param array $params $_GET and $_POST combined
     */
    public abstract function analyzeParams(array $params) : void;

    /**
     * Content of the whole page that will be displayed to
     * the user
     */
    public abstract function showContent() : void;

    /**
     * Show a message to the user, can be an error, notice, success, etc.
     *
     * @param string $type indicates if it's an error, notice, etc.
     * @param string $message the message to be displayed
     */
    public function showNoticeMessage(string $type, string $message) : void {
        if (strlen($type) > 0 && strlen($message) > 0) { ?>
            <div id="message" class="<?php echo $type ?> notice is-dismissible">
                <p><strong><?php echo $message ?></strong></p>
            </div><?php
        }
    }
}