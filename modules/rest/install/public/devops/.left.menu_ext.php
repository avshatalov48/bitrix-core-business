<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Rest\Url\DevOps;

$arMenu = [];

if (Loader::includeModule('rest'))
{
	Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/rest/install/public/devops/.left.menu_ext.php");
	$url = DevOps::getInstance();
	$arMenu[] = [
		Loc::getMessage("REST_MENU_DEVOPS_START"),
		$url->getIndexUrl(),
		[],
		["menu_item_id" => "menu_integration"],
		"",
	];

	$arMenu[] = [
		Loc::getMessage("REST_MENU_DEVOPS_LIST"),
		$url->getListUrl(),
		[],
		["menu_item_id" => "menu_integration_installed"],
		"",
	];

	if (\CRestUtil::isAdmin())
	{
		$arMenu[] = [
			Loc::getMessage("REST_MENU_DEVOPS_STATISTIC"),
			$url->getStatisticUrl(),
			[],
			["menu_item_id" => "menu_integration_installed"],
			"",
		];
	}

	$ext = 'com';
	if (in_array(LANGUAGE_ID, ['ru', 'by', 'kz']))
	{
		$ext = 'ru';
	}

	$subMenu = [
		[
			'TEXT' => Loc::getMessage("REST_MENU_DEVOPS_QUICK_START"),
			'URL' => "https://apidocs.bitrix24.$ext/",
			'ON_CLICK' => 'window.open("' . CUtil::JSescape("https://apidocs.bitrix24.$ext/") . '", "_blank"); return false;',
			'ID' => "menu_documentation_start"
		],
		[
			'TEXT' => Loc::getMessage("REST_MENU_DEVOPS_PRIVATE_CASE"),
			'URL' => "https://apidocs.bitrix24.$ext/tutorials/index.html",
			'ON_CLICK' => 'window.open("' . CUtil::JSescape("https://apidocs.bitrix24.$ext/tutorials/index.html") . '", "_blank"); return false;',
			'ID' =>  "menu_documentation_private_case"
		],
		[
			'TEXT' => Loc::getMessage("REST_MENU_DEVOPS_DOCUMENTATION_INTEGRATION"),
			'URL' => "https://apidocs.bitrix24.$ext/local-integrations/index.html",
			'ON_CLICK' => 'window.open("' . CUtil::JSescape("https://apidocs.bitrix24.$ext/local-integrations/index.html") . '", "_blank"); return false;',
			'ID' => "menu_documentation_integration"
		],
		[
			'TEXT' => Loc::getMessage("REST_MENU_DEVOPS_DOCUMENTATION_MARKET"),
			'URL' => "https://apidocs.bitrix24.$ext/market/index.html",
			'ON_CLICK' => 'window.open("' . CUtil::JSescape("https://apidocs.bitrix24.$ext/market/index.html") . '", "_blank"); return false;',
			'ID' => "menu_documentation_market"
		],
		[
			'TEXT' => Loc::getMessage("REST_MENU_DEVOPS_DOCUMENTATION_MAIN"),
			'URL' => "https://apidocs.bitrix24.$ext/api-reference/index.html",
			'ON_CLICK' => 'window.open("' . CUtil::JSescape("https://apidocs.bitrix24.$ext/api-reference/index.html") . '", "_blank"); return false;',
			'ID' => "menu_documentation_main"
		]
	];

	$documentation = [
		Loc::getMessage("REST_MENU_DEVOPS_DOCUMENTATION"),
		"",
		[],
		[
			"menu_item_id" => "menu_documentation",
			"sub_menu" => $subMenu
		],
		"",
	];

	$arMenu[] = $documentation;
}

$aMenuLinks = array_merge($arMenu, $aMenuLinks);
