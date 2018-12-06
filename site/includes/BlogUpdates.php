<?php
# Copyright 2018 Olli Helin
# This file is part of GainCMS, a free software released under the terms of the
# GNU General Public License v3: http://www.gnu.org/licenses/gpl-3.0.en.html

namespace BlogUpdates;

require_once DIR_INCLUDE . "/PostUtils.php";

class BlogUpdate
{
    private $_date;
    private $_title;
    private $_path;
    private $_changelog;
    private $_last_update;
    private $_order;

    function __construct($path, $order) {

        $this->_path = $path;
        $this->_order = $order;
        $updates = glob($path . DIR_UPDATE_PREFIX . "*");
        $this->_last_update = end($updates);
        if ($this->_last_update !== False) {
            $update_files = explode("/", $this->_last_update);
            $last_update_file = end($update_files);
            # Date format is: yyyy-mm-dd
            $this->_date = substr($last_update_file, strlen(DIR_UPDATE_PREFIX), 10);
        } else {
            $this->_date = \PostUtils\dateFromPath($path);
        }
    }

    function readInfo() {
        if ($this->_last_update === False) {
            $this->_changelog = CONFIG_DEFAULT_CHANGELOG;
        } else {
            $this->_changelog = file_get_contents($this->_last_update);
        }
        if (file_exists($this->_path . "intro.md")) {
            # Trim out Markdown header.
            $this->_title = trim(fgets(fopen($this->_path . "intro.md", 'r')), " #\n\r");
        }
    }

    function listInfo() {
        $url = substr($this->_path, strlen(DIR_SITE));
        return $this->_date . " <a href=\"" . $url . "\">" . $this->_title . "</a>: " . $this->_changelog;
    }

    static function sortBlogUpdate($a, $b) {
        if ($a->_date < $b->_date) {
            return 1;
        }
        if ($b->_date < $a->_date) {
            return -1;
        }
        return ($a->_order > $b->_order) ? 1 : -1;
    }

}

# List blog updates. Give one or more blog post directories (in priority order) and the maximum number of updates to fetch.
function listBlogUpdates($blog_paths, $max_updates) {

    $updates = array();
    $order_solver = 0;
    if (!is_array($blog_paths)) {
        $blog_paths = array($blog_paths);
    }
    foreach ($blog_paths as $path) {
        $order_solver += $max_updates;
        $posts = \PostUtils\getPostsByPath($path);
        for ($i = 0; $i < count($posts); $i++) {
            $updates[] = new BlogUpdate($posts[$i], $i + $order_solver);
        }
    }
    usort($updates, array("BlogUpdates\BlogUpdate", "sortBlogUpdate"));
    $updates = array_slice($updates, 0, $max_updates);?>

    <div class="blog-updates">
        <hr>
        <h4>Latest blog updates</h4>
        <ul class="custom-padding blog-updates">
        <?php
        foreach ($updates as $update) {
            $update->readInfo();
            echo "<li>" . $update->listInfo() . "</li>";
        }?>
        </ul>
        <hr>
    </div>
    <?php
}

?>
