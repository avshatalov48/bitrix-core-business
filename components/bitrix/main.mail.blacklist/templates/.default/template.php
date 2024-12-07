<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

\Bitrix\Main\Loader::includeModule("ui");

if($arParams['SET_TITLE'])
{
	$APPLICATION->SetTitle(Loc::getMessage('MAIN_MAIL_BLACKLIST_BLACKLIST_TITLE'));
}
foreach ($arResult["BLACKLIST"] as $key => $item)
{
	$item["DATE_INSERT"] = FormatDate("X", MakeTimeStamp($item["DATE_INSERT"]));
	foreach ($item as $itemKey => $dataValue)
	{
		if (is_string($dataValue))
		{
			$item[$itemKey] = htmlspecialcharsbx($dataValue);
		}
	}
	$actions = [];
	$actions[] = [
		"TITLE" => Loc::getMessage("MAIN_MAIL_BLACKLIST_REMOVE_BTN_TITLE"),
		"TEXT" => Loc::getMessage("MAIN_MAIL_BLACKLIST_REMOVE_BTN"),
		"ONCLICK" => "BX.Main.BlackList.showDeleteConfirm({$item["ID"]});"
	];
	$arResult["BLACKLIST"][$key] = [
		"id" => $item["ID"],
		"columns" => $item,
		"actions" => $actions,
	];
}

$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$controlPanel = ["GROUPS" => [["ITEMS" => []]]];
$button = $snippet->getRemoveButton();
$button["TEXT"] = Loc::getMessage("MAIN_MAIL_BLACKLIST_BTN_REMOVE_FROM_L");
$button["TITLE"] = Loc::getMessage("MAIN_MAIL_BLACKLIST_BTN_REMOVE_FROM_L_TITLE");
$controlPanel["GROUPS"][0]["ITEMS"][] = $button;

Toolbar::addFilter([
	"GRID_ID" => $arParams["GRID_ID"],
	"FILTER_ID" => $arParams["FILTER_ID"],
	"FILTER" => $arParams["FILTERS"],
	"DISABLE_SEARCH" => false,
	"ENABLE_LABEL" => false,
]);

$APPLICATION->IncludeComponent("bitrix:main.ui.grid",
	"",
	[
		"GRID_ID" => $arParams["GRID_ID"],
		"COLUMNS" => $arParams["COLUMNS"],
		"ROWS" => $arResult["BLACKLIST"],
		"NAV_OBJECT" => $arResult["NAVIGATION_OBJECT"],
		"~NAV_PARAMS" => ["SHOW_ALWAYS" => false],
		"SHOW_ROW_CHECKBOXES" => true,
		"SHOW_GRID_SETTINGS_MENU" => true,
		"SHOW_PAGINATION" => true,
		"SHOW_SELECTED_COUNTER" => true,
		"SHOW_TOTAL_COUNTER" => true,
		"ACTION_PANEL" => $controlPanel,
		"TOTAL_ROWS_COUNT" => $arResult["TOTAL_COUNT"],
		"ALLOW_COLUMNS_SORT" => true,
		"ALLOW_COLUMNS_RESIZE" => true,
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N",
		"MESSAGES" => $arResult["MESSAGES"] ?? null
	]);
?>
<script>

	BX.ready(function () {
		BX.message({
			MAIN_MAIL_BLACKLIST_DELETE_CONFIRM: "<?=\CUtil::jsEscape(getMessage("MAIN_MAIL_BLACKLIST_DELETE_CONFIRM")) ?>",
			MAIN_MAIL_BLACKLIST_DELETE_ERROR: "<?=\CUtil::jsEscape(getMessage("MAIN_MAIL_BLACKLIST_DELETE_ERROR")) ?>",
			MAIN_MAIL_BLACKLIST_DELETE_CONFIRM_TITLE:"<?=\CUtil::jsEscape(getMessage("MAIN_MAIL_BLACKLIST_DELETE_CONFIRM_TITLE")) ?>"
		});
		BX.Main.BlackList.init(<?=Json::encode(array(
			"gridId" => $arParams["GRID_ID"],
			"signedParameters" =>$this->getComponent()->getSignedParameters(),
  			"componentName"=> $this->getComponent()->getName(),
		))?>);
	});
</script>
