<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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
$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
}
?>
	<div class="pagetitle-container pagetitle-align-right-container">
		<a href="<?=htmlspecialcharsbx($backUrl)?>" class="lists-list-back">
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
$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.start", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_TYPE" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"DOCUMENT_ID" => $arResult["VARIABLES"]["element_id"],
	),
	$component
);
?>