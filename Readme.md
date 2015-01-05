Wordpress wp-login security
===========================
This technique will block all HTTP POST requests to `wp-login.php` while still
allowing users to log in normally.

This technique should not be relied upon as the sole source of security for the
Wordpress admin panel as it is simply security through obscurity.  Using
[strong passwords](http://xkcd.com/936/) and implementing a monitoring/intrusion
detection system should also be part of any site's repertoire.

Theory of Operation
-------------------
Wordpress logins are normally handled through `wp-login.php`.  When a user
points their browser to `wp-login.php`, they are presented with a form that will
POST to the same file.  When a POST request is received, Wordpress fires up its
authentication mechanism to attempt to authenticate the user.  Brute force
attacks usually bypass the GET request and instead send one or more POST
requests to `wp-login.php`.

This technique will block **all** POST requests to `wp-login.php`.  Normally
this would make it impossible for trusted users to log in to Wordpress, however
this technique changes the login form to POST to a separate file.  This separate
file is a simple PHP `include` call, so all of Wordpress' normal authentication
logic stays intact.  Since credentials are only accepted via POST (see the first
few lines of the `wp_signon()` function in `wp-includes/user.php` and how it
gets called in `wp-login.php`) and not GET, this has no effect on the login form
itself.

This technique has the added benefit of reducing server load since most requests
will be rejected instead of running queries on the database.

Alternatives
------------
A very effective way to combat brute force attacks without modifying Wordpress
is with [basic](http://en.wikipedia.org/wiki/Basic_access_authentication) or 
[digest](http://en.wikipedia.org/wiki/Digest_access_authentication) access
authentication. Bots will always receive a *401 Not Authorized* unless they
supply the correct credentials.  However, this method is cumbersome and
potentially confusing as the user(s) need to remember another set of
credentials.

There are also plugins that use time-based restrictions (e.g. no logins are
permitted between 1am and 5am).

Installation
------------
Add this to your theme's `functions.php`:
```php
add_filter('site_url', function($url, $path, $scheme, $blog_id) {
	if($scheme == 'login_post')
		return str_replace('wp-login.php', 'wp-login2.php', $url);
	return $url;
}, 10, 4);
```
Create a new file `wp-login2.php` and add the following contents:
```php
<?php
include 'wp-login.php';
```
And `.htaccess`:
```ApacheConf
RewriteEngine On
RewriteCond %{REQUEST_METHOD} POST
RewriteCond %{REQUEST_FILENAME} wp-login.php
RewriteRule ^ / [F]
```
If you have access to your site's `httpd.conf`, place the above lines in that
file instead.

Additional Considerations
-------------------------
A determined hacker (or one paying attention to their log files) will undoubtly
notice that all of their requests are being rejected.  Inspecting the HTML of
the login page will make it immediately apparent that requests are being sent to
`wp-login2.php` so the hacker can simply update their script to point to that
file.

One way to combat this is to dynamically change the URL to POST to.  The
filename could be a function based on the date and time or some other means in
which the next name is not easily guessable.  However, even this isn't foolproof
as a determined hacker can easily scrape the contents of the login page to find
the current filename.  Still, it can help curb the amount (or at least the 
rate) of attacks from semi-determined hackers.

License
-------
This code is provided for free and released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or distribute this
software, either in source code form or as a compiled binary, for any purpose,
commercial or non-commercial, and by any means.

In jurisdictions that recognize copyright laws, the author or authors of this
software dedicate any and all copyright interest in the software to the public
domain. We make this dedication for the benefit of the public at large and to
the detriment of our heirs and successors. We intend this dedication to be an
overt act of relinquishment in perpetuity of all present and future rights to
this software under copyright law.