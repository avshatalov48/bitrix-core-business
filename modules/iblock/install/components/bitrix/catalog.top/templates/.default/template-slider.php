<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var string $strElementEdit */
/** @var string $strElementDelete */
/** @var array $arElementDeleteParams */
/** @var array $arSkuTemplate */
$intRowsCount = count($arResult['ITEMS']);
$strContID = 'cat-top-cont-'.mt_rand(0, 1000000);
?><div id="<? echo $strContID; ?>" class="bx_catalog_tile_home_type_2 col<? echo $arParams['LINE_ELEMENT_COUNT']; ?>">
<div class="bx_catalog_tile_section">
<?
$boolFirst = true;
$arRowIDs = array();
foreach ($arResult['ITEMS'] as $keyRow => $arOneRow)
{
	$strRowID = 'cat-top-'.$keyRow.'_'.mt_rand(0, 1000000);
	$arRowIDs[] = $strRowID;
?>
<div id="<? echo $strRowID; ?>" class="bx_catalog_tile_slide <? echo ($boolFirst ? 'active' : 'notactive'); ?>">
<?
	foreach ($arOneRow as $keyItem => $arItem)
	{
		$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
		$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
		$strMainID = $this->GetEditAreaId($arItem['ID']);

		$arItemIDs = array(
			'ID' => $strMainID,
			'PICT' => $strMainID.'_pict',
			'SECOND_PICT' => $strMainID.'_secondpict',
			'MAIN_PROPS' => $strMainID.'_main_props',

			'QUANTITY' => $strMainID.'_quantity',
			'QUANTITY_DOWN' => $strMainID.'_quant_down',
			'QUANTITY_UP' => $strMainID.'_quant_up',
			'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
			'BUY_LINK' => $strMainID.'_buy_link',
			'SUBSCRIBE_LINK' => $strMainID.'_subscribe',

			'PRICE' => $strMainID.'_price',
			'DSC_PERC' => $strMainID.'_dsc_perc',
			'SECOND_DSC_PERC' => $strMainID.'_second_dsc_perc',

			'PROP_DIV' => $strMainID.'_sku_tree',
			'PROP' => $strMainID.'_prop_',
			'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop'
		);

		$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/i", "x", $strMainID);
?>
	<div class="<? echo ($arItem['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>"><div class="bx_catalog_item_container" id="<? echo $strMainID; ?>">
		<a id="<? echo $arItemIDs['PICT']; ?>"
			href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"
			class="bx_catalog_item_images"
			style="background-image: url(<? echo $arItem['PREVIEW_PICTURE']['SRC']; ?>)"
			title="<? echo (isset($arItem['PREVIEW_PICTURE']['TITLE']) ? $arItem['PREVIEW_PICTURE']['TITLE'] : ''); ?>">
<?
		if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
		{
?>
			<div id="<? echo $arItemIDs['DSC_PERC']; ?>"
				class="bx_stick_disc right bottom"
				style="display:<? echo (0 < $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT']; ?>%</div>
<?
		}
		if ($arItem['LABEL'])
		{
?>
			<div class="bx_stick average left top" title="<? echo $arItem['LABEL_VALUE']; ?>"><? echo $arItem['LABEL_VALUE']; ?></div>
<?
		}
?>
		</a>
<?
		if ($arItem['SECOND_PICT'])
		{
?>
		<a id="<? echo $arItemIDs['SECOND_PICT']; ?>"
			href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"
			class="bx_catalog_item_images_double"
			style="background-image: url(<? echo (
				!empty($arItem['PREVIEW_PICTURE_SECOND'])
				? $arItem['PREVIEW_PICTURE_SECOND']['SRC']
				: $arItem['PREVIEW_PICTURE']['SRC']
			); ?>)"
			title="<? echo (isset($arItem['PREVIEW_PICTURE']['TITLE']) ? $arItem['PREVIEW_PICTURE']['TITLE'] : ''); ?>">
<?
			if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
			{
?>
			<div
				id="<? echo $arItemIDs['SECOND_DSC_PERC']; ?>"
				class="bx_stick_disc right bottom"
				style="display:<? echo (0 < $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT']; ?>%</div>
<?
			}
			if ($arItem['LABEL'])
			{
?>
			<div class="bx_stick average left top" title="<? echo $arItem['LABEL_VALUE']; ?>"><? echo $arItem['LABEL_VALUE']; ?></div>
<?
			}
?>
		</a>
<?
		}
?>
		<div class="bx_catalog_item_title"><a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" title="<? echo $arItem['NAME']; ?>"><? echo $arItem['NAME']; ?></a></div>
		<div class="bx_catalog_item_price"><div id="<? echo $arItemIDs['PRICE']; ?>" class="bx_price">
<?
		if (!empty($arItem['MIN_PRICE']))
		{
			if ('N' == $arParams['PRODUCT_DISPLAY_MODE'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
			{
				echo GetMessage(
					'CT_BCT_TPL_MESS_PRICE_SIMPLE_MODE',
					array(
						'#PRICE#' => $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'],
						'#MEASURE#' => GetMessage(
							'CT_BCT_TPL_MESS_MEASURE_SIMPLE_MODE',
							array(
								'#VALUE#' => $arItem['MIN_PRICE']['CATALOG_MEASURE_RATIO'],
								'#UNIT#' => $arItem['MIN_PRICE']['CATALOG_MEASURE_NAME']
							)
						)
					)
				);
			}
			else
			{
				echo $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];
			}
			if ('Y' == $arParams['SHOW_OLD_PRICE'] && $arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE'])
			{
				?> <span><? echo $arItem['MIN_PRICE']['PRINT_VALUE']; ?></span><?
			}
		}
?>
		</div></div>
<?
		if (!isset($arItem['OFFERS']) || empty($arItem['OFFERS']))
		{
?>
		<div class="bx_catalog_item_controls">
<?
			if ($arItem['CAN_BUY'])
			{
				if ('Y' == $arParams['USE_PRODUCT_QUANTITY'])
				{
?>
			<div class="bx_catalog_item_controls_blockone"><div style="display: inline-block;position: relative;">
				<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_white bx_small" rel="nofollow">-</a>
				<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
				<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_white bx_small" rel="nofollow">+</a>
				<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"><? echo $arItem['CATALOG_MEASURE_NAME']; ?></span>
			</div></div>
<?
				}
?>
			<div class="bx_catalog_item_controls_blocktwo">
				<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_white bx_medium" href="javascript:void(0)" rel="nofollow">
<?
				echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCT_TPL_MESS_BTN_BUY'));
?>
				</a>
			</div>
<?
			}
			else
			{
?>
			<div class="bx_catalog_item_controls_blockone"><span class="bx_notavailable">
<?
				echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCT_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
?>
			</span></div>
<?
			}
?>
			<div style="clear: both;"></div>
		</div>
<?
			if (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']))
			{
?>
		<div class="bx_catalog_item_articul">
<?
				foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp)
				{
					?><br><strong><? echo $arOneProp['NAME']; ?></strong> <?
					echo (
						is_array($arOneProp['DISPLAY_VALUE'])
						? implode('<br>', $arOneProp['DISPLAY_VALUE'])
						: $arOneProp['DISPLAY_VALUE']
					);
				}
?>
		</div>
<?
			}
			$arJSParams = array(
				'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
				'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
				'SHOW_ADD_BASKET_BTN' => false,
				'SHOW_BUY_BTN' => true,
				'SHOW_ABSENT' => true,
				'PRODUCT' => array(
					'ID' => $arItem['ID'],
					'NAME' => $arItem['~NAME'],
					'PICT' => ('Y' == $arItem['SECOND_PICT'] ? $arItem['PREVIEW_PICTURE_SECOND'] : $arItem['PREVIEW_PICTURE']),
					'CAN_BUY' => $arItem["CAN_BUY"],
					'CHECK_QUANTITY' => $arItem['CHECK_QUANTITY'],
					'MAX_QUANTITY' => $arItem['CATALOG_QUANTITY'],
					'STEP_QUANTITY' => $arItem['CATALOG_MEASURE_RATIO'],
					'QUANTITY_FLOAT' => is_double($arItem['CATALOG_MEASURE_RATIO']),
					'ADD_URL' => $arItem['~ADD_URL'],
				),
				'VISUAL' => array(
					'ID' => $arItemIDs['ID'],
					'PICT_ID' => ('Y' == $arItem['SECOND_PICT'] ? $arItemIDs['SECOND_PICT'] : $arItemIDs['PICT']),
					'QUANTITY_ID' => $arItemIDs['QUANTITY'],
					'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
					'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
					'PRICE_ID' => $arItemIDs['PRICE'],
					'BUY_ID' => $arItemIDs['BUY_LINK'],
				),
				'BASKET' => array(
					'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
					'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE']
				),
				'AJAX_PATH' => POST_FORM_ACTION_URI
			);
?>
<script type="text/javascript">
	var <? echo $strObName; ?> = new JCCatalogTopSlider(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
		}
		else
		{
			if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'])
			{
?>
		<div class="bx_catalog_item_controls no_touch">
<?
				if ('Y' == $arParams['USE_PRODUCT_QUANTITY'])
				{
?>
			<div class="bx_catalog_item_controls_blockone">
				<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_white bx_small" rel="nofollow">-</a>
				<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
				<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_white bx_small" rel="nofollow">+</a>
				<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"></span>
			</div>
<?
				}
?>
			<div class="bx_catalog_item_controls_blocktwo">
				<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_white bx_medium" href="javascript:void(0)" rel="nofollow"><?
				echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCT_TPL_MESS_BTN_BUY'));
				?></a>
			</div>
			<div style="clear: both;"></div>
		</div>
<?
			}
			else
			{
?>
		<div class="bx_catalog_item_controls no_touch">
			<a class="bx_bt_white bx_medium" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"><?
			echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCT_TPL_MESS_BTN_DETAIL'));
			?></a>
		</div>
<?
			}
?>
		<div class="bx_catalog_item_controls touch">
			<a class="bx_bt_white bx_medium" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"><?
			echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCT_TPL_MESS_BTN_DETAIL'));
			?></a>
		</div>
<?
			$boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $arItem['OFFERS_PROPS_DISPLAY']);
			$boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
			if ($boolShowProductProps || $boolShowOfferProps)
			{
?>
		<div class="bx_catalog_item_articul">
<?
				if ($boolShowProductProps)
				{
					foreach ($arItem['DISPLAY_PROPERTIES'] as $arOneProp)
					{
						?><br><strong><? echo $arOneProp['NAME']; ?></strong> <?
						echo (
							is_array($arOneProp['DISPLAY_VALUE'])
							? implode(' / ', $arOneProp['DISPLAY_VALUE'])
							: $arOneProp['DISPLAY_VALUE']
						);
					}
				}
				if ($boolShowOfferProps)
				{
?>
			<span id="<? echo $arItemIDs['DISPLAY_PROP_DIV']; ?>" style="display: none;"></span>
<?
				}
?>
		</div>
<?
			}
			if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'])
			{
				if (!empty($arItem['OFFERS_PROP']))
				{
					$arSkuProps = array();
?>
		<div class="bx_catalog_item_scu" id="<? echo $arItemIDs['PROP_DIV']; ?>">
<?
					foreach ($arSkuTemplate as $code => $strTemplate)
					{
						if (!isset($arItem['OFFERS_PROP'][$code]))
							continue;
						echo '<div>', str_replace('#ITEM#_prop_', $arItemIDs['PROP'], $strTemplate), '</div>';
					}
					foreach ($arResult['SKU_PROPS'] as $arOneProp)
					{
						if (!isset($arItem['OFFERS_PROP'][$arOneProp['CODE']]))
							continue;
						$arSkuProps[] = array(
							'ID' => $arOneProp['ID'],
							'TYPE' => $arOneProp['PROPERTY_TYPE'],
							'VALUES_COUNT' => $arOneProp['VALUES_COUNT']
						);
					}
					foreach ($arItem['JS_OFFERS'] as &$arOneJs)
					{
						if (0 < $arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'])
							$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
					}
					unset($arOneJs);
?>
		</div>
<?
					if ($arItem['OFFERS_PROPS_DISPLAY'])
					{
						foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer)
						{
							$strProps = '';
							if (!empty($arJSOffer['DISPLAY_PROPERTIES']))
							{
								foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp)
								{
									$strProps .= '<br>'.$arOneProp['NAME'].' <strong>'.(
										is_array($arOneProp['VALUE'])
										? implode(' / ', $arOneProp['VALUE'])
										: $arOneProp['VALUE']
									).'</strong>';
								}
							}
							$arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
						}
					}
					$arJSParams = array(
						'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
						'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
						'SHOW_ADD_BASKET_BTN' => false,
						'SHOW_BUY_BTN' => true,
						'SHOW_ABSENT' => true,
						'SHOW_SKU_PROPS' => $arItem['OFFERS_PROPS_DISPLAY'],
						'SECOND_PICT' => $arItem['SECOND_PICT'],
						'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
						'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
						'DEFAULT_PICTURE' => array(
							'PICTURE' => $arItem['PREVIEW_PICTURE'],
							'PICTURE_SECOND' => $arItem['PREVIEW_PICTURE_SECOND']
						),
						'VISUAL' => array(
							'ID' => $arItemIDs['ID'],
							'PICT_ID' => $arItemIDs['PICT'],
							'SECOND_PICT_ID' => $arItemIDs['SECOND_PICT'],
							'QUANTITY_ID' => $arItemIDs['QUANTITY'],
							'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
							'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
							'QUANTITY_MEASURE' => $arItemIDs['QUANTITY_MEASURE'],
							'PRICE_ID' => $arItemIDs['PRICE'],
							'TREE_ID' => $arItemIDs['PROP_DIV'],
							'TREE_ITEM_ID' => $arItemIDs['PROP'],
							'BUY_ID' => $arItemIDs['BUY_LINK'],
							'ADD_BASKET_ID' => $arItemIDs['ADD_BASKET_ID'],
							'DSC_PERC' => $arItemIDs['DSC_PERC'],
							'SECOND_DSC_PERC' => $arItemIDs['SECOND_DSC_PERC'],
							'DISPLAY_PROP_DIV' => $arItemIDs['DISPLAY_PROP_DIV'],
						),
						'BASKET' => array(
							'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
							'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE']
						),
						'OFFERS' => $arItem['JS_OFFERS'],
						'OFFER_SELECTED' => $arItem['OFFERS_SELECTED'],
						'TREE_PROPS' => $arSkuProps,
						'AJAX_PATH' => POST_FORM_ACTION_URI
					);
?>
<script type="text/javascript">
	var <? echo $strObName; ?> = new JCCatalogTopSlider(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
				}
			}
		}
?>
	</div></div>
<?
	}
?>
	<div style="clear: both;"></div>
</div>
<?
	$boolFirst = false;
}
?>
</div>
<?
if (1 < $intRowsCount)
{
	$arJSParams = array(
		'cont' => $strContID,
		'left' => array(
			'id' => $strContID.'_left_arr',
			'class' => 'bx_catalog_tile_slider_arrow_left'
		),
		'right' => array(
			'id' => $strContID.'_right_arr',
			'class' => 'bx_catalog_tile_slider_arrow_right'
		),
		'pagination' => array(
			'id' => $strContID.'_pagination',
			'class' => 'bx_catalog_tile_slider_pagination'
		),
		'rows' => $arRowIDs,
		'rotate' => (0 < $arParams['ROTATE_TIMER']),
		'rotateTimer' => $arParams['ROTATE_TIMER']
	);
?>

<script type="text/javascript">
var my = new JCCatalogTopSliderList(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);

</script>
<?
}
?>
</div>