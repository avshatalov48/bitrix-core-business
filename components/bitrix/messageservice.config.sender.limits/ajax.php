<?
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");

$siteId = '';
if (isset($_REQUEST['site_id']) && is_string($_REQUEST['site_id']))
	$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site_id']), 0, 2);

if ($siteId)
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('messageservice'))
{
	return;
}
/** @var \CMain $APPLICATION */

$isAdmin = \Bitrix\MessageService\Context\User::isAdmin();
if (!$isAdmin || !check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	return;
}

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
$sendResponse = function($result)
{
	$GLOBALS['APPLICATION']->RestartBuffer();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	if(!empty($result))
	{
		echo CUtil::PhpToJSObject($result);
	}
	if(!defined('PUBLIC_AJAX_MODE'))
	{
		define('PUBLIC_AJAX_MODE', true);
	}
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
	die();
};

$action = isset($_POST['action']) ? (string)$_POST['action'] : '';

if ($action === 'set_limits')
{
	$limits = (array)$_POST['limits'];
	foreach ($limits as $limit => $value)
	{
		list ($senderId, $fromId) = explode(':', $limit);
		\Bitrix\MessageService\Sender\Limitation::setDailyLimit($senderId, $fromId, $value);
	}

	$sendResponse(array(
		'success' => true,
		'errors' => array()
	));
}
elseif ($action === 'set_retry_time')
{
	$time = (array)$_POST['retry_time'];

	\Bitrix\MessageService\Sender\Limitation::setRetryTime($time);
	$sendResponse(array(
		'success' => true,
		'errors' => array()
	));
}
$sendResponse(array('errors'=> array('Unknown action.')));