<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

CJSCore::Init(array('lists'));
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/lists/css/autorun_progress_bar.css');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/lists/js/autorun_progress_bar.js');

if($arResult["PROCESSES"] && $arResult["USE_COMMENTS"])
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');

$listAction = array();
$listActionAdd = array();
if($arResult["CAN_ADD_ELEMENT"])
{
	$listActionAdd[] = array(
		"id" => "addElement",
		"text" => $arResult["IBLOCK"]["ELEMENT_ADD"],
		"url" => $arResult["LIST_NEW_ELEMENT_URL"],
		"action" => 'document.location.href="'.$arResult["LIST_NEW_ELEMENT_URL"].'"',
	);
}
if($arResult["CAN_EDIT_SECTIONS"])
{
	$listActionAdd[] = array(
		"id" => "addSection",
		"text" => $arResult["IBLOCK"]["SECTION_ADD"],
		"url" => $arResult["LIST_SECTION_URL"],
		"action" => "BX.Lists['".$arResult["JS_OBJECT"]."'].addSection();",
	);
}
if($arParams["CAN_EDIT"])
{
	$listAction[] = array(
		"text" => $arParams["IBLOCK_TYPE_ID"] == Option::get("lists", "livefeed_iblock_type_id") ?
			Loc::getMessage("CT_BLL_TOOLBAR_PROCESS_TITLE") : Loc::getMessage("CT_BLL_TOOLBAR_LIST_TITLE"),
		"url" => $arResult["LIST_EDIT_URL"],
		"action" => 'document.location.href="'.$arResult["~LIST_EDIT_URL"].'"',
	);
	$listAction[] = array(
		"text" => Loc::getMessage("CT_BLL_TOOLBAR_FIELDS"),
		"url" => $arResult["LIST_FIELDS_URL"],
		"action" => 'document.location.href="'.$arResult["~LIST_FIELDS_URL"].'"',
	);
	if($arResult["IBLOCK"]["BIZPROC"] == "Y" && $arParams["CAN_EDIT_BIZPROC"])
	{
		$listAction[] = array(
			"text" => Loc::getMessage("CT_BLL_TOOLBAR_BIZPROC_SETTINGS"),
			"url" => $arResult["BIZPROC_WORKFLOW_ADMIN_URL"],
			"action" => 'document.location.href="'.$arResult["~BIZPROC_WORKFLOW_ADMIN_URL"].'"',
		);
	}
}
if($arResult["SHOW_SECTION_GRID"] == "Y")
{
	$textForActionSectionGrid = Loc::getMessage("CT_BLL_HIDE_SECTION_GRID");
}
else
{
	$textForActionSectionGrid = Loc::getMessage("CT_BLL_SHOW_SECTION_GRID");
}
if($arResult["CAN_READ"])
{
	if($USER->IsAuthorized())
	{
		$listAction[] = array(
			"id" => "showSectionGrid",
			"text" => $textForActionSectionGrid,
			"action" => "BX.Lists['".$arResult["JS_OBJECT"]."'].toogleSectionGrid();"
		);

		$url = CHTTP::urlAddParams((strpos($APPLICATION->GetCurPageParam(), "?") == false) ?
			$arResult["EXPORT_EXCEL_URL"] : $arResult["EXPORT_EXCEL_URL"].substr($APPLICATION->GetCurPageParam(),
				strpos($APPLICATION->GetCurPageParam(), "?")), array("ncc" => "y"));
		$listAction[] = array(
			"text" => Loc::getMessage("CT_BLL_EXPORT_IN_EXCEL"),
			"url" => $url,
			"action" => 'document.location.href="'.$url.'"',
		);
	}
}

if(!IsModuleInstalled("bitrix24")
	&& IsModuleInstalled("intranet") && CBXFeatures::IsFeatureEnabled("intranet_sharepoint"))
{
	if($icons = $APPLICATION->IncludeComponent('bitrix:sharepoint.link', '', array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'OUTPUT' => 'N',
	), null, array('HIDE_ICONS' => 'Y')))
	{
		if(count($icons['LINKS']) > 0)
		{
			$items = array();
			foreach ($icons['LINKS'] as $link)
			{
				$items[] = array(
					'text' => $link['TEXT'],
					'action' => $link['ONCLICK'],
				);
			}
			$listAction[] = array(
				'text' => 'SharePoint',
				'items' => $items
			);
		}
	}
}

$filterId = "";
foreach($arResult["FILTER_CUSTOM_ENTITY"] as $fieldType => $listField)
{
	switch($fieldType)
	{
		case 'employee':
			$filterId = $arResult["FILTER_ID"];
			break;
		case 'ECrm':
			$filterId = $arResult["FILTER_ID"];
			break;
		case 'E':
			$filterId = $arResult["FILTER_ID"];
			break;
		case 'CREATED_BY':
		case 'MODIFIED_BY':
		$filterId = $arResult["FILTER_ID"];
			$fieldType = 'employee';
			break;
	}
	if($filterId)
	{
		echo Bitrix\Iblock\Helpers\Filter\Property::render($filterId, $fieldType, $listField);
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
			"ENABLE_LABEL" => true,
			"ENABLE_LIVE_SEARCH" => true
		),
		$component,
		array("HIDE_ICONS" => true)
	); ?>
</div>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
	<? if($arResult["SECTION_ID"]):?>
		<a href="<?=$arResult["LIST_PARENT_URL"]?>" class="lists-list-back">
			<?=GetMessage("CT_BLL_SECTION_RETURN")?>
		</a>
	<?endif;?>
	<? if($listAction):?>
		<span id="lists-title-action" class="webform-small-button webform-small-button-transparent webform-small-button-dropdown">
		<span class="webform-small-button-text"><?=Loc::getMessage("CT_BLL_TOOLBAR_ACTION")?></span>
		<span id="lists-title-action-icon" class="webform-small-button-icon"></span>
	</span>
	<?endif;?>
	<?if($arResult["CAN_ADD_ELEMENT"] || $arResult["CAN_EDIT_SECTIONS"]):?>
		<span class="webform-small-button-separate-wrap">
		<a href="<?=$arResult["LIST_NEW_ELEMENT_URL"]?>" class="
			webform-small-button webform-small-button-blue" id="lists-title-action-add">
			<span class="webform-small-button-icon"></span>
			<span class="webform-small-button-text"><?=Loc::getMessage("CT_BLL_TOOLBAR_ADD")?></span>
		</a>
		<span class="webform-small-button-right-part" id="lists-title-action-select-add"></span>
	</span>
	<?endif?>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$sectionId = $arResult["SECTION_ID"] ? $arResult["SECTION_ID"] : 0;
$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;
$rebuildedData = Option::get("lists", "rebuild_seachable_content");
$rebuildedData = unserialize($rebuildedData);
$shouldStartRebuildSeachableContent = isset($rebuildedData[$arResult["IBLOCK_ID"]]);
$dataForAjax = array(
	"iblockTypeId" => $arParams["IBLOCK_TYPE_ID"],
	"iblockId" => $arResult["IBLOCK_ID"],
	"sectionId" => $sectionId,
	"socnetGroupId" => $socnetGroupId
);
if($shouldStartRebuildSeachableContent):?>
	<?
		$dataForAjax["totalItems"] = CLists::getNumberOfElements($arResult["IBLOCK_ID"]);
	?>
	<div id="rebuildSeachableContent"></div>
	<script>
		BX.ready(function(){
			if(BX.Lists.AutorunProcessPanel.isExists("rebuildSeachableContent"))
			{
				return;
			}
			BX.Lists.AutorunProcessManager.messages =
			{
				title: "<?=GetMessageJS("CT_BLL_REBUILD_SEARCH_CONTENT_TITLE")?>",
				stateTemplate: "<?=GetMessageJS("CT_BLL_REBUILD_SEARCH_CONTENT_STATE")?>"
			};
			var manager = BX.Lists.AutorunProcessManager.create("rebuildSeachableContent",
				{
					serviceUrl: "<?='/bitrix/components/bitrix/lists.list/ajax.php'?>",
					ajaxAction: "rebuildSeachableContent",
					dataForAjax: <?=Bitrix\Main\Web\Json::encode($dataForAjax)?>,
					container: "rebuildSeachableContent",
					enableLayout: true
				}
			);
			manager.runAfter(100);
		});
	</script>
<?endif;

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $arResult["GRID_ID"],
		"COLUMNS" => $arResult["ELEMENTS_HEADERS"],
		"ROWS" => $arResult["ELEMENTS_ROWS"],
		"NAV_STRING" => $arResult["NAV_STRING"],
		"TOTAL_ROWS_COUNT" => $arResult["NAV_OBJECT"]->NavRecordCount,
		"PAGE_SIZES" => $arResult["GRID_PAGE_SIZES"],
		"AJAX_MODE" => "Y",
		"AJAX_ID" => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		"ENABLE_NEXT_PAGE" => $arResult["GRID_ENABLE_NEXT_PAGE"],
		"ACTION_PANEL" => $arResult["GRID_ACTION_PANEL"],
		"AJAX_OPTION_JUMP" => "N",
		"SHOW_CHECK_ALL_CHECKBOXES" => true,
		"SHOW_ROW_CHECKBOXES" => true,
		"SHOW_ROW_ACTIONS_MENU" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_NAVIGATION_PANEL" => true,
		"SHOW_PAGINATION" => true,
		"SHOW_SELECTED_COUNTER" => true,
		"SHOW_TOTAL_COUNTER" => true,
		"SHOW_PAGESIZE" => true,
		"SHOW_ACTION_PANEL" => true,
		"ALLOW_COLUMNS_SORT" => true,
		"ALLOW_COLUMNS_RESIZE" => true,
		"ALLOW_HORIZONTAL_SCROLL" => true,
		"ALLOW_SORT" => true,
		"ALLOW_PIN_HEADER" => true,
		"AJAX_OPTION_HISTORY" => "N"
	),
	$component, array("HIDE_ICONS" => "Y")
);
?>

<script type="text/javascript">
	BX.ready(function(){
		BX.Lists['<?= $arResult['JS_OBJECT'] ?>'] = new BX.Lists.ListClass({
			randomString: '<?= $arResult["RAND_STRING"] ?>',
			iblockTypeId: '<?= $arParams["IBLOCK_TYPE_ID"] ?>',
			iblockId: '<?= $arResult["IBLOCK_ID"] ?>',
			sectionId: '<?= $sectionId ?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			jsObject: '<?= $arResult['JS_OBJECT'] ?>',
			listAction: <?=\Bitrix\Main\Web\Json::encode($listAction)?>,
			listActionAdd: <?=\Bitrix\Main\Web\Json::encode($listActionAdd)?>,
			gridId: '<?=$arResult["GRID_ID"]?>',
			filterId: '<?=$arResult["FILTER_ID"]?>'
		});

		BX.message({
			CT_BLL_ADD_SECTION_POPUP_TITLE: '<?=GetMessageJS("CT_BLL_ADD_SECTION_POPUP_TITLE")?>',
			CT_BLL_ADD_SECTION_POPUP_INPUT_NAME: '<?=GetMessageJS("CT_BLL_ADD_SECTION_POPUP_INPUT_NAME")?>',
			CT_BLL_ADD_SECTION_POPUP_BUTTON_ADD: '<?=GetMessageJS("CT_BLL_ADD_SECTION_POPUP_BUTTON_ADD")?>',
			CT_BLL_ADD_SECTION_POPUP_BUTTON_EDIT: '<?=GetMessageJS("CT_BLL_ADD_SECTION_POPUP_BUTTON_EDIT")?>',
			CT_BLL_ADD_SECTION_POPUP_BUTTON_CLOSE: '<?=GetMessageJS("CT_BLL_ADD_SECTION_POPUP_BUTTON_CLOSE")?>',
			CT_BLL_ADD_SECTION_POPUP_ERROR_NAME: '<?=GetMessageJS("CT_BLL_ADD_SECTION_POPUP_ERROR_NAME")?>',
			CT_BLL_EDIT_SECTION_POPUP_TITLE: '<?=GetMessageJS("CT_BLL_EDIT_SECTION_POPUP_TITLE")?>',
			CT_BLL_TOOLBAR_ELEMENT_DELETE_WARNING: '<?=GetMessageJS("CT_BLL_TOOLBAR_ELEMENT_DELETE_WARNING")?>',
			CT_BLL_TOOLBAR_SECTION_DELETE_WARNING: '<?=GetMessageJS("CT_BLL_TOOLBAR_SECTION_DELETE_WARNING")?>',
			CT_BLL_DELETE_POPUP_TITLE: '<?=GetMessageJS("CT_BLL_DELETE_POPUP_TITLE")?>',
			CT_BLL_DELETE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLL_DELETE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLL_DELETE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLL_DELETE_POPUP_CANCEL_BUTTON")?>',
			CT_BLL_SHOW_SECTION_GRID: '<?=GetMessageJS("CT_BLL_SHOW_SECTION_GRID")?>',
			CT_BLL_HIDE_SECTION_GRID: '<?=GetMessageJS("CT_BLL_HIDE_SECTION_GRID")?>'
		});

		if(BX["viewElementBind"])
		{
			BX.viewElementBind('<?=$arResult["GRID_ID"]?>', {showTitle: true},
				function(node)
				{
					return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer')
						|| node.getAttribute('data-bx-image'));
				}
			);
		}
	});
</script>

