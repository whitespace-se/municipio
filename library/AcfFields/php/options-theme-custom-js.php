<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_573184999aa2c',
    'title' => __('Custom JS', 'municipio'),
    'fields' => array(
        0 => array(
            'key' => 'field_5731849ed0729',
            'label' => __('JavaScript', 'municipio'),
            'name' => 'custom_js_input',
            'type' => 'textarea',
            'instructions' => __('Enter your JS-code here. It will be wrapped with "script" tags and included on all pages.', 'municipio'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'new_lines' => '',
            'maxlength' => '',
            'placeholder' => '',
            'rows' => 20,
            'readonly' => 0,
            'disabled' => 0,
        ),
        1 => array(
            'key' => 'field_5d9b4091e1fa0',
            'label' => __('Script Tags', 'municipio'),
            'name' => 'custom_js_tags',
            'type' => 'repeater',
            'instructions' => __('Enter your JS-code here. It will NOT be wrapped with "script" tags and included on all pages.', 'municipio'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => 0,
            'max' => 0,
            'layout' => 'row',
            'button_label' => '',
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_5d9b40f4e1fa2',
                    'label' => __('JavaScript', 'municipio'),
                    'name' => 'custom_js_tags_input',
                    'type' => 'textarea',
                    'instructions' => __('Enter your Script tags here. eg. <script>console.log(\'hello world\');</script>', 'municipio'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 5,
                    'new_lines' => '',
                ),
                1 => array(
                    'key' => 'field_5d9b428dd29cf',
                    'label' => __('Location', 'municipio'),
                    'name' => 'custom_js_tags_location',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'head' => __('Include script in head', 'municipio'),
                        'footer' => __('Include script in footer', 'municipio'),
                    ),
                    'default_value' => array(
                        0 => 'footer',
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                2 => array(
                    'key' => 'field_5d9b4175e1fa3',
                    'label' => __('Disable script', 'municipio'),
                    'name' => 'custom_js_tags_disabled',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
            ),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'acf-options-css',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}