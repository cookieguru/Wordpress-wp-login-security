<?php
//Add these lines to your theme's functions.php:
add_filter('site_url', function($url, $path, $scheme, $blog_id) {
	if($scheme == 'login_post')
		return 'wp-login2.php';
	return $url;
}, 10, 4);