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

CJSCore::Init(array('lists'));
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
	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
if($arParams['CAN_EDIT']): ?>
<div class="pagetitle-container pagetitle-align-right-container">
	<a href="<?= $arResult["LIST_EDIT_URL"] ?>"
	   class="webform-small-button webform-small-button-accept bp-small-button"
	   title="<?= $title ?>">
		<?= GetMessage("CT_BLL_TOOLBAR_ADD_NEW") ?>
	</a>
	<? if($claim && $arParams['CAN_EDIT']): ?>
		<a
			href="<?= $arParams["CATALOG_PROCESSES_URL"] ?>"
			class="webform-small-button webform-small-button-cancel"
			title="<?= GetMessage("CT_BLL_TOOLBAR_TRANSITION_PROCESSES") ?>"
		>
			<?= GetMessage("CT_BLL_TOOLBAR_TRANSITION_PROCESSES") ?>
		</a>
	<? endif; ?>
	<? if($arParams["IBLOCK_TYPE_ID"] != "lists" && $arParams["IBLOCK_TYPE_ID"] != "lists_socnet" && empty($arResult["ITEMS"])): ?>
		<p id="bx-lists-default-processes" onclick="javascript:BX.Lists['<?=$jsClass?>'].createDefaultProcesses();" class="
		webform-small-button webform-small-button-cancel bp-small-button" title="<?= GetMessage("CT_BLL_TOOLBAR_ADD_DEFAULT") ?>">
			<?= GetMessage("CT_BLL_TOOLBAR_ADD_DEFAULT") ?>
		</p>
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
				<a href="<?= $item["LIST_URL"]?>"  class="bp-bx-application-title"><?= $item["NAME"] ?></a>
				<? if($claim && $arParams['CAN_EDIT']): ?>
					<span class="bp-bx-application-check">
						<input type="checkbox" value="" id="bx-lists-show-live-feed-<?= intval($item['ID']) ?>"
						<?= intval($item['SHOW_LIVE_FEED']) ? 'checked' : '' ?>
					        onclick="javascript:BX.Lists['<?=$jsClass?>'].showLiveFeed(<?= $item['ID'] ?>);">
						<label for="bx-lists-show-live-feed-<?= $item['ID'] ?>"><?= GetMessage("CT_BLL_TOOLBAR_SHOW_LIVE_FEED") ?></label>
					</span>
				<? endif; ?>
			</span>
		</span>
	</div>
<? endforeach; ?>

<script type="text/javascript">
	BX(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsIblockClass({
			randomString: '<?= $randString ?>'
		});
	});
</script>