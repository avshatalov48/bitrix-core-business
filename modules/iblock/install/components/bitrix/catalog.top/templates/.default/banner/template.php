<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var string $strElementEdit */
/** @var string $strElementDelete */
/** @var array $arElementDeleteParams */
/** @var array $arSkuTemplate */
/** @var array $templateData */
$intCount = count($arResult['ITEMS']);
$strItemWidth = 100/$intCount;
$strAllWidth = 100*$intCount;
$arRowIDs = array();
$strContID = 'bx_catalog_slider_'.$this->randString();
?>
<div class="bx_slider_section <? echo $templateData['TEMPLATE_CLASS']; ?>" id="<? echo $strContID; ?>">
	<div class="bx_slider_container" style="width:<? echo $strAllWidth; ?>%;">
		<?
		$boolFirst = true;
		foreach ($arResult['ITEMS'] as $key => $arItem)
		{
			$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
			$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
			$strMainID = $this->GetEditAreaId($arItem['ID']);

			$arRowIDs[] = $strMainID;
			$arItemIDs = array(
				'ID' => $strMainID,
				'PICT' => $strMainID.'_pict',

				'QUANTITY' => $strMainID.'_quantity',
				'QUANTITY_DOWN' => $strMainID.'_quant_down',
				'QUANTITY_UP' => $strMainID.'_quant_up',
				'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
				'BUY_LINK' => $strMainID.'_buy_link',

				'PRICE' => $strMainID.'_price',
				'OLD_PRICE' => $strMainID.'_old_price',
				'DSC_PERC' => $strMainID.'_dsc_perc',
				'BASKET_PROP_DIV' => $strMainID.'_basket_prop',

				'NOT_AVAILABLE_MESS' => $strMainID.'_not_avail'
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
			?>
			<div id="<? echo $strMainID; ?>" class="bx_slider_block<? echo ($boolFirst ? ' active' : ''); ?>" style="width: <? echo $strItemWidth; ?>%;">
				<div class="bx_slider_photo_container">
					<div class="bx_slider_photo_background"></div>
					<a id="<? echo $arItemIDs['PICT']; ?>" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="bx_slider_photo_element" style="background:#fff url(<? echo $arItem['PREVIEW_PICTURE']['SRC']; ?>) no-repeat center;" title="<? echo $imgTitle; ?>">
						<?
						if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
						{
							?>
							<div id="<? echo $arItemIDs['DSC_PERC']; ?>" class="bx_stick_disc right bottom" style="display:<? echo (0 < $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT']; ?>%</div>
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
				</div>
				<div class="bx_slider_content_container">
					<h2 class="bx_slider_title"><a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" title="<? echo $productTitle; ?>"><? echo $productTitle; ?></a></h2>
					<?
					if ('' != $arItem['PREVIEW_TEXT'])
					{
						?>
						<div class="bx_slider_content_description"><? echo $arItem['PREVIEW_TEXT']; ?></div>
						<?
					}
					?>
					<div class="bx_slider_price_container">
						<div class="bx_slider_price_leftblock">
							<?
							if (!empty($arItem['MIN_PRICE']))
							{
								if ('N' == $arParams['PRODUCT_DISPLAY_MODE'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
								{
									?>
									<div id="<? echo $arItemIDs['PRICE']; ?>" class="bx_slider_current_price bx_no_oldprice">
										<?
										echo GetMessage(
											'CT_BCT_TPL_MESS_PRICE_SIMPLE_MODE_SHORT',
											array(
												'#PRICE#' => $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE']
											)
										);
										?>
									</div>
									<?
								}
								else
								{
									$boolOldPrice = ('Y' == $arParams['SHOW_OLD_PRICE'] && $arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE']);
									?>
									<div id="<? echo $arItemIDs['PRICE']; ?>" class="bx_slider_current_price<? echo ($boolOldPrice ? '' : ' bx_no_oldprice'); ?>">
										<?
										echo $arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];
										if ($boolOldPrice)
										{
											?>
											<div id="<? echo $arItemIDs['OLD_PRICE']; ?>" class="bx_slider_old_price"><? echo $arItem['MIN_PRICE']['PRINT_VALUE']; ?></div>
											<?
										}
										?>
									</div>
									<?
								}
							}
							if (isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
							{
								?>
								<a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="bx_bt_button big shadow cart"><span></span><strong><?
										echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCT_TPL_MESS_BTN_DETAIL'));
										?></strong></a>
								<?
							}
							else
							{
								if ($arItem['CAN_BUY'])
								{
									?>
									<a id="<? echo $arItemIDs['BUY_LINK']; ?>" href="javascript:void(0)" rel="nofollow" class="bx_bt_button big shadow cart"><span></span><strong><?
											if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY')
											{
												echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCT_TPL_MESS_BTN_BUY'));
											}
											else
											{
												echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCT_TPL_MESS_BTN_ADD_TO_BASKET'));
											}
											?></strong></a>
									<?
								}
								else
								{
									?>
									<span id="<? echo $arItemIDs['NOT_AVAILABLE_MESS']; ?>" class="bx_notavailable">
<?
echo ('' != $arParams['MESS_NOT_AVAILABLE'] ? $arParams['MESS_NOT_AVAILABLE'] : GetMessage('CT_BCT_TPL_MESS_PRODUCT_NOT_AVAILABLE'));
?>
			</span>
									<?
								}
							}
							?>
						</div>
						<div class="bx_slider_price_rightblock"></div>
					</div>
				</div>
			</div>
		<?
		if (!isset($arItem['OFFERS']) || empty($arItem['OFFERS']))
		{
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
				'SHOW_QUANTITY' => false,
				'SHOW_ADD_BASKET_BTN' => false,
				'SHOW_BUY_BTN' => true,
				'SHOW_ABSENT' => true,
				'PRODUCT' => array(
					'ID' => $arItem['ID'],
					'NAME' => $productTitle,
					'PICT' => $arItem['PREVIEW_PICTURE'],
					'CAN_BUY' => $arItem["CAN_BUY"],
					'SUBSCRIPTION' => ('Y' == $arItem['CATALOG_SUBSCRIPTION']),
					'CHECK_QUANTITY' => $arItem['CHECK_QUANTITY'],
					'MAX_QUANTITY' => $arItem['CATALOG_QUANTITY'],
					'STEP_QUANTITY' => $arItem['CATALOG_MEASURE_RATIO'],
					'QUANTITY_FLOAT' => is_double($arItem['CATALOG_MEASURE_RATIO']),
					'ADD_URL' => $arItem['~ADD_URL']
				),
				'VISUAL' => array(
					'ID' => $arItemIDs['ID'],
					'PICT_ID' => $arItemIDs['PICT'],
					'QUANTITY_ID' => $arItemIDs['QUANTITY'],
					'QUANTITY_UP_ID' => $arItemIDs['QUANTITY_UP'],
					'QUANTITY_DOWN_ID' => $arItemIDs['QUANTITY_DOWN'],
					'PRICE_ID' => $arItemIDs['PRICE'],
					'BUY_ID' => $arItemIDs['BUY_LINK'],
					'BASKET_PROP_DIV' => $arItemIDs['BASKET_PROP_DIV']
				),
				'BASKET' => array(
					'ADD_PROPS' => ('Y' == $arParams['ADD_PROPERTIES_TO_BASKET']),
					'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
					'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
					'EMPTY_PROPS' => $emptyProductProperties
				)
			);
			?><script type="text/javascript">
		  var <? echo $strObName; ?> = new JCCatalogTopBanner(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
		</script><?
		}
			$boolFirst = false;
		}
		?>
	</div>
	<?
	if (1 < $intCount)
	{
		$arJSParams = array(
			'cont' => $strContID,
			'arrows' => array(
				'id' => $strContID.'_arrows',
				'className' => 'bx_slider_controls'
			),
			'left' => array(
				'id' => $strContID.'_left_arr',
				'className' => 'bx_slider_arrow_left'
			),
			'right' => array(
				'id' => $strContID.'_right_arr',
				'className' => 'bx_slider_arrow_right'
			),
			'items' => $arRowIDs,
			'rotate' => (0 < $arParams['ROTATE_TIMER']),
			'rotateTimer' => $arParams['ROTATE_TIMER']
		);
		if ('Y' == $arParams['SHOW_PAGINATION'])
		{
			$arJSParams['pagination'] = array(
				'id' => $strContID.'_pagination',
				'className' => 'bx_slider_pagination'
			);
		}
		?>
		<script type="text/javascript">
		  var ob<? echo $strContID; ?> = new JCCatalogTopBannerList(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
		</script>
		<?
	}
	?>
</div>