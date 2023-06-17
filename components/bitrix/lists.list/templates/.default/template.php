<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load(["lists", "ui.fonts.opensans", 'ui.dialogs.messagebox']);

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/lists/css/autorun_progress_bar.css');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/lists/js/autorun_progress_bar.js');

if($arResult["PROCESSES"] && $arResult["USE_COMMENTS"])
{
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
}

$listAction = array();
$listActionAdd = array();
if($arResult["CAN_ADD_ELEMENT"])
{
	$listActionAdd[] = array(
		"text" => $arResult["IBLOCK"]["ELEMENT_ADD"],
		"href" => $arResult["LIST_NEW_ELEMENT_URL"],
	);
}
if($arResult["CAN_EDIT_SECTIONS"])
{
	$listActionAdd[] = array(
		"text" => $arResult["IBLOCK"]["SECTION_ADD"],
		"onclick" => new \Bitrix\UI\Buttons\JsHandler(
			"BX.Lists." . $arResult["JS_OBJECT"] . ".addSection",
			"BX.Lists." . $arResult["JS_OBJECT"]
		),
	);
}
if($arParams["CAN_EDIT"])
{
	$listAction[] = array(
		"text" => $arParams["IBLOCK_TYPE_ID"] == Option::get("lists", "livefeed_iblock_type_id") ?
			Loc::getMessage("CT_BLL_TOOLBAR_PROCESS_TITLE") : Loc::getMessage("CT_BLL_TOOLBAR_LIST_TITLE"),
		"href" => $arResult["LIST_EDIT_URL"],
	);
	$listAction[] = array(
		"text" => Loc::getMessage("CT_BLL_TOOLBAR_FIELDS"),
		"href" => $arResult["LIST_FIELDS_URL"],
	);
	if($arResult["IBLOCK"]["BIZPROC"] == "Y" && $arParams["CAN_EDIT_BIZPROC"])
	{
		$listAction[] = array(
			"text" => Loc::getMessage("CT_BLL_TOOLBAR_BIZPROC_SETTINGS"),
			"href" => $arResult["BIZPROC_WORKFLOW_ADMIN_URL"],
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
if ($arResult["CAN_READ"])
{
	if ($USER->isAuthorized())
	{
		$listAction[] = [
			"text" => $textForActionSectionGrid,
			"onclick" => new \Bitrix\UI\Buttons\JsHandler(
				"BX.Lists." . $arResult["JS_OBJECT"] . ".toogleSectionGrid",
				"BX.Lists." . $arResult["JS_OBJECT"]
			),
		];
	}
}
else
{
	CUserOptions::setOption("lists_show_section_grid", $arResult["GRID_ID"], "N");
}
if (isset($arResult['CAN_EXPORT']) && $arResult["CAN_EXPORT"])
{
	if ($USER->isAuthorized())
	{
		$url = CHTTP::urlAddParams((mb_strpos($APPLICATION->GetCurPageParam(), "?") == false) ?
			$arResult["EXPORT_EXCEL_URL"] : $arResult["EXPORT_EXCEL_URL"].mb_substr($APPLICATION->GetCurPageParam(), mb_strpos($APPLICATION->GetCurPageParam(), "?")), array("ncc" => "y"));
		$listAction[] = array(
			"text" => Loc::getMessage("CT_BLL_EXPORT_IN_EXCEL"),
			"href" => $url,
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
					"onclick" => new \Bitrix\UI\Buttons\JsCode($link['ONCLICK']),
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

if(!IsModuleInstalled("intranet"))
{
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'ui.fonts.opensans',
	]);

	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}


if ($arResult["CAN_ADD_ELEMENT"] || $arResult["CAN_EDIT_SECTIONS"])
{
	$splitButton = new Bitrix\UI\Buttons\Split\CreateButton([
		'text' => Loc::getMessage("CT_BLL_TOOLBAR_ADD"),
		'menu' => ['items' => $listActionAdd],
		'mainButton' => [
			'link' => $arResult["LIST_NEW_ELEMENT_URL"],
		],
	]);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($splitButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
}

\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
	"FILTER_ID" => $arResult["FILTER_ID"],
	"GRID_ID" => $arResult["GRID_ID"],
	"FILTER" => $arResult["FILTER"],
	"ENABLE_LABEL" => true,
	"ENABLE_LIVE_SEARCH" => true,
	'THEME' => Bitrix\Main\UI\Filter\Theme::MUTED,
]);

if($arResult["SECTION_ID"])
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
			'link' => $arResult["LIST_PARENT_URL"],
			'color' => \Bitrix\UI\Buttons\Color::LINK,
			'text' => GetMessage("CT_BLL_SECTION_RETURN"),
			'classList' => ['lists-list-back'],
		],
		\Bitrix\UI\Toolbar\ButtonLocation::AFTER_FILTER
	);
}
if ($listAction)
{
	$settingsButton = new Bitrix\UI\Buttons\SettingsButton([
		'menu' => [
			'items' => $listAction,
		],
	]);
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($settingsButton);
}

$sectionId = $arResult["SECTION_ID"] ?: 0;
$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ?: 0;
$rebuildedData = Option::get("lists", "rebuild_seachable_content");
$rebuildedData = unserialize($rebuildedData, ['allowed_classes' => false]);
$shouldStartRebuildSeachableContent = isset($rebuildedData[$arResult["IBLOCK_ID"]]);
$dataForAjax = array(
	"iblockTypeId" => $arParams["IBLOCK_TYPE_ID"],
	"iblockId" => $arResult["IBLOCK_ID"],
	"sectionId" => $sectionId,
	"socnetGroupId" => $socnetGroupId
);
if($shouldStartRebuildSeachableContent):?>
	<?php
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
<?php
endif;

if (Loader::includeModule("socialnetwork"))
{
	$APPLICATION->includeComponent(
		"bitrix:socialnetwork.copy.checker",
		"",
		[
			"moduleId" => "iblock",
			"queueId" => $arResult["IBLOCK_ID"],
			"stepperClassName" => "Bitrix\\Iblock\\Copy\\Stepper\\Iblock",
			"checkerOption" => "IblockGroupChecker_",
			"errorOption" => "IblockGroupError_",
			"titleMessage" => GetMessage("CT_BLL_GROUP_STEPPER_PROGRESS_TITLE"),
			"errorMessage" => GetMessage("CT_BLL_GROUP_STEPPER_PROGRESS_ERROR"),
		],
		$component,
		["HIDE_ICONS" => "Y"]
	);
}

$gridId = $arResult["GRID_ID"];

$countTitle = GetMessage("CT_BLL_GRID_ROW_COUNT_TITLE");
$countShowTitle = GetMessage("CT_BLL_GRID_SHOW_ROW_COUNT");

$rowCountHtml = <<<HTML
	<div id="lists-list-row-count-wrapper" class="lists-list-row-count-wrapper">
		{$countTitle}
		<a onclick="BX.Lists['{$arResult['JS_OBJECT']}'].getTotalCount();">
			{$countShowTitle}
		</a>
		<svg class="lists-circle-loader-circular" viewBox="25 25 50 50">
			<circle
				class="lists-circle-loader-path"
				cx="50"
				cy="50"
				r="20"
				fill="none"
				stroke-width="1"
				stroke-miterlimit="10"
			></circle>
		</svg>
	</div>
HTML;

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	[
		"GRID_ID" => $gridId,
		"COLUMNS" => $arResult["ELEMENTS_HEADERS"],
		"ROWS" => $arResult["ELEMENTS_ROWS"],
		"MESSAGES" => $arResult["GRID_MESSAGES"],

		"AJAX_MODE" => "Y",
		"AJAX_ID" => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
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
		"AJAX_OPTION_HISTORY" => "N",

		"NAV_OBJECT" => $arResult["NAV_OBJECT"],
		"TOTAL_ROWS_COUNT_HTML" => $rowCountHtml,
		"ENABLE_NEXT_PAGE" => $arResult["GRID_ENABLE_NEXT_PAGE"],
		"PAGE_SIZES" => $arResult["GRID_PAGE_SIZES"],
		"CURRENT_PAGE" => $arResult["CURRENT_PAGE"],
	],
	$component, array("HIDE_ICONS" => "Y")
);
?>

<script type="text/javascript">
	BX.ready(function(){
		BX.Lists['<?= $arResult['JS_OBJECT'] ?>'] = new BX.Lists.ListClass({
			randomString: '<?= $arResult["RAND_STRING"] ?>',
			iblockTypeId: '<?= $arParams["IBLOCK_TYPE_ID"] ?>',
			iblockId: '<?= $arResult["IBLOCK_ID"] ?>',
			sectionId: '<?= (int) $sectionId ?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			jsObject: '<?= $arResult['JS_OBJECT'] ?>',
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
	});
</script>

