<?php

use ComponentLibrary\Init as ComponentLibraryInit;

if (!function_exists('render_blade_view')) {
    function render_blade_view($view, $data = [], $overrideViewPaths = false, $formatError = true)
    {
        $viewPaths = \Municipio\Helper\Template::getViewPaths();

        if (!$viewPaths) {
            wp_die("No view paths registered, please register at least one.");
            return;
        }

        if (!empty($overrideViewPaths) && is_array($overrideViewPaths)) {
            $viewPaths = $overrideViewPaths;
        }

        $externalViewPaths = apply_filters('Municipio/blade/view_paths', array());
        $viewPaths         = array_merge($viewPaths, $externalViewPaths);
        $init              = new ComponentLibraryInit($viewPaths);
        $bladeEngine       = $init->getEngine();

        try {
            $markup = $bladeEngine->makeView(
                $view,
                array_merge(
                    $data,
                    array('errorMessage' => false)
                )
            )->render();
        } catch (\Throwable $e) {
            if ($formatError === true) {
                $markup .= '<pre style="border: 3px solid #f00; padding: 10px;">';
                $markup .= '<strong>' . $e->getMessage() . '</strong>';
                $markup .= '<hr style="background: #000; outline: none; border:none; display: block; height: 1px;"/>';
                $markup .= $e->getTraceAsString();
                $markup .= '</pre>';
            } else {
                throw $e;
            }
        }

        return $markup;
    }
}


if (!function_exists('municipio_show_posts_pag')) {
    function municipio_show_posts_pag()
    {
        global $wp_query;
        return ($wp_query->max_num_pages > 1);
    }
}

/**
 * Get a posts featured image thumbnail by post id
 * @param  int|null $post_id Post id or null
 * @param array $size Set image width/height eg array(400, 300)
 * @param string $ratio Sets the image ratio
 * @return string            Thumbnail url
 */
if (!function_exists('municipio_get_thumbnail_source')) {
    function municipio_get_thumbnail_source($post_id = null, $size = array(), $ratio = '16:9')
    {

        //Use current id as default
        if (is_null($post_id)) {
            $post_id = get_the_id();
        }

        //Get post thumbnail id (Default value for src)
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $src          = false;

        //Get default vale
        if (isset($size[0]) && isset($size[1])) {
            $src = wp_get_attachment_image_src(
                $thumbnail_id,
                municipio_to_aspect_ratio($ratio, array($size[0], $size[1]))
            );
        } else {
            $src = wp_get_attachment_image_src($thumbnail_id, 'medium');
        }

        //Get url from array
        $src = isset($src[0]) ? $src[0] : false;

        //Force get attachment url (full size)
        if (!$src) {
            $src = wp_get_attachment_url($thumbnail_id);
        }

        return $src;
    }
}

/**
 * Gets the html markup for the logotype
 * @param  string  $type    Logotype source
 * @param  boolean $tooltip Show tooltip or not
 * @return string           HTML markup
 */
if (!function_exists('municipio_get_logotype')) {
    function municipio_get_logotype($type = 'standard', $tooltip = false, $logo_include = true, $tagline = false, $use_text_replacement = true)
    {
        if ($type == '') {
            $type = 'standard';
        }

        $siteName = apply_filters('Municipio/logotype_text', get_bloginfo('name'));
        $logotype = array(
            'standard' => get_theme_mod('logotype', ''),
            'negative' => get_theme_mod('logotype_negative', '')
        );

        // Get the symbol to use (blog name or image)
        if ($use_text_replacement) {
            $logoText = $siteName;
        }

        $classes     = apply_filters('Municipio/logotype_class', array('logotype'));
        $tooltip     = apply_filters('Municipio/logotype_tooltip', $tooltip);
        $taglineHtml = '';

        if ($tagline === true) {
            $taglineText = get_bloginfo('description');

            if (get_field('header_tagline_type', 'option') == 'custom') {
                $taglineText = get_field('header_tagline_text', 'option');
            }

            $taglineHtml = $taglineText;
        }

        // Build the markup
        $logoData = [
            'url'           => home_url(),
            'src'           => $logotype,
            'text'          => $logoText,
            'classList'     => implode(' ', $classes),
            'attributeList' => ($tooltip !== false && !empty($tooltip)) ?
                ['data-tooltip' => $tooltip] : [],
            'tagline'       => $taglineHtml
        ];

        return $logoData;
    }
}

if (!function_exists('municipio_human_datediff')) {
    function municipio_human_datediff($date)
    {
        $diff = human_time_diff(strtotime($date), current_time('timestamp'));
        return $diff;
    }
}

if (!function_exists('municipio_get_mime_link_item')) {
    function municipio_get_mime_link_item($mime)
    {
        $mime = explode('/', $mime);

        if (!isset($mime[0])) {
            return '';
        }

        return 'link-item link-item-' . $mime[0];
    }
}

if (!function_exists('municipio_to_aspect_ratio')) {
    function municipio_to_aspect_ratio($ratio, $size)
    {
        if (count($ratio = explode(":", $ratio)) == 2) {
            $width  = round($size[0]);
            $height = round(($width / $ratio[0]) * $ratio[1]);
        }
        return array($width, $height);
    }
}

if (!function_exists('municiipio_format_currency')) {
    function municiipio_format_currency($value)
    {
        $value = str_split(strrev($value), 3);
        $value = strrev(implode(" ", $value));
        return $value;
    }
}

if (!function_exists('municipio_get_author_full_name')) {
    /**
     * Get url to manage subscriptions page
     * @param  mixed $user User id or login name, default is current logged in user
     * @return string
     */
    function municipio_get_author_full_name($author = null)
    {
        if (is_null($author)) {
            $author = get_the_author_meta('ID');
        }

        if (!empty(get_user_meta($author, 'first_name', true)) && !empty(get_user_meta($author, 'last_name', true))) {
            return get_user_meta($author, 'first_name', true) . ' ' . get_user_meta($author, 'last_name', true);
        }

        return get_user_meta($author, 'nicename', true);
    }
}

if (!function_exists('municipio_post_taxonomies_to_display')) {
    /**
     * Gets "public" (set via theme options) taxonomies and terms for a specific post
     * @param  int    $postId The id of the post
     * @return array          Taxs and terms
     */
    function municipio_post_taxonomies_to_display(int $postId): array
    {
        $stack = array();

        $taxonomies = get_theme_mod(
            'archive_' . get_post_type($postId) . '_taxonomies_to_display',
            false
        );

        if (is_array($taxonomies) && !empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $stack[$taxonomy] = apply_filters(
                    'Municipio/taxonomies_to_display/terms',
                    wp_get_post_terms($postId, $taxonomy),
                    $postId,
                    $taxonomy
                );
            }
        }

        return array_filter($stack);
    }
}

if (!function_exists('municipio_current_url')) {
    /**
     * Gets the current url
     * @return string
     */
    function municipio_current_url()
    {
        return "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    }
}


if (!function_exists('municipio_get_user_profile_url')) {
    /**
     * Get profile url
     * @param  mixed $user User id or login name, default is current logged in user
     * @return string
     */
    function municipio_get_user_profile_url($user = null)
    {
        if (is_null($user)) {
            $user = wp_get_current_user();
        } elseif (is_numeric($user)) {
            $user = get_user_by('ID', $user);
        } elseif (is_string($user)) {
            if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
                $user = get_user_by('email', $user);
            } else {
                $user = get_user_by('slug', $user);
            }
        }

        if (!is_a($user, 'WP_User')) {
            return null;
        }

        return network_site_url('user/' . $user->data->user_login);
    }
}
