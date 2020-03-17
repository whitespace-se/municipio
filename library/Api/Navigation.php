<?php

namespace Municipio\Api;

class Navigation
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerEndpoint'));
    }

    public function registerEndpoint()
    {
        register_rest_route('municipio/v1', '/navigation/(?P<parentID>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'getSubmenu'),
        ));
    }

    public function getSubmenu($data)
    {
        $children = get_children($data['parentID']);
        $subMenu =  [];
        
        foreach($children as $key =>  $child){
            $child = array(
                'ID' => $child->ID,
                'post_parent' => $child->post_parent,
                'post_title' => $child->post_title,
                'href' => $array['href'] = get_permalink($child->ID)
            );
            
            $subMenu[] = $child;
        }
        
        return $subMenu;
    }
}
