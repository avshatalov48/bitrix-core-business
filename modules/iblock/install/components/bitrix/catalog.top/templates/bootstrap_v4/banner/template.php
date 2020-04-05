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
//$strItemWidth = 100/$intCount;
$strAllWidth = 100*$intCount;
$arRowIDs = array();
$strContID = 'js_catalog_top_banner_s'.$this->randString();
?>
<div class="catalog-top-banner mb-4 <? echo $templateData['TEMPLATE_CLASS']; ?>" id="<? echo $strContID; ?>">
	<div class="catalog-top-banner-slider" style="width:<? echo $strAllWidth; ?>%;">
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
				isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
				? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
				: $arItem['NAME']
			);
			$imgTitle = (
				isset($arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] != ''
				? $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']
				: $arItem['NAME']
			);

			$showPrice = false;
			$price = null;
			if ($arItem['PRODUCT']['TYPE'] === null)
			{
				if (!empty($arItem['MIN_PRICE']) && is_array($arItem['MIN_PRICE']))
				{
					$showPrice = true;
					$price = $arItem['MIN_PRICE'];
					$price['PERCENT'] = $price['DISCOUNT_DIFF_PERCENT'];
					$price['PRINT_BASE_PRICE'] = $price['PRINT_VALUE'];
					$price['PRINT_PRICE'] = $price['PRINT_DISCOUNT_VALUE'];
				}
			}
			else
			{
				if (isset($arItem['ITEM_START_PRICE']) && is_array($arItem['ITEM_START_PRICE']))
				{
					$showPrice = true;
					$price = $arItem['ITEM_START_PRICE'];
				}
				elseif (!empty($arItem['ITEM_PRICES']) && isset($arItem['ITEM_PRICES'][$arItem['ITEM_PRICE_SELECTED']]))
				{
					$showPrice = true;
					$price = $arItem['ITEM_PRICES'][$arItem['ITEM_PRICE_SELECTED']];
				}
			}

			?>
			<div id="<? echo $strMainID; ?>" class="catalog-top-banner-slide<? echo ($boolFirst ? ' active' : ''); ?>">
				<div class="catalog-top-banner-img-block">
					<div class="catalog-top-banner-img-canvas">
						<div class="catalog-top-banner-img-understratum"></div>
						<a id="<? echo $arItemIDs['PICT']; ?>"
						   href="<? echo $arItem['DETAIL_PAGE_URL']; ?>"
						   class="catalog-top-banner-img-element"
						   style="background-image: url(<? echo $arItem['PREVIEW_PICTURE']['SRC']; ?>);"
						   title="<? echo $imgTitle; ?>">
							<?
							if ($showPrice && 'Y' == $arParams['SHOW_DISCOUNT_PERCENT'])
							{
								?>
								<span id="<? echo $arItemIDs['DSC_PERC']; ?>" class="catalog-top-banner-disc right bottom" style="display:<?=($price['PERCENT'] > 0 ? '' : 'none'); ?>;">-<?=$price['PERCENT']; ?>%</span>
								<?
							}
							if ($arItem['LABEL'])
							{
								?>
								<span class="catalog-top-banner-stick average left top" title="<? echo $arItem['LABEL_VALUE']; ?>"><? echo $arItem['LABEL_VALUE']; ?></span>
								<?
							}
							?>
						</a>
					</div>
				</div>
				<div class="catalog-top-banner-info-block">
					<h2 class="catalog-top-banner-title">
						<a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" title="<? echo $productTitle; ?>"><? echo $productTitle; ?></a>
					</h2>
					<?
					if ('' != $arItem['PREVIEW_TEXT'])
					{
						?>
						<div class="catalog-top-banner-description" itemprop="description"><? echo $arItem['PREVIEW_TEXT']; ?></div>
						<?
					}
					?>
					<div class="catalog-top-banner-price-container">
						<div class="catalog-top-banner-price-container-background"></div>
						<div class="catalog-top-banner-price-left-block">
							<?
							if ($showPrice)
							{
								if ('N' == $arParams['PRODUCT_DISPLAY_MODE'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
								{
									?>
									<div id="<? echo $arItemIDs['PRICE']; ?>" class="catalog-top-banner-price">
										<?
										echo GetMessage(
											'CT_BCT_TPL_MESS_PRICE_SIMPLE_MODE_SHORT',
											array(
												'#PRICE#' => $price['PRINT_PRICE']
											)
										);
										?>
									</div>
									<?
								}
								else
								{
									$boolOldPrice = ('Y' == $arParams['SHOW_OLD_PRICE'] && $price['PRICE'] < $price['BASE_PRICE']);
									?>
									<div id="<? echo $arItemIDs['PRICE']; ?>" class="catalog-top-banner-price">
										<div>
											<?
											echo $price['PRINT_PRICE'];
											if ($boolOldPrice)
											{
												?>
												<div id="<? echo $arItemIDs['OLD_PRICE']; ?>" class="catalog-top-banner-price-old"><?=$price['PRINT_BASE_PRICE'];
													?></div>
												<?
											}
											?>
										</div>
									</div>
									<?
								}
							}

							if (isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
							{
								?>
								<a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="btn btn-primary">
									<?
										echo ('' != $arParams['MESS_BTN_DETAIL'] ? $arParams['MESS_BTN_DETAIL'] : GetMessage('CT_BCT_TPL_MESS_BTN_DETAIL'));
										?>
								</a>
								<?
							}
							else
							{
								if ($arItem['CAN_BUY'])
								{
									?>
									<a id="<? echo $arItemIDs['BUY_LINK']; ?>" href="javascript:void(0)" rel="nofollow" class="btn btn-primary">
										<? if ($arParams['ADD_TO_BASKET_ACTION'] == 'BUY')
											{
												echo ('' != $arParams['MESS_BTN_BUY'] ? $arParams['MESS_BTN_BUY'] : GetMessage('CT_BCT_TPL_MESS_BTN_BUY'));
											}
											else
											{
												echo ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? $arParams['MESS_BTN_ADD_TO_BASKET'] : GetMessage('CT_BCT_TPL_MESS_BTN_ADD_TO_BASKET'));
											}
											?>
									</a>
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
						<svg class="catalog-top-banner-price-right-block" preserveAspectRatio="none" width="15" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 100">
							<polygon fill-rule="evenodd" points="0 0 15 50 0 100"></polygon>
						</svg>
					</div>
				</div>
				<script type="application/ld+json">
					{
						"@context": "http://schema.org/",
						"@type": "Product",
						"name": "<?=$productTitle; ?>",
						"image": {
							"@type": "ImageObject",
							"caption": "<?=$productTitle; ?>",
							"contentUrl": "<?=$arItem['PREVIEW_PICTURE']['SRC']; ?>"
						},
						<?=$arItem['PREVIEW_TEXT'] != '' ? '"description": "'.$arItem['PREVIEW_TEXT'].'",' : "" ?>
						"offers": {
							"@type": "Offer",
							<?=$price['CURRENCY'] != '' ? '"priceCurrency": "'.$price['CURRENCY'].'",' : "" ?>
							<?=$price['PRICE'] != '' ? '"price": "'.$price['PRICE'].'"' : "" ?>
						}
					}
				</script>
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
				'PRODUCT_TYPE' => $arItem['PRODUCT']['TYPE'],
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
				'className' => 'catalog-top-banner-controls'
			),
			'left' => array(
				'id' => $strContID.'_left_arr',
				'className' => 'catalog-top-banner-arrow-left'
			),
			'right' => array(
				'id' => $strContID.'_right_arr',
				'className' => 'catalog-top-banner-arrow-right'
			),
			'items' => $arRowIDs,
			'rotate' => (0 < $arParams['ROTATE_TIMER']),
			'rotateTimer' => $arParams['ROTATE_TIMER']
		);
		if ('Y' == $arParams['SHOW_PAGINATION'])
		{
			$arJSParams['pagination'] = array(
				'id' => $strContID.'_pagination',
				'className' => 'catalog-top-banner-pagination'
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