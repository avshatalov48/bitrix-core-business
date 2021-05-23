<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $elementEdit
 * @var string $elementDelete
 * @var string $elementDeleteParams
 */

global $APPLICATION;

$positionClassMap = array(
	'left' => 'product-item-label-left',
	'center' => 'product-item-label-center',
	'right' => 'product-item-label-right',
	'bottom' => 'product-item-label-bottom',
	'middle' => 'product-item-label-middle',
	'top' => 'product-item-label-top'
);

$discountPositionClass = '';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION']))
{
	foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos)
	{
		$discountPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}

$labelPositionClass = '';
if (!empty($arParams['LABEL_PROP_POSITION']))
{
	foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos)
	{
		$labelPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}

$arParams['~MESS_BTN_BUY'] = $arParams['~MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCT_TPL_MESS_BTN_BUY');
$arParams['~MESS_BTN_DETAIL'] = $arParams['~MESS_BTN_DETAIL'] ?: Loc::getMessage('CT_BCT_TPL_MESS_BTN_DETAIL');
$arParams['~MESS_BTN_COMPARE'] = $arParams['~MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCT_TPL_MESS_BTN_COMPARE');
$arParams['~MESS_BTN_SUBSCRIBE'] = $arParams['~MESS_BTN_SUBSCRIBE'] ?: Loc::getMessage('CT_BCT_TPL_MESS_BTN_SUBSCRIBE');
$arParams['~MESS_BTN_ADD_TO_BASKET'] = $arParams['~MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCT_TPL_MESS_BTN_ADD_TO_BASKET');
$arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCT_TPL_MESS_PRODUCT_NOT_AVAILABLE');
$arParams['~MESS_SHOW_MAX_QUANTITY'] = $arParams['~MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCT_CATALOG_SHOW_MAX_QUANTITY');
$arParams['~MESS_RELATIVE_QUANTITY_MANY'] = $arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCT_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['~MESS_RELATIVE_QUANTITY_FEW'] = $arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCT_CATALOG_RELATIVE_QUANTITY_FEW');

$generalParams = array(
	'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
	'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
	'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
	'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
	'MESS_SHOW_MAX_QUANTITY' => $arParams['~MESS_SHOW_MAX_QUANTITY'],
	'MESS_RELATIVE_QUANTITY_MANY' => $arParams['~MESS_RELATIVE_QUANTITY_MANY'],
	'MESS_RELATIVE_QUANTITY_FEW' => $arParams['~MESS_RELATIVE_QUANTITY_FEW'],
	'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
	'USE_PRODUCT_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
	'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
	'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
	'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
	'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
	'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'],
	'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
	'COMPARE_PATH' => $arParams['COMPARE_PATH'],
	'COMPARE_NAME' => $arParams['COMPARE_NAME'],
	'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
	'PRODUCT_BLOCKS_ORDER' => $arParams['PRODUCT_BLOCKS_ORDER'],
	'LABEL_POSITION_CLASS' => $labelPositionClass,
	'DISCOUNT_POSITION_CLASS' => $discountPositionClass,
	'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
	'SLIDER_PROGRESS' => $arParams['SLIDER_PROGRESS'],
	'~BASKET_URL' => $arParams['~BASKET_URL'],
	'~ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
	'~BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
	'~COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
	'~COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
	'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
	'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
	'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
	'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY'],
	'MESS_BTN_BUY' => $arParams['~MESS_BTN_BUY'],
	'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
	'MESS_BTN_COMPARE' => $arParams['~MESS_BTN_COMPARE'],
	'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
	'MESS_BTN_ADD_TO_BASKET' => $arParams['~MESS_BTN_ADD_TO_BASKET'],
	'MESS_NOT_AVAILABLE' => $arParams['~MESS_NOT_AVAILABLE']
);

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($this->randString()));
$containerName = 'catalog-top-container';
?>

<div class="mb-4 catalog-top bx-<?=$arParams['TEMPLATE_THEME']?>" data-entity="<?=$containerName?>" id="<?=$obName?>">
	<?
	if (!empty($arResult['RAW_ITEMS']) && !empty($arResult['ITEM_ROWS']))
	{
		$areaIds = [];
		$rowIds = [];

		foreach ($arResult['RAW_ITEMS'] as $item)
		{
			$uniqueId = $item['ID'].'_'.md5($this->randString().$component->getAction());
			$areaIds[$item['ID']] = $this->GetEditAreaId($uniqueId);
			$this->AddEditAction($uniqueId, $item['EDIT_LINK'], $elementEdit);
			$this->AddDeleteAction($uniqueId, $item['DELETE_LINK'], $elementDelete, $elementDeleteParams);
		}
		?>
		<!-- items-container -->
		<?
		foreach ($arResult['ITEM_ROWS'] as $key => $rowData)
		{
			$rowItems = array_splice($arResult['RAW_ITEMS'], 0, $rowData['COUNT']);

			$activeClass = $key === 0 ? 'active' : 'not-active';
			$rowId = 'bx-catalog-top-row-'.$key.'-'.$this->randString();
			$rowIds[] = $rowId;
			?>
			<div class="row <?=$rowData['CLASS']?> catalog-top-slide <?=$activeClass?>" id="<?=$rowId?>" data-entity="items-row">
				<?
				switch ($rowData['VARIANT'])
				{
					case 0:
						?>
						<div class="col product-item-small-card">
							<?
							$item = reset($rowItems);
							$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
								'RESULT' => array(
									'ITEM' => $item,
									'AREA_ID' => $areaIds[$item['ID']],
									'TYPE' => $rowData['TYPE'],
									'BIG_LABEL' => 'N',
									'BIG_DISCOUNT_PERCENT' => 'N',
									'BIG_BUTTONS' => 'N',
									'SCALABLE' => 'N'
								),
								'PARAMS' => $generalParams
									+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
							),
														   $component,
														   array('HIDE_ICONS' => 'Y')
							);
							?>
						</div>
						<?
						break;

					case 1:
						foreach ($rowItems as $item)
						{
							?>
							<div class="col-6 product-item-big-card">
								<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
									'RESULT' => array(
										'ITEM' => $item,
										'AREA_ID' => $areaIds[$item['ID']],
										'TYPE' => $rowData['TYPE'],
										'BIG_LABEL' => 'N',
										'BIG_DISCOUNT_PERCENT' => 'N',
										'BIG_BUTTONS' => 'N',
										'SCALABLE' => 'N'
									),
									'PARAMS' => $generalParams
										+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
								),
																  $component,
																  array('HIDE_ICONS' => 'Y')
								);
								?>
							</div>
							<?
						}
						break;

					case 2:
						foreach ($rowItems as $item)
						{
							?>
							<div class="col-sm-4 product-item-big-card">
								<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
									'RESULT' => array(
										'ITEM' => $item,
										'AREA_ID' => $areaIds[$item['ID']],
										'TYPE' => $rowData['TYPE'],
										'BIG_LABEL' => 'N',
										'BIG_DISCOUNT_PERCENT' => 'N',
										'BIG_BUTTONS' => 'Y',
										'SCALABLE' => 'N'
									),
									'PARAMS' => $generalParams
										+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
								),
																  $component,
																  array('HIDE_ICONS' => 'Y')
								);
								?>
							</div>
							<?
						}
						break;

					case 3:
						foreach ($rowItems as $item)
						{
							?>
							<div class="col-6 col-md-3 product-item-small-card">
								<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
									'RESULT' => array(
										'ITEM' => $item,
										'AREA_ID' => $areaIds[$item['ID']],
										'TYPE' => $rowData['TYPE'],
										'BIG_LABEL' => 'N',
										'BIG_DISCOUNT_PERCENT' => 'N',
										'BIG_BUTTONS' => 'N',
										'SCALABLE' => 'N'
									),
									'PARAMS' => $generalParams
										+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
								),
							  	$component,
							  	array('HIDE_ICONS' => 'Y')
								);
								?>
							</div>
							<?
						}

						break;

					case 4:
						$rowItemsCount = count($rowItems);
						?>
						<div class="col-sm-6 product-item-big-card">
							<?
							$item = array_shift($rowItems);
							$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
								'RESULT' => array(
									'ITEM' => $item,
									'AREA_ID' => $areaIds[$item['ID']],
									'TYPE' => $rowData['TYPE'],
									'BIG_LABEL' => 'N',
									'BIG_DISCOUNT_PERCENT' => 'N',
									'BIG_BUTTONS' => 'Y',
									'SCALABLE' => 'Y'
								),
								'PARAMS' => $generalParams
									+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
							),
														   $component,
														   array('HIDE_ICONS' => 'Y')
							);
							unset($item);
							?>
						</div>
						<div class="col-sm-6 product-item-small-card">
							<div class="row">
								<?
								for ($i = 0; $i < $rowItemsCount - 1; $i++)
								{
									?>
									<div class="col-6">
										<?
										$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
											'RESULT' => array(
												'ITEM' => $rowItems[$i],
												'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
												'TYPE' => $rowData['TYPE'],
												'BIG_LABEL' => 'N',
												'BIG_DISCOUNT_PERCENT' => 'N',
												'BIG_BUTTONS' => 'N',
												'SCALABLE' => 'N'
											),
											'PARAMS' => $generalParams
												+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
										),
																	   $component,
																	   array('HIDE_ICONS' => 'Y')
										);
										?>
									</div>
									<?
								}
								?>
							</div>
						</div>
						<?
						break;

					case 5:
						$rowItemsCount = count($rowItems);
						?>
						<div class="col-sm-6 product-item-small-card">
							<div class="row">
								<?
								for ($i = 0; $i < $rowItemsCount - 1; $i++)
								{
									?>
									<div class="col-6">
										<?
										$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
											'RESULT' => array(
												'ITEM' => $rowItems[$i],
												'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
												'TYPE' => $rowData['TYPE'],
												'BIG_LABEL' => 'N',
												'BIG_DISCOUNT_PERCENT' => 'N',
												'BIG_BUTTONS' => 'N',
												'SCALABLE' => 'N'
											),
											'PARAMS' => $generalParams
												+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
										),
																	   $component,
																	   array('HIDE_ICONS' => 'Y')
										);
										?>
									</div>
									<?
								}
								?>
							</div>
						</div>
						<div class="col-sm-6 product-item-big-card">
							<?
							$item = end($rowItems);
							$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
								'RESULT' => array(
									'ITEM' => $item,
									'AREA_ID' => $areaIds[$item['ID']],
									'TYPE' => $rowData['TYPE'],
									'BIG_LABEL' => 'N',
									'BIG_DISCOUNT_PERCENT' => 'N',
									'BIG_BUTTONS' => 'Y',
									'SCALABLE' => 'Y'
								),
								'PARAMS' => $generalParams
									+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
							),
														   $component,
														   array('HIDE_ICONS' => 'Y')
							);
							unset($item);
							?>
						</div>
						<?
						break;

					case 6:
						foreach ($rowItems as $item)
						{
							?>
							<div class="col-6 col-sm-4 col-md-2 product-item-small-card">
								<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
									'RESULT' => array(
										'ITEM' => $item,
										'AREA_ID' => $areaIds[$item['ID']],
										'TYPE' => $rowData['TYPE'],
										'BIG_LABEL' => 'N',
										'BIG_DISCOUNT_PERCENT' => 'N',
										'BIG_BUTTONS' => 'N',
										'SCALABLE' => 'N'
									),
									'PARAMS' => $generalParams
										+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
								),
																  $component,
																  array('HIDE_ICONS' => 'Y')
								);
								?>
							</div>
							<?
						}
						break;

					case 7:
						$rowItemsCount = count($rowItems);
						?>
						<div class="col-sm-6 product-item-big-card">
							<?
							$item = array_shift($rowItems);
							$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
								'RESULT' => array(
									'ITEM' => $item,
									'AREA_ID' => $areaIds[$item['ID']],
									'TYPE' => $rowData['TYPE'],
									'BIG_LABEL' => 'N',
									'BIG_DISCOUNT_PERCENT' => 'N',
									'BIG_BUTTONS' => 'Y',
									'SCALABLE' => 'Y'
								),
								'PARAMS' => $generalParams
									+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
							),
														   $component,
														   array('HIDE_ICONS' => 'Y')
							);
							unset($item);
							?>
						</div>
						<div class="col-sm-6 product-item-small-card">
							<div class="row">
								<?
								for ($i = 0; $i < $rowItemsCount - 1; $i++)
								{
									?>
									<div class="col-6 col-md-4">
										<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
											'RESULT' => array(
												'ITEM' => $rowItems[$i],
												'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
												'TYPE' => $rowData['TYPE'],
												'BIG_LABEL' => 'N',
												'BIG_DISCOUNT_PERCENT' => 'N',
												'BIG_BUTTONS' => 'N',
												'SCALABLE' => 'N'
											),
											'PARAMS' => $generalParams
												+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
										),
																		  $component,
																		  array('HIDE_ICONS' => 'Y')
										);
										?>
									</div>
									<?
								}
								?>
							</div>
						</div>
						<?
						break;

					case 8:
						$rowItemsCount = count($rowItems);
						?>
						<div class="col-sm-6 product-item-small-card">
							<div class="row">
								<?
								for ($i = 0; $i < $rowItemsCount - 1; $i++)
								{
									?>
									<div class="col-6 col-md-4">
										<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
											'RESULT' => array(
												'ITEM' => $rowItems[$i],
												'AREA_ID' => $areaIds[$rowItems[$i]['ID']],
												'TYPE' => $rowData['TYPE'],
												'BIG_LABEL' => 'N',
												'BIG_DISCOUNT_PERCENT' => 'N',
												'BIG_BUTTONS' => 'N',
												'SCALABLE' => 'N'
											),
											'PARAMS' => $generalParams
												+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$rowItems[$i]['IBLOCK_ID']])
										),
																		  $component,
																		  array('HIDE_ICONS' => 'Y')
										);
										?>
									</div>
									<?
								}
								?>
							</div>
						</div>
						<div class="col-sm-6 product-item-big-card">
							<?
							$item = end($rowItems);
							$APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
								'RESULT' => array(
									'ITEM' => $item,
									'AREA_ID' => $areaIds[$item['ID']],
									'TYPE' => $rowData['TYPE'],
									'BIG_LABEL' => 'N',
									'BIG_DISCOUNT_PERCENT' => 'N',
									'BIG_BUTTONS' => 'Y',
									'SCALABLE' => 'Y'
								),
								'PARAMS' => $generalParams
									+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
							),
														   $component,
														   array('HIDE_ICONS' => 'Y')
							);
							unset($item);
							?>
						</div>
						<?
						break;

					case 9:
						foreach ($rowItems as $item)
						{
							?>
							<div class="col product-line-item-card">
								<? $APPLICATION->IncludeComponent('bitrix:catalog.item', 'bootstrap_v4', array(
									'RESULT' => array(
										'ITEM' => $item,
										'AREA_ID' => $areaIds[$item['ID']],
										'TYPE' => $rowData['TYPE'],
										'BIG_LABEL' => 'N',
										'BIG_DISCOUNT_PERCENT' => 'N',
										'BIG_BUTTONS' => 'N'
									),
									'PARAMS' => $generalParams
										+ array('SKU_PROPS' => $arResult['SKU_PROPS'][$item['IBLOCK_ID']])
								),
																  $component,
																  array('HIDE_ICONS' => 'Y')
								);
								?>
							</div>
							<?
						}
						break;
				}
				?>
			</div>
			<?
		}
		unset($generalParams, $rowItems);
		?>
		<!-- items-container -->
		<?
	}
	else
	{
		// load css for bigData/deferred load
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.item',
			'bootstrap_v4',
			array(),
			$component,
			array('HIDE_ICONS' => 'Y')
		);
	}

	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedTemplate = $signer->sign($templateName, 'catalog.top');
	$signedParams = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'catalog.top');
	?>
</div>
<script>
	BX.message({
		RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
		RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>'
	});
</script>

<?
if (count($arResult['ITEMS']) > 1)
{
	$jsParams = array(
		'cont' => $obName,
		'left' => array(
			'id' => $obName.'-left-arr',
			'className' => 'catalog-top-slider-arrow-left'
		),
		'right' => array(
			'id' => $obName.'-right-arr',
			'className' => 'catalog-top-slider-arrow-right'
		),
		'rows' => $rowIds,
		'rotate' => $arParams['ROTATE_TIMER'] > 0,
		'rotateTimer' => $arParams['ROTATE_TIMER']
	);
	if ($arParams['SHOW_PAGINATION'] === 'Y')
	{
		$jsParams['pagination'] = array(
			'id' => $obName.'-pagination',
			'className' => 'catalog-top-slider-pagination'
		);
	}
	?>
	<script type="text/javascript">
		var ob<?=$obName?> = new JCCatalogTopSliderList(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
	</script>
	<?
}
?>