<?php 

namespace Municipio\Helper;

/**
* NAVIGATION
* 
* Fetching a structured array of a menu, and fallbacks 
* (if needed) to the page structure on the site. 
*
* @author   Sebastian Thulin <sebastian.thulin@helsingborg.se>
* @since    3.0.0
* @package  Municipio\Theme
*/

class Navigation
{
  private static $db;                     //A wpdb private instance (in the state when originally inited).
  private static $postId = null;          //The current post id
  private static $runtimeCache = [];      //Caches the response of the class, if called multiple times. 

  /**
   * Get WordPress menu items (from default menu management)
   *
   * @param string $menu      The menu id to get
   * @param integer $postId   The current post id
   * 
   * @return null|array
   */
  public static function getMenuItems($menu, $postId, $fallbackToPageTree = false)
  {

    //Query hash
    $queryHash = md5(json_encode(array($menu, $postId, $fallbackToPageTree))); 

    //Get cache
    if(isset(self::$runtimeCache[$queryHash])) {
      return self::$runtimeCache[$queryHash];
    }

    //Create local instance of wpdb
    self::globalToLocal('wpdb', 'db');

    //Store the post id globally, also verify that the post id is correct
    if(is_null(self::$postId)) {
      if(is_numeric($postId)) {
        self::$postId = $postId; 
      } else {
        throw new \Exception("Navigation build error: Provided is not a valid post id (should be number)."); 
      }
    }

    //Check for existing wp menu and fetch it if exists
    if (has_nav_menu($menu)) {
      $menuItems = wp_get_nav_menu_items(get_nav_menu_locations()[$menu]); 

      if(is_array($menuItems) && !empty($menuItems)) {

        $result = []; //Storage of result

        foreach ($menuItems as $item) {
          $result[$item->ID] = [
            'ID' => $item->ID,
            'post_title' => $item->title,
            'href' => $item->url,
            'post_parent' => $item->menu_item_parent,
            'object_id' => $item->object_id
          ];
        }

        return self::$runtimeCache[$queryHash] = self::buildTree(self::complementObjects($result, true));

      }
    }

    //Get menu from page/post structure if no menu defined
    if($fallbackToPageTree === true && is_numeric($postId)) {
      return self::$runtimeCache[$queryHash] = self::buildTree(self::getNested());
    } 

    //Nothing found, return null
    return self::$runtimeCache[$queryHash] = null;
  }

  /**
   * BreadCrumbData
   * Fetching data for breadcrumbs
   * @return array|void
   * @throws \Exception
   */
  public static function getBreadcrumbItems() //TODO: Integrate with class more. 
  {
    global $post; //TODO: Ugh remove

    if (!is_a($post, 'WP_Post')) {
        return;
    }

    if (!is_front_page()) {

      $post_type = get_post_type_object($post->post_type);
      $pageData = array();

      $id = \Municipio\Helper\Hash::mkUniqueId();

      $pageData[$id]['label'] = __('Home');
      $pageData[$id]['href'] = get_home_url();
      $pageData[$id]['current'] = false;
      $pageData[$id]['icon'] = "home"; 

      if (is_single() && $post_type->has_archive) {

        $id = \Municipio\Helper\Hash::mkUniqueId();
        $pageData[$id]['label'] = $post_type->label;

        $pageData[$id]['href'] = (is_string($post_type->has_archive))
            ? get_permalink(get_page_by_path($post_type->has_archive))
            : get_post_type_archive_link($post_type->name);

        $pageData[$id]['current'] = false;

      }

      if (is_page() || (is_single() && $post_type->hierarchical === true)) {
        if ($post->post_parent) {

          $ancestors = array_reverse(get_post_ancestors($post->ID));
          $title = get_the_title();

          foreach ($ancestors as $ancestor) {
              if (get_post_status($ancestor) !== 'private') {
                  $id = \Municipio\Helper\Hash::mkUniqueId();
                  $pageData[$id]['label'] = get_the_title($ancestor);
                  $pageData[$id]['href'] = get_permalink($ancestor);
                  $pageData[$id]['current'] = false;
              }
          }

          $id = \Municipio\Helper\Hash::mkUniqueId();
          $pageData[$id]['label'] = $title;
          $pageData[$id]['href'] = '';
          $pageData[$id]['current'] = true;

        } else {
          $id = \Municipio\Helper\Hash::mkUniqueId();
          $pageData[$id]['label'] = get_the_title();
          $pageData[$id]['href'] = '';
          $pageData[$id]['current'] = true;
        }

      } else {

        if (is_home()) {
            $title = single_post_title("", false);
        } elseif (is_tax()) {
            $title = single_cat_title(null, false);
        } elseif (is_category() && $title = get_the_category()) {
            $title = $title[0]->name;
        } elseif (is_archive()) {
            $title = post_type_archive_title(null, false);
        } else {
            $title = get_the_title();
        }

        $id = \Municipio\Helper\Hash::mkUniqueId();
        $pageData[$id]['label'] = $title;
        $pageData[$id]['href'] = '';
        $pageData[$id]['current'] = false;

      }

      return apply_filters('Municipio/Breadcrumbs/Items', $pageData, get_queried_object());
    }
  }

  /**
   * Get nested array representing page structure
   * 
   * @param   array     $postId             The current post id
   * @param   bool      $includeTopLevel    Include top level in response
   * @param   int|bool  $maxLevels          The maximum levels to traverse
   * 
   * @return  array                         Nested page array
   */
  private static function getNested($includeTopLevel = true) : array
  {
    //Get all ancestors, append top level if true
    if($includeTopLevel === true) {
      $parents = array_merge([0], (array) self::getAncestors(self::$postId));
    } else {
      $parents = array_merge((array) self::getAncestors(self::$postId));
    }

    //Get all parents
    $result = self::getItems($parents); 

    //Add more values
    $result = self::complementObjects($result, false); 

    //Return done
    return $result; 
  }

  /**
   * Check if a post has children
   * 
   * @param   array   $postId    The post id
   * 
   * @return  array              Flat array with parents
   */
  private static function hasChildren($array) : array
  {  
    if(!is_array($array)) {
      return new \WP_Error("Has children must recive an array."); 
    }

    $children = self::$db->get_results("
      SELECT ID
      FROM " . self::$db->posts . " 
      WHERE post_parent = '". $array['ID'] . "'
      LIMIT 1
    ", ARRAY_A);

    if(!empty($children)) {
      $array['children'] = true; 
    } else {
      $array['children'] = false; 
    }

    return $array; 
  }

  /**
   * Recusivly traverse flat array and make a nested variant
   * 
   * @param   array   $postId    The current post id
   * 
   * @return  array              Flat array with parents
   */
  private static function getAncestors($postId) : array
  {  
    return array_reverse(get_post_ancestors($postId));
  }

  /**
   * Recusivly traverse flat array and make a nested variant
   * 
   * @param   array   $elements    A list of pages
   * @param   integer $parentId    Parent id
   * 
   * @return  array               Nested array representing page structure
   */
  private static function buildTree(array $elements, int $parentId = 0) : array 
  {

    $branch = array();

    if(is_array($elements) && !empty($elements)) {
      foreach ($elements as $element) {
        if ($element['post_parent'] == $parentId) {

          $children = self::buildTree($elements, $element['id']);

          if ($children) {
            $element['children'] = $children;
          } else {
            $element['children'] = []; 
          }

          $branch[] = $element;
        }
      }
    }

    return $branch;
  }

  /**
   * Get pages/posts 
   * 
   * @param   integer  $parent    Post parent
   * @param   string   $postType  The post type to query
   * 
   * @return  array               Array of post id:s, post_titles and post_parent
   */
  private static function getItems($parent = 0, $postType = 'page') : array 
  {

    //Check if if valid post type string
    if($postType != 'all' && !is_array($postType) && !post_type_exists($postType)) {
      return new \WP_Error("Could not create navigation menu for " . $postType . "since it dosen't exist."); 
    }

    //Check if if valid post type array
    if(is_array($postType)) {
      foreach($postType as $item) {
        if(!post_type_exists($item)) {
          return new \WP_Error("Could not create navigation menu for " . $item . "since it dosen't exist."); 
        }
      }
    }

    //Handle post type cases
    if($postType == 'all') {
      $postTypeSQL = "post_type IN(" . implode(", ", get_post_types(['public' => true])) . ")"; 
    } elseif(is_array($postType)) {
      $postTypeSQL = "post_type IN(" . implode(", ", $postType ) . ")"; 
    } else {
      $postTypeSQL = "post_type = '" . $postType . "'"; 
    }

    //Default to parent = 0
    if(empty($parent)) {
      $parent = 0; 
    }

    //Support multi level query
    if(!is_array($parent)) {
      $parent = [$parent]; 
    }
    $parent = implode(", ", $parent); 

    //Run query
    return self::$db->get_results("
      SELECT ID, post_title, post_parent 
      FROM " . self::$db->posts . " 
      WHERE post_parent IN(" . $parent . ")
      AND " . $postTypeSQL . "
      AND ID NOT IN(" . implode(", ", self::getHiddenPostIds()) . ")
      AND post_status='publish'
      ORDER BY menu_order ASC 
      LIMIT 500
    ", ARRAY_A);
  }

  /**
   * Calculate add add data to array
   * 
   * @param   object   $objects     The post array
   * 
   * @return  array    $objects     The post array, with appended data
   */
  private static function complementObjects($objects, $menu = false) {
    
    if(is_array($objects) && !empty($objects)) {
      foreach($objects as $key => $item) {

        //Label empty = remove
        if(empty($item['post_title'])) {
          unset($objects[$key]);
        }

        if($menu == false) {
          $objects[$key] = self::transformObject(
            self::hasChildren(
              self::appendIsAncestorPost(
                self::appendIsCurrentPost(
                  self::customTitle(
                    self::appendHref($item)
                  )
                )
              )
            )
          );
        } else {
          $objects[$key] = self::transformObject(
            self::appendIsAncestorPost(
              self::appendIsCurrentPost(
                $item
              )
            )
          );
        }
      }
    }

    return $objects; 
  }

  /**
   * Add post is ancestor data on post array
   * 
   * @param   object   $array         The post array
   * 
   * @return  array    $postArray     The post array, with appended data
   */
  private static function appendIsAncestorPost($array) : array
  {
    if(!is_array($array)) {
      return new \WP_Error("Append permalink object must recive an array."); 
    }

    //Is parent post
    if(in_array($array['ID'], self::getAncestors(self::$postId))) {
      $array['ancestor'] = true; 
    }

    //Is parent tax item
    if(isset($array['object_id']) && in_array($array['object_id'], self::getAncestors(self::$postId))) {
      $array['ancestor'] = true;
    }

    return $array; 
  }

  /**
   * Add post is current data on post array
   * 
   * @param   object   $array         The post array
   * 
   * @return  array    $postArray     The post array, with appended data
   */
  private static function appendIsCurrentPost($array) : array
  {
    if(!is_array($array)) {
      return new \WP_Error("Append permalink function must recive an array."); 
    }

    //Is parent post
    if($array['ID'] == self::$postId) {
      $array['active'] = true; 
    }

    //Is parent tax item
    if(isset($array['object_id']) && $array['object_id'] == self::$postId) {
      $array['active'] = true; 
    }
    
    return $array; 
  }

  /**
   * Add post href data on post array
   * 
   * @param   object   $array         The post array
   * @param   boolean  $leavename     Leave name wp default param
   * 
   * @return  array    $postArray     The post array, with appended data
   */
  private static function appendHref($array, $leavename = false) : array
  {
    if(!is_array($array)) {
      return new \WP_Error("Append permalink function must recive an array."); 
    }

    $array['href'] = get_permalink($array['ID'], $leavename);

    return $array; 
  }

  /**
   * Add post data on post array
   * 
   * @param   array   $array  The post array
   * 
   * @return  array   $array  The post array, with appended data
   */
  private static function transformObject($array) : array
  {
    if(!is_array($array)) {
      return new \WP_Error("Transform object function must recive an array."); 
    }

    //Move post_title to label key
    $array['label'] = $array['post_title'];
    $array['id'] = $array['ID'];
    
    //Unset data not needed
    unset($array['post_title']); 
    unset($array['ID']); 

    //Sort & enshure existence of keys
    $array = array_merge(
      array(
        'id' => null, 
        'post_parent' => null, 
        'label' => null, 
        'href' => null, 
        'children' => null
      ),
      $array
    ); 

    return $array; 
  }

  /**
   * Get a list of hidden post id's
   * 
   * Optimzing: We are getting all meta keys since it's the 
   * fastest way of doing this due to missing indexes in database. 
   * 
   * This is a calculated risk that should be caught 
   * by the object cache. Tests have been made to enshure
   * good performance. 
   * 
   * @param string $metaKey The meta key to get data from
   * 
   * @return array
   */
  private static function getHiddenPostIds(string $metaKey = "hide_in_menu") : array
  {

    //Get meta
    $result = (array) self::$db->get_results(
      self::$db->prepare("
        SELECT post_id, meta_value 
        FROM ". self::$db->postmeta ." 
        WHERE meta_key = %s"
        , $metaKey
      )
    ); 

    //Declare result
    $hiddenPages = []; 

    //Add visible page ids
    if(is_array($result) && !empty($result)) {
      foreach($result as $item) {
        if($item->meta_value != "1") {
          continue; 
        }
        $hiddenPages[] = $item->post_id; 
      }
    }

    return $hiddenPages; 
  }

  /**
   * Get a list of custom page titles
   * 
   * Optimzing: We are getting all meta keys since it's the 
   * fastest way of doing this due to missing indexes in database. 
   * 
   * This is a calculated risk that should be caught 
   * by the object cache. Tests have been made to enshure
   * good performance. 
   * 
   * @param string $metaKey The meta key to get data from
   * 
   * @return array
   */
  private static function getMenuTitle(string $metaKey = "custom_menu_title") : array
  {

    //Get meta
    $result = (array) self::$db->get_results(
      self::$db->prepare("
        SELECT post_id, meta_value 
        FROM ". self::$db->postmeta ." 
        WHERE meta_key = %s
        AND meta_value != ''
        ", $metaKey
      )
    ); 

    //Declare result
    $pageTitles = []; 

    //Add visible page ids
    if(is_array($result) && !empty($result)) {
      foreach($result as $result) {
        if(empty($result->meta_value)) {
          continue; 
        }
        $pageTitles[$result->post_id] = $result->meta_value; 
      }
    }

    return $pageTitles; 
  }

  /**
   * Replace native title with custom menu name
   * 
   * @param array $array
   * 
   * @return object
   */
  private static function customTitle($array) : array
  {
    $customTitles = self::getMenuTitle(); 

    if(isset($customTitles[$array['ID']])) {
      $array['post_title'] = $customTitles[$array['ID']]; 
    }

    return $array; 
  }

  /**
   * Creates a local copy of the global instance
   * The target var should be defined in class header as private or public
   * 
   * @param string $global The name of global varable that should be made local
   * @param string $local Handle the global with the name of this string locally
   * 
   * @return void
   */
  private static function globalToLocal($global, $local = null)
  {
    global $$global;
    if (is_null($local)) {
        self::$$global = $$global;
    } else {
        self::$$local = $$global;
    }
  }

}