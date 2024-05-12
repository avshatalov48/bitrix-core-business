<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"lists",
	"ui.buttons",
]);

$randString = $component->randString();
$jsClass = 'ListsIblockClass_'.$randString;

$claim = false;
$title = GetMessage("CT_BLL_TOOLBAR_ADD_TITLE_LIST");
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$title = GetMessage("CT_BLL_TOOLBAR_ADD_TITLE_PROCESS");
	$claim = true;
}
$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
}
elseif(!IsModuleInstalled("intranet"))
{
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'ui.fonts.opensans',
	]);

	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
if($arParams['CAN_EDIT']): ?>
<div class="pagetitle-container pagetitle-align-right-container">
	<a href="<?= $arResult["LIST_EDIT_URL"] ?>" target="_top" class="ui-btn ui-btn-success" title="<?/*= $title */?>"><?= GetMessage("CT_BLL_TOOLBAR_ADD_NEW") ?></a>
	<? if($claim && $arParams['CAN_EDIT']): ?>
		<a class="ui-btn ui-btn-light-border ui-btn-themes" href="<?= $arParams["CATALOG_PROCESSES_URL"] ?>" title="<?= GetMessage("CT_BLL_TOOLBAR_TRANSITION_PROCESSES") ?>">
			<?= GetMessage("CT_BLL_TOOLBAR_TRANSITION_PROCESSES") ?>
		</a>
	<? endif; ?>
	<? if($arParams["IBLOCK_TYPE_ID"] != "lists" && $arParams["IBLOCK_TYPE_ID"] != "lists_socnet" && empty($arResult["ITEMS"])): ?>
		<button class="ui-btn ui-btn-light-border ui-btn-themes" id="bx-lists-default-processes" onclick="javascript:BX.Lists['<?=$jsClass?>'].createDefaultProcesses();" title="<?= GetMessage("CT_BLL_TOOLBAR_ADD_DEFAULT") ?>">
			<?= GetMessage("CT_BLL_TOOLBAR_ADD_DEFAULT") ?></button>
	<? endif; ?>
	<input type="hidden" id="bx-lists-select-site" value="<?= SITE_ID ?>" />
</div>
<? endif;
	if($isBitrix24Template)
		$this->EndViewTarget();
?>

<? foreach($arResult["ITEMS"] as $item): ?>
	<div class="bp-bx-application">
		<span class="bp-bx-application-link">
			<a href="<?= $item["LIST_URL"]?>"  class="bp-bx-application-icon"><?= $item["IMAGE"] ?></a>
			<span class="bp-bx-application-title-wrapper">
				<a href="<?= $item["LIST_URL"]?>"  class="bp-bx-application-title"><?= htmlspecialcharsbx($item['NAME']) ?></a>
				<? if($claim && $arParams['CAN_EDIT']): ?>
					<span class="bp-bx-application-check">
						<input
							type="checkbox"
							value=""
							id="bx-lists-show-live-feed-<?= intval($item['ID']) ?>"
							<?= intval($item['SHOW_LIVE_FEED']) ? 'checked' : '' ?>
							onclick="javascript:BX.Lists['<?=$jsClass?>'].showLiveFeed(<?= $item['ID'] ?>);"
						>
						<label
							for="bx-lists-show-live-feed-<?= $item['ID'] ?>"
						><?= GetMessage("CT_BLL_TOOLBAR_SHOW_LIVE_FEED_NEW") ?></label>
					</span>
				<? endif; ?>
			</span>
		</span>
	</div>
<? endforeach; ?>

<script>
	BX(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsIblockClass({
			randomString: '<?= $randString ?>'
		});
	});
</script>
