<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\Filter\AdditionalDateType;
use Bitrix\Main\UI\Filter\AdditionalNumberType;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Main\UI\Filter\NumberType;
use Bitrix\Main\UI\Filter\Type;
use Bitrix\Main\Web\Json;

Extension::load([
	"ui.design-tokens",
	"ui.buttons",
	"ui.fonts.opensans",
	"ui.layout-form",
	"ui",
	"dnd",
	"loader",
	"date",
	"ui.icons.service",
	"ui.notification",
]);

global $USER;

$arParams["CONFIG"] = $component->prepareConfig();
$currentPreset = $arResult["CURRENT_PRESET"];
$isCurrentPreset = (
		!isset($currentPreset["ID"])
		|| ($currentPreset["ID"] !== "default_filter" && $currentPreset["ID"] !== "tmp_filter")
		|| ($currentPreset["ID"] === "default_filter" && $currentPreset["FIELDS_COUNT"] > 0)
		|| ($currentPreset["ID"] === "tmp_filter" && $currentPreset["FIELDS_COUNT"] > 0)
);

if (!empty($arResult["TARGET_VIEW_ID"]))
{
	$this->SetViewTarget($arResult["TARGET_VIEW_ID"], $arResult["TARGET_VIEW_SORT"]);
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."headerless-mode");
}

$placeholder = "MAIN_UI_FILTER__PLACEHOLDER_DEFAULT";

if ($arResult["LIMITS_ENABLED"])
{
	$placeholder = "MAIN_UI_FILTER__PLACEHOLDER_LIMITS_EXCEEDED";
}
elseif ($arResult["DISABLE_SEARCH"] || !$arParams["CONFIG"]["SEARCH"])
{
	$placeholder = "MAIN_UI_FILTER__PLACEHOLDER";
}

$arResult = array_merge($arResult, array(
		"CONFIRM_MESSAGE" => Loc::getMessage("MAIN_UI_FILTER__CONFIRM_RESET_MESSAGE"),
		"CONFIRM_APPLY" => Loc::getMessage("MAIN_UI_FILTER__CONFIRM_RESET_APPLY"),
		"CONFIRM_CANCEL" => Loc::getMessage("MAIN_UI_FILTER__BUTTON_CANCEL")
));

$filterSearchClass = "main-ui-filter-theme-".mb_strtolower($arResult["THEME"]);
if ($arResult["DISABLE_SEARCH"] || !$arParams["CONFIG"]["SEARCH"])
{
	$filterSearchClass .= " main-ui-filter-no-search";
}

if (
	$arResult["THEME"] === \Bitrix\Main\UI\Filter\Theme::LIGHT
	&& isset($arResult["CURRENT_PRESET"]["FIND"])
	&& strlen($arResult["CURRENT_PRESET"]["FIND"]) > 0
)
{
	$filterSearchClass .= " main-ui-filter-search--active";
}

if ($arResult["COMPACT_STATE"])
{
	$filterSearchClass .= " main-ui-filter-compact-state";
}

if ($arResult["LIMITS_ENABLED"])
{
	$filterSearchClass .= " main-ui-filter-field-limits-active";
}

if (isset($currentPreset["IS_SET_OUTSIDE"]) && $currentPreset["IS_SET_OUTSIDE"])
{
	$filterSearchClass .= " main-ui-filter-set-outside";
}
else
{
	$filterSearchClass .= " main-ui-filter-set-inside";
}

$filterValue = $arResult["LIMITS_ENABLED"] ? '' : HtmlFilter::encode($arResult["CURRENT_PRESET"]["FIND"] ?? '', ENT_COMPAT, false);
?>

<!-- Final :: Search -->
<?php if ($arResult['THEME'] === 'SPACES'): ?>
	<div class="sn-spaces__toolbar-space_search main-ui-filter-search ui-ctl-element <?=$filterSearchClass?>"
		 id="<?=$arParams["FILTER_ID"]?>_search_container"
	>
		<input
			id="<?=$arParams["FILTER_ID"]?>_search"
			type="text"
			class="sn-spaces__toolbar-space_search-input ui-ctl-element"
			placeholder="<?=Loc::getMessage($placeholder)?>"
			autocomplete="off"
		>
		<div class="sn-spaces__toolbar-search-btn ui-ctl-after">
			<div class="ui-icon-set --search-2 sn-spaces__toolbar-search-icon"></div>
		</div>
	</div>
<?php else: ?>
	<div class="main-ui-filter-search <?=$filterSearchClass?>" id="<?=$arParams["FILTER_ID"]?>_search_container">
		<input
				type="text"
				tabindex="1" <?php
				if($arParams["CONFIG"]["AUTOFOCUS"]):?>autofocus="" <?php endif;
				?>value="<?=$filterValue?>"
				name="FIND"
				placeholder="<?=Loc::getMessage($placeholder)?>"
				class="main-ui-filter-search-filter"
				id="<?=$arParams["FILTER_ID"]?>_search"
				autocomplete="off"
		>
		<div class="main-ui-item-icon-block">
			<span class="main-ui-item-icon main-ui-search"></span>
			<span class="main-ui-item-icon main-ui-delete"></span>
		</div>
	</div>
<?php endif;

$frame = $this->createFrame()->begin(false);

$filterWrapperClass = "main-ui-filter-theme-".mb_strtolower($arResult["THEME"]);
if (isset($arParams["VALUE_REQUIRED_MODE"]) && $arParams["VALUE_REQUIRED_MODE"] === true)
{
	$filterWrapperClass .= " main-ui-filter-value-required-mode";
}

if ($arResult["LIMITS_ENABLED"])
{
	$filterWrapperClass .= " main-ui-filter-field-limits-active main-ui-filter-field-limits-animate";
}

if ($arResult["ENABLE_ADDITIONAL_FILTERS"])
{
	$filterWrapperClass .= " main-ui-filter-with-additional-filters";
}
?>

<script type="text/html" id="<?=$arParams["FILTER_ID"]?>_GENERAL_template">
	<div class="main-ui-filter-wrapper <?=$filterWrapperClass?>">
		<div class="main-ui-filter-inner-container">
			<div class="main-ui-filter-sidebar">
				<div class="main-ui-filter-sidebar-title">
					<h5 class="main-ui-filter-sidebar-title-item"><?=Loc::getMessage("MAIN_UI_FILTER__FILTER")?></h5>
				</div><!--main-ui-filter-sidebar-->
				<div class="main-ui-filter-sidebar-item-container">
					<? if (is_array($arResult["PRESETS"])) : ?>
						<? foreach ($arResult["PRESETS"] as $key => $preset) : ?>
							<div class="main-ui-filter-sidebar-item<?=(isset($arResult["CURRENT_PRESET"]["ID"]) && $preset["ID"] === $arResult["CURRENT_PRESET"]["ID"]) ? " main-ui-filter-current-item" : ""?><?
							?><?=$preset["ID"] === "default_filter" || $preset["ID"] === "tmp_filter" ? " main-ui-hide" : ""?><?
							?><?=!empty($preset["IS_PINNED"]) && $arParams["CONFIG"]["DEFAULT_PRESET"] ? " main-ui-item-pin" : ""?>" data-id="<?=htmlspecialcharsbx($preset["ID"])?>"<?
							?><?=!empty($preset["IS_PINNED"]) && $arParams["CONFIG"]["DEFAULT_PRESET"] ? " title=\"".Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET")."\"" : " "?>>
								<span class="main-ui-item-icon main-ui-filter-icon-grab" title="<?=Loc::getMessage("MAIN_UI_FILTER__DRAG_TITLE")?>"></span>
								<span class="main-ui-filter-sidebar-item-text-container">
									<span class="main-ui-filter-sidebar-item-text" title="<?=HtmlFilter::encode($preset["TITLE"], ENT_COMPAT, false)?>"><?=HtmlFilter::encode($preset["TITLE"], ENT_COMPAT, false)?></span>
									<input type="text" placeholder="<?=Loc::getMessage("MAIN_UI_FILTER__FILTER_NAME_PLACEHOLDER")?>" value="<?=HtmlFilter::encode($preset["TITLE"], ENT_COMPAT, false)?>" class="main-ui-filter-sidebar-item-input">
									<span class="main-ui-item-icon main-ui-filter-icon-pin" title="<?=Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET")?>"></span>
								</span>
								<? if ($arParams["CONFIG"]["DEFAULT_PRESET"]) : ?>
									<span class="main-ui-item-icon main-ui-filter-icon-pin" title="<?=Loc::getMessage("MAIN_UI_FILTER__IS_SET_AS_DEFAULT_PRESET")?>"></span>
								<? endif; ?>
								<span class="main-ui-item-icon main-ui-filter-icon-edit" title="<?=Loc::getMessage("MAIN_UI_FILTER__EDIT_PRESET_TITLE")?>"></span>
								<span class="main-ui-item-icon main-ui-delete" title="<?=Loc::getMessage("MAIN_UI_FILTER__REMOVE_PRESET")?>"></span>
								<div class="main-ui-filter-edit-mask"></div>
							</div>
						<? endforeach; ?>
					<? endif; ?>
					<div class="main-ui-filter-sidebar-item main-ui-filter-new-filter">
						<div class="main-ui-filter-edit-mask"></div>
						<input class="main-ui-filter-sidebar-edit-control" type="text" placeholder="<?=Loc::getMessage("MAIN_UI_FILTER__FILTER_NAME_PLACEHOLDER")?>">
					</div>
				</div><!--main-ui-filter-sidebar-item-container-->
			</div><!--main-ui-filter-sidebar-->
			<div class="main-ui-filter-field-container">
				<? if ($arResult["LIMITS_ENABLED"]): ?>
				<div class="main-ui-filter-field-limits">
					<div class="main-ui-filter-field-limits-title"><?=$arResult["LIMITS"]["TITLE"]?></div>
					<div class="main-ui-filter-field-limits-description">
						<?=$arResult["LIMITS"]["DESCRIPTION"]?>
					</div>
					<div class="ui-btn-container ui-btn-container-center main-ui-filter-field-limits-button-box">
					<? foreach ($arResult["LIMITS"]["BUTTONS"] as $button): ?>
						<?=$button?>
					<? endforeach ?>
					</div>
				</div>
				<? endif ?>
				<div class="main-ui-filter-field-container-list">

				</div>

				<div class="main-ui-filter-field-add">
					<span class="main-ui-filter-field-add-item"><?=Loc::getMessage("MAIN_UI_FILTER__ADD_FIELD")?></span>
					<span class="main-ui-filter-field-restore-items"><?=Loc::getMessage("MAIN_UI_FILTER__RESTORE_FIELDS")?></span>
				</div><!--main-ui-filter-field-add-->
			</div><!--main-ui-filter-field-container-->
			<div class="main-ui-filter-bottom-controls">
				<? if ($USER->IsAuthorized()) : ?>
					<div class="main-ui-filter-add-container">
						<span class="main-ui-filter-add-item"><?=Loc::getMessage("MAIN_UI_FILTER__ADD_FILTER")?></span>
						<span class="main-ui-filter-add-edit" title="<?=Loc::getMessage("MAIN_UI_FILTER__FILTER_SETTINGS_TITLE")?>"></span>
						<span class="main-ui-filter-reset-link">
							<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__RESET_LINK")?></span>
						</span>
					</div><!--main-ui-filter-add-container-->
				<? endif; ?>

				<div class="main-ui-filter-field-preset-button-container">
					<div class="main-ui-filter-field-button-inner">
						<button class="ui-btn ui-btn-primary ui-btn-icon-search main-ui-filter-field-button  main-ui-filter-find">
							<?=Loc::getMessage("MAIN_UI_FILTER__FIND")?></button>
						<span class="ui-btn ui-btn-light-border main-ui-filter-field-button main-ui-filter-reset">
							<?=Loc::getMessage("MAIN_UI_FILTER__RESET")?></span>
					</div>
				</div>
				<div class="main-ui-filter-field-button-container">
					<div class="main-ui-filter-field-button-inner">
						<? if ($USER->CanDoOperation("edit_other_settings")) : ?>
							<label class="main-ui-filter-field-button main-ui-filter-save-for-all" for="save-for-all">
								<input id="save-for-all" class="main-ui-filter-field-button-checkbox" type="checkbox">
								<span class="main-ui-filter-field-button-item"><?=Loc::getMessage("MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL_CHECKBOX")?></span>
							</label>
						<? endif; ?>
						<span class="ui-btn ui-btn-success main-ui-filter-field-button main-ui-filter-save">
							<?=Loc::getMessage("MAIN_UI_FILTER__BUTTON_SAVE")?></span>
						<span class="ui-btn ui-btn-light-border main-ui-filter-field-button main-ui-filter-cancel">
							<?=Loc::getMessage("MAIN_UI_FILTER__BUTTON_CANCEL")?></span>
					</div>
				</div>
			</div><!--main-ui-filter-bottom-controls-->
		</div><!--main-ui-filter-inner-container-->
	</div><!--main-ui-filter-wrapper-->
</script>

<?
    $messages = Json::encode(Loc::loadLanguageFile(__FILE__));
?>

<script>
	BX.Loc.setMessage(<?= $messages ?>);
	BX.ready(function() {
		BX.Main.filterManager.push(
			'<?=\CUtil::jSEscape($arParams["FILTER_ID"])?>',
			new BX.Main.Filter(
				<?= Json::encode($arResult) ?>,
				<?= Json::encode($arParams["CONFIG"]) ?>,
				<?= Json::encode(Type::getList()) ?>,
				<?= Json::encode(DateType::getList()) ?>,
				<?= Json::encode(NumberType::getList()) ?>,
				<?= Json::encode(AdditionalDateType::getList()) ?>,
				<?= Json::encode(AdditionalNumberType::getList()) ?>
			)
		);
	});
</script>
<?
	$frame->end();
	if (!empty($arResult["TARGET_VIEW_ID"]))
	{
		$this->EndViewTarget();
	}
?>
