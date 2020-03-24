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
        register_rest_route('municipio/v1', '/navigation', array(
            'methods' => 'GET',
            'callback' => array($this, 'getSubmenu'),
        ));
    }

    public function getSubmenu($data)
    {
        
        if(isset($data->get_params()['parentID'])){
            
            $children = get_children($data->get_params()['parentID']);
            $subMenu =  [];
            //die(print_r($children));
            foreach($children as $key =>  $child){
                
                $child = array(
                    'ID' => $child->ID,
                    'post_parent' => $child->post_parent,
                    'post_title' => $child->post_title,
                    'href' => $array['href'] = get_permalink($child->ID),
                    'children' => get_children($child->ID)
                );
                
                $subMenu[] = $child;
            }
            
            return $subMenu;
        }
    }
}
