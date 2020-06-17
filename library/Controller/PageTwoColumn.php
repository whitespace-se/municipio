<?php

namespace Municipio\Controller;

class PageTwoColumn extends \Municipio\Controller\Singular
{
    public function init()
    {
        $this->data['template'] = [
            'xs' => [1,13],
            'sm' => [1,13],
            'md' => [1,13],
            'lg' => [1,13],
            'xl' => [1,13]
        ];

        $this->data['template']['sidebar'] = true;

        //Get post data
        $this->data['post'] = \Municipio\Helper\Post::preparePostObject(get_post());

        //Get feature image data
        $this->data['feature_image'] = $this->getFeatureImage($this->data['post']->id);

        //Get Author data
        $this->data['authorName'] = $this->getAuthor($this->data['post']->id)->name;
        $this->data['authorAvatar'] = $this->getAuthor($this->data['post']->id)->avatar;

        //Get published data
        $this->data['publishedDate'] = $this->getPostDates($this->data['post']->id)->published;
        $this->data['updatedDate'] = $this->getPostDates($this->data['post']->id)->updated;

        $this->data['publishTranslations'] = array(
            'updated'   => __('Last updated', 'municipio'),
            'published' => __('Published date', 'municipio'),
            'by'        => __('Published by', 'municipio'),
            'on'        => __('on', 'municipio'),
        );

        //Comments
        $this->data['comments'] = get_comments(array(
            'post_id'   => $this->data['post']->id,
            'order'     => get_option('comment_order')
        ));

        //Replies
        $this->data['replyArgs'] = array(
            'add_below'  => 'comment',
            'respond_id' => 'respond',
            'reply_text' => __('Reply'),
            'login_text' => __('Log in to Reply'),
            'depth'      => 1,
            'before'     => '',
            'after'      => '',
            'max_depth'  => get_option('thread_comments_depth')
        );

        //Post settings
        $this->data['settingItems'] = apply_filters_deprecated('Municipio/blog/post_settings', array($this->data['post']), '3.0', 'Municipio/blog/postSettings');

        //Should link author page
        $this->data['authorPages'] = apply_filters('Municipio/author/hasAuthorPage', false);

    }
}