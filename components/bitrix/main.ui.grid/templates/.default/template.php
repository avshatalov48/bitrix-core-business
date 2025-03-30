<?php

/**
 * @var $arParams
 * @var $arResult
 */

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
	'ui',
	'resize_observer',
	'loader',
	'ui.actionpanel',
	'ui.dialogs.checkbox-list',
	'ui.fonts.opensans',
	'ui.buttons',
	'ui.buttons.icons',
	'dnd',
	'ui.hint',
	'ui.cnt',
	'ui.label',
	'ui.layout-form',
]);

global $APPLICATION;
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."grid-mode");

if (isset($arParams['FLEXIBLE_LAYOUT']) && $arParams['FLEXIBLE_LAYOUT'])
{
	$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'flexible-layout')));
}

$additionalColumnsCount = 1;

if ($arParams["SHOW_ROW_CHECKBOXES"])
{
	$additionalColumnsCount += 1;
}

if ($arParams["SHOW_GRID_SETTINGS_MENU"] || $arParams["SHOW_ROW_ACTIONS_MENU"])
{
	$additionalColumnsCount += 1;
}

if ($arParams["ALLOW_ROWS_SORT"])
{
	$additionalColumnsCount += 1;
}

$stickedColumnsCount = 0;

foreach ($arResult["COLUMNS"] as $header)
{
	if ($header["sticked"] === true)
	{
		$stickedColumnsCount += 1;
	}
}

$displayedCount = count(
	array_filter(
		$arParams["ROWS"],
		function($val)
		{
			return !isset($val["not_count"]) || $val["not_count"] !== true;
		}
	)
);

$adjustColumnItem = static function(array $column, array $arParams, array $arResult): string
{
	$columnId = Text\HtmlFilter::encode($column['id']);
	$stickedClass = (
		$arParams['ALLOW_STICKED_COLUMNS']
		&& $column['sticked']
		&& array_key_exists($column['id'], $arResult['COLUMNS'])
			? 'main-grid-settings-window-list-item-sticked' : ''
	);

	$result = '<div 
		data-name="'. $columnId .'" 
		class="main-grid-settings-window-list-item ' . $stickedClass . '" 
		data-sticked-default="' . $column['sticked_default'] .'"
	>';

	$checked = (array_key_exists($column['id'], $arResult['COLUMNS']) ? ' checked' : '');
	$result .= '<input 
		id="' . $columnId . '-checkbox" 
		type="checkbox" 
		class="main-grid-settings-window-list-item-checkbox"'
		. $checked
		. '>';

	$result .= '<label 
		for="' . $columnId . '-checkbox" 
		class="main-grid-settings-window-list-item-label">'
		. htmlspecialcharsbx(htmlspecialcharsback($column['name']))
		.'</label>';

	$spanStickedClass = (!$arParams['ALLOW_STICKED_COLUMNS'] ? ' main-grid-reset-right' : '');
	$result .= '<span class="main-grid-settings-window-list-item-edit-button' . $spanStickedClass . '"></span>';

	if ($arParams['ALLOW_STICKED_COLUMNS'])
	{
		$result .= '<span class="main-grid-settings-window-list-item-sticky-button"></span>';
	}

	$result .= '</div>';

	return $result;
};

$gridClasses = ['main-grid'];
if ($arResult["IS_AJAX"])
{
	$gridClasses[] = 'main-grid-load-animation';
}

if (!$arParams["ALLOW_HORIZONTAL_SCROLL"])
{
	$gridClasses[] = 'main-grid-full';
}

if ($arParams["HIDE_TOP_BORDER_RADIUS"])
{
	$gridClasses[] = '--hide-top-border-radius';
}

if ($arParams["HIDE_BOTTOM_BORDER_RADIUS"])
{
	$gridClasses[] = '--hide-bottom-border-radius';
}

$emptyFooter =
	(!$arResult["SHOW_MORE_BUTTON"] || !$arParams["SHOW_MORE_BUTTON"])
	&& ($arParams["SHOW_NAVIGATION_PANEL"] === false && $arParams["SHOW_ACTION_PANEL"] === false)
;

if ($emptyFooter)
{
	$gridClasses[] = 'main-grid-empty-footer';
}

?>

<div class="<?=join(' ', $gridClasses)?>" id="<?=$arParams["GRID_ID"]?>" data-ajaxid="<?=$arParams["AJAX_ID"]?>"<?=$arResult['IS_AJAX'] ? " style=\"display: none;\"" : ""?>><?
	?><form name="form_<?=$arParams["GRID_ID"]?>" action="<?=POST_FORM_ACTION_URI; ?>" method="POST"><?
		?><?=bitrix_sessid_post() ?><?
		if (!$arResult['USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP'])
		{
			?><div class="main-grid-settings-window"><?php
				$headersSectionsEnabled = !empty($arResult['HEADERS_SECTIONS']) && is_array($arParams['HEADERS_SECTIONS']);
				if ($headersSectionsEnabled || !empty($arResult['ENABLE_FIELDS_SEARCH']))
				{
				?><div class="main-grid-settings-window-search-wrapper"><?php
					?><div class="main-grid-settings-window-search-entities"><?php
						?><div class="ui-form-row-inline"><?php
							if ($headersSectionsEnabled)
							{
							?><div class="ui-form-row"><?php
								?><div class="ui-form-content main-grid-settings-window-search-section-wrapper"><?php
									foreach ($arResult['HEADERS_SECTIONS'] as $headerSection)
									{
										$activeClass = (
											$headerSection['selected']
												? ' main-grid-settings-window-search-section-item-icon-active'
												: ''
										);
										?><div class="main-grid-settings-window-search-section-item" data-ui-grid-filter-section-button="<?= $headerSection['id'] ?>"><?php
										?><div class="main-grid-settings-window-search-section-item-icon <?= $activeClass ?>"><?php
											?><div><?php
												print Text\HtmlFilter::encode($headerSection['name']);
											?></div><?php
										?></div><?php
										?></div><?php
									}
								?></div><?php
							?></div><?php
							}
							if ($arResult["ENABLE_FIELDS_SEARCH"]):
							?><div class="ui-form-row"><?php
								?><div class="ui-form-content main-grid-settings-window-search-input-wrapper"><?php
									?><div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon"><?php
										?><div class="ui-ctl-before ui-ctl-icon-search"></div><?php
										?><button class="ui-ctl-after ui-ctl-icon-clear"></button><?php
										?><input type="text" class="ui-ctl-element main-grid-settings-window-search-section-input"><?php
									?></div><?php
								?></div><?php
							?></div><?php
							endif;
						?></div><?php

					?></div><?php //main-grid-settings-window-search-entities
				?></div><?php // grid-search-wrapper
					if ($headersSectionsEnabled)
					{
						foreach ($arResult["HEADERS_SECTIONS"] as $headerSection)
						{
							$sectionId = $headerSection['id'];
							$isHiddenSection = empty($headerSection['selected']);
							?><div <?= $isHiddenSection ? 'hidden' : '' ?> data-ui-grid-filter-section="<?= $sectionId ?>">
							<h3 class="main-grid-settings-window-section-title">
								<?= Text\HtmlFilter::encode($headerSection['name']) ?>
								<?php if (isset($headerSection["hint"])): ?>
									<script>
										BX.ready(function() {
											BX.UI.Hint.init(BX('hint_section_<?= CUtil::JSEscape($headerSection['id']) ?>'));
										});
									</script>
									<span id="hint_section_<?= Text\HtmlFilter::encode($headerSection['id']) ?>" class="main-grid-head-title-tooltip" title="">
										<span <?= empty($headerSection['hintInteractivity']) ? '' : 'data-hint-interactivity'?> <?= empty($headerSection['hintHtml']) ? '' : 'data-hint-html' ?> data-hint="<?= Text\HtmlFilter::encode($headerSection["hint"]) ?>"></span>
									</span>
								<? endif; ?>
							</h3>
							<div class="main-grid-settings-window-list"><?php
									if (!empty($arResult['COLUMNS_ALL_WITH_SECTIONS'][$sectionId]))
									{
										foreach ($arResult['COLUMNS_ALL_WITH_SECTIONS'][$sectionId] as $column)
										{
											print $adjustColumnItem($column, $arParams, $arResult);
										}
									}
								?></div><? //main-grid-settings-window-list
							?></div><?php
						}
					}
				}
				if (!$headersSectionsEnabled)
				{
					?><div class="main-grid-settings-window-list"><?
					foreach ($arResult["COLUMNS_ALL"] as $column)
					{
						print $adjustColumnItem($column, $arParams, $arResult);
					}
					?></div><?
				}
				?><div class="popup-window-buttons"><?
					?><span class="main-grid-settings-window-buttons-wrapper"><?
						?><span class="main-grid-settings-window-actions-item-button main-grid-settings-window-actions-item-reset" id="<?=$arParams["GRID_ID"]?>-grid-settings-reset-button"><?=Loc::getMessage("interface_grid_restore_to_default")?></span><?
						if ($USER->CanDoOperation("edit_other_settings")) :
						?><span class="main-grid-settings-window-actions-item-button main-grid-settings-window-for-all">
							<input name="grid-settings-window-for-all" type="checkbox" id="<?=$arParams["GRID_ID"]?>-main-grid-settings-window-for-all-checkbox" class="main-grid-settings-window-for-all-checkbox">
							<label for="<?=$arParams["GRID_ID"]?>-main-grid-settings-window-for-all-checkbox" class="main-grid-settings-window-for-all-label"><?=Loc::getMessage("interface_grid_settings_for_all_label")?></label><?
						?></span><?
						endif;
					?></span><?
					?><span class="ui-btn ui-btn-success main-grid-settings-window-actions-item-button" id="<?=$arParams["GRID_ID"]?>-grid-settings-apply-button"><?=Loc::getMessage("interface_grid_apply_settings")?></span><?
					?><span class="ui-btn ui-btn-link main-grid-settings-window-actions-item-button" id="<?=$arParams["GRID_ID"]?>-grid-settings-cancel-button"><?=Loc::getMessage("interface_grid_cancel_settings")?></span><?
					?><div class="main-grid-settings-window-select-links"><?
						?><span class="main-grid-settings-window-select-link main-grid-settings-window-select-all"><?=Loc::getMessage("interface_grid_settings_select_all_columns")?></span><?
						?><span class="main-grid-settings-window-select-link main-grid-settings-window-unselect-all"><?=Loc::getMessage("interface_grid_settings_unselect_all_columns")?></span><?
					?></div><?
				?></div><?
			?></div><?
		}
		?><div class="main-grid-wrapper<?=!$arParams["ALLOW_HORIZONTAL_SCROLL"] ? " main-grid-full" : "" ?>"><?
			?><div class="<?=$arParams["ALLOW_HORIZONTAL_SCROLL"] ? "main-grid-fade" : "" ?>"><?
				if ($arParams["ALLOW_HORIZONTAL_SCROLL"]) : ?><?
					?><div class="main-grid-fade-shadow-left"></div><?
					?><div class="main-grid-fade-shadow-right"></div><?
					?><div class="main-grid-ear main-grid-ear-left"></div><?
					?><div class="main-grid-ear main-grid-ear-right"></div><?
				endif; ?><?
				?><div class="main-grid-loader-container"></div><?
				?><div class="main-grid-container<?=$arParams["ALLOW_STICKED_COLUMNS"] && $arResult["HAS_STICKED_COLUMNS"] ? " main-grid-with-sticked" : ""?>"><?
					?><table class="main-grid-table" id="<?=$arParams["GRID_ID"]?>_table">
						<thead class="main-grid-header" data-relative="<?=$arParams["GRID_ID"]?>"><?
								?><tr class="main-grid-row-head"><?
									if ($arParams["ALLOW_ROWS_SORT"]) :
									?><th class="main-grid-cell-head main-grid-cell-static main-grid-cell-drag<?=$arParams["ALLOW_STICKED_COLUMNS"] && $arResult["HAS_STICKED_COLUMNS"] ? " main-grid-sticked-column" : ""?>"><?
										?><span class="main-grid-cell-head-container">&nbsp;</span><?
									?></th><?
									endif;
									if ($arParams["SHOW_ROW_CHECKBOXES"]): ?><?
										?><th class="main-grid-cell-head main-grid-cell-static main-grid-cell-checkbox<?=$arParams["ALLOW_STICKED_COLUMNS"] && $arResult["HAS_STICKED_COLUMNS"] ? " main-grid-sticked-column" : ""?>"><?
											if ($arParams["SHOW_CHECK_ALL_CHECKBOXES"]): ?><?
												?><span class="main-grid-cell-head-container"><?
													?><span class="main-grid-checkbox-container main-grid-head-checkbox-container"><?
														?><input class="main-grid-checkbox main-grid-row-checkbox main-grid-check-all" id="<?=$arParams["GRID_ID"]?>_check_all" type="checkbox" title="<?=getMessage('interface_grid_check_all') ?>"<? if (!$arResult['ALLOW_EDIT']): ?> disabled<? endif ?>><?
														?><label class="main-grid-checkbox" for="<?=$arParams["GRID_ID"]?>_check_all"></label><?
												?></span><?
											?></span><?
										endif; ?><?
										?></th><?
									endif ?><?
									if ($arParams["SHOW_GRID_SETTINGS_MENU"] || $arParams["SHOW_ROW_ACTIONS_MENU"]) : ?><?
										?><th class="main-grid-cell-head main-grid-cell-static main-grid-cell-action<?=$arParams["ALLOW_STICKED_COLUMNS"] && $arResult["HAS_STICKED_COLUMNS"] ? " main-grid-sticked-column" : ""?>"><?
											if ($arParams["SHOW_GRID_SETTINGS_MENU"]) : ?><?
												?><span class="main-grid-interface-settings-icon"></span><?
											endif; ?><?
										?></th><?
									endif; ?><?
									foreach ($arResult['COLUMNS'] as $id => $header) :
										?><th class="main-grid-cell-head<?=$header["layout"]["cell"]["class"]?>"<?=$header["layout"]["cell"]["attributes"]?>><?
											?><div class="main-grid-cell-inner"<?=$header["layout"]["container"]["attributes"]?>><?
												if (isset($header["layout"]["hasLeftAlignedCounter"]) && $header["layout"]["hasLeftAlignedCounter"]) :
													?><span class="main-grid-cell-counter main-grid-cell-counter-left-aligned"></span><?
												endif;
												?><span class="main-grid-cell-head-container"><?
												if (!empty($header['iconUrl'])) :
													$iconTitle = Text\HtmlFilter::encode($header['iconTitle'] ?? '');
													?><span class="main-grid-head-icon"><img src="<?= Text\HtmlFilter::encode($header['iconUrl']) ?>" title="<?= $iconTitle ?>" alt=""></span><?
												endif;
												?><span class="main-grid-head-title<?=$arParams['DISABLE_HEADERS_TRANSFORM'] ? " main-grid-head-title-without-transform" : ""?>"><?
												echo Text\HtmlFilter::encode($header["showname"] ? $header["name"] : "");
												if (isset($header["hint"])) :
													?><script><?
														?>BX.ready(function() {
															BX.UI.Hint.init(BX('hint_<?=$header["id"]?>'));
														});<?
													?></script><?
													?><span id="hint_<?=$header["id"]?>" class="main-grid-head-title-tooltip" title=""><?
														?><span <?=empty($header['hintInteractivity']) ? '' : 'data-hint-interactivity'?> <?=empty($header['hintHtml']) ? '' : 'data-hint-html'?> data-hint="<?= Text\HtmlFilter::encode($header["hint"]) ?>"></span><?
													?></span><?
												endif;
												?></span><?
												if ($arParams["ALLOW_COLUMNS_RESIZE"] && $header["resizeable"] !== false) : ?><?
													?><span class="main-grid-resize-button" onclick="event.stopPropagation(); " title=""></span><?
												endif; ?><?
												if ($header["sort"] && $arParams["ALLOW_SORT"]) : ?><?
													?><span class="main-grid-control-sort main-grid-control-sort-<?=$header["sort_state"] ? $header["sort_state"] : "hover-".$header["order"]?>"></span><?
												endif;
												?></span><?
											?></div><?
										?></th><?
									endforeach ?><?
									?><th class="main-grid-cell-head main-grid-cell-static main-grid-special-empty"></th><?
								?></tr><?
							?></thead>
							<tbody><?
							if (
								empty($arParams['ROWS'])
								|| (count($arParams['ROWS']) === 1 && $arParams['ROWS'][0]['id'] === 'template_0')
								|| isset($arParams['STUB'])
							): ?><?
								?><tr class="main-grid-row main-grid-row-empty main-grid-row-body"><?
									?><td class="main-grid-cell main-grid-cell-center" colspan="<?=count($arParams['COLUMNS']) + $additionalColumnsCount + $stickedColumnsCount?>"><?
										?><div class="main-grid-empty-block"><?
											?><div class="main-grid-empty-inner"><?
												if (isset($arParams['STUB']) && is_array($arParams['STUB'])) :
													if (isset($arParams['STUB']['title'])) :
														?><div class="main-grid-empty-block-title"><?=$arParams['STUB']['title']?></div><?
													endif;
													if (isset($arParams['STUB']['description'])) :
														?><div class="main-grid-empty-block-description"><?=$arParams['STUB']['description']?></div><?
													endif;
												elseif (isset($arParams['STUB']) && is_string($arParams['STUB'])) :
													echo htmlspecialcharsback($arParams['STUB']);
												else :
													?><div class="main-grid-empty-image"></div><?
													?><div class="main-grid-empty-text"><?
														if (isset($_REQUEST["apply_filter"])) :
															echo getMessage('interface_grid_filter_no_data');
														else :
															echo getMessage('interface_grid_no_data');
														endif;
													?></div><?
												endif;
											?></div><?
										?></div><?
									?></td><?
								?></tr><?
							endif;
							if (!empty($arResult['ROWS']) || (count($arParams['ROWS']) === 1 && $arParams['ROWS'][0]['id'] === 'template_0')) :
								foreach($arParams['ROWS'] as $key => $arRow):
									$rowClasses = isset($arRow['columnClasses']) && is_array($arRow['columnClasses'])
										? $arRow['columnClasses'] : array();
								if (!empty($arRow["custom"])) :
									$lastCollapseGroup = isset($arRow["expand"]) && $arRow["expand"] === false ? $arRow["group_id"] : null;
									?><tr class="main-grid-row main-grid-row-body main-grid-row-custom<?=$arRow["layout"]["row"]["class"]?>"<?=$arRow["layout"]["row"]["attributes"]?>><?
										?><td colspan="<?=count($arResult["COLUMNS"]) + $additionalColumnsCount?>" class="main-grid-cell main-grid-cell-center"><?
											if ($arParams["ENABLE_COLLAPSIBLE_ROWS"] && $arRow["has_child"] == true) :
												?><span class="main-grid-plus-button"></span><?
											endif;
											?><div class="main-grid-cell-content"><?=$arRow["custom"]?></div><?
										?></td><?
									?></tr><?
								elseif (!empty($arParams["ROW_LAYOUT"])) :
									$actions = Text\HtmlFilter::encode(Json::encode($arRow["actions"]));
									$depth = $arRow["depth"] > 0 ? 20*$arRow["depth"] : 0;
									?><tr class="main-grid-row main-grid-row-body<?=$arRow["layout"]["row"]["class"]?>"<?=$arRow["layout"]["row"]["attributes"]?>>
										<? if ($arParams["ALLOW_ROWS_SORT"] && $arRow["draggable"] !== false) : ?>
											<td class="main-grid-cell main-grid-cell-drag" rowspan="<?=count($arParams["ROW_LAYOUT"])?>">
												<span class="main-grid-cell-content">&nbsp;</span>
											</td>
										<? endif; ?>
										<? if ($arParams["SHOW_ROW_CHECKBOXES"]): ?>
											<td class="main-grid-cell main-grid-cell-checkbox" rowspan="<?=count($arParams["ROW_LAYOUT"])?>">
												<span class="main-grid-cell-content">
													<input type="checkbox" class="main-grid-row-checkbox main-grid-checkbox" name="ID[]" value="<?=$arRow["id"] ?>" <? if ($arRow['editable'] !== false): ?> title="<?=getMessage('interface_grid_check') ?>" id="checkbox_<?=$arParams["GRID_ID"]?>_<?=$arRow["id"] ?>"<? endif ?> <? if (!$arResult['ALLOW_EDIT'] || $arRow['editable'] === false): ?> data-disabled="1" disabled<? endif ?>>
													<label class="main-grid-checkbox" for="checkbox_<?=$arParams["GRID_ID"]?>_<?=$arRow["id"] ?>"></label>
												</span>
											</td>
										<? endif ?>
										<? if ($arParams["SHOW_ROW_ACTIONS_MENU"] || $arParams["SHOW_GRID_SETTINGS_MENU"]) : ?>
											<td class="main-grid-cell main-grid-cell-action" rowspan="<?=count($arParams["ROW_LAYOUT"])?>">
												<span class="main-grid-cell-content">
													<? if (!empty($arRow["actions"]) && $arParams["SHOW_ROW_ACTIONS_MENU"]) : ?>
														<a href="#" class="main-grid-row-action-button" data-actions="<?=$actions?>"></a>
													<? endif; ?>
												</span>
											</td>
										<? endif; ?>

								<?
									foreach ($arParams["ROW_LAYOUT"] as $rowIndex => $rowLayout) :
										foreach ($rowLayout as $rowLayoutCellIndex => $rowLayoutCell) :
											$showedColumns[] = $rowLayoutCell["column"];
										endforeach;
									endforeach;

									$showedColumns = array_unique($showedColumns);

									$showedColumnsFromLayout = array();

									foreach ($arParams["ROW_LAYOUT"] as $rowIndex => $rowLayout) :
										foreach ($rowLayout as $rowLayoutCellIndex => $rowLayoutCell) :
											if (array_key_exists($rowLayoutCell["column"], $arResult["COLUMNS"]) && !isset($rowLayoutCell["rowspan"]))
											{
												$showedColumnsFromLayout[] = $rowLayoutCell["column"];
											}
										endforeach;
									endforeach;

								?>

								<? foreach ($arParams["ROW_LAYOUT"] as $rowIndex => $rowLayout) : ?>
									<? if ($rowIndex > 0) : ?>
										<tr class="main-grid-row main-grid-row-body<?=$arRow["layout"]["row"]["class"]?>" data-bind="<?=$arRow["id"]?>"<?=$arRow["layout"]["row"]["attributes"]?>>
									<? endif; ?>
										<? foreach ($rowLayout as $rowLayoutCellIndex => $rowLayoutCell) :
											$colLayout = $arRow["layout"]["columns"][$rowLayoutCell["column"]];
											if (!$colLayout)
											{
												$colLayout = [
													"cell" => [
														"class" => "main-grid-cell",
														"attributes" => "",
													],
													"container" => [
														"attributes" => "",
													],
													"plusButton" => [
														"enabled" => false,
													],
												];
											}
											$header = $arResult["COLUMNS"][$rowLayoutCell["column"]];

											$className = "";
											if (count($arParams["ROW_LAYOUT"]) > 1 && $rowIndex < (count($arParams["ROW_LAYOUT"])-1) && !isset($rowLayoutCell["rowspan"]))
											{
												$className .= " main-grid-cell-no-border";
											}

											$colspan = 0;
											if (isset($rowLayoutCell["colspan"]))
											{
												$colspan = min($rowLayoutCell["colspan"], count($showedColumnsFromLayout));
											}
										?>
											<? if (isset($rowLayoutCell["data"]) || array_key_exists($rowLayoutCell["column"], $arResult["COLUMNS"])) : ?>
												<td class="<?=$colLayout["cell"]["class"]?><?=$className?>"<?=$colLayout["cell"]["attributes"]?><?=$rowLayoutCell["rowspan"] ? " rowspan=\"".$rowLayoutCell["rowspan"]."\"" : ""?><?=$rowLayoutCell["colspan"] ? " colspan=\"".$colspan."\"" : ""?>>
													<span class="main-grid-cell-content"<?=$colLayout["container"]["attributes"]?>>
														<? if ($colLayout["plusButton"]["enabled"]) : ?>
															<span class="main-grid-plus-button"></span>
														<? endif; ?>
														<?
															if (isset($rowLayoutCell["column"]) && isset($arRow["columns"][$rowLayoutCell["column"]]))
															{
																echo $arRow["columns"][$rowLayoutCell["column"]];
															}
															else if (isset($rowLayoutCell["data"]) && isset($arRow["data"][$rowLayoutCell["data"]]))
															{
																echo $arRow["data"][$rowLayoutCell["data"]];
															}
														?>
													</span>
												</td>
											<? endif; ?>
										<? endforeach; ?>

										<? if ($rowIndex === 0) : ?>
											<? foreach ($arResult['COLUMNS'] as $id => $header) : ?>
												<? if (!in_array($header["id"], $showedColumns)) :
													$colLayout = $arRow["layout"]["columns"][$header["id"]];
													$preventDefault = $header["prevent_default"] ? "true" : "false";
													$showedColumns[] = $rowLayoutCell["column"];

													$className = "";
													if (count($arParams["ROW_LAYOUT"]) > 1 && $rowIndex < (count($arParams["ROW_LAYOUT"])-1) && !isset($rowLayoutCell["rowspan"]))
													{
														$className .= " main-grid-cell-no-border";
													}

													$isShift = $arParams["ENABLE_COLLAPSIBLE_ROWS"] && $arResult["HEADERS"][$header["id"]]["shift"] == true;
													$isWithButton = $arParams["ENABLE_COLLAPSIBLE_ROWS"] && $arRow["has_child"] == true && $isShift;
													?>
													<td class="<?=$colLayout["cell"]["class"]?><?=$className?>"<?=$colLayout["cell"]["attributes"]?> rowspan="<?=count($arParams["ROW_LAYOUT"])?>">
														<span class="main-grid-cell-content"<?=$colLayout["container"]["attributes"]?>>
															<? if ($colLayout["plusButton"]["enabled"]) : ?>
																<span class="main-grid-plus-button"></span>
															<? endif; ?>
															<?
																if (isset($arRow["columns"][$header["id"]]))
																{
																	echo $arRow["columns"][$header["id"]];
																}
																else if (isset($arRow["data"][$header["id"]]))
																{
																	echo $arRow["data"][$header["id"]];
																}
															?>
														</span>
													</td>

												<? endif; ?>
											<? endforeach; ?>
											<td class="main-grid-cell" rowspan="<?=count($arParams["ROW_LAYOUT"])?>"></td>
										<? endif; ?>
									</tr>
								<? endforeach; ?>

								<?
								else :
								?><tr class="main-grid-row main-grid-row-body<?=$arRow["layout"]["row"]["class"]?>"<?=$arRow["layout"]["row"]["attributes"]?>><?
									if ($arRow["layout"]["columns"]["drag"]["cell"]["enabled"]) :
										?><td class="main-grid-cell main-grid-cell-drag"><?
											?><span class="main-grid-cell-content">&nbsp;</span><?
										?></td><?
									endif;
									if ($arRow["layout"]["columns"]["checkbox"]["cell"]["enabled"]): ?><?
										?><td class="main-grid-cell main-grid-cell-checkbox"><?
											?><span class="main-grid-cell-content"><?
												?><input type="checkbox" class="main-grid-row-checkbox main-grid-checkbox"<?=$arRow["layout"]["columns"]["checkbox"]["input"]["attributes"]?>><?
												?><label class="main-grid-checkbox" for="checkbox_<?=$arParams["GRID_ID"]?>_<?=$arRow["id"] ?>"></label><?
											?></span><?
										?></td><?
									endif ?><?
										if ($arRow["layout"]["columns"]["actions"]["cell"]["enabled"]) :
											?><td class="main-grid-cell main-grid-cell-action"><?
												?><span class="main-grid-cell-content"><?
													if ($arRow["layout"]["columns"]["actions"]["button"]["enabled"]) : ?><?
														?><a href="#" class="main-grid-row-action-button"<?=$arRow["layout"]["columns"]["actions"]["button"]["attributes"]?>></a><?
													endif;
												?></span><?
											?></td><?
										endif; ?><?
										foreach ($arResult['COLUMNS'] as $id => $header):
											$colLayout = $arRow["layout"]["columns"][$id];
											?><td class="<?=$colLayout["cell"]["class"]?>"<?=$colLayout["cell"]["attributes"]?>><?
												?><div class="main-grid-cell-inner"><?
													if ($colLayout["counter"]["enabled"] && $colLayout["counter"]["align"] === "left") :
														?><span class="main-grid-cell-counter<?=$colLayout["counter"]["class"]?>"><?
															if ($colLayout["counter"]["inner"]["enabled"]) :
																?><span class="ui-counter<?=$colLayout["counter"]["counter"]["class"]?>"<?=$colLayout["counter"]["counter"]["attributes"]?>><?
																	?><span class="ui-counter-inner"><?=$arRow["counters"][$id]["value"]?></span>
																<?
																if (!empty($colLayout["counter"]["counter"]["isDouble"])) :
																	?><span class="ui-counter-secondary<?=$colLayout["counter"]["counter"]["secondaryClass"]?>"></span><?
																endif;
																?></span><?
															endif;
														?></span><?
													endif;
													?><span class="main-grid-cell-content"<?=$colLayout["container"]["attributes"]?>><?
														if ($colLayout["plusButton"]["enabled"]) :
															?><span class="main-grid-plus-button"></span><?
														endif;

														if (
															isset($header["type"])
															&& is_string($header["type"])
															&& isset($arRow["columns"][$header["id"]])
														)
														{
															if (
																$header["type"] === Grid\Column\Type::CHECKBOX
																&& in_array($arRow["columns"][$header["id"]], ["Y", "N"])
															)
															{
																if ($arRow["columns"][$header["id"]] === "Y")
																{
																	echo Loc::getMessage("interface_grid_yes");
																}
																else
																{
																	echo Loc::getMessage("interface_grid_no");
																}
															}
															else if (
																$header["type"] === Grid\Column\Type::LABELS
																&& is_array($arRow["columns"][$header["id"]])
															)
															{
																?><div class="main-grid-labels"><?
																	foreach ($arRow["columns"][$header["id"]] as $labelKey => $label) :
																		$labelLayout = $colLayout["content"][$labelKey];
																		?><span class="ui-label<?=$labelLayout["class"]?>"<?=$labelLayout["attributes"]?>><?
																			if (isset($label["html"]) && is_string($label["html"])) :
																				?><span class="ui-label-inner"><?=$label["html"]?></span><?
																			else :
																				?><span class="ui-label-inner"><?=htmlspecialcharsbx($label["text"])?></span><?
																			endif;
																			if ($labelLayout["removeButton"]["enabled"]) :
																				if ($labelLayout["removeButton"]["type"] === Grid\Cell\Label\RemoveButtonType::INSIDE) :
																					?><span class="ui-label-icon"<?=$labelLayout["removeButton"]["attributes"]?>></span><?
																				else :
																					?><span class="main-grid-labels-remove-button<?=$labelLayout["removeButton"]["class"]?>"<?=$labelLayout["removeButton"]["attributes"]?>></span><?
																				endif;
																			endif;
																		?></span><?
																	endforeach;
																?></div><?
															}
															else if (
																$header["type"] === Grid\Column\Type::TAGS
																&& is_array($arRow["columns"][$header["id"]])
															)
															{
																?><div class="main-grid-tags"><?
																	foreach ($arRow["columns"][$header["id"]]["items"] as $tagKey => $tag) :
																		$tagLayout = $colLayout["content"]["items"][$tagKey];
																		?><span class="main-grid-tag<?=$tagLayout["class"]?>"<?=$tagLayout["attributes"]?>><?
																			if (isset($tag["html"]) && is_string($tag["html"])) :
																				?><span class="main-grid-tag-inner"><?=$tag["html"]?></span><?
																			else :
																				?><span class="main-grid-tag-inner"><?=htmlspecialcharsbx($tag["text"])?></span><?
																			endif;
																			if ($tagLayout["active"]) :
																				?><span class="main-grid-tag-remove"<?=$tagLayout["removeButton"]["attributes"]?>></span><?
																			endif;
																		?></span><?
																	endforeach;
																	if ($colLayout["content"]["addButton"]["enabled"]) :
																		?><span class="main-grid-tag-add"<?=$colLayout["content"]["addButton"]["attributes"]?>></span><?
																	endif;
																?></div><?
															}
															else
															{
																echo $arRow["columns"][$header["id"]] ?? '';
															}
														}
														else
														{
															echo $arRow["columns"][$header["id"]] ?? '';
														}
													?></span><?
													if ($colLayout["cellActions"]["enabled"]) :
														?><span class="main-grid-cell-content-actions"><?
															foreach ($colLayout["cellActions"]["items"] as $item) :
																?><span class="main-grid-cell-content-action<?=$item["class"]?>"<?=$item["attributes"]?>></span><?
															endforeach;
														?></span><?
													endif;
													if ($colLayout["counter"]["enabled"] && $colLayout["counter"]["align"] === "right") :
														?><span class="main-grid-cell-counter<?=$colLayout["counter"]["class"]?>"><?
															if ($colLayout["counter"]["inner"]["enabled"]) :
																?><span class="ui-counter<?=$colLayout["counter"]["counter"]["class"]?>"<?=$colLayout["counter"]["counter"]["attributes"]?>><?
																	?><span class="ui-counter-inner"><?=$arRow["counters"][$id]["value"]?></span>
																<?
																if (!empty($colLayout["counter"]["counter"]["isDouble"])) :
																	?><span class="ui-counter-secondary<?=$colLayout["counter"]["counter"]["secondaryClass"]?>"></span><?
																endif;
																?></span><?
															endif;
														?></span><?
													endif;
												?></div><?
											?></td><?
										endforeach ?><?
									?><td class="main-grid-cell"></td><?
								?></tr><?
							endif; ?>
						<? endforeach ?><?
						if (!empty($arResult['AGGREGATE'])): ?><?
						?><tr class="main-grid-row-foot main-grid-aggr-row" id="datarow_<?=$arParams["GRID_ID"]?>_bxaggr"><?
							if ($arParams['ALLOW_GROUP_ACTIONS']): ?><td class="main-grid-cell-foot"></td><? endif ?><?
								if ($arParams['ALLOW_ROW_ACTIONS']): ?><td class="main-grid-cell-foot"></td><? endif ?><?
									foreach ($arResult['COLUMNS'] as $id => $header): ?><?
											?><td class="main-grid-cell-foot <? if ($header['align']) echo 'main-grid-cell-', $header['align']; ?>" <? if ($isHidden): ?> style="display: none; "<? endif ?>><?
													?><span class="main-grid-cell-content main-grid-cell-text-line"><?
														if (!empty($arResult['AGGREGATE'][$id])): ?><?
															foreach ($arResult['AGGREGATE'][$id] as $item): ?><?
																?><?=$item; ?><br><?
															endforeach; ?><?
														endif; ?><?
													?></span><?
											?></td><?
									endforeach; ?><?
								?><td class="main-grid-cell-foot"></td><?
							?></tr><?
						endif ?><?
					endif ?><?
				?></tbody><?
			?></table><?
		?></div><?
	?></div><?
?></div><?
	?><div class="main-grid-bottom-panels" id="<?=$arParams["GRID_ID"]?>_bottom_panels"><?
		?><div class="main-grid-nav-panel"><?
			?><div class="main-grid-more" id="<?=$arParams["GRID_ID"]?>_nav_more"><?
				?><a href="<?=$arResult["NEXT_PAGE_URL"]?>" class="main-grid-more-btn" data-slider-ignore-autobinding="true" <? if (!$arResult["SHOW_MORE_BUTTON"] || !$arParams["SHOW_MORE_BUTTON"] || !count($arResult["ROWS"])): ?>style="display: none; "<? endif ?>><?
					?><span class="main-grid-more-text"><?=getMessage('interface_grid_nav_more') ?></span><?
					?><span class="main-grid-more-load-text"><?=getMessage('interface_grid_load') ?></span><?
					?><span class="main-grid-more-icon"></span><?
				?></a><?
			?></div><?
		if ($arParams["SHOW_NAVIGATION_PANEL"]) : ?><?
			?><div class="main-grid-panel-wrap"><?
				?><table class="main-grid-panel-table"><?
					?><tr><?
						if ($arParams["SHOW_SELECTED_COUNTER"]) : ?><?
							?><td class="main-grid-panel-cell main-grid-panel-counter main-grid-cell-left"><?
								?><div class="main-grid-panel-content main-grid-panel-counter-for-selected"><?
									?><span class="main-grid-panel-content-title"><?=getMessage('interface_grid_checked') ?></span>&nbsp;<?
									?><span class="main-grid-panel-content-text"><?
										?><span class="main-grid-counter-selected">0</span><?
										?>&nbsp;/&nbsp;<?
										?><span class="main-grid-counter-displayed"><?=$displayedCount?></span><?
									?></span><?
								?></div><?
								?><div class="main-grid-panel-content main-grid-panel-counter-for-all"><?
									?><span class="main-grid-panel-content-text"><?=Loc::getMessage("interface_grid_all_selected")?></span><?
								?></div><?
							?></td><?
							endif; ?><?
							if ($arParams["SHOW_TOTAL_COUNTER"] && (isset($arResult["TOTAL_ROWS_COUNT"]) || !empty($arParams["TOTAL_ROWS_COUNT_HTML"]))) : ?><?
							?><td class="main-grid-panel-total main-grid-panel-cell main-grid-cell-left"><?
								?><div class="main-grid-panel-content"><?
									if (empty($arParams["TOTAL_ROWS_COUNT_HTML"])) : ?><?
										?><span class="main-grid-panel-content-title"><?=GetMessage("interface_grid_total")?>:</span><?
										?>&nbsp;<span class="main-grid-panel-content-text"><?=count($arResult["ROWS"]) ? $arResult["TOTAL_ROWS_COUNT"] : 0?></span><?
									else : ?><?
										?><?=Text\HtmlConverter::getHtmlConverter()->decode($arParams["TOTAL_ROWS_COUNT_HTML"])?><?
									endif; ?><?
								?></div><?
							?></td><?
						endif; ?><?
						?><td class="main-grid-panel-cell main-grid-panel-cell-pagination main-grid-cell-left"><?
							if ($arParams["SHOW_PAGINATION"]) : ?><?
								?><?=Bitrix\Main\Text\Converter::getHtmlConverter()->decode($arResult["NAV_STRING"]);?><?
							endif; ?><?
						?></td><?
						?><td class="main-grid-panel-cell main-grid-panel-limit main-grid-cell-right"><?
							if ($arParams["SHOW_PAGESIZE"] && is_array($arParams["PAGE_SIZES"]) && !empty($arParams["PAGE_SIZES"])) :
									$pageSize = $arResult['OPTIONS']['views'][$arResult['OPTIONS']['current_view']]['page_size'] ?? $arParams["DEFAULT_PAGE_SIZE"]; ?><?
								?><span class="main-grid-panel-content"><?
									?><span class="main-grid-panel-content-title"><?=getMessage('interface_grid_page_size') ?></span> <?
										?><span class="main-dropdown main-grid-popup-control main-grid-panel-select-pagesize" id="<?=$arParams["GRID_ID"]?>_grid_page_size" data-value="<?=$pageSize;?>" data-items="<?=$arResult["PAGE_SIZES_JSON"]?>">
											<span class="main-dropdown-inner"> <?=$pageSize; ?></span><?
										?></span><?
									?></span><?
								endif; ?><?
							?></td><?
						?></tr><?
					?></table><?
				?></div><?
			endif; ?><?
		?></div>
		<? if ($arParams["SHOW_ACTION_PANEL"] && isset($arParams["ACTION_PANEL"]) && !empty($arParams["ACTION_PANEL"]) && is_array($arParams["ACTION_PANEL"]["GROUPS"])) : ?><?
			?><div class="main-grid-action-panel main-grid-disable"><?
				?><div class="main-grid-control-panel-wrap"><?
					?><table class="main-grid-control-panel-table"><?
						?><tr class="main-grid-control-panel-row"><?
							foreach ($arParams["ACTION_PANEL"]["GROUPS"] as $groupKey => $group) : ?><?
								?><td class="main-grid-control-panel-cell<?= isset($group["CLASS"]) && $group["CLASS"] ? " ".$group["CLASS"] : "" ?>"><?
									$itemsCounter = 0;
									foreach ($group["ITEMS"] as $itemKey => $item) : ?><?
										if (!isset($item["ID"]))
										{
											$item["ID"] = "group-{$groupKey}-item-{$itemsCounter}";
											$itemsCounter++;
										}

										$item["CLASS"] = $item["CLASS"] ?? '';

										if ($item["TYPE"] === "CHECKBOX") :
											?><span class="main-grid-panel-control-container<?= (isset($item["DISABLED"]) && $item["DISABLED"] ? " main-grid-disable" : "")?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>"><?
												if ($item["NAME"] === Grid\Panel\DefaultValue::FOR_ALL_CHECKBOX_NAME) : ?><?
													?><span class="main-grid-checkbox-container main-grid-control-panel-checkbox-container"><?
															?><input class="main-grid-panel-checkbox main-grid-checkbox main-grid-panel-control <?=$item["CLASS"]?>" id="<?=Text\HtmlFilter::encode($item["ID"])?><?=$arParams["GRID_ID"]?>" name="<?=Text\HtmlFilter::encode($item["NAME"])?><?=$arParams["GRID_ID"]?>" type="checkbox" value="<?=Text\HtmlFilter::encode($item["VALUE"])?>" title="<?=Text\HtmlFilter::encode($item["TITLE"] ?? '')?>" data-onchange="<?=Text\HtmlFilter::encode(Json::encode($item["ONCHANGE"]))?>"<?= (isset($item["CHECKED"]) && $item["CHECKED"] ? " checked" : "")?>> <?
															?> <label class="main-grid-checkbox" for="<?=Text\HtmlFilter::encode($item["ID"])?><?=$arParams["GRID_ID"]?>"></label><?
													?></span><?
													?><span class="main-grid-control-panel-content-title"><?
														?> <label for="<?=Text\HtmlFilter::encode($item["ID"])?><?=$arParams["GRID_ID"]?>" title="<?=Loc::getMessage("interface_grid_for_all")?>"><?=Loc::getMessage("interface_grid_for_all_box")?></label><?
													?></span><?
												else : ?><?
													?><span class="main-grid-checkbox-container main-grid-control-panel-checkbox-container"><?
														?><input class="main-grid-panel-checkbox main-grid-checkbox main-grid-panel-control" id="<?=Text\HtmlFilter::encode($item["ID"])?>_control" name="<?=Text\HtmlFilter::encode($item["NAME"])?>" type="checkbox" value="<?=Text\HtmlFilter::encode($item["VALUE"])?>" title="<?=Text\HtmlFilter::encode($item["TITLE"])?>" data-onchange="<?=Text\HtmlFilter::encode(Json::encode($item["ONCHANGE"]))?>"<?=$item["CHECKED"] ? " checked" : ""?>><?
														?> <label class="main-grid-checkbox" for="<?=Text\HtmlFilter::encode($item["ID"])?>_control"></label><?
													?></span><?
													?><span class="main-grid-control-panel-content-title"><?
														?> <label for="<?=Text\HtmlFilter::encode($item["ID"])?>_control" title="<?=Text\HtmlFilter::encode($item["TITLE"])?>"><?=Text\HtmlFilter::encode($item["LABEL"])?></label><?
													?></span><?
												endif;
											?></span><?
										endif; ?><?
										if ($item["TYPE"] === "DROPDOWN") :
											?><span class="main-grid-panel-control-container<?= isset($item["DISABLED"]) && $item["DISABLED"] ? " main-grid-disable" : "";?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>"><?
												?><span class="main-dropdown main-grid-panel-control" data-popup-position="fixed" id="<?=Text\HtmlFilter::encode($item["ID"])?>_control" data-name="<?=Text\HtmlFilter::encode($item["NAME"])?>" data-value="<?=Text\HtmlFilter::encode(Json::encode($item["ITEMS"][0]["VALUE"]))?>" data-items="<?=Text\HtmlFilter::encode(Json::encode($item["ITEMS"]))?>"><?
													?><span class="main-dropdown-inner"><?=$item["ITEMS"][0]["NAME"]?></span><?
												?></span><?
											?></span><?
										endif; ?><?
										if ($item["TYPE"] === "CUSTOM") : ?><?
											?><span class="main-grid-panel-control-container<?= isset($item["DISABLED"]) && $item["DISABLED"] ? " main-grid-disable" : "";?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>">
												<div class="main-grid-panel-custom">
													<?=$item["VALUE"]?>
												</div>
											</span><?
										endif; ?><?
										if ($item["TYPE"] === "TEXT") : ?><?
										?><span class="main-grid-panel-control-container<?= isset($item["DISABLED"]) && $item["DISABLED"] ? " main-grid-disable" : "";?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>"><?
											if ($item["LABEL"]) : ?><?
												?><label for="<?=Text\HtmlFilter::encode($item["ID"])?>_control"><?=Text\HtmlFilter::encode($item["LABEL"])?></label><?
											endif;
											?> <input type="text" class="main-grid-control-panel-input-text main-grid-panel-control" name="<?=Text\HtmlFilter::encode($item["NAME"])?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>_control" placeholder="<?=Text\HtmlFilter::encode($item["PLACEHOLDER"])?>" value="<?=Text\HtmlFilter::encode($item["VALUE"])?>" title="<?=Text\HtmlFilter::encode($item["TITLE"])?>"><?
										?></span><?
										endif; ?><?
										if ($item["TYPE"] === "BUTTON") : ?><?
                                            $buttonModifier = "ui-btn-light-border";
                                            if (is_string($item["CLASS"]) && !empty($item["CLASS"]))
                                            {
                                                $buttonModifier = $item["CLASS"];
                                            }
										?><span class="main-grid-panel-control-container<?= isset($item["DISABLED"]) && $item["DISABLED"] ? " main-grid-disable" : "";?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>"><?
											?><span class="ui-btn <?=Text\HtmlFilter::encode($buttonModifier)?>" data-name="<?=Text\HtmlFilter::encode($item["NAME"] ?? '')?>" data-value="<?=Text\HtmlFilter::encode($item["VALUE"] ?? '')?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>_control" data-onchange="<?=Text\HtmlFilter::encode(Json::encode($item["ONCHANGE"]))?>" title="<?=Text\HtmlFilter::encode($item["TITLE"] ?? '')?>"><?
												?><?=$item["TEXT"]
											?></span><?
										?></span><?
										endif; ?><?
										if ($item["TYPE"] === "LINK") :
											?><span class="main-grid-panel-control-container<?=$item["DISABLED"] ? " main-grid-disable" : "";?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>" data-onchange="<?=Text\HtmlFilter::encode(Json::encode($item["ONCHANGE"]))?>" title="<?=Text\HtmlFilter::encode($item["TITLE"])?>"><?
												?><a href="<?=Text\HtmlFilter::encode($item["HREF"])?>" class="main-grid-link<?=$item["CLASS"] ? " ".Text\HtmlFilter::encode($item["CLASS"]) : ""?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>_control"><?=$item["TEXT"]?></a><?
											?></span><?
										endif; ?><?
										if ($item["TYPE"] === "DATE") :
										?><span class="main-grid-panel-control-container<?=$item["DISABLED"] ? " main-grid-disable" : "";?>" id="<?=Text\HtmlFilter::encode($item["ID"])?>" data-onchange="<?=Text\HtmlFilter::encode(Json::encode($item["ONCHANGE"]))?>" title="<?=Text\HtmlFilter::encode($item["TITLE"])?>"><?
											?><span class="main-ui-control main-ui-date main-grid-panel-date"><?
												?><span class="main-ui-date-button"></span><?
												?><input type="text" name="<?=$item["TYPE"]?>" tabindex="1" autocomplete="off" data-time="<?=$item["TIME"] ? "true" : "false"?>" class="main-ui-control-input main-ui-date-input" value="<?=$item["VALUE"]?>" placeholder="<?=$item["PLACEHOLDER"]?>"><?
												?><div class="main-ui-control-value-delete<?=empty($item["VALUE"]) ? " main-ui-hide" : ""?>"><?
													?><span class="main-ui-control-value-delete-item"></span><?
												?></div><?
											?></span><?
										?></span><?
										endif; ?><?
									endforeach; ?><?
								?></td><?
							endforeach; ?><?
							if ($arParams["SHOW_SELECTED_COUNTER"]) : ?><?
								?><td class="main-grid-panel-cell main-grid-panel-counter main-grid-cell-left"><?
									?><div class="main-grid-panel-content main-grid-panel-counter-for-selected"><?
										?><span class="main-grid-panel-content-title"><?=getMessage('interface_grid_checked') ?></span>&nbsp;<?
										?><span class="main-grid-panel-content-text"><?
											?><span class="main-grid-counter-selected">0</span><?
											?>&nbsp;/&nbsp;<?
											?><span class="main-grid-counter-displayed"><?=$displayedCount?></span><?
										?></span><?
									?></div><?
									?><div class="main-grid-panel-content main-grid-panel-counter-for-all"><?
										?><span class="main-grid-panel-content-text"><?=Loc::getMessage("interface_grid_all_selected")?></span><?
									?></div><?
								?></td><?
							endif; ?><?
						?></tr><?
					?></table><?
				?></div><?
			?></div><?
		endif; ?><?
	?></div><?
?></form><?
?><iframe height="0" width="100%" id="main-grid-tmp-frame-<?=$arParams["GRID_ID"]?>" name="main-grid-tmp-frame-<?=$arParams["GRID_ID"]?>" style="position: absolute; z-index: -1; opacity: 0; border: 0;"></iframe><?
?></div>

<?
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if (\Bitrix\Main\Grid\Context::isInternalRequest()) :
?><script>
	(function() {
		var action = '<?=\CUtil::JSEscape($request->get("grid_action"))?>';
		var editableData = <?= Json::encode($arResult["DATA_FOR_EDIT"]) ?>;
		var defaultColumns = <?= Json::encode($arResult["DEFAULT_COLUMNS"]) ?>;
		var Grid = BX.Main.gridManager.getById('<?=\CUtil::JSEscape($arParams["GRID_ID"])?>');
		var messages = <?= Json::encode($arResult["MESSAGES"]) ?>;
		var currentPage = '<?=\CUtil::JSEscape($arParams["CURRENT_PAGE"] ?? '')?>';

		Grid = Grid ? Grid.instance : null;

		if (Grid)
		{
			Grid.arParams.DEFAULT_COLUMNS = defaultColumns;
			Grid.arParams.MESSAGES = messages;
			Grid.arParams.CURRENT_PAGE = currentPage;

			Object.keys(editableData).forEach(function(key) {
				Grid.arParams.EDITABLE_DATA[key] = editableData[key];
			});

			BX.onCustomEvent(window, 'BX.Main.grid:paramsUpdated', []);
		}
	})();
</script><?
endif; ?>

<? if (!$arResult['IS_AJAX'] || !$arResult['IS_INTERNAL']) : ?><?
?><script>
		BX(function() { BX.Main.dropdownManager.init(); });
		BX(function() {

			<? if(isset($arParams['TOP_ACTION_PANEL_RENDER_TO'])): ?>
				var actionPanel = new BX.UI.ActionPanel({
					params: {
						gridId: '<?=\CUtil::jsEscape($arParams['GRID_ID']) ?>'
					},
					pinnedMode: <?= Json::encode($arParams['TOP_ACTION_PANEL_PINNED_MODE']) ?>,
					renderTo: document.querySelector('<?=\CUtil::jsEscape($arParams['TOP_ACTION_PANEL_RENDER_TO']) ?>'),
					className: '<?=\CUtil::jsEscape($arParams['TOP_ACTION_PANEL_CLASS'] ?? '') ?>',
					groupActions: <?= Json::encode($arParams['ACTION_PANEL']) ?>,
					maxHeight: <?= (int)($arParams['ACTION_PANEL_OPTIONS']['MAX_HEIGHT'] ?? null)?>
				});
				actionPanel.draw();
			<? endif; ?>
			<?php
				$columnsAllWithSections = $arResult['COLUMNS_ALL_WITH_SECTIONS'] ?? [];
				$columnsAll = $arResult['COLUMNS_ALL'] ?? [];
			?>
			BX.Main.gridManager.push(
				'<?=\CUtil::jSEscape($arParams["GRID_ID"])?>',
				new BX.Main.grid(
					'<?=\CUtil::jSEscape($arParams["GRID_ID"])?>',
					<?= Json::encode(
						array(
							"ALLOW_COLUMNS_SORT" => $arParams["ALLOW_COLUMNS_SORT"],
							"ALLOW_ROWS_SORT" => $arParams["ALLOW_ROWS_SORT"],
							"ALLOW_ROWS_SORT_IN_EDIT_MODE" => $arParams["ALLOW_ROWS_SORT_IN_EDIT_MODE"],
							"ALLOW_ROWS_SORT_INSTANT_SAVE" => $arParams["ALLOW_ROWS_SORT_INSTANT_SAVE"],
							"ALLOW_COLUMNS_RESIZE" => $arParams["ALLOW_COLUMNS_RESIZE"],
							"SHOW_ROW_CHECKBOXES" => $arParams["SHOW_ROW_CHECKBOXES"],
							"ALLOW_HORIZONTAL_SCROLL" => $arParams["ALLOW_HORIZONTAL_SCROLL"],
							"ALLOW_PIN_HEADER" => $arParams["ALLOW_PIN_HEADER"],
							"SHOW_ACTION_PANEL" => $arParams["SHOW_ACTION_PANEL"],
							"PRESERVE_HISTORY" => $arParams["PRESERVE_HISTORY"] ?? '',
							"ALLOW_CONTEXT_MENU" => $arResult["ALLOW_CONTEXT_MENU"],
							"COLUMNS_ALL" => $columnsAll,
							"DEFAULT_COLUMNS" => $arResult["DEFAULT_COLUMNS"],
							"ENABLE_COLLAPSIBLE_ROWS" => $arParams["ENABLE_COLLAPSIBLE_ROWS"],
							"EDITABLE_DATA" => $arResult["DATA_FOR_EDIT"],
							"SETTINGS_TITLE" => Loc::getMessage("interface_grid_settings_title"),
							"APPLY_SETTINGS" => Loc::getMessage("interface_grid_apply_settings"),
							"CANCEL_SETTINGS" => Loc::getMessage("interface_grid_cancel_settings"),
							"CONFIRM_APPLY" => Loc::getMessage("interface_grid_confirm_apply"),
							"CONFIRM_CANCEL" => Loc::getMessage("interface_grid_confirm_cancel"),
							"CONFIRM_MESSAGE" => Loc::getMessage("interface_grid_confirm_message"),
							"CONFIRM_FOR_ALL_MESSAGE" => Loc::getMessage("interface_grid_confirm_for_all_message_v2"),
							"CONFIRM_RESET_MESSAGE" => Loc::getMessage("interface_grid_settings_confirm_message"),
							"RESET_DEFAULT" => Loc::getMessage("interface_grid_restore_to_default"),
							"SETTINGS_FOR_ALL_LABEL" => Loc::getMessage("interface_grid_settings_for_all_label"),
							"SETTINGS_FOR_LABEL" => Loc::getMessage("interface_grid_settings_for_label"),
							"SETTINGS_FOR_FOR_ALL_LABEL" => Loc::getMessage("interface_grid_settings_for_for_all_label"),
							"SETTINGS_FOR_FOR_ME_LABEL" => Loc::getMessage("interface_grid_settings_for_for_me_label"),
							"SETTINGS_FOR_ALL_CONFIRM_MESSAGE" => Loc::getMessage("interface_grid_settings_for_all_confirm_message"),
							"SETTINGS_FOR_ALL_CONFIRM_APPLY" => Loc::getMessage("interface_grid_settings_for_all_apply"),
							"SETTINGS_FOR_ALL_CONFIRM_CANCEL" => Loc::getMessage("interface_grid_settings_for_all_cancel"),
							"SETTINGS_FIELD_SEARCH_PLACEHOLDER" => Loc::getMessage("interface_grid_field_search_placeholder"),
							"SETTINGS_FIELD_SEARCH_EMPTY_STATE_TITLE" => Loc::getMessage("interface_grid_field_empty_state_title"),
							"SETTINGS_FIELD_SEARCH_EMPTY_STATE_DESCRIPTION" => Loc::getMessage("interface_grid_field_empty_state_description"),
							"SETTINGS_FIELD_SEARCH_ALL_SECTIONS_DISABLED" => Loc::getMessage("interface_grid_field_all_sections_disabled"),
							"MAIN_UI_GRID_IMAGE_EDITOR_BUTTON_EDIT" => Loc::getMessage("interface_grid_image_editor_button_edit"),
							"MAIN_UI_GRID_IMAGE_EDITOR_BUTTON_REMOVE" => Loc::getMessage("interface_grid_image_editor_button_remove"),
							"SAVE_BUTTON_LABEL" => Loc::getMessage("interface_grid_save"),
							"CANCEL_BUTTON_LABEL" => Loc::getMessage("interface_grid_cancel"),
							"CLOSE" => Loc::getMessage("interface_grid_settings_close"),
							"EMPTY_STUB_TEXT" => Loc::getMessage("interface_grid_no_data"),
							"IS_ADMIN" => $USER->CanDoOperation("edit_other_settings"),
							"MESSAGES" => $arResult["MESSAGES"],
							"LAZY_LOAD" => $arResult["LAZY_LOAD"],
							"ALLOW_VALIDATE" => $arParams["ALLOW_VALIDATE"] ?? false,
							"HANDLE_RESPONSE_ERRORS" => $arResult["HANDLE_RESPONSE_ERRORS"],
							"ALLOW_STICKED_COLUMNS" => $arParams["ALLOW_STICKED_COLUMNS"],
							"CHECKBOX_COLUMN_ENABLED" => $arParams["SHOW_ROW_CHECKBOXES"],
							"ACTION_COLUMN_ENABLED" => ($arParams["SHOW_ROW_ACTIONS_MENU"] || $arParams["SHOW_GRID_SETTINGS_MENU"]),
							"ADVANCED_EDIT_MODE" => $arParams["ADVANCED_EDIT_MODE"],
							"ALLOW_EDIT_SELECTION" => $arParams["ALLOW_EDIT_SELECTION"],
							"SETTINGS_WINDOW_TITLE" => $arParams["SETTINGS_WINDOW_TITLE"],
							"COLUMNS_ALL_WITH_SECTIONS" => $columnsAllWithSections,
							"ENABLE_FIELDS_SEARCH" => ($arResult["ENABLE_FIELDS_SEARCH"] ?? false),
							"CURRENT_PAGE" => $arParams["CURRENT_PAGE"],
							'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => $arResult['USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP'],
							'CHECKBOX_LIST_OPTIONS' => [
								'sections' => array_values($arResult['HEADERS_SECTIONS'] ?? []),
								'categories' => array_values($arResult['HEADERS_CATEGORIES'] ?? []),
								'columnsWithSections' => $columnsAllWithSections,
								'columns' => array_values($columnsAll),
								'checked' => array_keys($arResult['COLUMNS']),
							],
						)
					) ?>,
					<?= Json::encode($arResult["OPTIONS"]) ?>,
					<?= Json::encode($arResult["OPTIONS_ACTIONS"]) ?>,
					'<?=$arResult["OPTIONS_HANDLER_URL"]?>',
					<?= Json::encode($arResult["PANEL_ACTIONS"]) ?>,
					<?= Json::encode($arResult["PANEL_TYPES"]) ?>,
					<?= Json::encode($arResult["EDITOR_TYPES"]) ?>,
					<?= Json::encode($arResult["MESSAGE_TYPES"] ?? []) ?>
				)
			);
		});
	</script>
<? endif; ?>
