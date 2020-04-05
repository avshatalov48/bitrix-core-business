<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

CJSCore::Init(array('lists', 'popup'));

$jsClass = 'ListsEditClass_'.$arResult['RAND_STRING'];
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$processes = true;
	$typeTranslation = '_PROCESS';
}
else
{
	$processes = false;
	$typeTranslation = '';
}

$listAction = array();
if($arResult["IBLOCK_ID"])
{
	$listAction[] = array(
		"id" => 'deleteList',
		"text" => GetMessage("CT_BLLE_TOOLBAR_DELETE".$typeTranslation),
		"action"=>"BX.Lists['".$jsClass."'].deleteIblock('". CUtil::JSEscape("form_".$arResult["FORM_ID"])."', '".
			GetMessage("CT_BLLE_TOOLBAR_DELETE_WARNING".$typeTranslation)."')",
	);

	if(!$processes && IsModuleInstalled('intranet') && !$arParams["SOCNET_GROUP_ID"])
	{
		$listAction[] = array(
			"id" => 'migrateList',
			"text" => GetMessage("CT_BLLE_TOOLBAR_MIGRATE_PROCESSES"),
			"action"=>"BX.Lists['".$jsClass."'].migrateList('".CUtil::JSEscape("form_".$arResult["FORM_ID"])."', '".
				GetMessage("CT_BLLE_TOOLBAR_MIGRATE_WARNING_PROCESS")."')",
		);
	}
	$listAction[] = array(
		"id" => 'copyList',
		"text" => GetMessage("CT_BLLE_TOOLBAR_LIST_COPY".$typeTranslation),
		"action" => "BX.Lists['".$jsClass."'].copyIblock()",
	);

	$listAction[] = array(
		"id" => 'fieldSettings',
		"text" => GetMessage("CT_BLLE_TOOLBAR_FIELDS_TITLE".$typeTranslation),
		"action" => 'document.location.href="'.$arResult["LIST_FIELDS_URL"].'"',
	);
}

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
	$pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
	<?if($arResult["IBLOCK_ID"]):?>
	<a href="<?=$arResult["LIST_URL"]?>" class="lists-list-back">
		<?=GetMessage("CT_BLLE_TOOLBAR_RETURN_LIST_ELEMENT")?>
	</a>
	<?endif;?>
	<?if($listAction):?>
	<span id="lists-title-action" class="webform-small-button webform-small-button-transparent bx-filter-button">
		<span class="webform-small-button-text"><?=GetMessage("CT_BLLE_TOOLBAR_ACTION")?></span>
		<span id="lists-title-action-icon" class="webform-small-button-icon"></span>
	</span>
	<?endif;?>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

ob_start();
IBlockShowRights(
	/*$entity_type=*/'iblock',
	/*$iblock_id=*/$arResult["IBLOCK_ID"],
	/*$id=*/$arResult["IBLOCK_ID"],
	/*$section_title=*/"",
	/*$variable_name=*/"RIGHTS",
	/*$arPossibleRights=*/$arResult["TASKS"],
	/*$arActualRights=*/$arResult["RIGHTS"],
	/*$bDefault=*/true,
	/*$bForceInherited=*/false
);
$rights_html = ob_get_contents();
ob_end_clean();

$rights_fields = array(
	array(
		"id"=>"RIGHTS",
		"name"=>GetMessage("CT_BLLE_ACCESS_RIGHTS"),
		"type"=>"custom",
		"colspan"=>true,
		"value"=>$rights_html,
	),
);

$custom_html = '<input type="hidden" name="action" id="action" value="">';

$arTab1 = array(
	"id" => "tab1",
	"name" => GetMessage("CT_BLLE_TAB_EDIT"),
	"title" => GetMessage("CT_BLLE_TAB_EDIT_TITLE".$typeTranslation),
	"icon" => "",
	"fields" => array(
		array("id"=>"NAME", "name"=>GetMessage("CT_BLLE_FIELD_NAME".$typeTranslation), "required"=>true),
		array("id"=>"DESCRIPTION", "name"=>GetMessage("CT_BLLE_FIELD_DESCRIPTION".$typeTranslation), "type"=>"textarea"),
		array("id"=>"SORT", "name"=>GetMessage("CT_BLLE_FIELD_SORT"), "params"=>array("size"=>5)),
		array("id"=>"PICTURE", "name"=>GetMessage("CT_BLLE_FIELD_PICTURE"), "type"=>"file"),
	),
);
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	if(isset($arResult["FORM_DATA"]["BIZPROC"]))
	{
		$arTab1["fields"][] = array(
			"id"=>"BIZPROC",
			"type"=>"custom",
			"value"=>'<input type="hidden" name="BIZPROC" value="Y">',
		);
	}
}
else
{
	if(isset($arResult["FORM_DATA"]["BIZPROC"]))
		$arTab1["fields"][] = array(
			"id" => "BIZPROC",
			"name" => GetMessage("CT_BLLE_FIELD_BIZPROC"),
			"type"=>"checkbox",
		);
}

$backUrl = $arResult["IBLOCK"] ? $arResult["~LIST_URL"] : $arResult["~LISTS_URL"];

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.form",
	"",
	array(
		"FORM_ID"=>$arResult["FORM_ID"],
		"TABS"=>array(
			$arTab1,
			array("id"=>"tab2", "name"=>GetMessage("CT_BLLE_TAB_MESSAGES"), "title"=>GetMessage("CT_BLLE_TAB_MESSAGES_TITLE".$typeTranslation), "icon"=>"", "fields"=>array(
				array("id"=>"ELEMENTS_NAME", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENTS_NAME")),
				array("id"=>"ELEMENT_NAME", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_NAME")),
				array("id"=>"ELEMENT_ADD", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_ADD")),
				array("id"=>"ELEMENT_EDIT", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_EDIT")),
				array("id"=>"ELEMENT_DELETE", "name"=>GetMessage("CT_BLLE_FIELD_ELEMENT_DELETE")),
				array("id"=>"SECTIONS_NAME", "name"=>GetMessage("CT_BLLE_FIELD_SECTIONS_NAME")),
				array("id"=>"SECTION_NAME", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_NAME")),
				array("id"=>"SECTION_ADD", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_ADD")),
				array("id"=>"SECTION_EDIT", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_EDIT")),
				array("id"=>"SECTION_DELETE", "name"=>GetMessage("CT_BLLE_FIELD_SECTION_DELETE")),
			)),
			array(
				"id"=>"tab3",
				"name"=>GetMessage("CT_BLLE_TAB_ACCESS"),
				"title"=>GetMessage("CT_BLLE_TAB_ACCESS_TITLE".$typeTranslation),
				"icon"=>"",
				"fields"=>$rights_fields,
			),
		),
		"BUTTONS"=>array("back_url"=>$backUrl, "custom_html"=>$custom_html),
		"DATA"=>$arResult["FORM_DATA"],
		"SHOW_SETTINGS"=>"N",
		"THEME_GRID_ID"=>$arResult["GRID_ID"],
	),
	$component, array("HIDE_ICONS" => "Y")
);

$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;
?>

<script type="text/javascript">
	BX(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsEditClass({
			randomString: '<?=$arResult['RAND_STRING']?>',
			iblockTypeId: '<?=$arParams["IBLOCK_TYPE_ID"]?>',
			iblockId: '<?=$arResult["IBLOCK_ID"]?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			listsUrl: '<?=CUtil::JSEscape($arResult["LISTS_URL"])?>',
			listAction: <?=\Bitrix\Main\Web\Json::encode($listAction)?>,
			listTemplateEditUrl: '<?=$arParams["LIST_EDIT_URL"]?>'
		});

		BX.message({
			CT_BLLE_TOOLBAR_LIST_COPY_BUTTON_TITLE: '<?=GetMessageJS("CT_BLLE_TOOLBAR_LIST_COPY_BUTTON_TITLE")?>',
			CT_BLLE_MIGRATE_POPUP_TITLE: '<?=GetMessageJS("CT_BLLE_MIGRATE_POPUP_TITLE")?>',
			CT_BLLE_MIGRATE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLLE_MIGRATE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLLE_MIGRATE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLLE_MIGRATE_POPUP_CANCEL_BUTTON")?>',
			CT_BLLE_DELETE_POPUP_TITLE: '<?=GetMessageJS("CT_BLLE_DELETE_POPUP_TITLE")?>',
			CT_BLLE_DELETE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLLE_DELETE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLLE_DELETE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLLE_DELETE_POPUP_CANCEL_BUTTON")?>',
			CT_BLLE_COPY_POPUP_TITLE: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_TITLE")?>',
			CT_BLLE_COPY_POPUP_CONTENT: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_CONTENT")?>',
			CT_BLLE_COPY_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_ACCEPT_BUTTON")?>',
			CT_BLLE_COPY_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLLE_COPY_POPUP_CANCEL_BUTTON")?>'
		});
	});
</script>