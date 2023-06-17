<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (defined('IM_COMPONENT_INIT'))
	return;
else
	define("IM_COMPONENT_INIT", true);

if (intval($USER->GetID()) <= 0)
	return;

if (!CModule::IncludeModule('im'))
	return;

if (\Bitrix\Im\Settings::isBetaActivated())
{
	$arResult['MESSENGER_V2'] = true;

	if (CModule::IncludeModule('disk'))
	{
		CJSCore::Init([
			'file_dialog',
			'im.integration.viewer'
		]);
	}
	CModule::IncludeModule('voximplant');

	$this->IncludeComponentTemplate();
	return;
}

CModule::IncludeModule('voximplant');
CModule::IncludeModule('disk');

$arParams["DESKTOP"] = isset($arParams['DESKTOP']) && $arParams['DESKTOP'] == 'Y'? 'Y': 'N';

$arResult = Array();

$isFullscreen = $arParams['FULLSCREEN'] ?? null;

if ($arParams['CONTEXT'] == 'DESKTOP' || $arParams['DESKTOP'] == 'Y')
{
	$darkClass = \CIMSettings::GetSetting(CIMSettings::SETTINGS, 'isCurrentThemeDark')? 'bx-messenger-dark': '';
	$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "im-desktop $darkClass");

	CIMMessenger::SetDesktopVersion(empty($_GET['BXD_API_VERSION'])? 0 : $_GET['BXD_API_VERSION']);
	CIMMessenger::SetDesktopStatusOnline(null, false);

	$arParams["DESIGN"] = "DESKTOP";
	$arResult["CONTEXT"] = "DESKTOP";

	$event = new \Bitrix\Main\Event("im", "onDesktopStart", array('USER_ID' => $USER->GetID()));
	$event->send();
}
else if ($arParams["CONTEXT"] == "FULLSCREEN" || $isFullscreen)
{
	$APPLICATION->SetPageProperty("BodyClass", "bx-im-fullscreen bx-language-".LANGUAGE_ID);
	if (!isset($arParams["DESIGN"]))
	{
		$arParams["DESIGN"] = "DESKTOP";
	}
	$arResult["CONTEXT"] = "FULLSCREEN";
}
else if ($arParams["CONTEXT"] == "PAGE")
{
	$arResult["CONTEXT"] = "PAGE";
	$arParams["DESIGN"] = "DESKTOP";
}
else if ($arParams["CONTEXT"] == "POPUP-FULLSCREEN")
{
	$arResult["CONTEXT"] = "POPUP-FULLSCREEN";
	$arParams["DESIGN"] = "DESKTOP";
}
else if ($arParams["CONTEXT"] == "DIALOG")
{
	$arResult["CONTEXT"] = "DIALOG";
	$arParams["DESIGN"] = "DESKTOP";
}
else if ($arParams["CONTEXT"] == "LINES")
{
	$arResult["CONTEXT"] = "LINES";
	$arParams["DESIGN"] = "DESKTOP";
}
else
{
	$arResult["CONTEXT"] = "MESSENGER";
	$arResult["DESIGN"] = "POPUP";
}

// Counters
$arResult["COUNTERS"] = CUserCounter::GetValues($USER->GetID(), SITE_ID);

CIMContactList::SetOnline(null, $arResult["CONTEXT"] != "DESKTOP");

$arSettings = CIMSettings::Get();
$arResult['SETTINGS'] = $arSettings['settings'];

if (isset($arParams['DESIGN']))
{
	$arResult["DESIGN"] = $arParams['DESIGN'];
}

$arParams["INIT"] = 'Y';
$arParams["DESKTOP_LINK_OPEN"] = 'N';

// Exchange
$arResult["PATH_TO_USER_MAIL"] = "";
$arResult["MAIL_COUNTER"] = 0;
if ($arParams["INIT"] == 'Y')
{
	if (CIMMail::IsExternalMailAvailable())
	{
		$arResult["PATH_TO_USER_MAIL"] = $arParams['PATH_TO_SONET_EXTMAIL'] ?? null;
		$arResult["MAIL_COUNTER"] = (int)($arResult["COUNTERS"]["mail_unseen"] ?? null);
	}
	else if (CModule::IncludeModule("dav"))
	{
		$ar = CDavExchangeMail::GetTicker($GLOBALS["USER"]);
		if ($ar !== null)
		{
			$arResult["PATH_TO_USER_MAIL"] = $ar["exchangeMailboxPath"];
			$arResult["MAIL_COUNTER"] = intval($ar["numberOfUnreadMessages"]);
		}
	}
}
// Message & Notify

$arResult['SETTINGS_NOTIFY_BLOCKED'] = CIMSettings::GetSimpleNotifyBlocked();

$arResult['CURRENT_USER'] = \CIMContactList::GetUserData(Array(
	'ID' => $USER->GetID(),
	'PHONES' => 'Y',
	'SHOW_ONLINE' => 'N',
	'EXTRA_FIELDS' => 'Y',
	'DATE_ATOM' => 'Y'
))['users'][$USER->GetID()];

if ($arParams["INIT"] == 'Y')
{
	$arSmile = CIMMessenger::PrepareSmiles();
	$arResult['SMILE'] = $arSmile['SMILE'];
	$arResult['SMILE_SET'] = $arSmile['SMILE_SET'];

	if ($arResult["CONTEXT"] == "LINES")
	{
		$arResult['PATH_TO_IM'] = '/online/im.ajax.php';
		$arResult['PATH_TO_CALL'] = '/online/call.ajax.php';
		$arResult['PATH_TO_FILE'] = '/online/file.ajax.php';
	}
	else if ($arResult["CONTEXT"] == "DESKTOP")
	{
		$arResult['PATH_TO_IM'] = '/desktop_app/im.ajax.php';
		$arResult['PATH_TO_CALL'] = '/desktop_app/call.ajax.php';
		$arResult['PATH_TO_FILE'] = '/desktop_app/file.ajax.php';
	}

	if (isset($arParams['CURRENT_TAB']))
	{
		$_REQUEST['IM_DIALOG'] = $arParams['CURRENT_TAB'];
		$arResult['CURRENT_TAB'] = $arParams['CURRENT_TAB'];
	}
}

$arResult['BOT'] = \Bitrix\Im\Bot::getListForJs();
$arResult['COMMAND'] = \Bitrix\Im\Command::getListForJs();
$arResult['TEXTAREA_ICON'] = \Bitrix\Im\App::getListForJs();

$arResult['INIT'] = $arParams['INIT'];
$arResult['DESKTOP'] = $arResult["CONTEXT"] == "DESKTOP"? 'true': 'false';
$arResult['PHONE_ENABLED'] = CIMMessenger::CheckPhoneStatus() && CIMMessenger::CanUserPerformCalls();
$arResult['OL_OPERATOR'] = CModule::IncludeModule('imopenlines') && \Bitrix\ImOpenLines\Config::isOperator($USER->GetID());
$arResult['DESKTOP_LINK_OPEN'] = $arParams['DESKTOP_LINK_OPEN'] == 'Y'? 'true': 'false';
$arResult['PATH_TO_USER_PROFILE_TEMPLATE'] = CIMContactList::GetUserPath();
$arResult['PATH_TO_USER_PROFILE'] = CIMContactList::GetUserPath($USER->GetId());
$arResult['PATH_TO_LF'] = IsModuleInstalled('intranet') && \Bitrix\Main\IO\File::isFileExists(\Bitrix\Main\Application::getDocumentRoot().'/stream/index.php')? '/stream/': '/';

$arResult['TURN_SERVER'] = COption::GetOptionString('im', 'turn_server');
$arResult['TURN_SERVER_FIREFOX'] = COption::GetOptionString('im', 'turn_server_firefox');
$arResult['TURN_SERVER_LOGIN'] = COption::GetOptionString('im', 'turn_server_login');
$arResult['TURN_SERVER_PASSWORD'] = COption::GetOptionString('im', 'turn_server_password');

$initJs = 'im_web';
$promoType = \Bitrix\Im\Promotion::DEVICE_TYPE_BROWSER;
if ($arResult["CONTEXT"] == 'DESKTOP')
{
	$initJs = 'im_desktop';
	$promoType = \Bitrix\Im\Promotion::DEVICE_TYPE_DESKTOP;
}
else if ($arResult["DESIGN"] == 'DESKTOP')
{
	$initJs = 'im_page';
}

$arResult['PROMO'] = \Bitrix\Im\Promotion::getActive($promoType);
$arResult['LIMIT'] = \Bitrix\Im\Limit::getTypesForJs();

CJSCore::Init($initJs);
\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.buttons.icons']);

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;