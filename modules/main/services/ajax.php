<?php
define('NOT_CHECK_FILE_PERMISSIONS', true);
define('PUBLIC_AJAX_MODE', true);
define('NO_KEEP_STATISTIC', 'Y');
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

$siteId = '';
if (isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']))
{
	$siteId = $_REQUEST['SITE_ID'];
}
if ($siteId === '' && isset($_SERVER['HTTP_X_BITRIX_SITE_ID']) && is_string($_SERVER['HTTP_X_BITRIX_SITE_ID']))
{
	$siteId = $_SERVER['HTTP_X_BITRIX_SITE_ID'];
}
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}
if (isset($_REQUEST['admin_section']) && $_REQUEST['admin_section'] === 'Y')
{
	define('ADMIN_SECTION', true);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Application::getInstance()->run();