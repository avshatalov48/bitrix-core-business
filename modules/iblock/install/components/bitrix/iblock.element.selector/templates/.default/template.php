<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $element */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$randomString = $this->randString();
$jsObject = $arResult['SELECTOR_ID'];

$selectorId = $arResult['SELECTOR_ID'];
$contentClass = 'ies-content';
$contentClass .= $arResult['MULTIPLE'] == 'Y' ? ' ies-content-multiple' : '';
$contentClass .= $arResult['POPUP'] == 'Y' ? ' ies-content-hide' : '';
?>

<?if($arResult['PANEL_SELECTED_VALUES'] == 'Y'):?>
<div id="<?=HtmlFilter::encode($selectorId)?>_panel_selected_values">
	<div id="<?=HtmlFilter::encode($selectorId)?>_hidden_values"></div>
	<div id="<?=HtmlFilter::encode($selectorId)?>_visible_values"></div>
	<div id="<?=HtmlFilter::encode($selectorId)?>_panel_buttons">
		<?if($arResult['POPUP'] == 'Y' && $arResult['ONLY_READ'] == "N"):?>
			<span id="<?=HtmlFilter::encode($selectorId)?>_select_button" class="ies-content-select-button">
			<?=Loc::getMessage('IEST_SELECT_ITEMS')?>
		</span>
		<?endif?>
	</div>
</div>
<?endif?>

<div id="<?=HtmlFilter::encode($selectorId)?>" class="<?=$contentClass?>">
	<?if($arResult['ACCESS_DENIED'] == 'Y'):?>
		<? ShowError(Loc::getMessage('IEST_ERROR_ACCESS_DENIED')); ?>
	<?else:?>
	<table class="ies-content-layout" cellspacing="0">
		<tr>
			<td class="ies-content-left-column">
				<?if(empty($arResult['SEARCH_INPUT_ID'])):?>
					<div class="ies-content-search">
						<input name="<?=HtmlFilter::encode($selectorId)?>_search_input" autocomplete="off" id="<?=
							HtmlFilter::encode($selectorId)?>_search_input" class="ies-content-search-textbox">
					</div>
				<?endif?>
				<div class="ies-content-tabs">
					<span class="ies-content-tab ies-content-tab-selected" id="<?=
						HtmlFilter::encode($selectorId)?>_tab_last" onclick="
							BX.Iblock['<?=$jsObject?>'].displayTab('last');">
						<span class="ies-content-tab-left"></span>
						<span class="ies-content-tab-text"><?=Loc::getMessage('IEST_LAST_ELEMENT')?></span>
						<span class="ies-content-tab-right"></span>
					</span>
					<span class="ies-content-tab" id="<?=HtmlFilter::encode($selectorId)?>_tab_search" onclick="
							BX.Iblock['<?=$jsObject?>'].displayTab('search');">
						<span class="ies-content-tab-left"></span>
						<span class="ies-content-tab-text"><?=Loc::getMessage('IEST_ELEMENT_SEARCH')?></span>
						<span class="ies-content-tab-right"></span>
					</span>
				</div>
				<div class="ies-content-tabs-content">
					<div class="ies-content-tab-content ies-content-tab-content-selected" id="<?=
						HtmlFilter::encode($selectorId)?>_last">
						<table class="ies-content-tab-columns" cellspacing="0">
						<tr>
						<td>
							<?foreach($arResult['LAST_ELEMENTS'] as $element):?>
								<?
									$selected = in_array($element["ID"], $arResult['CURRENT_ELEMENTS_ID']);
									$class = ($selected ? ' ies-content-item-selected' : '')
								?>
								<div class="ies-content-item<?=$class?>" id="<?=HtmlFilter::encode($selectorId)
									?>_last_elements_<?=intval($element['ID'])
									?>" onclick="BX.Iblock['<?=$jsObject?>'].select(event);">
								<?if($arResult['MULTIPLE'] == 'Y'):?>
									<input type="checkbox" name="<?=HtmlFilter::encode($selectorId)?>[]" value="<?=
									intval($element['ID'])?>"<?=$selected ? ' checked' : ''?> class="ies-hidden-input">
								<?else:?>
									<input type="radio" name="<?=HtmlFilter::encode($selectorId)?>" value="<?=
									intval($element['ID'])?>"<?=$selected ? ' checked' : ''?> class="ies-hidden-input">
								<?endif?>
								<div class="ies-content-item-text"><?=HtmlFilter::encode($element['NAME'])?></div>
								<div class="ies-content-item-icon"></div>
								</div>
							<?endforeach?>
							<?foreach($arResult["CURRENT_ELEMENTS"] as $element):?>
								<?
									$selected = in_array($element["ID"], $arResult['CURRENT_ELEMENTS_ID']);
								?>
								<?if(!in_array($element, $arResult['LAST_ELEMENTS'])):?>
									<?if($arResult['MULTIPLE'] == 'Y'):?>
										<input type="checkbox" name="<?=HtmlFilter::encode($selectorId)?>[]" value="<?=
										intval($element['ID'])?>"<?=$selected?' checked':''?> class="ies-hidden-input">
									<?else:?>
										<input type="radio" name="<?=HtmlFilter::encode($selectorId)?>" value="<?=
										intval($element['ID'])?>"<?=$selected?' checked':''?> class="ies-hidden-input">
									<?endif?>
								<?endif?>
							<?endforeach?>
						</td>
						</tr>
						</table>
					</div>
					<div class="ies-content-tab-content" id="<?=HtmlFilter::encode($selectorId)?>_search"></div>
				</div>
			</td>
			<?if($arResult['MULTIPLE'] == 'Y'):?>
				<td class="ies-content-right-column" id="<?=HtmlFilter::encode($selectorId)?>_selected_elements">
					<div class="ies-content-selected-title">
						<?=Loc::getMessage('IEST_CURRENT_SELECTED_ITEMS')?>
						(<span id="<?=HtmlFilter::encode($selectorId)?>_current_count"><?=
							count($arResult["CURRENT_ELEMENTS"])?></span>)
					</div>
					<div class="ies-content-selected-items">
						<?foreach($arResult['CURRENT_ELEMENTS'] as $element):?>
							<div class="ies-content-selected-item" id="<?=
								HtmlFilter::encode($selectorId)?>_element_selected_<?=intval($element['ID'])?>">
								<div class="ies-content-selected-item-icon" id="<?=
									HtmlFilter::encode($selectorId)?>-element-unselect-<?=intval($element['ID'])?>"
									onclick="BX.Iblock['<?=$jsObject?>'].unselect(<?=intval($element['ID'])?>);">
								</div>
								<span class="ies-content-selected-item-text">
									<?=HtmlFilter::encode($element['NAME'])?>
								</span>
							</div>
						<?endforeach?>
					</div>
				</td>
			<?endif?>
		</tr>
	</table>
	<?endif?>
</div>

<script type="text/javascript">
	BX.ready(function() {
		BX.Iblock['<?=$jsObject?>'] = new BX.Iblock.IblockElementSelector({
			randomString: '<?=$randomString?>',
			jsObject: '<?=$jsObject?>',
			selectorId: '<?=CUtil::JSEscape($selectorId)?>',
			multiple: '<?=CUtil::JSEscape($arResult['MULTIPLE'])?>',
			panelSelectedValues: '<?=CUtil::JSEscape($arResult['PANEL_SELECTED_VALUES'])?>',
			popup: '<?=CUtil::JSEscape($arResult['POPUP'])?>',
			searchInputId: '<?=CUtil::JSEscape($arResult['SEARCH_INPUT_ID'])?>',
			iblockId: '<?=intval($arResult['IBLOCK_ID'])?>',
			onChange: '<?=CUtil::JSEscape($arResult['ON_CHANGE'])?>',
			onSelect: '<?=CUtil::JSEscape($arResult['ON_SELECT'])?>',
			onUnSelect: '<?=CUtil::JSEscape($arResult['ON_UNSELECT'])?>',
			currentElements: <?=\Bitrix\Main\Web\Json::encode($arResult['CURRENT_ELEMENTS'])?>,
			lastElements: <?=\Bitrix\Main\Web\Json::encode($arResult['LAST_ELEMENTS'])?>,
			inputName: '<?=$arResult['INPUT_NAME']?>',
			onlyRead: '<?=$arResult['ONLY_READ']?>',
			admin: '<?=$arResult['ADMIN_SECTION']; ?>',
			templateUrl: '<?=CUtil::JSEscape($arResult['TEMPLATE_URL'])?>'
		});
		BX.message({

		});
	});
</script>