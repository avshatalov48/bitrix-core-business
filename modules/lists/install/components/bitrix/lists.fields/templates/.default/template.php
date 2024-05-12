<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

use Bitrix\Main\Localization\Loc;

CJSCore::Init(array('lists'));
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');

if($arParams['IBLOCK_TYPE_ID'] == COption::GetOptionString('lists', 'livefeed_iblock_type_id'))
	$typeTranslation = '_PROCESS';
else
	$typeTranslation = '';

$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
	$pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'ui.fonts.opensans',
	]);

	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
	<a href="<?=$arResult["LIST_URL"]?>" class="ui-btn ui-btn-sm ui-btn-link ui-btn-themes lists-list-back">
		<?=GetMessage("CT_BLF_TOOLBAR_RETURN_LIST_ELEMENT")?>
	</a>
	<a class="ui-btn ui-btn-sm ui-btn-primary ui-btn-icon-add" href="<?=$arResult['LIST_FIELD_EDIT_URL']?>">
		<?=Loc::getMessage('CT_BLF_TOOLBAR_ADD')?>
	</a>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$arResult['HEADERS'] = array(
	array(
		'id' => 'SORT',
		'name' => Loc::getMessage('CT_BLF_LIST_SORT'),
		'default' => true,
		'editable' => array(
			'TYPE' => Bitrix\Main\Grid\Editor\Types::TEXT
		),
	),
	array(
		'id' => 'NAME',
		'name' => Loc::getMessage('CT_BLF_LIST_NAME'),
		'default' => true,
		'editable' => array(
			'TYPE' => Bitrix\Main\Grid\Editor\Types::TEXT
		),
	),
	array(
		'id' => 'TYPE',
		'name' => Loc::getMessage('CT_BLF_LIST_TYPE'),
		'default' => true,
		'editable' => false
	),
	array(
		'id' => 'CODE',
		'name' => Loc::getMessage('CT_BLF_LIST_CODE'),
		'default' => false,
		'editable' => false
	),
	array(
		'id' => 'IS_REQUIRED',
		'name' => Loc::getMessage('CT_BLF_LIST_IS_REQUIRED'),
		'default' => true,
		'type' => 'checkbox',
		'editable' => array(
			'TYPE' => Bitrix\Main\Grid\Editor\Types::CHECKBOX
		),
	),
	array(
		'id' => 'MULTIPLE',
		'name' => Loc::getMessage('CT_BLF_LIST_MULTIPLE'),
		'default' => true,
		'type' => 'checkbox',
		'editable' => array(
			'TYPE' => Bitrix\Main\Grid\Editor\Types::CHECKBOX
		),
	),
	array(
		'id' => 'DEFAULT_VALUE',
		'name' => Loc::getMessage('CT_BLF_LIST_DEFAULT_VALUE'),
		'default' => false,
		'editable' => false
	),
);

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'COLUMNS' => $arResult['HEADERS'],
		'ROWS' => $arResult['ROWS'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		'ACTION_PANEL' => $arResult['ACTION_PANEL'],
		'SHOW_CHECK_ALL_CHECKBOXES' => true,
		'SHOW_ROW_CHECKBOXES' => true,
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => false,
		'SHOW_SELECTED_COUNTER' => true,
		'SHOW_PAGESIZE' => true,
		'SHOW_ACTION_PANEL' => true,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	),
	$component, array('HIDE_ICONS' => 'Y')
);
?>

<script>
	BX(function () {
		BX.Lists['<?= $arResult['JS_OBJECT'] ?>'] = new BX.Lists.FieldsClass({
			randomString: '<?= $arResult["RAND_STRING"] ?>',
			iblockTypeId: '<?= $arParams["IBLOCK_TYPE_ID"] ?>',
			iblockId: '<?= $arResult["IBLOCK_ID"] ?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			jsObject: '<?= $arResult['JS_OBJECT'] ?>'
		});

		BX.message({
			CT_BLF_DELETE_POPUP_TITLE: '<?=GetMessageJS("CT_BLF_DELETE_POPUP_TITLE")?>',
			CT_BLF_DELETE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLF_DELETE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLF_DELETE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLF_DELETE_POPUP_CANCEL_BUTTON")?>',
			CT_BLF_TOOLBAR_ELEMENT_DELETE_WARNING: '<?=GetMessageJS("CT_BLF_TOOLBAR_ELEMENT_DELETE_WARNING")?>'
		});
	});
</script>
