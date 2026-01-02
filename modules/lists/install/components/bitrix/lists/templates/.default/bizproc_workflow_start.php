<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

if(isset($_REQUEST['back_url']))
{
	$backUrl = urldecode($_REQUEST["back_url"]);
}
else
{
	$backUrl = $arResult["FOLDER"];
	$backUrl .= CComponentEngine::MakePathFromTemplate(
		$arResult["URL_TEMPLATES"]["list_element_edit"],
		array(
			"list_id" => $arResult["VARIABLES"]["list_id"],
			"section_id" => 0,
			"element_id" => $arResult["VARIABLES"]["element_id"]
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
$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.start", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_TYPE" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"DOCUMENT_ID" => $arResult["VARIABLES"]["element_id"],
	),
	$component
);
