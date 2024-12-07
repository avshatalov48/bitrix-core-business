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
	define('SITE_ID', $siteId);

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

if (!isset($_REQUEST['sender_id']))
{
	return;
}

$action = isset($_POST['action']) ? (string)$_POST['action'] : '';
/** @var \Bitrix\MessageService\Sender\BaseConfigurable $sender */
$sender = \Bitrix\MessageService\Sender\SmsManager::getSenderById($_REQUEST['sender_id']);

if (!$sender || !$sender->isConfigurable())
{
	return;
}

if ($action === 'registration')
{
	$registerResult = $sender->register($_POST);
	$sendResponse(array(
		'success' => $registerResult->isSuccess(),
		'errors' => $registerResult->getErrorMessages()
	));
}
elseif ($action === 'demo')
{
	$registerResult = $sender->registerDemo($_POST['info']);
	$sendResponse(array(
		'success' => $registerResult->isSuccess(),
		'errors' => $registerResult->getErrorMessages()
	));
}
else if ($action === 'confirmation' && !$sender->isConfirmed())
{
	$confirmResult = $sender->confirmRegistration(array(
		'confirm' => $_POST['confirm']
	));

	$sendResponse(array(
		'success' => $confirmResult->isSuccess(),
		'errors' => $confirmResult->getErrorMessages()
	));
}
else if ($action === 'send_message'
	&& $sender->canUse()
	&& $sender->getId() === 'smsru'
)
{
	$ownerInfo = $sender->getOwnerInfo();
	$message = \Bitrix\MessageService\Sender\SmsManager::createMessage(array(
		'MESSAGE_TO' => $ownerInfo['phone'],
		'MESSAGE_BODY' => $_POST['text'],
		'MESSAGE_FROM' => $sender->getDefaultFrom()
	), $sender);

	$sendResult = $message->sendDirectly();

	$sendResponse(array(
		'success' => $sendResult->isSuccess(),
		'errors' => $sendResult->getErrorMessages()
	));
}
else if ($action === 'send_confirmation')
{
	$sendConfirmationResult = $sender->sendConfirmationCode();
	$sendResponse(array(
		'success' => $sendConfirmationResult->isSuccess(),
		'errors' => $sendConfirmationResult->getErrorMessages()
	));
}
else if ($action === 'disable_demo')
{
	$fromList = $sender->sync()->getFromList();
	//Try to find alphanumeric from
	foreach ($fromList as $item)
	{
		if (!preg_match('#^[0-9]+$#', $item['id']))
		{
			$sender->disableDemo();
			$sendResponse(array(
				'success' => true
			));
			break;
		}
	}
	$sendResponse(array(
		'success' => false,
		'errors' => array(\Bitrix\Main\Localization\Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_DISABLE_DEMO_ERROR'))
	));
}
else if ($action === 'clear_options')
{
	$sender->clearOptions();
	$sendResponse(array(
		'success' => true
	));
}
$sendResponse(array('errors'=> array('Unknown action.')));