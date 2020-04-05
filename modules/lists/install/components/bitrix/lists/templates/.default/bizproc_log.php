<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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
$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
}
?>
	<div class="pagetitle-container pagetitle-align-right-container">
		<a href="<?=htmlspecialcharsbx($backUrl)?>" class="ui-btn ui-btn-sm ui-btn-link ui-btn-themes lists-list-back">
			<?=GetMessage("CT_BL_LIST_GO_BACK")?>
		</a>
	</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}


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
$APPLICATION->IncludeComponent("bitrix:bizproc.log", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"COMPONENT_VERSION" => 2,
	"ID" => $arResult["VARIABLES"]["document_state_id"],
	),
	$component
);