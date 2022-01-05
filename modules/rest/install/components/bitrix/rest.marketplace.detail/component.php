<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Rest\Engine\Access;
use Bitrix\Main\ModuleManager;

if(!CModule::IncludeModule("rest"))
{
	return;
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$arResult['SUBSCRIPTION_AVAILABLE'] = \Bitrix\Rest\Marketplace\Client::isSubscriptionAvailable();
$arResult['SUBSCRIPTION_BUY_URL'] = \Bitrix\Rest\Marketplace\Url::getSubscriptionBuyUrl();
$arResult['ANALYTIC_FROM'] = '';
if (!empty($request->get('from')))
{
	$arResult['ANALYTIC_FROM'] = htmlspecialcharsbx($request->get('from'));
}

$arParams['APP'] = !empty($arParams['APP']) ? $arParams['APP'] : $_GET['app'];

$arResult['REST_ACCESS'] = Access::isAvailable($arParams['APP']) && Access::isAvailableCount(Access::ENTITY_TYPE_APP, $arParams['APP']);

$ver = false;

$arResult['CHECK_HASH'] = false;
$arResult['INSTALL_HASH'] = false;

if (isset($_GET["ver"]) && intval($_GET["ver"]) && isset($_GET["check_hash"]) && isset($_GET['install_hash']))
{
	$checkHash = $_GET['check_hash'];
	$check = md5(rtrim(CHTTP::URN2URI('/'), '/').'|'.$_GET['ver'].'|'.$arParams['APP']);

	if($checkHash === $check)
	{
		$ver = intval($_GET["ver"]);

		$arResult['CHECK_HASH'] = $check;
		$arResult['INSTALL_HASH'] = $_GET['install_hash'];

		$arResult['START_INSTALL'] = isset($_GET["install"]) && $_GET["install"] == "Y";
	}
}

$dbApp = \Bitrix\Rest\AppTable::getList(array(
	'filter' => array("=CODE" => $arParams["APP"])
));
$ar = $dbApp->fetch();

if($ver === false && $ar['ACTIVE'] === 'N' && $ar['STATUS'] === \Bitrix\Rest\AppTable::STATUS_PAID)
{
	$ver = intval($ar['VERSION']);
}
elseif (
	$arResult['START_INSTALL']
	&& $ar['ID'] > 0
	&& $ar['ACTIVE'] === \Bitrix\Rest\AppTable::ACTIVE
	&& $ar['INSTALLED'] === \Bitrix\Rest\AppTable::INSTALLED
	&& (int)$ar['VERSION'] === (int)$_GET['ver']
)
{
	$arResult['START_INSTALL'] = false;
}

if(
	($ar['ID'] > 0 && $ar['ACTIVE'] === \Bitrix\Rest\AppTable::ACTIVE && $ar['INSTALLED'] === \Bitrix\Rest\AppTable::INSTALLED)
	|| ($request->isPost() && $request['install'] && check_bitrix_sessid())
	|| ($ver > 0 && isset($arResult['CHECK_HASH']) && isset($arResult['INSTALL_HASH']))
)
{
	$arApp = \Bitrix\Rest\Marketplace\Client::getApp($arParams['APP'], $ver, $arResult['CHECK_HASH'], $arResult['INSTALL_HASH']);
}
else
{
	$arApp = \Bitrix\Rest\Marketplace\Client::getAppPublic($arParams['APP'], $ver, $arResult['CHECK_HASH'], $arResult['INSTALL_HASH']);
}

if($arApp)
{
	$arApp = $arApp["ITEMS"];

	$APPLICATION->SetTitle(htmlspecialcharsbx($arApp["NAME"]));

	if($ar)
	{
		$arApp["ID"] = $ar["ID"];
		$arApp["INSTALLED"] = $ar["INSTALLED"];
		$arApp["ACTIVE"] = $ar["ACTIVE"];
		$arApp["STATUS"] = $ar["STATUS"];
		$arApp["DATE_FINISH"] = $ar["DATE_FINISH"];
		$arApp["IS_TRIALED"] = $ar["IS_TRIALED"];

		$arApp['APP_STATUS'] = \Bitrix\Rest\AppTable::getAppStatusInfo($arApp, $APPLICATION->GetCurPageParam());

		if(isset($arApp['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']))
		{
			$arApp['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']++;
			$arApp['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#'] = FormatDate("ddiff", time(), time() + 24 * 60 * 60 * $arApp['APP_STATUS']['MESSAGE_REPLACE']["#DAYS#"]);
		}
	}

	if ($arApp["ACTIVE"] == "Y" && $arApp['TYPE'] !== \Bitrix\Rest\AppTable::TYPE_CONFIGURATION)
	{
		$arApp["UPDATES"] = \Bitrix\Rest\Marketplace\Client::getAvailableUpdate($arApp["CODE"]);
	}

	if ($arApp["DATE_PUBLIC"])
	{
		$stmp = MakeTimeStamp($arApp["DATE_PUBLIC"], "DD.MM.YYYY");
		$arApp["DATE_PUBLIC"] = ConvertTimeStamp($stmp);
	}
	if ($arApp["DATE_UPDATE"])
	{
		$stmp = MakeTimeStamp($arApp["DATE_UPDATE"], "DD.MM.YYYY");
		$arApp["DATE_UPDATE"] = ConvertTimeStamp($stmp);
	}

	$subscribeFinish = \Bitrix\Rest\Marketplace\Client::getSubscriptionFinalDate();
	$arResult["SUBSCRIPTION_ACTIVE"] = \Bitrix\Rest\Marketplace\Client::isSubscriptionAvailable();
	$arResult["PAID_APP_IN_SUBSCRIBE"] = Access::isActiveRules() && \Bitrix\Rest\Marketplace\Client::isSubscriptionAccess();

	if ($arResult["PAID_APP_IN_SUBSCRIBE"] && $arApp['TRIAL_PERIOD'] > 0)
	{
		$days = (int) $arApp['TRIAL_PERIOD'];
		$now = new \Bitrix\Main\Type\DateTime();
		if (!$subscribeFinish || $now->getTimestamp() > $subscribeFinish->getTimestamp())
		{
			$arApp['TRIAL_PERIOD'] = 0;
		}
		else
		{
			$diff = $subscribeFinish->getDiff($now);
			$subscribeFinishDays = (int) $diff->days;

			if ($days > $subscribeFinishDays)
			{
				$arApp['TRIAL_PERIOD'] = $subscribeFinishDays;
			}
		}
	}
	if ($arApp["BY_SUBSCRIPTION"] === "Y" && $arResult["SUBSCRIPTION_ACTIVE"])
	{
		$arApp["STATUS"] = \Bitrix\Rest\AppTable::STATUS_PAID;
		$arApp["DATE_FINISH"] = $subscribeFinish;
	}

	$arResult['REDIRECT_PRIORITY'] = false;
	if($arApp['TYPE'] === \Bitrix\Rest\AppTable::TYPE_CONFIGURATION)
	{
		$arResult['REDIRECT_PRIORITY'] = true;
	}

	$arApp['SILENT_INSTALL'] = $arApp['SILENT_INSTALL'] !== 'Y' ? 'N' : 'Y';

	$arResult["APP"] = $arApp;

	$arResult['REST_ACCESS_HELPER_CODE'] = Access::getHelperCode(Access::ACTION_INSTALL, Access::ENTITY_TYPE_APP, $arResult["APP"]);

	if (!ModuleManager::isModuleInstalled('bitrix24'))
	{
		$arResult['POPUP_BUY_SUBSCRIPTION_PRIORITY'] = true;
	}
}

$arResult["ADMIN"] = \CRestUtil::isAdmin();
$arResult["CAN_INSTALL"] = $arResult['ADMIN'] || \CRestUtil::canInstallApplication($arApp);

if($request->isPost() && $request['install'] && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();

	if(!$arResult['CAN_INSTALL'])
	{
		ShowError(GetMessage("RMP_ACCESS_DENIED"));
	}
	else
	{
		$scopeList = \Bitrix\Rest\Engine\ScopeManager::getInstance()->listScope();
		\Bitrix\Main\Localization\Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/rest/scope.php');
		$arResult['SCOPE_DENIED'] = array();
		if(is_array($arResult['APP']['RIGHTS']))
		{
			foreach($arResult['APP']['RIGHTS'] as $key => $scope)
			{
				$arResult['APP']['RIGHTS'][$key] = [
					"TITLE" => Loc::getMessage("REST_SCOPE_".mb_strtoupper($key)) ?: $scope,
					"DESCRIPTION" => Loc::getMessage("REST_SCOPE_".mb_strtoupper($key)."_DESCRIPTION")
				];
				if(!in_array($key, $scopeList, true))
				{
					$arResult['SCOPE_DENIED'][$key] = 1;
				}
			}
		}
		if(Loader::IncludeModule('bitrix24')
			&& !in_array(\CBitrix24::getLicensePrefix(), array('ru', 'ua', 'kz', 'by')))
		{
			$arResult['TERMS_OF_SERVICE_LINK'] = Loc::getMessage('REST_MARKETPLACE_TERMS_OF_SERVICE_LINK');
		}
		$arResult['IS_HTTPS'] = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps();

		$this->includeComponentTemplate('install');
	}
	CMain::FinalActions();
	die();
}
else
{
	if($arResult["APP"]['FREE'] === 'N' && is_array($arResult["APP"]["PRICE"]) && !empty($arResult["APP"]["PRICE"]) && $arResult['ADMIN'])
	{
		$arResult['BUY'] = array();
		foreach($arResult["APP"]["PRICE"] as $num => $price)
		{
			if($num > 1)
			{
				$arResult['BUY'][] = array(
					'LINK' => \Bitrix\Rest\Marketplace\Client::getBuyLink($num, $arResult['APP']['CODE']),
					'TEXT' => GetMessage("RMP_APP_TIME_LIMIT_".$num).' - '.$price
				);
			}
		}
	}

	if($arResult['APP']['TYPE'] == \Bitrix\Rest\AppTable::TYPE_CONFIGURATION)
	{
		$url = \Bitrix\Rest\Marketplace\Url::getConfigurationImportAppUrl($arResult['APP']['CODE']);

		$check_hash = $request->getQuery("check_hash");
		$install_hash = $request->getQuery("install_hash");
		if($install_hash && $check_hash)
		{
			$uri = new Bitrix\Main\Web\Uri($url);
			$uri->addParams(
				[
					'check_hash' => $check_hash,
					'install_hash' => $install_hash
				]
			);
			$arResult['IMPORT_PAGE'] = $uri->getUri();
		}
		else
		{
			$arResult['IMPORT_PAGE'] = $url;
		}
	}
	CJSCore::Init(array('marketplace', 'image', 'applayout'));

	$this->IncludeComponentTemplate();
}
