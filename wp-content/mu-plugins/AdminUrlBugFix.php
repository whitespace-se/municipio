<?php

/*
Plugin Name: Admin URL bugfix
Description: This is a temporary fix for faulty admin URL's. Somehow we cannot localize the cause of the faulty links. However; We can identify them.
Version:     1.0
Author:      Sebastian Thulin
*/

namespace AdminUrlBugFix;

class AdminUrlBugFix
{
    public function __construct()
    {
        add_filter('admin_url', array($this, 'cleanAdminUrl'), 99999, 3);
    }

    public function acfLoadClean($url, $path, $blog_id)
    {
        return str_replace("/wp/wp-admin/post.php/wp/wp-admin/", "/wp/wp-admin/", $url);
    }
}

new \AdminUrlBugFix\AdminUrlBugFix();
