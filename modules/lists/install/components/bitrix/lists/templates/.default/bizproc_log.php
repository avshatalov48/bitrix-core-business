<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @global CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

if(isset($_REQUEST['back_url']))
{
	$backUrl = urldecode($_REQUEST["back_url"]);
}
else
{
	$backUrl = $arResult["FOLDER"];
	$backUrl .= CComponentEngine::MakePathFromTemplate(
		$arResult["URL_TEMPLATES"]["list"],
		array(
			"list_id" => $arResult["VARIABLES"]["list_id"],
			"section_id" => 0,
		)
	);
}
if(!preg_match('#^(?:/|\?|https?://)(?:\w|$)#D', $backUrl))
	$backUrl = '#';

CJSCore::Init(array('lists'));

$returnButton = new Bitrix\UI\Buttons\Button([
	'text' => Loc::getMessage('CT_BL_LIST_GO_BACK'),
	'color' => Bitrix\UI\Buttons\Color::LINK,
	'link' => $backUrl,
	'icon' => Bitrix\UI\Buttons\Icon::BACK,
]);
$returnButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($returnButton);


if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$moduleId = "lists";
	$entity = "BizprocDocument";
}
else
{
	$moduleId = "lists";
	$entity = 'Bitrix\Lists\BizprocDocumentLists';
}
$APPLICATION->IncludeComponent(
	"bitrix:bizproc.log",
	"modern",
	[
		"MODULE_ID" => $moduleId,
		"ENTITY" => $entity,
		"COMPONENT_VERSION" => 2,
		"ID" => $arResult["VARIABLES"]["document_state_id"],
	],
	$component
);
