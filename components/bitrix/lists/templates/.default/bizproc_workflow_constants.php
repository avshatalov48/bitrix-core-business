<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$link = str_replace(
	array("#list_id#"),
	array($arResult["VARIABLES"]["list_id"]),
	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
);

CJSCore::Init(array('lists'));
$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
}
?>
<div class="pagetitle-container pagetitle-align-right-container">
	<a href="<?=$link?>" class="ui-btn ui-btn-sm ui-btn-link ui-btn-themes lists-list-back">
		<?=GetMessage("CT_BL_LIST_PROCESSES")?>
	</a>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

?>
	<div style="background: #eef2f4; width: 600px; padding: 5px 20px;">
		<?
		$APPLICATION->IncludeComponent('bitrix:bizproc.workflow.setconstants', '',
			array('ID' => $arResult['VARIABLES']['ID'], 'POPUP' => 'N'),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?>
	</div>
