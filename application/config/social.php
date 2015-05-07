<?php //echo 11;
/*****
 * social media application configuration
 ****/

/* Google */
$config['g_appid'] = '650410450224-ls33eho5vtruc92or7jipbs38rv2bkgr.apps.googleusercontent.com';
$config['g_secret'] = 'lZmfi0c9208dWX0mkz14osed';

/* Google Import Live */
$config['g_import_appid'] = '650410450224-ls33eho5vtruc92or7jipbs38rv2bkgr.apps.googleusercontent.com';
$config['g_import_secret'] = 'lZmfi0c9208dWX0mkz14osed';

/* GoogleImport Localhost */
$config['g_local_appid'] = '650410450224-lnfso26m1onebd16kv8dl0f0c61pdark.apps.googleusercontent.com';
$config['g_local_secret'] = 'uFvHtJ4jaAk3qfkKbM_h1NaD';

/* Google Invite Friend*/
$config['g_invite_appid'] = '650410450224-gs832e2ss9797vl8ht5tm9t41uo2jpi4.apps.googleusercontent.com';
$config['g_invite_secret'] = 'vNlsZuOoiWQU0iQ1m0vpQkw0';

/* Facebook */
$config['fb_appid'] = '533415556794626';//'381988758618815';
$config['fb_secret'] = 'ec862ca72e812659937e7525db226272';//'e328efd89ae419ddd70234a72615fbc0';

/* Twitter */
$config['tw_consumer_key'] = 'Mb709s2GO1GjzHGxSmnjhcob2';
$config['tw_consumer_secret'] = 'iY7t2AMo1At4y3ClK6o62ZOU3MLEwHPygYT5OiWGgTfgumJ91w';
$config['tw_callback_url'] = $config['site_url'] . "social/social/twittercallback";

/* Linkedin */
$config['in_consumer_key'] = '75qe8mf4iemwpf';//'75n8676c4gsr36';
$config['in_consumer_secret'] = 'cb765SIdwEW1ifEc';//'xgYHUqOUM6QmJdqL';
$config['in_callback_url'] = $config['site_url']. "social/linkedinuser/linkedin_submit";
$config['linkedin_submit'] = $config['site_url']. "user/linkedin_submit";