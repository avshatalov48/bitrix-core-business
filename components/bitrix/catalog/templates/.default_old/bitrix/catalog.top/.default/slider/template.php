<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
$this->setFrameMode(true);

$intRowsCount = count($arResult['ITEMS']);
$strRand = $this->randString();
$strContID = 'cat_top_cont_'.$strRand;
?><div id="<? echo $strContID; ?>" class="bx_catalog_tile_home_type_2 col<? echo $arParams['LINE_ELEMENT_COUNT']; ?> <? echo $templateData['TEMPLATE_CLASS']; ?>">
<div class="bx_catalog_tile_section">
<?
$boolFirst = true;
$arRowIDs = array();
foreach ($arResult['ITEMS'] as $keyRow => $arOneRow)
{
	$strRowID = 'cat-top-'.$keyRow.'_'.$strRand;
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
			'BASKET_ACTIONS' => $strMainID.'_basket_actions',
			'NOT_AVAILABLE_MESS' => $strMainID.'_not_avail',
			'SUBSCRIBE_LINK' => $strMainID.'_subscribe',
			'COMPARE_LINK' => $strMainID.'_compare_link',

			'PRICE' => $strMainID.'_price',
			'DSC_PERC' => $strMainID.'_dsc_perc',
			'SECOND_DSC_PERC' => $strMainID.'_second_dsc_perc',

			'PROP_DIV' => $strMainID.'_sku_tree',
			'PROP' => $strMainID.'_prop_',
			'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop',
			'BASKET_PROP_DIV' => $strMainID.'_basket_prop'
		);

		$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);
		$productTitle = (
			isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])&& $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
			? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
			: $arItem['NAME']
		);
		$imgTitle = (
			isset($arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] != ''
			? $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']
			: $arItem['NAME']
		);

		$minPrice = false;
		if (isset($arItem['MIN_PRICE']) || isset($arItem['RATIO_PRICE']))
			$minPrice = (isset($arItem['RATIO_PRICE']) ? $arItem['RATIO_PRICE'] : $arItem['MIN_PRICE']);
?>
	<div class="<? echo ($arItem['SECOND_PICT'] ? 'bx_catalog_item double' : 'bx_catalog_item'); ?>"><div class="bx_catalog_item_container" id="<? echo $strMainID; ?>">
		<a id="<? echo $arItemIDs['PICT']; ?>" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="bx_catalog_item_images" style="background-image: url('<? echo $arItem['PREVIEW_PICTURE']['SRC']; ?>')" title="<? echo $imgTitle; ?>">
<?
		if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
		{
?>
			<div id="<? echo $arItemIDs['DSC_PERC']; ?>" class="bx_stick_disc right bottom" style="display:<? echo (0 < $minPrice['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $minPrice['DISCOUNT_DIFF_PERCENT']; ?>%</div>
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
		<a id="<? echo $arItemIDs['SECOND_PICT']; ?>" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="bx_catalog_item_images_double" style="background-image: url('<? echo (
				!empty($arItem['PREVIEW_PICTURE_SECOND'])
				? $arItem['PREVIEW_PICTURE_SECOND']['SRC']
				: $arItem['PREVIEW_PICTURE']['SRC']
			); ?>')" title="<? echo $imgTitle; ?>">
<?
			if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
			{
?>
			<div id="<? echo $arItemIDs['SECOND_DSC_PERC']; ?>" class="bx_stick_disc right bottom" style="display:<? echo (0 < $minPrice['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $minPrice['DISCOUNT_DIFF_PERCENT']; ?>%</div>
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
		<div class="bx_catalog_item_title"><a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" title="<? echo $productTitle; ?>"><? echo $productTitle; ?></a></div>
		<div class="bx_catalog_item_price"><div id="<? echo $arItemIDs['PRICE']; ?>" class="bx_price">
<?
		if (!empty($minPrice))
		{
			if ('N' == $arParams['PRODUCT_DISPLAY_MODE'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
			{
				echo GetMessage(
					'CT_BCT_TPL_MESS_PRICE_SIMPLE_MODE',
					array(
						'#PRICE#' => $minPrice['PRINT_DISCOUNT_VALUE'],
						'#MEASURE#' => GetMessage(
							'CT_BCT_TPL_MESS_MEASURE_SIMPLE_MODE',
							array(
								'#VALUE#' => $minPrice['CATALOG_MEASURE_RATIO'],
								'#UNIT#' => $minPrice['CATALOG_MEASURE_NAME']
							)
						)
					)
				);
			}
			else
			{
				echo $minPrice['PRINT_DISCOUNT_VALUE'];
			}
			if ('Y' == $arParams['SHOW_OLD_PRICE'] && $minPrice['DISCOUNT_VALUE'] < $minPrice['VALUE'])
			{
				?> <span><? echo $minPrice['PRINT_VALUE']; ?></span><?
			}
		}
		unset($minPrice);
?>
		</div></div>
<?
		$showSubscribeBtn = false;
		$compareBtnMessage = ($arParams['MESS_BTN_COMPARE'] != '' ? $arParams['MESS_BTN_COMPARE'] : GetMessage('CT_BCT_TPL_MESS_BTN_COMPARE'));
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
				<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">-</a>
				<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
				<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">+</a>
				<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"><? echo $arItem['CATALOG_MEASURE_NAME']; ?></span>
			</div></div>
<?
				}
?>
			<div id="<? echo $arItemIDs['BASKET_ACTIONS']; ?>" class="bx_catalog_item_controls_blocktwo">
				<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_button bx_medium" href="javascript:void(0)" rel="nofollow">
<?
			if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY')
			{
				echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCT_TPL_MESS_BTN_BUY'));
			}
			else
			{
				echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCT_TPL_MESS_BTN_ADD_TO_BASKET'));
			}
?>
				</a>
			</div>
<?
				if ($arParams['DISPLAY_COMPARE'])
				{
					?>
					<div class="bx_catalog_item_controls_blocktwo">
						<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
					</div>
				<?
				}
			}
			else
			{
?>
			<div id="<? echo $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="bx_catalog_item_controls_blockone"><span class="bx_notavailable">
<?
				echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCT_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
?>
			</span></div>
<?
				if ($arParams['DISPLAY_COMPARE'] || $showSubscribeBtn)
				{
					?>
					<div class="bx_catalog_item_controls_blocktwo"><?
					if ($arParams['DISPLAY_COMPARE'])
					{
						?><a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a><?
					}
					if ($showSubscribeBtn)
					{
						?>
						<a id="<? echo $arItemIDs['SUBSCRIBE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><?
						echo ('' != $arParams['MESS_BTN_SUBSCRIBE'] ? $arParams['MESS_BTN_SUBSCRIBE'] : GetMessage('CT_BCT_TPL_MESS_BTN_SUBSCRIBE'));
						?></a><?
					}
					?>
					</div><?
				}
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
$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
if ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET'] && !$emptyProductProperties)
{
?>
			<div id="<? echo $arItemIDs['BASKET_PROP_DIV']; ?>" style="display: none;">
				<?
				if (!empty($arItem['PRODUCT_PROPERTIES_FILL']))
				{
					foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo)
					{
						?>
						<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>"><?
						if (isset($arItem['PRODUCT_PROPERTIES'][$propID]))
							unset($arItem['PRODUCT_PROPERTIES'][$propID]);
					}
				}
				$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
				if (!$emptyProductProperties)
				{
					?>
					<table>
						<?
						foreach ($arItem['PRODUCT_PROPERTIES'] as $propID => $propInfo)
						{
							?>
							<tr><td><? echo $arItem['PROPERTIES'][$propID]['NAME']; ?></td>
								<td>
									<?
									if(
										'L' == $arItem['PROPERTIES'][$propID]['PROPERTY_TYPE']
										&& 'C' == $arItem['PROPERTIES'][$propID]['LIST_TYPE']
									)
									{
										foreach($propInfo['VALUES'] as $valueID => $value)
										{
											?><label><input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?></label><br><?
										}
									}
									else
									{
										?><select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]"><?
										foreach($propInfo['VALUES'] as $valueID => $value)
										{
											?><option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"selected"' : ''); ?>><? echo $value; ?></option><?
										}
										?></select><?
									}
									?>
								</td></tr>
						<?
						}
						?>
					</table>
				<?
				}
				?>
			</div>
		<?
		}
			$arJSParams = array(
				'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
				'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
				'SHOW_ADD_BASKET_BTN' => false,
				'SHOW_BUY_BTN' => true,
				'SHOW_ABSENT' => true,
				'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
				'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
				'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
				'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
				'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
				'PRODUCT' => array(
					'ID' => $arItem['ID'],
					'NAME' => $productTitle,
					'PICT' => ('Y' == $arItem['SECOND_PICT'] ? $arItem['PREVIEW_PICTURE_SECOND'] : $arItem['PREVIEW_PICTURE']),
					'CAN_BUY' => $arItem["CAN_BUY"],
					'CHECK_QUANTITY' => $arItem['CHECK_QUANTITY'],
					'MAX_QUANTITY' => $arItem['CATALOG_QUANTITY'],
					'STEP_QUANTITY' => $arItem['CATALOG_MEASURE_RATIO'],
					'QUANTITY_FLOAT' => is_double($arItem['CATALOG_MEASURE_RATIO']),
					'BASIS_PRICE' => $arItem['MIN_BASIS_PRICE']
				),
				'VISUAL' => array(
					'ID' => $arItemIDs['ID'],
					'PICT_ID' => ('Y' == $arItem['SECOND_PICT'] ? $arItemIDs['SECOND_PICT'] : $arItemIDs['PICT']),
					'QUANTITY_ID' => $arItemIDs['QUANTITY'],
					'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
					'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
					'PRICE_ID' => $arItemIDs['PRICE'],
					'BUY_ID' => $arItemIDs['BUY_LINK'],
					'BASKET_PROP_DIV' => $arItemIDs['BASKET_PROP_DIV'],
					'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
					'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
					'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
				),
				'BASKET' => array(
					'ADD_PROPS' => ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET']),
					'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
					'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
					'EMPTY_PROPS' => $emptyProductProperties,
					'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
					'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
				)
			);
		if ($arParams['DISPLAY_COMPARE'])
		{
			$arJSParams['COMPARE'] = array(
				'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
				'COMPARE_PATH' => $arParams['COMPARE_PATH']
			);
		}
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
				$canBuy = $arItem['JS_OFFERS'][$arItem['OFFERS_SELECTED']]['CAN_BUY'];
?>
		<div class="bx_catalog_item_controls no_touch">
<?
				if ('Y' == $arParams['USE_PRODUCT_QUANTITY'])
				{
?>
			<div class="bx_catalog_item_controls_blockone">
				<a id="<? echo $arItemIDs['QUANTITY_DOWN']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">-</a>
				<input type="text" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
				<a id="<? echo $arItemIDs['QUANTITY_UP']; ?>" href="javascript:void(0)" class="bx_bt_button_type_2 bx_small" rel="nofollow">+</a>
				<span id="<? echo $arItemIDs['QUANTITY_MEASURE']; ?>"></span>
			</div>
<?
				}
?>
			<div id="<? echo $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="bx_catalog_item_controls_blockone" style="display: <? echo ($canBuy ? 'none' : ''); ?>;"><span class="bx_notavailable">
<?
				echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCT_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
?>
			</span></div>
			<div id="<? echo $arItemIDs['BASKET_ACTIONS']; ?>" class="bx_catalog_item_controls_blocktwo" style="display: <? echo ($canBuy ? '' : 'none'); ?>;">
				<a id="<? echo $arItemIDs['BUY_LINK']; ?>" class="bx_bt_button bx_medium" href="javascript:void(0)" rel="nofollow"><?
				if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY')
				{
					echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCT_TPL_MESS_BTN_BUY'));
				}
				else
				{
					echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCT_TPL_MESS_BTN_ADD_TO_BASKET'));
				}
				?></a>
			</div>
<?
if ($arParams['DISPLAY_COMPARE'])
{
	?>
	<div class="bx_catalog_item_controls_blocktwo">
	<a id="<? echo $arItemIDs['COMPARE_LINK']; ?>" class="bx_bt_button_type_2 bx_medium" href="javascript:void(0)"><? echo $compareBtnMessage; ?></a>
	</div><?
}
?>
			<div style="clear: both;"></div>
		</div>
<?
				unset($canBuy);
			}
			else
			{
?>
		<div class="bx_catalog_item_controls no_touch">
			<a class="bx_bt_button_type_2 bx_medium" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"><?
			echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCT_TPL_MESS_BTN_DETAIL'));
			?></a>
		</div>
<?
			}
?>
		<div class="bx_catalog_item_controls touch">
			<a class="bx_bt_button_type_2 bx_medium" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"><?
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
							'SHOW_MODE' => $arOneProp['SHOW_MODE'],
							'VALUES_COUNT' => $arOneProp['VALUES_COUNT']
						);
					}
					foreach ($arItem['JS_OFFERS'] as &$arOneJs)
					{
						if (0 < $arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'])
						{
							$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
							$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
						}
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
						'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
						'SHOW_ADD_BASKET_BTN' => false,
						'SHOW_BUY_BTN' => true,
						'SHOW_ABSENT' => true,
						'SHOW_SKU_PROPS' => $arItem['OFFERS_PROPS_DISPLAY'],
						'SECOND_PICT' => $arItem['SECOND_PICT'],
						'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
						'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
						'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
						'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
						'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
						'DEFAULT_PICTURE' => array(
							'PICTURE' => $arItem['PRODUCT_PREVIEW'],
							'PICTURE_SECOND' => $arItem['PRODUCT_PREVIEW_SECOND']
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
							'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
							'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
							'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
						),
						'BASKET' => array(
							'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
							'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
							'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
							'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
							'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
						),
						'PRODUCT' => array(
							'ID' => $arItem['ID'],
							'NAME' => $productTitle
						),
						'OFFERS' => $arItem['JS_OFFERS'],
						'OFFER_SELECTED' => $arItem['OFFERS_SELECTED'],
						'TREE_PROPS' => $arSkuProps
					);
					if ($arParams['DISPLAY_COMPARE'])
					{
						$arJSParams['COMPARE'] = array(
							'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
							'COMPARE_PATH' => $arParams['COMPARE_PATH']
						);
					}
?>
<script type="text/javascript">
	var <? echo $strObName; ?> = new JCCatalogTopSlider(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
				}
			}
			else
			{
				$arJSParams = array(
					'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
					'SHOW_QUANTITY' => false,
					'SHOW_ADD_BASKET_BTN' => false,
					'SHOW_BUY_BTN' => false,
					'SHOW_ABSENT' => false,
					'SHOW_SKU_PROPS' => false,
					'SECOND_PICT' => $arItem['SECOND_PICT'],
					'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
					'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
					'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
					'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
					'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
					'DEFAULT_PICTURE' => array(
						'PICTURE' => $arItem['PRODUCT_PREVIEW'],
						'PICTURE_SECOND' => $arItem['PRODUCT_PREVIEW_SECOND']
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
						'BASKET_ACTIONS_ID' => $arItemIDs['BASKET_ACTIONS'],
						'NOT_AVAILABLE_MESS' => $arItemIDs['NOT_AVAILABLE_MESS'],
						'COMPARE_LINK_ID' => $arItemIDs['COMPARE_LINK']
					),
					'BASKET' => array(
						'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
						'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
						'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
						'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
						'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
					),
					'PRODUCT' => array(
						'ID' => $arItem['ID'],
						'NAME' => $productTitle
					),
					'OFFERS' => array(),
					'OFFER_SELECTED' => 0,
					'TREE_PROPS' => array()
				);
				if ($arParams['DISPLAY_COMPARE'])
				{
					$arJSParams['COMPARE'] = array(
						'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
						'COMPARE_PATH' => $arParams['COMPARE_PATH']
					);
				}
?>
<script type="text/javascript">
var <? echo $strObName; ?> = new JCCatalogTopSlider(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
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
			'className' => 'bx_catalog_tile_slider_arrow_left'
		),
		'right' => array(
			'id' => $strContID.'_right_arr',
			'className' => 'bx_catalog_tile_slider_arrow_right'
		),
		'rows' => $arRowIDs,
		'rotate' => (0 < $arParams['ROTATE_TIMER']),
		'rotateTimer' => $arParams['ROTATE_TIMER']
	);
	if ('Y' == $arParams['SHOW_PAGINATION'])
	{
		$arJSParams['pagination'] = array(
			'id' => $strContID.'_pagination',
			'className' => 'bx_catalog_tile_slider_pagination'
		);
	}
?>
<script type="text/javascript">
var ob<? echo $strContID; ?> = new JCCatalogTopSliderList(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
}
?>
</div>