<?php

namespace Municipio\Theme;

class RegisterUtility
{
    public function __construct()
    {
        \BladeComponentLibrary\Register::setCachePath(
            WP_CONTENT_DIR . '/uploads/cache/blade-cache/utility'
        );

        \BladeComponentLibrary\Register::addViewPath(
            MUNICIPIO_PATH . 'views/utility'
        ); 

        \BladeComponentLibrary\Register::addControllerPath(
            MUNICIPIO_PATH . 'library/Controller/Utility/'
        );

        \BladeComponentLibrary\Register::add(
            'button',
            [
                'isPrimary' => true,
                'isDisabled' => false, 
                'isOutlined' => true,

                'label' => "Button text",
                'href' => "https://google.se",

                'target' => "_self"
            ],
            'button.blade.php' // You can leave this out, it will automatically be generated from slug. 
        );

        \BladeComponentLibrary\Register::add(
            'date',
            [
                'hasTime' => false,
                'hasDate' => true, 
                'isHumanReadable' => true
            ],
            'date-time.blade.php' 
        );
    }
}