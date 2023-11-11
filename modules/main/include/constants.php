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
define('BX_UTF_PCRE_MODIFIER', (defined('BX_UTF') ? 'u' : ''));

/**
 * All constants can be defined in dbconn.php
 * @todo Should be in .settings.php
 */
if (!defined('CACHED_b_lang'))
{
	define('CACHED_b_lang', 3600);
}
if (!defined('CACHED_b_option'))
{
	define('CACHED_b_option', 3600);
}
if (!defined('CACHED_b_lang_domain'))
{
	define('CACHED_b_lang_domain', 3600);
}
if (!defined('CACHED_b_site_template'))
{
	define('CACHED_b_site_template', 3600);
}
if (!defined('CACHED_b_event'))
{
	define('CACHED_b_event', 3600);
}
if (!defined('CACHED_b_agent'))
{
	define('CACHED_b_agent', 3660);
}
if (!defined('CACHED_menu'))
{
	define('CACHED_menu', 3600);
}
if (!defined('CACHED_b_file'))
{
	define('CACHED_b_file', false);
}
if (!defined('CACHED_b_file_bucket_size'))
{
	define('CACHED_b_file_bucket_size', 100);
}
if (!defined('CACHED_b_group'))
{
	define('CACHED_b_group', 3600);
}
if (!defined('CACHED_b_user_field'))
{
	define('CACHED_b_user_field', 3600);
}
if (!defined('CACHED_b_user_field_enum'))
{
	define('CACHED_b_user_field_enum', 3600);
}
if (!defined('CACHED_b_task'))
{
	define('CACHED_b_task', 3600);
}
if (!defined('CACHED_b_task_operation'))
{
	define('CACHED_b_task_operation', 3600);
}
if (!defined('CACHED_b_rating'))
{
	define('CACHED_b_rating', 3600);
}
if (!defined('CACHED_b_rating_vote'))
{
	define('CACHED_b_rating_vote', 86400);
}
if (!defined('CACHED_b_rating_bucket_size'))
{
	define('CACHED_b_rating_bucket_size', 100);
}
if (!defined('CACHED_b_user_access_check'))
{
	define('CACHED_b_user_access_check', 3600);
}
if (!defined('CACHED_b_user_counter'))
{
	define('CACHED_b_user_counter', 3600);
}
if (!defined('CACHED_b_group_subordinate'))
{
	define('CACHED_b_group_subordinate', 31536000);
}
if (!defined('CACHED_b_smile'))
{
	define('CACHED_b_smile', 31536000);
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
