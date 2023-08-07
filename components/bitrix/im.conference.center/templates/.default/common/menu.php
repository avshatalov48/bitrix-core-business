<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

use Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages(__FILE__);

if ($_REQUEST['IFRAME'] !== 'Y'):
	$currentMenuItem = $currentMenuItem ?? 'list';

	$menuItems = [];
	$menuItems[] = [
		"TEXT" => Loc::getMessage('IM_CONFERENCE_MENU_LIST'),
		"URL" => "/conference/list/",
		"ID" => "im-conference-menu-list",
		"IS_ACTIVE" => $currentMenuItem === 'list',
		'IS_DISABLED'=> false
	];

	if(SITE_TEMPLATE_ID === "bitrix24")
	{
		$this->SetViewTarget('above_pagetitle', 100);
	}

	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.buttons",
		"",
		array(
			"ID" => 'crm-tracking-menu',
			"ITEMS" => $menuItems,
		)
	);

	if(SITE_TEMPLATE_ID === "bitrix24")
	{
		$this->EndViewTarget();
	}
endif;