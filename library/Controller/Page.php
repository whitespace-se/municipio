<?php

namespace Municipio\Controller;

/**
 * Class Single
 * @package Municipio\Controller
 */
class Page extends \Municipio\Controller\Singular
{
    public function init()
    {
        // Show or hide sidebars
        $this->data['showSidebars'] = true;
    }
}
