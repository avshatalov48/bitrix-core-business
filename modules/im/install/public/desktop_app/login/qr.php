<?php
/**
 * @global CUser $USER
 */
use Bitrix\Main\Authentication\ApplicationPasswordTable as ApplicationPasswordTable;

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
{
	header('Access-Control-Allow-Methods: POST, HEAD, OPTIONS');
	header('Access-Control-Max-Age: 60');
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
	exit;
}

define("BX_SKIP_USER_LIMIT_CHECK", true);
define("ADMIN_SECTION",false);
define("RESPONSE_JSON",false);

require_once($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/desktop_app/login/helper.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$controller = new \Bitrix\Main\Controller\QrCodeAuth();
if (!$controller->isAllowed())
{
	sendResponse(
		[
			"success" => false,
			"code" => "qrcodeauth_error",
			"reason" => "QR code authentication is disabled"
		],
		"404 Not found"
	);
}

if (!$USER->IsAuthorized())
{
	sendResponse(
		[
			"success" => false,
			"code" => "user_unauthorized",
			"reason" => "User is not authorized"
		],
		"401 Unauthorized"
	);
}

if ($_SERVER["REQUEST_METHOD"] == "HEAD")
{
	header('X-Bitrix-Csrf-Token: '.bitrix_sessid());
	exit;
}

if (!check_bitrix_sessid())
{
	sendResponse(
		[
			"success" => false,
			"code" => "csrf_token_mismatch",
			"reason" => "Bitrix csrf-token mismatch"
		],
		"401 Unauthorized"
	);
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	sendResponse(
		[
			"success" => false,
			"code" => "module_not_installed",
			"reason" => "Messenger module is not installed"
		],
		"403 Forbidden"
	);
}

if (!\isAccessAllowed())
{
	sendResponse(
		[
			"success" => false,
			"code" => "blocked_type",
			"reason" => "Access denied for this type of user"
		],
		"401 Unauthorized"
	);
}

\Bitrix\Main\Localization\Loc::loadMessages($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public/desktop_app/login/index.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$mark = $request->getHeader('X-Desktop-Mark');
$account = $request->getHeader('X-Desktop-Account');
if (empty($mark) || empty($account))
{
	sendResponse(
		[
			"success" => false,
			"code" => "desktop_environment_error",
			"reason" => "Error getting desktop environment"
		],
		"403 Forbidden"
	);
}

$code = md5($mark . $account);

$orm = ApplicationPasswordTable::getList([
	'select' => ['ID'],
	'filter' => [
		'=USER_ID' => $USER->GetID(),
		'=CODE' => $code
	]
]);
if ($row = $orm->fetch())
{
	ApplicationPasswordTable::delete($row['ID']);
}

$password = ApplicationPasswordTable::generatePassword();

$result = ApplicationPasswordTable::add(array(
	'USER_ID' => $USER->GetID(),
	'APPLICATION_ID' => 'desktop',
	'PASSWORD' => $password,
	'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
	'CODE' => $code,
	'COMMENT' => GetMessage('DESKTOP_APP_GENERATOR'),
	'SYSCOMMENT' => GetMessage('DESKTOP_APP_TITE'),
));
if(!$result->isSuccess())
{
	sendResponse(
		[
			"success" => false,
			"code" => "application_password_error",
			"reason" => "Unable to register app password"
		],
		"403 Forbidden"
	);
}

sendResponse([
	"success" => true,
	"desktopRevision" => \Bitrix\Im\Revision::getDesktop(),
	"userId" => (int)$USER->GetID(),
	"userLogin" => $USER->GetLogin(),
	"appPassword" => $password
]);
