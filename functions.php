<?php

define('MUNICIPIO_PATH', get_template_directory() . '/');

require_once MUNICIPIO_PATH . '/library/Bootstrap.php';

// Start recording user login dates
function ws_capture_login_time($user_login, $user) {
	update_user_meta($user->ID, 'last_login', time());
}
add_action('wp_login', 'ws_capture_login_time', 10, 2);

add_filter('manage_users_columns', 'ws_user_last_login_column');
add_filter('manage_users_custom_column', 'ws_last_login_column', 10, 3);

function ws_user_last_login_column($columns) {
	$columns['last_login'] = 'Senaste inloggning';
	return $columns;
}

function ws_last_login_column($output, $column_id, $user_id) {
	if ($column_id == 'last_login') {
		$last_login = get_user_meta($user_id, 'last_login', true);
		$date_format = 'M j, Y';
		$hover_date_format = 'F j, Y, g:i a';
		$output = $last_login
			? '<div title="Last login: ' .
				date($hover_date_format, $last_login) .
				'">' .
				human_time_diff($last_login) .
				'</div>'
			: 'Never';
	}
	return $output;
}

add_filter('manage_users_sortable_columns', 'ws_sortable_last_login_column');
add_action('pre_get_users', 'ws_sort_last_login_column');

function ws_sortable_last_login_column($columns) {
	return wp_parse_args(
		array(
			'last_login' => 'last_login',
		),
		$columns
	);
}

function ws_sort_last_login_column($query) {
	if (!is_admin()) {
		return $query;
	}
	$screen = get_current_screen();
	if (isset($screen->base) && $screen->base !== 'users') {
		return $query;
	}
	if (isset($_GET['orderby']) && $_GET['orderby'] == 'last_login') {
		$query->query_vars['meta_key'] = 'last_login';
		$query->query_vars['orderby'] = 'meta_value';
	}
	return $query;
}

add_action('after_setup_theme', function () {
    load_theme_textdomain('municipio', get_template_directory() . '/languages');
});
