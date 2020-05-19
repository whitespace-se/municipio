<?php

namespace Municipio\Helper;

class NavigationTree
{
    public $args = array();

    protected $postStatuses = array('publish');

    protected $currentPage = null;
    protected $currentNavigation = null;
    protected $currentPostType = [];
    protected $currentNavigationPageForPosttype = [];
    protected $ancestors = null;
    // protected $pageForPostTypeOptions = []; 
    // protected $isPostTypeArchive = null;

    protected $topLevelPages = null;
    protected $secondLevelPages = null;

    protected static $pageForPostTypeIds = null;

    public $itemCount = 0;
    protected $depth = 0;
    protected $currentDepth = 0;

    protected $output = '';

    protected $isAjaxParent = false;

    private $frontPageIdCache = null;
    private static $runtimeCache = [];

    public function __construct($args = array(), $parent = false)
    {
        $this->currentNavigation = $this->getCurrentNavigation();

        if ($parent) {
            $parent = $this->getPost($parent);
            $this->isAjaxParent = true;
        }

        // Merge args
        $this->args = array_merge(array(
            'theme_location' => '',
            'include_top_level' => false,
            'sublevel' => false,
            'top_level_type' => 'tree',
            'render' => 'active',
            'depth' => -1,
            'start_depth' => 1,
            'wrapper' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'classes' => 'nav',
            'id' => '',
            'sidebar' => false
        ), $args);

        if ($this->args['depth'] > -1 && $this->args['start_depth'] > 1) {
            $this->args['depth'] += $this->args['start_depth'];
        }

        if (is_user_logged_in()) {
            $this->postStatuses[] = 'private';
        }

        // Get valuable page information
        if ($parent) {
            $parent->post_parent = 0;
            $this->currentPage = $parent;
        } else {
            $this->currentPage = $this->getCurrentPage();
        }

        $this->ancestors = array();
        if (is_a($this->currentPage, 'WP_Post')) {
            $this->ancestors = $this->getAncestors($this->currentPage->ID);
        }

        if ($this->args['top_level_type'] == 'mobile') {
            $themeLocations = get_nav_menu_locations();
            $this->topLevelPages = wp_get_nav_menu_items($themeLocations['main-menu'], array(
                'menu_item_parent' => 0
            ));

            if (is_array($this->topLevelPages)) {
                $this->topLevelPages = array_filter($this->topLevelPages, function ($item) {
                    return intval($item->menu_item_parent) === 0;
                });
            }
        } else {
            if ($parent) {
                $this->topLevelPages = array($parent);
            } else {
                $this->getTopLevelPages();
            }
        }

        if ($this->args['include_top_level']) {
            if ($this->args['sublevel']) {
                $this->walk($this->topLevelPages, 1, 'nav-has-sublevel');
                $this->getSecondLevelPages();

                $walkIndex = null;
                if (!empty($this->ancestors)) {
                    $walkIndex = $this->ancestors[0];
                } else {
                    $walkIndex = $this->currentPage->ID;
                }

                if (isset($this->secondLevelPages[$walkIndex]) && !is_null($walkIndex)) {
                    if ($this->currentPage->post_parent == 0) {
                        global $isSublevel;
                        $isSublevel = true;
                    }

                    $this->walk($this->secondLevelPages[$walkIndex], 2, 'nav-sublevel');
                }
            } else {
                $this->startWrapper();
                $this->walk($this->topLevelPages);
                $this->endWrapper();
            }
        } else {
            $ancestors = $this->getAncestors($this->currentPage);

            $navigationBase = $this->currentNavigation ? $this->currentNavigation : $this->currentPage;

            $page = isset($ancestors[0]) ? $ancestors[0] : $navigationBase;

            if ($page) {
                $this->startWrapper();
                $this->walk(array($page));
                $this->endWrapper();
            }
        }
    }

    /**
     * Gets top level pages
     * @return void
     */
    protected function getTopLevelPages()
    {
        $topLevelQuery = new \WP_Query(array(
            'post_parent' => 0,
            'post_type' => 'page',
            'post_status' => $this->postStatuses,
            'orderby' => 'menu_order post_title',
            'order' => 'asc',
            'posts_per_page' => -1,
            'meta_query'    => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'hide_in_menu',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'   => 'hide_in_menu',
                        'value' => '0',
                        'compare' => '='
                    )
                )
            )
        ));

        $this->topLevelPages = $topLevelQuery->posts;
        return $this->topLevelPages;
    }

    /**
     * Gets second level pages
     * @return array
     */
    protected function getSecondLevelPages()
    {
        $secondLevel = array();

        foreach ($this->topLevelPages as $topLevelPage) {
            $pages = get_posts(array(
                'post_parent' => $topLevelPage->ID,
                'post_type' => 'page',
                'orderby' => 'menu_order post_title',
                'order' => 'asc',
                'posts_per_page' => -1
            ));

            $secondLevel[$topLevelPage->ID] = $pages;
        }

        $this->secondLevelPages = $secondLevel;
        return $secondLevel;
    }

    /**
     * Walks pages in the menu
     * @param  array $pages Pages to walk
     * @return void
     */
    protected function walk($pages, $depth = 1, $classes = null)
    {
        $this->currentDepth = $depth;

        if ($this->args['sublevel']) {
            $this->startWrapper($classes, $depth === 1);
        }

        if (!is_array($pages)) {
            return;
        }

        foreach ($pages as $page) {
            $pageId = $this->getPageId($page);
            $attributes = array();
            $attributes['class'] = array();
            $output = true;

            if (is_numeric($page)) {
                $page = $this->getPost($page);
            }

            if ($this->isAncestors($pageId)) {
                $attributes['class'][] = 'current-node current-menu-ancestor';

                if (count($this->getChildren($pageId)) > 0) {
                    $attributes['class'][] = 'is-expanded';
                }
            }

            if ($this->getPageId($this->currentPage) == $pageId) {
                $attributes['class'][] = 'current current-menu-item';
                if (count($this->getChildren($this->currentPage->ID)) > 0 && $depth != $this->args['depth']) {
                    $attributes['class'][] = 'is-expanded';
                }
            }

            if (($this->isAjaxParent && $depth === 1) || $depth < $this->args['start_depth']) {
                $output = false;
            }

            $this->item($page, $depth, $attributes, $output);
        }

        if ($this->args['sublevel']) {
            $this->endWrapper($depth === 1);
        }
    }

    /**
     * Outputs item
     * @param  object $page    The item
     * @param  array  $classes Classes
     * @return void
     */
    protected function item($page, $depth, $attributes = array(), $output = true)
    {
        $pageId = $this->getPageId($page);
        $children = $this->getChildren($pageId);

        $hasChildren = false;
        if (count($children) > 0) {
            $hasChildren = true;
            $attributes['class'][] = 'has-children';
            $attributes['class'][] = 'has-sub-menu';

        }

        if(is_null($this->frontPageIdCache)) {
            $this->frontPageIdCache = $this->getOption('page_on_front'); 
        }

        if($pageId == $this->frontPageIdCache) {
            $attributes['class'][] = 'is-front-page';
        }

        if ($output) {
            $this->startItem($page, $attributes, $hasChildren);
        }

        if ($this->isActiveItem($this->getCurrentPage()->ID) && count($children) > 0 && ($this->args['depth'] <= 0 || $depth < $this->args['depth'])) {
            if ($output) {
                $this->startSubmenu($page);
            }

            $this->walk($children, $depth + 1);

            if ($output) {
                $this->endSubmenu($page);
            }
        }

        if ($output) {
            $this->endItem($page);
        }
    }

    /**
     * Gets the current page object
     * @return object
     */
    protected function getCurrentPage()
    {

        //Get cached value
        if(!isset(self::$runtimeCache['isPostTypeArchive'])) {
            self::$runtimeCache['isPostTypeArchive'] = is_post_type_archive(); 
        }

        if (self::$runtimeCache['isPostTypeArchive']) {
            $pt = get_post_type();
            if(array_key_exists('page_for_' . $pt, self::$runtimeCache['pageForPostTypeOptions'])) {
                $pageForPostType =  self::$runtimeCache['pageForPostTypeOptions']['page_for_' . $pt]; 
            } else {
                $pageForPostType = self::$runtimeCache['pageForPostTypeOptions']['page_for_' . $pt] = $this->getOption('page_for_' . $pt); 
            }
            return $this->getPost($pageForPostType);
        }

        global $post;

        if (!is_object($post)) {
            return get_queried_object();
        }

        return $post;
    }

    /**
     * Gets the page object to base navigation on
     * @return object
     */
    protected function getCurrentNavigation()
    {
        $slug = 'page_for_' . get_post_type() . '_navigation'; 
        if(array_key_exists($slug, $this->currentNavigationPageForPosttype)) {
            return $this->currentNavigationPageForPosttype[$slug]; 
        }
        return $this->currentNavigationPageForPosttype[$slug] = $this->getOption($slug); 
    }

    /**
     * Get page children
     * @param  integer $parent The parent page ID
     * @return object          Page objects for children
     */
    protected function getChildren($parent)
    {
        $key = array_search($parent, $this->getPageForPostTypeIds());

        if ($key && $this->isPostTypeHierarchical($key)) {
            $inMenu = false;

            foreach ($this->getField('avabile_dynamic_post_types', 'options') as $type) {
                if (sanitize_title(substr($type['post_type_name'], 0, 19)) !== $key) {
                    continue;
                }

                $inMenu = $type['show_posts_in_sidebar_menu'];
            }

            if ($inMenu) {
                return get_posts(array(
                    'post_type' => $key,
                    'post_status' => $this->postStatuses,
                    'post_parent' => 0,
                    'orderby' => 'menu_order post_title',
                    'order' => 'asc',
                    'posts_per_page' => -1,
                    'meta_query'    => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'hide_in_menu',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key'   => 'hide_in_menu',
                            'value' => '0',
                            'compare' => '='
                        )
                    )
                ), 'OBJECT');
            }

            return array();
        }

        return get_posts(array(
            'post_parent' => $parent,
            'post_type' => get_post_type($parent),
            'post_status' => $this->postStatuses,
            'orderby' => 'menu_order post_title',
            'order' => 'asc',
            'posts_per_page' => -1,
            'meta_query'    => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'hide_in_menu',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'   => 'hide_in_menu',
                        'value' => '0',
                        'compare' => '='
                    )
                )
            )
        ), 'OBJECT');
    }

    protected function getPageForPostTypeIds()
    {
        if (is_array(self::$pageForPostTypeIds)) {
            return self::$pageForPostTypeIds;
        }

        $pageIds = array();

        foreach (get_post_types(array(), 'objects') as $postType) {
            if (! $postType->has_archive) {
                continue;
            }

            if ('post' === $postType->name) {
                $pageId = $this->getOption('page_for_posts');
            } else {
                $pageId = $this->getOption("page_for_{$postType->name}");
            }

            if (!$pageId) {
                continue;
            }

            $pageIds[$postType->name] = $pageId;
        }

        return self::$pageForPostTypeIds = $pageIds;
    }

    /**
     * Get ancestors of the current page
     * @param  integer / post object $post
     * @return array ID's of ancestors
     */
    protected function getAncestors($post)
    {
        return array_reverse(get_post_ancestors($post));
    }

    /**
     * Checks if a specific id is in the ancestors array
     * @param  integer  $id
     * @return boolean
     */
    protected function isAncestors($id)
    {
        $ancestors  = $this->ancestors;
        $baseParent = $this->getAncestors($this->currentPage);
        if (is_array($baseParent) && !empty($baseParent)) {
            $ancestors = array_merge($ancestors, $baseParent);
        }

        return in_array($id, $ancestors);
    }

    /**
     * Checks if the given id is in a active/open menu scope
     * @param  integer  $id Page id
     * @return boolean
     */
    protected function isActiveItem($id)
    {
        if ($this->args['render'] == 'all' || !is_object($this->currentPage)) {
            return true;
        }

        return $this->isAncestors($id) || $id === $this->currentPage->ID;
    }

    /**
     * Opens a menu item
     * @param  object $item    The menu item
     * @param  array  $classes Classes
     * @return void
     */
    protected function startItem($item, $attributes = array(), $hasChildren = false)
    {
        if (!$this->shouldBeIncluded($item) || !is_object($item)) {
            return;
        }

        $this->itemCount++;
        $outputSubmenuToggle = false;

        $attributes['class'][] = 'page-' . $item->ID;

        if ($hasChildren && ($this->args['depth'] === -1 || $this->currentDepth < $this->args['depth'] + 1)) {
            $outputSubmenuToggle = true;

            if (array_search('has-children', $attributes['class']) > -1) {
                unset($attributes['class'][array_search('has-children', $attributes['class'])]);
            }
        }

        $title = isset($item->post_title) ? $item->post_title : '';
        $objId = $this->getPageId($item);

        if (isset($item->post_type) && $item->post_type == 'nav_menu_item') {
            $title = $item->title;
        }

        if (!empty($this->getField('custom_menu_title', $objId))) {
            $title = $this->getField('custom_menu_title', $objId);
        }

        $href = get_permalink($objId);
        if (isset($item->type) && $item->type == 'custom') {
            $href = $item->url;
        }

        if ($outputSubmenuToggle) {
            $this->addOutput(sprintf(
                '<li%1$s><a href="%2$s">%3$s</a>',
                $this->attributes($attributes),
                $href,
                $title
            ));
            $this->addOutput('<button data-load-submenu="' . $objId . '" data-load-blog-id="' . get_current_blog_id() . '"><span class="sr-only">' . __('Show submenu', 'municipio') . '</span><span class="icon"></span></button>');
        } else {
            $this->addOutput(sprintf(
                '<li%1$s><a href="%2$s">%3$s</a>',
                $this->attributes($attributes),
                $href,
                $title
            ));
        }
    }

    private function attributes($attributes = array())
    {
        foreach ($attributes as $attribute => &$data) {
            $data = implode(' ', (array) $data);
            $data = $attribute . '="' . $data . '"';
        }

        return $attributes ? ' ' . implode(' ', $attributes) : '';
    }

    /**
     * Closes a menu item
     * @param  object $item The menu item
     * @return void
     */
    protected function endItem($item)
    {
        if (!$this->shouldBeIncluded($item)) {
            return;
        }

        $this->addOutput('</li>');
    }

    /**
     * Starts wrapper
     * @return void
     */
    protected function startWrapper($classes = null, $filters = true)
    {
        $wrapperStart = explode('%3$s', $this->args['wrapper'])[0];

        if ($filters) {
            $wrapperStart = apply_filters('Municipio/main_menu/wrapper_start', $wrapperStart, $this->args);
        }

        $this->addOutput(sprintf(
            $wrapperStart,
            $this->args['id'],
            trim($this->args['classes'] . ' ' . $classes)
        ));
    }

    /**
     * Ends wrapper
     * @return void
     */
    protected function endWrapper($filters = true)
    {
        $wrapperEnd = explode('%3$s', $this->args['wrapper'])[1];

        if ($filters) {
            $wrapperEnd = apply_filters('Municipio/main_menu/wrapper_end', $wrapperEnd, $this->args);
        }

        $this->addOutput(sprintf(
            $wrapperEnd,
            $this->args['id'],
            $this->args['classes']
        ));
    }

    /**
     * Opens a submenu
     * @return void
     */
    protected function startSubmenu($item)
    {
        if (!$this->shouldBeIncluded($item)) {
            return;
        }

        $this->addOutput('<ul class="sub-menu">');
    }

    /**
     * Closes a submenu
     * @return void
     */
    protected function endSubmenu($item)
    {
        if (!$this->shouldBeIncluded($item)) {
            return;
        }

        $this->addOutput('</ul>');
    }

    /**
     * Datermines if page should be included in the menu or not
     * @param  object $item The menu item
     * @return boolean
     */
    public function shouldBeIncluded($item)
    {
        if (!is_object($item)) {
            return false;
        }

        $pageId = $this->getPageId($item);
        $showInMenu = $this->getField('hide_in_menu', $pageId) ? !$this->getField('hide_in_menu', $pageId) : true;
        $isNotTopLevelItem = !($item->post_type === 'page' && isset($item->post_parent) && $item->post_parent === 0);
        $showTopLevel = $this->args['include_top_level'];

        return ($showTopLevel || $isNotTopLevelItem) && $showInMenu;
    }


    /**
     * Adds markup to the output string
     * @param string $string Markup to add
     */
    protected function addOutput($string)
    {
        $this->output .= $string;
    }

    /**
     * Echos the output
     * @return void
     */
    public function render($echo = true, $wrapper = array())
    {
        if ($echo) {
            echo $this->output;
            return true;
        }

        return $this->output;
    }

    /**
     * Gets the item count
     * @return void
     */
    public function itemCount()
    {
        return $this->itemCount;
    }

    public function getPageId($page)
    {
        if (is_null($page)) {
            return false;
        }

        if (!is_object($page)) {
            $page = $this->getPost($page);
        }

        if (isset($page->post_type) && $page->post_type == 'nav_menu_item') {
            return intval($page->object_id);
        }

        return $page->ID;
    }

    /**
     * Gets field value from cache if exists else gets post via WP func
     * @param string $field Name of field
     * @param int $objectId Object which field to get
     * @return mixed Field data
     */
    public function getField($field, $objectId)
    {
        if(!array_key_exists('fields',self::$runtimeCache)) {
            self::$runtimeCache['fields'] = [];
        }

        if(!array_key_exists($field, self::$runtimeCache['fields'])) {
            self::$runtimeCache['fields'][$field] = [];
        }

        if (array_key_exists($objectId, self::$runtimeCache['fields'][$field])) {
            return self::$runtimeCache['fields'][$field][$objectId];
        }

        $query = is_int($objectId) ?
            get_post_meta($objectId, $field, true) :
                get_field($field, $objectId);

        return self::$runtimeCache['fields'][$field][$objectId] = $query;
    }

    /**
     * Gets option from cache if exists else gets post via WP func
     * @param string $option Name of option
     * @param mixed $default What to return if option doesn't exist
     * @return mixed Option data
     */
    public function getOption($option, $default = false)
    {
        if(!array_key_exists('options',self::$runtimeCache)) {
            self::$runtimeCache['options'] = [];
        }

        if (array_key_exists($option, self::$runtimeCache['options'])) {
            return self::$runtimeCache['options'][$option];
        }

        return self::$runtimeCache['options'][$option] = get_option($option, $default);
    }

    /**
     * Gets post type from cache if exists else gets post via WP func
     * @param mixed $post WP_Post object or post ID
     * @return string Post type
     */
    public function getPostType($post = 'post')
    {
        $postId = !is_object($post) ? $post : $postId = $post->ID;

        if(!array_key_exists('post_type',self::$runtimeCache)) {
            self::$runtimeCache['post_type'] = [];
        }

        if (array_key_exists($postId, self::$runtimeCache['post_type'])) {
            return self::$runtimeCache['post_type'][$postId];
        }

        return self::$runtimeCache['post_type'][$postId] = get_post_type($post === 'post' ? null : $post);
    }

    /**
     * Gets post from cache if exists else gets post via WP func
     * @param mixed $post WP_Post object or post ID
     * @return object WP_Post
     */
    public function getPost($post = 'post')
    {
        if(!array_key_exists('posts',self::$runtimeCache)) {
            self::$runtimeCache['posts'] = [];
        }

        $arrKey = !is_object($post) ? $post : $post->ID;

        if (array_key_exists($arrKey, self::$runtimeCache['posts'])) {
            return self::$runtimeCache['posts'][$arrKey];
        }

        return self::$runtimeCache['posts'][$arrKey] = get_post($post === 'post' ? null : $arrKey);
    }

    /**
     * Check if a posttype is hierarchical
     *
     * @param string $postType
     * @return boolean
     */
    public static function isPostTypeHierarchical($postType) {
        
        //Create array
        if(isset(self::$runtimeCache['postTypeHierarchical'])) {
            self::$runtimeCache['postTypeHierarchical'] = array();
        }

        //Get from cache
        if(isset(self::$runtimeCache['postTypeHierarchical'][$postType])) {
            return self::$runtimeCache['postTypeHierarchical'][$postType]; 
        }
        
        //Store to cache & return
        return self::$runtimeCache['postTypeHierarchical'][$postType] = is_post_type_hierarchical($postType); 
    }
}


