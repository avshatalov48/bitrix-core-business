<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);

use Bitrix\Main\Loader,
	Bitrix\Landing;

if (isset($_REQUEST['site']) && is_string($_REQUEST['site']))
{
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['site']))
	{
		define('SITE_ID', $_REQUEST['site']);
	}
}
if (isset($_REQUEST['template']) && is_string($_REQUEST['template']))
{
	$template = preg_replace(
		"/[^a-zA-Z0-9_.]/",
		"",
		$_REQUEST['template']
	);
	if ($template !== '')
	{
		define('SITE_TEMPLATE_ID', $template);
	}
}
if (isset($_REQUEST['admin_section']) && $_REQUEST['admin_section'] === 'Y')
{
	define('ADMIN_SECTION', true);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (Loader::includeModule('landing'))
{
	$selector = new Landing\Source\Selector();
	$selector->showSourceFilterByRequest();
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');