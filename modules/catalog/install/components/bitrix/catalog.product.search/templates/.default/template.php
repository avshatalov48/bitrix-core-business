<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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

global $APPLICATION;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;

if (isset($_REQUEST['public_mode']) && $_REQUEST['public_mode'] === "Y")
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
}

if (!$arResult['IS_ADMIN_SECTION'])
	return;
$listImageSize = (int)Main\Config\Option::get('iblock', 'list_image_size');
$viewFileParams = [
	'IMAGE' => 'Y',
	'PATH' => 'Y',
	'FILE_SIZE' => 'Y',
	'DIMENSIONS' => 'Y',
	'IMAGE_POPUP' => 'Y',
	'MAX_SIZE' => [
		'W' => $listImageSize,
		'H' => $listImageSize,
	],
	'MIN_SIZE' => [
		'W' => 1,
		'H' => 1,
	],
];
unset($listImageSize);

$selectPrefix = 'item_data_';

function getTreeOffsetWidth($level = 0): int
{
	// Some magic numbers
	return 30 + $level * 21;
}

function renderTree($sections, $level, $tableId): string
{
	$content = '';
	$level = (int)$level;

	foreach ($sections as $section)
	{
		$section["active"] ??= false;
		$bSubmenu = $section["dynamic"];
		$bSectionActive = $section["open"] ?? false;

		$icon = isset($section["icon"]) && $section["icon"] <> ""
			? '<span class="adm-submenu-item-link-icon ' . $section["icon"] . '"></span>' : '';
		$id = $tableId . '_section_' . $section['id'];
		$onclick = '';
		if ($bSubmenu)
		{
			$onclick = $tableId . "_helper.toggleDynSection(" . getTreeOffsetWidth($level) . ", this.parentNode.parentNode, '"
				. (int)$section["id"] . "', '" . ($level + 1) . "')";
		}
		$content .= '<div
				class="adm-sub-submenu-block' . ($level > 0 ? ' adm-submenu-level-' . ($level + 1) : '') . ($bSectionActive ? ' adm-sub-submenu-open' : '')
			. ($section["active"] ? ' adm-submenu-item-active' : '') . '">
				<div class="adm-submenu-item-name' . (!$bSubmenu ? ' adm-submenu-no-children' : '') . '"
					id="' . $id . '" data-level="' . $level . '" data-offset="' . getTreeOffsetWidth($level) . '"
					tabindex="2"><span class="adm-submenu-item-arrow"' . ($level > 0 ? ' style="width:' . getTreeOffsetWidth($level) . 'px;"' : '')
			. ($onclick ? ' onclick="' . $onclick . '"' : '') . '>
					<span class="adm-submenu-item-arrow-icon"></span></span><a
						class="adm-submenu-item-name-link"' . ($level > 0 ? ' style="padding-left:' . (getTreeOffsetWidth($level) + 8) . 'px;"' : '') . '
						href="#" . " onclick="return '.$tableId . '_helper.onSectionClick(\'' . $section["id"] . '\')">' . $icon . '
						<span class="adm-submenu-item-name-link-text">' . htmlspecialcharsbx($section["text"]) . '</span></a></div>';
		$content .= '<div class="adm-sub-submenu-block-children">' . ($bSubmenu ? renderTree($section["items"], $level + 1, $tableId) : '') . '</div>';
		$content .= '</div>';
	}
	unset($section);
	return $content;
}

/**
 * @param $name
 * @param $property_fields
 * @param $values
 * @return string
 */
function _ShowGroupPropertyFieldList($name, $property_fields, $values): string
{
	if (!is_array($values)) $values = array();

	$options = "";
	$result = "";
	$bWas = false;
	$sections = ProductSearchComponent::getPropertyFieldSections($property_fields["LINK_IBLOCK_ID"]);
	if (!empty($sections) && is_array($sections))
	{
		foreach ($sections as &$section)
		{
			$options .= '<option value="' . $section["ID"] . '"';
			if (in_array($section["ID"], $values))
			{
				$bWas = true;
				$options .= ' selected';
			}
			$options .= '>' . str_repeat(" . ", $section["DEPTH_LEVEL"]) . $section["NAME"] . '</option>';
		}
		unset($section);
	}
	$result .= '<select name="' . $name . '[]" size="' . ($property_fields["MULTIPLE"] == "Y" ? "5" : "1") . '" ' . ($property_fields["MULTIPLE"] == "Y" ? "multiple" : "") . '>';
	$result .= '<option value=""' . (!$bWas ? ' selected' : '') . '>' . GetMessage("SPS_A_PROP_NOT_SET") . '</option>';
	$result .= $options;
	$result .= '</select>';
	return $result;
}

function getImageField($property_value_id, $property_value): string
{
	global $viewFileParams;
	$res = CFileInput::Show('NO_FIELDS[' . $property_value_id . ']', $property_value, $viewFileParams, array(
			'upload' => false,
			'medialib' => false,
			'file_dialog' => false,
			'cloud' => false,
			'del' => false,
			'description' => false,
		)
	);

	return preg_replace('!<script[^>]*>.*</script>!isU','', $res);
}

function getHiddenSelect(string $prefix, array $params): string
{
	if (empty($params))
	{
		return '';
	}

	$result = '<span id="' . $prefix . $params['id'] . '"'
		. ' class="select-hidden"'
	;

	foreach ($params as $key => $value)
	{
		$result .= ' data-product' . htmlspecialcharsbx($key) . '="'
			. htmlspecialcharsbx($value). '"'
		;
	}

	$result .= '>';

	return $result;
}

if (!empty($arResult['OPEN_SECTION_MODE']))
{
	echo renderTree($arResult['SECTIONS'],$arResult['LEVEL'],$arResult['TABLE_ID']);
}
else
{
	$arProps = $arResult['PROPS'];
	$arSKUProps = $arResult['SKU_PROPS'];
	$arFilter = $arResult['FILTER'];

	$tableId = CUtil::JSEscape($arResult['TABLE_ID']);

	// START TEMPLATE
	$APPLICATION->SetAdditionalCSS('/bitrix/panel/main/admin.css');
	if (
		!$arResult['RELOAD']
		&& $arResult['IS_EXTERNALCONTEXT']
	)
	{
		$APPLICATION->SetAdditionalCSS('/bitrix/panel/main/admin.css');
	}
	$lAdmin = new CAdminList($arResult['TABLE_ID'], new CAdminSorting($arResult['TABLE_ID'], "ID", "ASC"));
	$lAdmin->InitFilter($arResult['FILTER_FIELDS']);

	// fix
	$_REQUEST['admin_history'] = 1;
	$lAdmin->NavText($arResult['DB_RESULT_LIST']->GetNavPrint(GetMessage("SPS_NAV_LABEL")));

	foreach (array_keys($arResult['HEADERS']) as $index)
	{
		$arResult['HEADERS'][$index]['content'] = htmlspecialcharsbx($arResult['HEADERS'][$index]['content']);
		if (isset($arResult['HEADERS'][$index]['title']))
			$arResult['HEADERS'][$index]['title'] = htmlspecialcharsbx($arResult['HEADERS'][$index]['title']);
	}
	unset($index);

	$lAdmin->AddHeaders($arResult['HEADERS']);

	$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
	if (!in_array('ID', $arSelectedFields))
	{
		$arSelectedFields[] = 'ID';
		$lAdmin->AddVisibleHeaderColumn('ID');
	}
	$arSelectedProps = array();

	$allProps = array_merge($arProps, $arSKUProps);
	foreach ($allProps as $prop)
	{
		if ($key = array_search("PROPERTY_" . $prop['ID'], $arSelectedFields))
		{
			$arSelectedProps[] = $prop;
			unset($arSelectedFields[$key]);
		}
	}
	$allProps = null;
	$arSelectedFields = null;
	$arSku = array();

	$showSkuName = Main\Config\Option::get('catalog', 'product_form_show_offer_name') === 'Y';

	//Add 'Level Up' row to grid
	if ($arResult['PARENT_SECTION_ID'] >= 0)
	{
		$row =& $lAdmin->AddRow(0, []);
		$row->bReadOnly = true;
		$row->AddViewField('NAME', '<a class="adm-list-table-link"><span class="bx-s-iconset folder"></span>..</a>');
		$row->AddActions([
			[
				'TEXT' => GetMessage('SPS_GOTO_PARENT_SECTION'),
				'DEFAULT' => 'Y',
				'ACTION' => $tableId . '_helper.onSectionClick(' . (int)$arResult['PARENT_SECTION_ID'] . ');'
			]
		]);
	}
	foreach ($arResult['PRODUCTS'] as $arItems)
	{
		$isSection = $arItems['TYPE'] === 'S';
		$icon = '';
		$arCatalogProduct = [];
		if (!$isSection)
		{
			$arCatalogProduct = $arItems['PRODUCT'] ?? [];
		}
		if (!empty($arCatalogProduct['TYPE']))
		{
			$icon = $arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SET ? 'f2' : 'f1';
		}

		$row = &$lAdmin->AddRow($arItems["ID"], $arItems);

		$row->AddField("ACTIVE", $arItems["ACTIVE"] == 'Y' ? GetMessage('SPS_PRODUCT_ACTIVE') : GetMessage('SPS_PRODUCT_NO_ACTIVE'));
		$row->AddViewFileField('PREVIEW_PICTURE', $viewFileParams);
		$row->AddViewFileField('DETAIL_PICTURE', $viewFileParams);

		$arActions = array();
		$selectData = [];

		if (!empty($arItems['SKU_ITEMS']) && !empty($arItems['SKU_ITEMS']["SKU_ELEMENTS"]))
		{
			$icon = 'f3';
			$arSkuResult = $arItems['SKU_ITEMS'];

			$row->AddViewField("EXPAND", '<a class="expand-sku">' . GetMessage('SPS_EXPAND') . '</a><a class="collapse-sku">' . GetMessage('SPS_COLLAPSE') . '</a>');

			$arActions[] = [
				"ICON" => "view",
				"TEXT" => GetMessage("SPS_SKU_SHOW"),
				"DEFAULT" => "Y",
				"ACTION" => $tableId . '_helper.fShowSku(' . CUtil::PhpToJSObject($arSkuResult["SKU_ELEMENTS_ID"]) . ', this);'
			];

			if ($arResult['ALLOW_SELECT_PARENT'] === 'Y')
			{
				$selectData = [
					'id' => $arItems['ID'],
					'type' => $arCatalogProduct['TYPE'],
					'quantity' => 1,
					'name' => $arItems['NAME'],
				];

				$jsClick = $tableId . '_helper.selectItem(' . $arItems['ID'] . ');';

				$row->AddField("ACTION", '<a href="javascript:void(0)" onclick="'.$jsClick.'; BX.PreventDefault(); return false;">'.GetMessage('SPS_SELECT').'</a>');

				$arActions[] = [
					"TEXT" => GetMessage("SPS_SELECT"),
					"DEFAULT" => "N",
					"ACTION" => $jsClick
				];
				unset($jsClick);
			}
			else
			{
				$row->bReadOnly = true;
			}

			foreach ($arSkuResult["SKU_ELEMENTS"] as $val)
			{
				$arSku[] = $val["ID"];
				$rowSku = &$lAdmin->AddRow($val["ID"], $val);

				$arSkuActions = array();

				$rowSku->AddViewFileField('DETAIL_PICTURE', $viewFileParams);
				$rowSku->AddViewFileField('PREVIEW_PICTURE', $viewFileParams);

				$rowSku->AddField(
					'ID',
					$arItems['ID'] . '-' . $val['ID'] . '<input type="hidden" name="prd" id="' . $tableId . '_sku-' . $val['ID'] . '">'
				);
				if (!empty($arResult['PRICES']))
				{
					foreach ($arResult['PRICES'] as $price)
					{
						$priceValue = '';
						if (isset($arResult['SKU_PRICES'][$price['ID']]))
						{
							$priceValue = CCurrencyLang::CurrencyFormat(
								$arResult['SKU_PRICES'][$price['ID']][$val["ID"]]['PRICE'] ?? '',
								$arResult['SKU_PRICES'][$price['ID']][$val["ID"]]['CURRENCY'] ?? false,
								true
							);
						}
						$rowSku->AddViewField("PRICE" . $price['ID'], $priceValue);
					}
					unset($price);
				}

				$balance = (float)$val["BALANCE"];

				$ratio = ($val['MEASURE_RATIO'] ?? 1);
				$measure = (isset($val['MEASURE']['SYMBOL_RUS']) ? '&nbsp;'.$val['MEASURE']['SYMBOL_RUS'] : '');
				$offerSelectData = [
					'id' => $val["ID"],
					'type' => $val["TYPE"],
					'name' => $val['NAME'],
					'full_quantity' => $balance,
					'measureRatio' => ($val['MEASURE_RATIO'] ?? 1),
					'measure' => htmlspecialcharsbx($val['MEASURE']['~SYMBOL_RUS'] ?? ''),
					'quantity' => $val['DEFAULT_QUANTITY'],
				];

				$skuProperty = '';
				if ($showSkuName)
				{
					$skuProperty .= '<i>' . htmlspecialcharsbx($val['NAME']) . '</i>';
				}

				$rowSku->AddField(
					'NAME',
					'<div class="sku-item-name">'
						. $skuProperty
						. '</div>'
						. getHiddenSelect($selectPrefix, $offerSelectData)
				);

				$rowSku->AddField("QUANTITY", '<span style="white-space: nowrap;"><input style="text-align: center;" type="text" id="'.$tableId.'_qty_'.$val["ID"].'" value="'.$val['DEFAULT_QUANTITY'].'" size="4" />'.$measure.'</span>');
				unset($measure, $ratio);

				$arSkuActions[] = array(
					"TEXT" => GetMessage("SPS_SELECT"),
					"DEFAULT" => "Y",
					"ACTION" => $tableId . '_helper.selectItem(' . $val["ID"] . ');'
				);

				$active = ($val["ACTIVE"] == 'Y' ? GetMessage('SPS_PRODUCT_ACTIVE') : GetMessage('SPS_PRODUCT_NO_ACTIVE'));

				$rowSku->AddActions($arSkuActions);
				$rowSku->AddField("BALANCE", $balance);
				$rowSku->AddField("ACTIVE", $active);
				$rowSku->AddField("ACTION", '<a class="select-sku">' . GetMessage('SPS_SELECT') . '</a>');

				if (!empty($val['PROPERTIES']))
				{
					foreach ($arSelectedProps as $property)
					{
						if (empty($val['PROPERTIES'][$property['ID']]))
							continue;
						$separator = ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_FILE ? '' : '/ ');
						$rowSku->AddViewField('PROPERTY_'.$property['ID'], implode($separator, $val['PROPERTIES'][$property['ID']]));
						unset($separator);
					}
					unset($property);
				}
			}
		}
		else
		{
			if ($isSection)
				$icon = 'folder';
			elseif (!empty($arCatalogProduct['IS_GROUP']))
				$icon = 'f4';

			$balance = '';
			if (!$isSection)
			{
				$balance = isset($arCatalogProduct["STORE_AMOUNT"]) ? (float)$arCatalogProduct["QUANTITY"] . " / " . (float)$arCatalogProduct["STORE_AMOUNT"] : (float)$arCatalogProduct["QUANTITY"];
			}

			$row->AddField("BALANCE", $balance);

			if (!$isSection)
			{
				$ratio = ($arCatalogProduct['MEASURE_RATIO'] ?? 1);
				$measure = (isset($arCatalogProduct['MEASURE']['SYMBOL_RUS']) ? '&nbsp;'.$arCatalogProduct['MEASURE']['SYMBOL_RUS'] : '');
				$selectData = [
					'id' => $arItems["ID"],
					'type' => $arCatalogProduct["TYPE"],
					'name' => $arItems['NAME'],
					'full_quantity' => $arCatalogProduct['QUANTITY'],
					'measureRatio' => ($arCatalogProduct['MEASURE_RATIO'] ?? 1),
					'measure' => htmlspecialcharsbx($arCatalogProduct['MEASURE']['~SYMBOL_RUS'] ?? ''),
					'quantity' => $arCatalogProduct['DEFAULT_QUANTITY'],
				];
				$row->AddField("QUANTITY", '<span style="white-space: nowrap;"><input style="text-align: center;" type="text" id="'.$tableId.'_qty_'.$arItems["ID"].'" value="'.$arCatalogProduct['DEFAULT_QUANTITY'].'" size="4" />'.$measure.'</span>');
				unset($measure, $ratio);

				$arActions[] = array(
					"TEXT" => GetMessage("SPS_SELECT"),
					"DEFAULT" => "Y",
					"ACTION" => $tableId . '_helper.selectItem(' . $arItems['ID'] . ');',
				);

				$row->AddField("ACTION", '<a class="select-sku">' . GetMessage('SPS_SELECT') . '</a>');
			}
			else
			{
				$arActions[] = array(
					"TEXT" => GetMessage("BX_CATALOG_CPS_TPL_MESS_APPEND_SECTION"),
					"DEFAULT" => "Y",
					"ACTION" => $tableId.'_helper.onSectionClick('.$arItems["ID"].');'
				);
			}
			if (!empty($arResult['PRICES']))
			{
				foreach ($arResult['PRICES'] as $price)
				{
					$priceValue = '';
					if (isset($arItems['PRICES'][$price['ID']]))
					{
						$priceValue = CCurrencyLang::CurrencyFormat(
							$arItems['PRICES'][$price['ID']]['PRICE'],
							$arItems['PRICES'][$price['ID']]['CURRENCY'],
							true
						);
					}
					$row->AddViewField("PRICE" . $price['ID'], $priceValue);
				}
				unset($price);
			}
		}

		if (!empty($arItems['PROPERTIES']))
		{
			foreach ($arSelectedProps as $property)
			{
				if (empty($arItems['PROPERTIES'][$property['ID']]))
					continue;
				$separator = ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_FILE ? '' : '/ ');
				$row->AddViewField('PROPERTY_'.$property['ID'], implode($separator, $arItems['PROPERTIES'][$property['ID']]));
				unset($separator);
			}
			unset($property);
		}

		$hiddenSelect = '';

		$row->AddViewField(
			'NAME',
			'<a class="adm-list-table-link">'
				. '<span class="bx-s-iconset ' . $icon . '"></span>'
				. htmlspecialcharsEx($arItems['NAME'])
				. '</a>'
				. getHiddenSelect($selectPrefix, $selectData)
		);
		if ($isSection)
		{
			$row->bReadOnly = true;
		}
		$row->AddActions($arActions);
	}

	if ($arResult['MULTIPLE'])
	{
		$lAdmin->AddGroupActionTable(
			[
				[
					'action' => $tableId . '_helper.selectItemList();',
					'value' => 'select',
					'type' => 'button',
					'name' => GetMessage('BX_CATALOG_CPS_TPL_MESS_SELECT_BUTTON'),
				],
			],
			[
				'disable_action_target' => true,
			]
		);
	}

	$lAdmin->BeginEpilogContent();
	?>
	<script>
	BX.ready(function(){
	<?php
	if (!empty($arSku))
	{
	?>
		var skuIds = <?=\CUtil::PhpToJSObject($arSku); ?>,
			i,
			skuRow;

		for (i = 0; i < skuIds.length; i++)
		{
			skuRow = BX('<?=$tableId?>_sku-' + skuIds[i]).parentNode.parentNode;
			if (BX.type.isElementNode(skuRow))
			{
				BX.addClass(skuRow, 'is-sku-row');
				BX.hide(skuRow);
			}
			skuRow = null;
		}
		skuIds = [];
	<?php
	}
	?>
		// double click patch
		var rows = BX.findChildren(BX('<?=$tableId?>'), {className: 'adm-list-table-row'}, true);
		if (rows) {
			for (i = 0; i < rows.length; ++i) {

				var isExpandable = BX.findChildren(rows[i], {className: 'expand-sku'}, true);
				if (isExpandable.length !== 0)
				{
					rows[i].onclick = function () {
						this.ondblclick();
					};
				}

				var hasActionButton = BX.findChildren(rows[i], {className: 'select-sku'}, true);
				if (hasActionButton.length > 0)
				{
					hasActionButton[0].onclick = rows[i].ondblclick;
				}
			}
		}
		if (typeof <?=$tableId?>_helper !== 'undefined')
		{
			<?=$tableId?>_helper.setBreadcrumbs(<?=CUtil::PhpToJSObject($arResult['BREADCRUMBS'])?>);
			<?php
			if (!empty($_REQUEST['set_filter']) && $_REQUEST['set_filter'] == 'Y'):
			?><?=$tableId?>_helper.setIgnoreFilter(false);
			<?php
			elseif (!empty($_REQUEST['del_filter']) && $_REQUEST['del_filter'] == 'Y'):
			?><?=$tableId?>_helper.setIgnoreFilter(true);
			<?php
			endif;
			?>
		}
		BX('form_<?=$tableId?>').style.overflow = 'auto';
	});
	</script>
	<?php
	$lAdmin->EndEpilogContent();
	$lAdmin->AddAdminContextMenu(array(), false);
	$lAdmin->CheckListMode();

	?>
	<!-- START HTML -->
	<?php
	if (!$arResult['RELOAD']):
	?>
	<div id="<?= $tableId ?>_reload_container" class="catalog-product-search-dialog">
	<?php
	endif;
	?>
	<div class="adm-s-search-sidebar-container-left " style="width: 20%;padding-bottom: 28px">
		<table class="adm-main-wrap" style="min-width:10px;">
			<tr><td class="adm-left-side-wrap" style="background: none;" id="<?= $tableId ?>_resizable">
				<div class="adm-left-side" style="width:300px;">
					<div class="adm-submenu" id="adm-submenu">
						<div class="adm-submenu-items-wrap" id="adm-submenu-favorites">
							<div class="adm-submenu-items-stretch-wrap">
								<table class="adm-submenu-items-stretch">
									<tr><td class="adm-submenu-items-stretch-cell">
										<div class="adm-submenu-items-block" id="<?= $tableId ?>_catalog_tree_wrap">
											<div class="adm-sub-submenu-block adm-sub-submenu-open root-submenu <?= empty($arResult["SECTION_ID"]) ? 'adm-submenu-item-active' : '' ?>">
												<div class="adm-submenu-item-name" id="<?= $tableId ?>_section_0">
													<a
														href="#" class="adm-submenu-item-name-link product-search-top-item"
														onclick="return <?= $tableId ?>_helper.onSectionClick('0')"><?php
														if (count($arResult['IBLOCKS']) > 1)
														{
															?><span class="adm-s-arrow-cont" title="<?= GetMessage('SPS_CHOOSE_CATALOG') ?>" id="<?= $tableId ?>_iblock_menu_opener"></span><?php
														}
														?><span class="adm-submenu-item-link-icon icon-default fileman_menu_icon"></span>
														<span class="adm-submenu-item-name-link-text" title="<?= htmlspecialcharsbx($arResult['IBLOCKS'][$arResult['IBLOCK_ID']]['NAME']); ?>">
															<?=htmlspecialcharsbx($arResult['IBLOCKS'][$arResult['IBLOCK_ID']]['NAME']); ?>
														</span>
													</a>
												</div>
												<div class="adm-sub-submenu-block-children"><?= renderTree($arResult['SECTIONS'], 1, $arResult['TABLE_ID']) ?></div>
											</div>
										</div>
									</td></tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</td>
			<td class="adm-workarea-wrap"></td></tr>
		</table>
	</div>
	<div class="adm-s-search-content-container-right" style="width: 80%;">
		<div class="adm-s-content">
			<div class="adm-s-search-container">
				<div class="adm-s-search-box">
					<table>
						<tr>
							<td class="adm-s-search-tag-cell"><span class="adm-s-search-tag"
																	id="<?= $tableId ?>_section_label"
																	style="<?= $arResult['SECTION_LABEL'] ? '' : 'display:none' ?>"><?=htmlspecialcharsbx($arResult['SECTION_LABEL']); ?>
									<span class="adm-s-search-tag-del" onclick="return <?= $tableId ?>_helper.onSectionClick('0')"></span></span>
							</td>
							<td class="adm-s-search-input-cell"><input type="text" value="<?= htmlspecialcharsbx($arFilter['QUERY']) ?>" id="<?= $tableId ?>_query" onkeyup="<?= $tableId ?>_helper.onSearch(this.value)">
							</td>
						</tr>
					</table>

				</div>
				<div class="adm-s-search-control-box">
					<input class="adm-s-search-submit" type="submit" value="" onclick="<?= $tableId ?>_helper.search();">
					<span class="adm-s-search-box-separator" id="<?= $tableId ?>_query_clear_separator" style="<?= $arFilter['QUERY'] ? '' : 'display:none' ?>"></span>
					<input class="adm-s-search-reset" id="<?= $tableId ?>_query_clear" type="reset" value="" style="<?= $arFilter['QUERY'] ? '' : 'display:none' ?>" onclick="return <?= $tableId ?>_helper.clearQuery()">
				</div>
			</div>
			<div class="adm-s-search-query-settings-container">
				<div class="adm-s-search-query-settings">
					<input type="checkbox" value="Y" <?=($arFilter['USE_SUBSTRING_QUERY'] == 'Y' ? ' checked="checked"' : '');?>name="USE_SUBSTRING_QUERY" id="<?= $tableId ?>_query_substring" onclick="return <?= $tableId ?>_helper.checkSubstring()">&nbsp;<?=GetMessage('BX_CATALOG_CPS_TPL_MESS_USE_SUBSTRING_QUERY'); ?>
				</div>
			</div>

			<form name="<?= $tableId ?>_find_form" method="GET" action="<?php echo $APPLICATION->GetCurPage() ?>?" accept-charset="<?php echo LANG_CHARSET; ?>" id="<?= $tableId ?>_form">
				<input type="hidden" name="mode" value="list">
				<input type="hidden" name="SECTION_ID" value="<?= (int)$arResult['SECTION_ID'] ?>" id="<?= $tableId ?>_section_id">
				<input type="hidden" name="QUERY" value="<?= htmlspecialcharsbx($arFilter['QUERY']) ?>" id="<?= $tableId ?>_query_value">
				<input type="hidden" name="USE_SUBSTRING_QUERY" value="<?=htmlspecialcharsbx($arFilter['USE_SUBSTRING_QUERY']) ?>" id="<?= $tableId ?>_query_substring_value">
				<input type="hidden" name="func_name" value="<?php echo htmlspecialcharsbx($arResult['JS_CALLBACK']) ?>">
				<input type="hidden" name="event" value="<?php echo htmlspecialcharsbx($arResult['JS_EVENT']) ?>">
				<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID ?>">
				<input type="hidden" name="LID" value="<?= htmlspecialcharsbx($arResult['LID']) ?>">
				<input type="hidden" name="caller" value="<?= htmlspecialcharsbx($arResult['CALLER']) ?>">
				<input type="hidden" name="IBLOCK_ID" value="<?= (int)$arResult['IBLOCK_ID'] ?>" id="<?= $tableId ?>_iblock">
				<input type="hidden" name="subscribe" value="<?= $arResult['SUBSCRIPTION']? 'Y' : 'N' ?>">
				<input type="hidden" name="allow_select_parent" value="<?= htmlspecialcharsbx($arResult['ALLOW_SELECT_PARENT']) ?>">
				<input type="hidden" name="multiple_select" value="<?php echo $arResult['MULTIPLE'] ? 'Y' : 'N'; ?>">
				<?php
				$oFilter = new CAdminFilter(
					$arResult['TABLE_ID'] .'_iblock_'.(int)$arResult['IBLOCK_ID']. "_filter",
					$arResult['FILTER_LABELS']
				);
				$oFilter->SetDefaultRows("find_code");
				$oFilter->Begin();
				?>
				<tr>
					<td><?= GetMessage("SPS_CODE") ?>:</td>
					<td>
						<input type="text" name="filter_code" size="50" value="<?php echo htmlspecialcharsbx($_REQUEST["filter_code"] ?? '') ?>">
					</td>
				</tr>
				<tr>
					<td><?= GetMessage("SPS_TIMESTAMP") ?>:</td>
					<td><?php echo CalendarPeriod("filter_timestamp_from", htmlspecialcharsbx($_REQUEST['filter_timestamp_from'] ?? ''), "filter_timestamp_to", htmlspecialcharsbx($_REQUEST['filter_timestamp_to'] ?? ''), "form1") ?></td>
				</tr>
				<tr>
					<td><?= GetMessage("SPS_ACTIVE") ?>:</td>
					<td>
						<select name="filter_active">
							<option value="*"><?= htmlspecialcharsbx("(" . GetMessage("SPS_ANY") . ")") ?></option>
							<option
								value="Y"<?php if (empty($_REQUEST['filter_active']) || $_REQUEST['filter_active'] == "Y") echo " selected" ?>><?= htmlspecialcharsbx(GetMessage("SPS_YES")) ?></option>
							<option
								value="N"<?php if (!empty($_REQUEST['filter_active']) && $_REQUEST['filter_active'] == "N") echo " selected" ?>><?= htmlspecialcharsbx(GetMessage("SPS_NO")) ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<td>ID (<?= GetMessage("SPS_ID_FROM_TO") ?>):</td>
					<td>
						<input type="text" name="filter_id_start" size="10" value="<?php echo htmlspecialcharsbx($_REQUEST['filter_id_start'] ?? '')?>">
						...
						<input type="text" name="filter_id_end" size="10" value="<?php echo htmlspecialcharsbx($_REQUEST['filter_id_end'] ?? '')?>">
					</td>
				</tr>
				<tr>
					<td><?= GetMessage("SPS_XML_ID") ?>:</td>
					<td>
						<input type="text" name="filter_xml_id" size="50" value="<?php echo htmlspecialcharsbx($_REQUEST['filter_xml_id'] ?? '')?>">
					</td>
				</tr>

				<?php
				foreach ($arProps as $arProp):
					if ($arProp["FILTRABLE"] == "Y" && $arProp["PROPERTY_TYPE"] != "F")
					{
						?>
						<tr>
							<td><?= htmlspecialcharsbx($arProp["NAME"]) ?>:</td>
							<td>
								<?php
								if (array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
									echo "<script>var arClearHiddenFields = [];</script>";
									echo call_user_func_array(
										$arProp['PROPERTY_USER_TYPE']['GetAdminFilterHTML'],
										[
											$arProp,
											[
												'FORM_NAME' => $arResult['TABLE_ID'] . '_find_form',
												'TABLE_ID' => $arResult['TABLE_ID'],
												'VALUE' => 'filter_el_property_' . $arProp['ID'],
											],
										]
									);
								elseif ($arProp["PROPERTY_TYPE"] == 'S'):?>
									<input type="text" name="filter_el_property_<?= $arProp["ID"] ?>" value="<?php echo htmlspecialcharsbx($_REQUEST["filter_el_property_" . $arProp["ID"]] ?? '') ?>" size="30">&nbsp;<?= ShowFilterLogicHelp() ?>
								<?php
								elseif ($arProp["PROPERTY_TYPE"] == 'N' || $arProp["PROPERTY_TYPE"] == 'E'): ?>
									<input type="text" name="filter_el_property_<?= $arProp["ID"] ?>" value="<?php echo htmlspecialcharsbx($_REQUEST["filter_el_property_" . $arProp["ID"]] ?? '') ?>" size="30">
								<?php
								elseif ($arProp["PROPERTY_TYPE"] == 'L'): ?>
									<select name="filter_el_property_<?= $arProp["ID"] ?>">
										<option value=""><?php echo GetMessage("SPS_VALUE_ANY") ?></option>
										<option value="NOT_REF"><?php echo GetMessage("SPS_A_PROP_NOT_SET") ?></option><?php
										$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC", "VALUE" => "ASC"), Array("PROPERTY_ID" => $arProp["ID"]));
										while ($arPEnum = $dbrPEnum->GetNext()):
											?>
											<option
												value="<?= $arPEnum["ID"] ?>"<?php if (($_REQUEST["filter_el_property_" . $arProp["ID"]] ?? '') == $arPEnum["ID"]) echo " selected" ?>><?= $arPEnum["VALUE"] ?></option>
										<?php
										endwhile;
										?></select>
								<?php
								elseif ($arProp["PROPERTY_TYPE"] == 'G'):
									echo _ShowGroupPropertyFieldList('filter_el_property_' . $arProp["ID"], $arProp, $_REQUEST['filter_el_property_' . $arProp["ID"]] ?? '');
								endif;
								?>
							</td>
						</tr>
					<?php
					}
					endforeach;

				foreach ($arSKUProps as $arProp)
				{
					if ($arProp["FILTRABLE"] == "Y" && $arProp["PROPERTY_TYPE"] != "F" && $arResult['SKU_CATALOG']['SKU_PROPERTY_ID'] != $arProp['ID'])
					{
						?>
						<tr>
							<td><?php echo htmlspecialcharsbx($arProp["NAME"]) ?> (<?=GetMessage("SPS_OFFER")?>):</td>
							<td>
								<?php
								if (array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
									echo "<script>var arClearHiddenFields = [];</script>";
									echo call_user_func_array(
										$arProp['PROPERTY_USER_TYPE']['GetAdminFilterHTML'],
										[
											$arProp,
											[
												'FORM_NAME' => $arResult['TABLE_ID'] . '_find_form',
												'TABLE_ID' => $arResult['TABLE_ID'],
												'VALUE' => 'filter_sub_el_property_' . $arProp['ID'],
											],
										]
									);
								elseif ($arProp["PROPERTY_TYPE"] == 'S'):?>
									<input type="text" name="filter_sub_el_property_<?= $arProp["ID"] ?>" value="<?php echo htmlspecialcharsbx($_REQUEST["filter_sub_el_property_" . $arProp["ID"]] ?? '') ?>" size="30">&nbsp;<?= ShowFilterLogicHelp() ?>
								<?php
								elseif ($arProp["PROPERTY_TYPE"] == 'N' || $arProp["PROPERTY_TYPE"] == 'E'): ?>
									<input type="text" name="filter_sub_el_property_<?= $arProp["ID"] ?>" value="<?php echo htmlspecialcharsbx($_REQUEST["filter_sub_el_property_" . $arProp["ID"]] ?? '') ?>" size="30">
								<?php
								elseif ($arProp["PROPERTY_TYPE"] == 'L'): ?>
									<select name="filter_sub_el_property_<?= $arProp["ID"] ?>">
										<option value=""><?php echo GetMessage("SPS_VALUE_ANY") ?></option>
										<option value="NOT_REF"><?php echo GetMessage("SPS_A_PROP_NOT_SET") ?></option><?php
										$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC", "VALUE" => "ASC"), Array("PROPERTY_ID" => $arProp["ID"]));
										while ($arPEnum = $dbrPEnum->GetNext()):
											?>
											<option
												value="<?= $arPEnum["ID"] ?>"<?php if (($_REQUEST["filter_sub_el_property_" . $arProp["ID"]] ?? '') == $arPEnum["ID"]) echo " selected" ?>><?= $arPEnum["VALUE"] ?></option>
										<?php
										endwhile;
										?></select>
								<?php
								elseif ($arProp["PROPERTY_TYPE"] == 'G'):
									echo _ShowGroupPropertyFieldList('filter_sub_el_property_' . $arProp["ID"], $arProp, $_REQUEST['filter_sub_el_property_' . $arProp["ID"]] ?? '');
								endif;
								?>
							</td>
						</tr>
					<?php
					}
				}

				$oFilter->Buttons([
					'table_id' => $arResult['TABLE_ID'],
					'url' => $APPLICATION->GetCurPage(),
					'form' => $tableId . '_find_form',
				]);

				$oFilter->End();
				?>
			</form>
			<div class="adm-navchain" style="vertical-align: middle; margin-left: 0;" id="<?= $tableId ?>_breadcrumbs">

			</div>
			<?php
			$lAdmin->DisplayList();
			?>
		</div>
	</div>
	<?php
	if (!$arResult['RELOAD']):
		$helperParams = [
			'tableId' => $tableId,
			'multiple' => $arResult['MULTIPLE'],
			'callerName' => $arResult['CALLER'],
			'currentUri' => $APPLICATION->GetCurPage(),
			'iblockName' => $arResult['IBLOCKS'][$arResult['IBLOCK_ID']]['NAME'],
			'itemDataNamePrefix' => $selectPrefix,
		];
		if ($arResult['JS_CALLBACK'] !== '')
		{
			$helperParams['callback'] = $arResult['JS_CALLBACK'];
		}
		if ($arResult['JS_EVENT'] !== '')
		{
			$helperParams['event'] = $arResult['JS_EVENT'];
		}
	?>
	</div>
	<script>
		<?=$tableId?>_helper = new BX.Catalog.ProductSearchDialog(<?=CUtil::PhpToJSObject(
			$helperParams,
			false,
			false,
			true
		); ?>);
		<?=$tableId?>_helper.setBreadcrumbs(<?=CUtil::PhpToJSObject($arResult['BREADCRUMBS'])?>);
		BX('<?=$tableId?>_query').focus();
	</script>
	<?php
	endif;
	?>
	<script>
		<?php
		if (sizeof($arResult['IBLOCKS']) > 1):
			$iblockMenu = [
				[
					'HTML' => '<b>'.GetMessage('SPS_CHOOSE_CATALOG').':</b>',
					'CLOSE_ON_CLICK' => false,
				],
				[
					'SEPARATOR' => true,
				]
			];
			foreach ($arResult['IBLOCKS'] as $arIblock)
			{
				$iblockMenu[] = [
					'HTML' => '<span class="psd-catalog-menu-name" title="'.htmlspecialcharsbx($arIblock['NAME']).'">'.htmlspecialcharsEx($arIblock['NAME']).'</span><span class="psd-catalog-menu-lid" title="'.htmlspecialcharsbx($arIblock['SITE_NAME']).'">'.htmlspecialcharsbx($arIblock['SITE_NAME']).'</span>',
					'ONCLICK' => $tableId.'_helper.onIblockChange('.(int)$arIblock['ID'].',\''.CUtil::JSEscape($arIblock['NAME']).'\')',
				];
			}
			?>
			new BX.COpener({
				DIV: '<?=$tableId?>_iblock_menu_opener',
				MENU: <?=CUtil::PhpToJSObject($iblockMenu)?>
			});
		<?php
		endif;
		?>
		// override SaveSetting to fix URL
		<?=$tableId?>.SaveSettings = function (el) {
			var sCols = '', sBy = '', sOrder = '', sPageSize;

			var oSelect = document.list_settings.selected_columns;
			var n = oSelect.length;
			for (var i = 0; i < n; i++)
				sCols += (sCols !== '' ? ',' : '') + oSelect[i].value;

			oSelect = document.list_settings.order_field;
			if (oSelect)
				sBy = oSelect[oSelect.selectedIndex].value;

			oSelect = document.list_settings.order_direction;
			if (oSelect)
				sOrder = oSelect[oSelect.selectedIndex].value;

			oSelect = document.list_settings.nav_page_size;
			sPageSize = oSelect[oSelect.selectedIndex].value;

			var bCommon = (document.list_settings.set_default && document.list_settings.set_default.checked);

			BX.userOptions.save('list', this.table_id, 'columns', sCols, bCommon);
			BX.userOptions.save('list', this.table_id, 'by', sBy, bCommon);
			BX.userOptions.save('list', this.table_id, 'order', sOrder, bCommon);
			BX.userOptions.save('list', this.table_id, 'page_size', sPageSize, bCommon);
			//>>>patch start
			var url = <?=$tableId?>_helper.buildUrl();
			//<<<patch end

			BX.WindowManager.Get().showWait(el);
			BX.userOptions.send(BX.delegate(function () {
				this.GetAdminList(
					url,
					function () {
						BX.WindowManager.Get().closeWait(el);
						BX.WindowManager.Get().Close();
					}
				);
			}, this));
		};

		<?=$tableId?>.ShowSettings = function(url)
		{
			(new BX.CDialog({
				content_url: url,
				resizable: false,
				resize_id: '<?=$tableId?>_settings',
				height: 475,
				width: 560
			})).Show();
		};

		<?=$tableId?>.DeleteSettings = function(bCommon)
		{
			BX.showWait();
			//>>>patch start
			var url = <?=$tableId?>_helper.buildUrl();
			//<<<patch end
			BX.userOptions.del('list', this.table_id, bCommon, BX.delegate(function(){
				BX.closeWait();
				this.GetAdminList(
					url,
					function(){BX.WindowManager.Get().Close();}
				);
			}, this));
		};

	</script>
<?php
}
