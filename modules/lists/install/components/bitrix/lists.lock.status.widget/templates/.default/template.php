<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;

Extension::load("ui.hint");
Extension::load("ui.tooltip");

$widgetContainerId = "lists-lock-status-widget-container";

$lockStatus = HtmlFilter::encode($arResult["LOCK_STATUS"]);
$elementName = HtmlFilter::encode($arResult["ELEMENT_NAME"]);
$lockedUserName = HtmlFilter::encode($arResult["LOCKED_USER_NAME"]);
$lockedBy = (int) $arResult["LOCKED_BY"];
$locPlaceholders = [
	"#ELEMENT#" => $elementName,
];

if ($lockStatus === 'red')
{
	$locPlaceholders["[LOCKED_BY]"] = "<a class=\"lists-lock-status-widget-locked-by\" href=\"/company/personal/user/$lockedBy/\" bx-tooltip-user-id=\"$lockedBy\">";
	$locPlaceholders["[\LOCKED_BY]"] = "</a>";
	$locPlaceholders["#LOCKED_BY_USERNAME#"] = $lockedUserName;
}
?>

<div id="<?=$widgetContainerId?>" class="lists-lock-status-widget-container">
	<div class="lists-lock-status-widget-<?=$lockStatus?>">
		<?=Loc::getMessage(
			"LISTS_LOCK_STATUS_" . mb_strtoupper($lockStatus) . '_MSGVER_1',
			$locPlaceholders,
		)?>
	</div>
	<div class="ui-hint" data-hint="<?=HtmlFilter::encode(Loc::getMessage("LISTS_LOCK_STATUS_HINT_".mb_strtoupper($lockStatus),
		["#ELEMENT#" => $elementName, "#USER_NAME#" => $lockedUserName]))?>"></div>
</div>

<script>
	BX.ready(function() {
		new BX.Lists.Widget.LockStatus({
			widgetContainerId: "<?=$widgetContainerId?>"
		});
	});
</script>
