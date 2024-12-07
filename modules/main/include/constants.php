<?php

// deprecated
define('MODULE_NOT_FOUND', 0);
define('MODULE_INSTALLED', 1);
define('MODULE_DEMO', 2);
define('MODULE_DEMO_EXPIRED', 3);

// also deprecated
define('BX_RESIZE_IMAGE_PROPORTIONAL_ALT', 0);
define('BX_RESIZE_IMAGE_PROPORTIONAL', 1);
define('BX_RESIZE_IMAGE_EXACT', 2);

// BX_UTF can be defined in dbconn.php
if (!defined('BX_UTF'))
{
	define('BX_UTF', true);
}
// deprecated
define('BX_UTF_PCRE_MODIFIER', 'u');

/**
 * All constants can be defined in dbconn.php
 * @todo Should be in .settings.php
 */

/** @var int | bool $_hour */
$_hour = 3600;
/** @var int | bool $_day */
$_day = 86400;
/** @var int | bool $_year */
$_year = 31536000;

if (!defined('CACHED_b_lang'))
{
	define('CACHED_b_lang', $_hour);
}
if (!defined('CACHED_b_option'))
{
	define('CACHED_b_option', $_hour);
}
if (!defined('CACHED_b_event'))
{
	define('CACHED_b_event', $_hour);
}
if (!defined('CACHED_b_agent'))
{
	define('CACHED_b_agent', $_hour);
}
if (!defined('CACHED_menu'))
{
	define('CACHED_menu', $_hour);
}
if (!defined('CACHED_b_file'))
{
	define('CACHED_b_file', $_hour);
}
if (!defined('CACHED_b_file_bucket_size'))
{
	define('CACHED_b_file_bucket_size', 100);
}
if (!defined('CACHED_b_user_field'))
{
	define('CACHED_b_user_field', $_hour);
}
if (!defined('CACHED_b_user_field_enum'))
{
	define('CACHED_b_user_field_enum', $_hour);
}
if (!defined('CACHED_b_rating'))
{
	define('CACHED_b_rating', $_hour);
}
if (!defined('CACHED_b_rating_vote'))
{
	define('CACHED_b_rating_vote', $_day);
}
if (!defined('CACHED_b_rating_bucket_size'))
{
	define('CACHED_b_rating_bucket_size', 100);
}
if (!defined('CACHED_b_user_access_check'))
{
	define('CACHED_b_user_access_check', $_hour);
}
if (!defined('CACHED_b_user_counter'))
{
	define('CACHED_b_user_counter', $_hour);
}
if (!defined('CACHED_b_smile'))
{
	define('CACHED_b_smile', $_year);
}
if (!defined('TAGGED_user_card_size'))
{
	define('TAGGED_user_card_size', 100);
}

define('BX_AJAX_PARAM_ID', 'bxajaxid');

if (!defined('BX_FILE_PERMISSIONS'))
{
	define('BX_FILE_PERMISSIONS', 0644);
}
if (!defined('BX_DIR_PERMISSIONS'))
{
	define('BX_DIR_PERMISSIONS', 0755);
}

if (!defined('BX_CRONTAB_SUPPORT'))
{
	define('BX_CRONTAB_SUPPORT', defined('BX_CRONTAB'));
}

define("AM_PM_NONE", false);
define("AM_PM_UPPER", 1);
define("AM_PM_LOWER", 2);
