<?php

namespace Municipio\Controller;

class Archive extends \Municipio\Controller\BaseController
{
    private static $gridSize;

    private static $randomGridBase = array();
    private static $gridRow = array();
    private static $gridColumns = array();

    public function init()
    {
        
        $this->data['postType'] = get_post_type();
        $this->data['template'] = !empty(get_field('archive_' . sanitize_title($this->data['postType']) . '_post_style', 'option')) ? get_field('archive_' . sanitize_title($this->data['postType']) . '_post_style', 'option') : 'collapsed';
        $this->data['posts'] = $this->getPosts();
        $this->data['paginationList'] = $this->preparePaginationObject();
        $this->data['queryParameters'] = $this->setQueryParameters();
        $this->data['taxonomies'] = $this->getTaxonomies(); 
        $this->data['taxonomyTags'] = $this->getTaxonomyTags();
        
    }
    
    private function getTaxonomyTags()
    {

        parse_str($_SERVER['QUERY_STRING'], $queryParameters);
        $tags = ['tags' => [], 'beforeLabel' => ''];

        if($queryParameters['filter']) {
            foreach($queryParameters['filter'] as $filter) {
                $tags['tags'][] = ['label' => $filter];   
            }
            
            return $tags;
        }
        
    }

    private function preparePaginationObject(){
        global $wp_query;
        $pagination = [];
        $paginationLinks = paginate_links([
                'type' => 'array', 
                'prev_next' => false, 
                'show_all' => true, 
                'current' => $wp_query->max_num_pages + 1
        ]);

        for($i = 0; $i < count((array) $paginationLinks); $i++){
            $anchor = new \SimpleXMLElement($paginationLinks[$i]);
         
            $pagination[] = array(
               'href' => (string) $anchor['href']  . '&pagination=' . (string) ($i + 1),
               'label' => (string) $i + 1
            );
        }

        return \apply_filters('Municipio/Controller/Search/prepareSearchResultObject', $pagination); 
    }


    private function setQueryParameters() 
    {
        $queryParameters = [
            'search' =>  isset($_GET['s']) ? $_GET['s'] : '',
            'from' =>  isset($_GET['from']) ? $_GET['from'] : '',
            'to' =>  isset($_GET['to']) ? $_GET['to'] : ''
        ];

        return \apply_filters('Municipio/Controller/Archive/setQueryParameters', 
                (object) $queryParameters);
    }

    private function getTaxonomies() 
    {
       
        $taxonomies = get_object_taxonomies($this->data['postType']);
        
        $taxonomiesList = [];
        
        foreach($taxonomies as $taxonomy){
            $text = str_replace('-',' ',$taxonomy);
            $terms = get_terms( array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                ) );
            $currentTerm = get_term_by('slug', $_GET['filter'][$taxonomy], $taxonomy);
            //die(var_dump($currentTerm));
            $taxonomiesList[$text]['currentSlug'] = $currentTerm ? $currentTerm->name : $taxonomy;

            foreach($terms as $term){
                $taxonomiesList[$text]['categories'][] = ['text' => $term->name, 'link' => "filter[{$taxonomy}]={$term->slug}"];
            }
            
        }

        return \apply_filters('Municipio/Controller/Archive/getTaxonomies', $taxonomiesList);
    }

    private function getPosts()
    {        
        
        $this->globalToLocal('posts', 'posts');
        
        $template = $this->data['template'];
        $items    = null;
        if(is_array($this->posts) && !empty($this->posts)) {

            if ($template == 'list') {
                $items = $this->getListItems($this->posts);
            } elseif ($template == 'cards') {
                $items = $this->getCardItems($this->posts);
            } elseif ($template == 'compressed') {
                $items = $this->getCompressedItems($this->posts);
            }

            return \apply_filters('Municipio/Controller/Archive/getArchivePosts', $items);
        }
        
    }

    private function getCardItems($posts)
    {
        $preparedPosts = [];

        foreach($posts as $post) {
            $post->href = $post->permalink;
            $post->featuredImage = $this->getFeaturedImage($post);
            $post->excerpt =  wp_trim_words($post->post_content, 15);
            $preparedPosts[] = \Municipio\Helper\Post::preparePostObject($post);
        }
        
        return $preparedPosts;
    }

    private function getListItems($posts)
    {
        
        $preparedPosts = [
            'items' => [],
            'headings' => ['Title', 'Published', 'Updated']
        ];

        foreach($posts as $post) {
            $preparedPosts['items'][] = [
                $post->post_title,
                $post->post_date,
                $post->post_modified
            ];
        }
        
        return $preparedPosts;
    }

    private function getFeaturedImage($post) 
    {
        $featuredImageID = get_post_thumbnail_id();
        $featuredImageSRC = \get_the_post_thumbnail_url($post->ID);
        $featuredImageAlt = get_post_meta($featuredImageID, '_wp_attachment_image_alt', TRUE);
        $featuredImageTitle = get_the_title($featuredImageID);

        $featuredImage = [
            'src' => $featuredImageSRC ? $featuredImageSRC : null,
            'alt' => $featuredImageAlt ? $featuredImageAlt : null,
            'title' => $featuredImageTitle ? $featuredImageTitle : null
        ];

        return $featuredImage;
        
    }


    public function setEqualContainer($equalContainer, $postType, $template)
    {
        $templatesWithEqualContainer = array('cards');

        if (in_array($template, $templatesWithEqualContainer)) {
            $equalContainer = true;
        }

        return $equalContainer;
    }

    public function gridAlterColumns()
    {
        $gridRand = array();

        switch ($this->data['gridSize']) {
            case 12:
                $gridRand = array(
                    array(12)
                );
                break;

            case 6:
                $gridRand = array(
                    array(12),
                    array(6, 6),
                    array(6, 6)
                );
                break;

            case 4:
                $gridRand = array(
                    array(8, 4),
                    array(4, 4, 4),
                    array(4, 8)
                );
                break;

            case 3:
                $gridRand = array(
                    array(6, 3, 3),
                    array(3, 3, 3, 3),
                    array(3, 3, 6),
                    array(3, 3, 3, 3),
                    array(3, 6, 3)
                );
                break;

            default:
                $gridRand = array(
                    array(12)
                );
                break;
        }

        self::$randomGridBase = $gridRand;
    }

    public static function getColumnSize()
    {
        // Fallback if not set
        if (empty(self::$randomGridBase)) {
            return 'grid-md-' . self::$gridSize;
        }

        if (empty(self::$gridRow)) {
            self::$gridRow = self::$randomGridBase;
        }

        if (empty(self::$gridColumns)) {
            self::$gridColumns = self::$gridRow[0];
            array_shift(self::$gridRow);
        }

        $columnSize = 'grid-md-' . self::$gridColumns[0];
        array_shift(self::$gridColumns);

        return $columnSize;
    }

    public static function getColumnHeight()
    {
        switch (self::$gridSize) {
            case 3:
                return '280px';

            case 4:
                return '400px';

            case 6:
                return '500px';

            case 12:
                return '500px';

            default:
                return false;
        }

        return false;
    }
}
