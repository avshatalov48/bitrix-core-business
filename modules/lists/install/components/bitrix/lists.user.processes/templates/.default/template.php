<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/bizproc.workflow.faces/templates/.default/style.css');

$randString = $component->randString();
$jsClass = 'ListsProcessesClass_'.$randString;
?>

<div id="bx-lists-store_items" class="bx-lists-store-items"></div>
<input type="hidden" id="bx-lists-select-site" value="<?= SITE_DIR ?>" />
<input type="hidden" id="bx-lists-select-site-id" value="<?= SITE_ID ?>" />
<?
if (is_array($arResult["RECORDS"]))
{
	foreach ($arResult["RECORDS"] as &$record)
	{
		if (strlen($record['data']["DOCUMENT_URL"]) > 0 && strlen($record['data']["DOCUMENT_NAME"]) > 0)
		{
			$record['data']['DOCUMENT_NAME'] = '<a href="'.htmlspecialcharsbx($record['data']["DOCUMENT_URL"]).'" class="lists-folder-title-link">'.htmlspecialcharsbx($record['data']['DOCUMENT_NAME']).'</a>';
		}

		if($record['data']['DOCUMENT_STATE'])
		{
			$record['data']['COMMENTS'] = '<div class="bp-comments"><a href="#" onclick="if (BX.Bizproc.showWorkflowInfoPopup) return BX.Bizproc.showWorkflowInfoPopup(\''.$record['data']["WORKFLOW_ID"].'\')"><span class="bp-comments-icon"></span>'
				.(!empty($arResult["COMMENTS_COUNT"]['WF_'.$record['data']["WORKFLOW_ID"]]) ? (int) $arResult["COMMENTS_COUNT"]['WF_'.$record['data']["WORKFLOW_ID"]] : '0')
				.'</a></div>';

			$record['data']["NAME"] .= '<span class="bp-status"><span class="bp-status-inner"><span>'.htmlspecialcharsbx($record['data']["WORKFLOW_STATE"]).'</span></span></span>';
			ob_start();
			$APPLICATION->IncludeComponent(
				"bitrix:bizproc.workflow.faces",
				"",
				array(
					"WORKFLOW_ID" => $record['data']["WORKFLOW_ID"]
				),
				$component
			);
			$record['data']['WORKFLOW_PROGRESS'] = ob_get_contents();
			ob_end_clean();
		}
	}
}

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleFlexibleSpace = "lists-pagetitle-flexible-space";
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", "pagetitle-toolbar-field-view");
	$this->SetViewTarget("inside_pagetitle");
	$pagetitleFlexibleSpace = "";
	$pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-flexible-space <?=$pagetitleFlexibleSpace?>">
	<? $APPLICATION->IncludeComponent(
		"bitrix:main.ui.filter",
		"",
		array(
			"FILTER_ID" => $arResult["FILTER_ID"],
			"GRID_ID" => $arResult["GRID_ID"],
			"FILTER" => $arResult["FILTER"],
			"ENABLE_LABEL" => true
		),
		$component,
		array("HIDE_ICONS" => true)
	); ?>
</div>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
	<span class="webform-small-button webform-small-button-blue" id="lists-title-action-add">
		<span class="webform-small-button-text"><?=Loc::getMessage("CT_BLL_BUTTON_NEW_PROCESSES")?></span>
	</span>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $arResult["GRID_ID"],
		"COLUMNS" => $arResult["HEADERS"],
		"ROWS" => $arResult["RECORDS"],
		"NAV_STRING" => $arResult["NAV_STRING"],
		"TOTAL_ROWS_COUNT" => $arResult["NAV_OBJECT"]->NavRecordCount,
		"PAGE_SIZES" => $arResult["GRID_PAGE_SIZES"],
		"AJAX_MODE" => "Y",
		"AJAX_ID" => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		"ENABLE_NEXT_PAGE" => $arResult["GRID_ENABLE_NEXT_PAGE"],
		"ACTION_PANEL" => $arResult["GRID_ACTION_PANEL"],
		"SHOW_CHECK_ALL_CHECKBOXES" => true,
		"SHOW_ROW_CHECKBOXES" => false,
		"SHOW_ROW_ACTIONS_MENU" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_NAVIGATION_PANEL" => true,
		"SHOW_PAGINATION" => true,
		"SHOW_SELECTED_COUNTER" => false,
		"SHOW_TOTAL_COUNTER" => true,
		"SHOW_PAGESIZE" => true,
		"SHOW_ACTION_PANEL" => false,
		"ALLOW_COLUMNS_SORT" => true,
		"ALLOW_COLUMNS_RESIZE" => true,
		"ALLOW_HORIZONTAL_SCROLL" => true,
		"ALLOW_SORT" => true,
		"ALLOW_PIN_HEADER" => true,
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	),
	$component, array("HIDE_ICONS" => "Y")
);
?>

<script type="text/javascript">
	BX(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsProcessesClass({});
	});
</script>