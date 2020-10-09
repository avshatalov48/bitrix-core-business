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
}

$aMenuLinks = array_merge($arMenu, $aMenuLinks);
