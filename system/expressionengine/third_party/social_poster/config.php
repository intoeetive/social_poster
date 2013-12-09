<?php

if ( ! defined('SOCIAL_POSTER_ADDON_NAME'))
{
	define('SOCIAL_POSTER_ADDON_NAME',         'Social Poster');
	define('SOCIAL_POSTER_ADDON_DESC',      'Automatic updates to social networks upon user actions');
    define('SOCIAL_POSTER_ADDON_VERSION',      '1.0');
}

$config['name'] = SOCIAL_POSTER_ADDON_NAME;
$config['version'] = SOCIAL_POSTER_ADDON_VERSION;

$config['nsm_addon_updater']['versions_xml'] = 'http://www.intoeetive.com/index.php/update.rss/150';