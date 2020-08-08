<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $item
 * @var array $actualItem
 * @var array $minOffer
 * @var array $itemIds
 * @var array $price
 * @var array $measureRatio
 * @var bool $haveOffers
 * @var bool $showSubscribe
 * @var array $morePhoto
 * @var bool $showSlider
 * @var bool $itemHasDetailUrl
 * @var string $imgTitle
 * @var string $productTitle
 * @var string $buttonSizeClass
 * @var CatalogSectionComponent $component
 */

if ($haveOffers)
{
	$showDisplayProps = !empty($item['DISPLAY_PROPERTIES']);
	$showProductProps = $arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $item['OFFERS_PROPS_DISPLAY'];
	$showPropsBlock = $showDisplayProps || $showProductProps;
	$showSkuBlock = $arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && !empty($item['OFFERS_PROP']);
}
else
{
	$showDisplayProps = !empty($item['DISPLAY_PROPERTIES']);
	$showProductProps = $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !empty($item['PRODUCT_PROPERTIES']);
	$showPropsBlock = $showDisplayProps || $showProductProps;
	$showSkuBlock = false;
}
?>

<div class="product-item">
	<div class="mb-3">
		<h3 class="product-item-title">
			<? if ($itemHasDetailUrl): ?>
			<a href="<?=$item['DETAIL_PAGE_URL']?>" title="<?=$productTitle?>">
			<? endif; ?>
			<?=$productTitle?>
			<? if ($itemHasDetailUrl): ?>
			</a>
			<? endif; ?>
		</h3>
	</div>
	<div class="row justify-content-between">
		<div class="col-md-3">
			<? if ($itemHasDetailUrl): ?>
			<a class="product-item-image-wrapper" href="<?=$item['DETAIL_PAGE_URL']?>" title="<?=$imgTitle?>"
					data-entity="image-wrapper">
			<? else: ?>
			<span class="product-item-image-wrapper" data-entity="image-wrapper">
			<? endif; ?>
				<span class="product-item-image-slider-slide-container slide" id="<?=$itemIds['PICT_SLIDER']?>" <?=($showSlider ? '' : 'style="display: none;"')?> data-slider-interval="<?=$arParams['SLIDER_INTERVAL']?>" data-slider-wrap="true">
					<?
					if ($showSlider)
					{
						foreach ($morePhoto as $key => $photo)
						{
							?>
							<span class="product-item-image-slide item <?=($key == 0 ? 'active' : '')?>" style="background-image: url('<?=$photo['SRC']?>');"></span>
							<?
						}
					}
					?>
				</span>
				<span class="product-item-image-original" id="<?=$itemIds['PICT']?>" style="background-image: url('<?=$item['PREVIEW_PICTURE']['SRC']?>'); <?=($showSlider ? 'display: none;' : '')?>"></span>
				<?
				if ($item['SECOND_PICT'])
				{
					$bgImage = !empty($item['PREVIEW_PICTURE_SECOND']) ? $item['PREVIEW_PICTURE_SECOND']['SRC'] : $item['PREVIEW_PICTURE']['SRC'];
					?>
						<span class="product-item-image-alternative" id="<?=$itemIds['SECOND_PICT']?>" style="background-image: url('<?=$bgImage?>'); <?=($showSlider ? 'display: none;' : '')?>"></span>
					<?
				}

				if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y')
				{
					?>
						<div class="product-item-label-ring <?=$discountPositionClass?>" id="<?=$itemIds['DSC_PERC']?>" <?=($price['PERCENT'] > 0 ? '' : 'style="display: none;"')?>>
							<span><?=-$price['PERCENT']?>%</span>
						</div>
					<?
				}

				if ($item['LABEL'])
				{
					?>
						<div class="product-item-label-text <?=$labelPositionClass?>" id="<?=$itemIds['STICKER_ID']?>">
							<?
							if (!empty($item['LABEL_ARRAY_VALUE']))
							{
								foreach ($item['LABEL_ARRAY_VALUE'] as $code => $value)
								{
									?>
									<div<?=(!isset($item['LABEL_PROP_MOBILE'][$code]) ? ' class="d-none d-sm-block"' : '')?>>
										<span title="<?=$value?>"><?=$value?></span>
									</div>
									<?
								}
							}
							?>
						</div>
					<?
				}
				?>
				<div class="product-item-image-slider-control-container" id="<?=$itemIds['PICT_SLIDER']?>_indicator" <?=($showSlider ? '' : 'style="display: none;"')?>>
					<?
					if ($showSlider)
					{
						foreach ($morePhoto as $key => $photo)
						{
							?>
								<div class="product-item-image-slider-control<?=($key == 0 ? ' active' : '')?>" data-go-to="<?=$key?>"></div>
							<?
						}
					}
					?>
				</div>
				<?
				if ($arParams['SLIDER_PROGRESS'] === 'Y')
				{
					?>
					<span class="product-item-image-slider-progress-bar-container">
						<span class="product-item-image-slider-progress-bar" id="<?=$itemIds['PICT_SLIDER']?>_progress_bar" style="width: 0;"></span>
					</span>
					<?
				}
				?>
			<? if ($itemHasDetailUrl): ?>
			</a>
			<? else: ?>
			</span>
			<? endif; ?>
		</div>
		<?
		if (!$haveOffers)
		{
			if ($showPropsBlock)
			{
				?>
				<div class="col col-sm-6 col-md-6 col-lg-7">
					<?
					if ($showDisplayProps)
					{
						?>
						<div class="product-item-info-container" data-entity="props-block">
							<dl class="product-item-properties">
								<?
								foreach ($item['DISPLAY_PROPERTIES'] as $code => $displayProperty)
								{
									?>
									<dt<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' class="d-none d-sm-block"' : '')?>>
										<?=$displayProperty['NAME']?>
									</dt>
									<dd<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' class="d-none d-sm-block"' : '')?>>
										<?=(is_array($displayProperty['DISPLAY_VALUE'])
											? implode(' / ', $displayProperty['DISPLAY_VALUE'])
											: $displayProperty['DISPLAY_VALUE'])?>
									</dd>
									<?
								}
								?>
							</dl>
						</div>
						<?
					}

					if ($showProductProps)
					{
						?>
						<div id="<?=$itemIds['BASKET_PROP_DIV']?>" style="display: none;">
							<?
							if (!empty($item['PRODUCT_PROPERTIES_FILL']))
							{
								foreach ($item['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo)
								{
									?>
									<input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]"
										value="<?=htmlspecialcharsbx($propInfo['ID'])?>">
									<?
									unset($item['PRODUCT_PROPERTIES'][$propID]);
								}
							}

							if (!empty($item['PRODUCT_PROPERTIES']))
							{
								?>
								<table>
									<?
									foreach ($item['PRODUCT_PROPERTIES'] as $propID => $propInfo)
									{
										?>
										<tr>
											<td><?=$item['PROPERTIES'][$propID]['NAME']?></td>
											<td>
												<?
												if (
													$item['PROPERTIES'][$propID]['PROPERTY_TYPE'] === 'L'
													&& $item['PROPERTIES'][$propID]['LIST_TYPE'] === 'C'
												)
												{
													foreach ($propInfo['VALUES'] as $valueID => $value)
													{
														?>
														<label>
															<? $checked = $valueID === $propInfo['SELECTED'] ? 'checked' : ''; ?>
															<input type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]"
																value="<?=$valueID?>" <?=$checked?>>
															<?=$value?>
														</label>
														<br />
														<?
													}
												}
												else
												{
													?>
													<select name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]">
														<?
														foreach ($propInfo['VALUES'] as $valueID => $value)
														{
															$selected = $valueID === $propInfo['SELECTED'] ? 'selected' : '';
															?>
															<option value="<?=$valueID?>" <?=$selected?>>
																<?=$value?>
															</option>
															<?
														}
														?>
													</select>
													<?
												}
												?>
											</td>
										</tr>
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
					?>
				</div>
				<?
			}
		}
		else
		{
			if ($showPropsBlock)
			{
				$gridPropsBlockClass = $showSkuBlock ? 'col-lg col-md-9' : 'col';

				?>
				<div class="<?=$gridPropsBlockClass; ?> product-item-info-container" data-entity="props-block">
					<dl class="product-item-properties">
						<?
						if ($showDisplayProps)
						{
							foreach ($item['DISPLAY_PROPERTIES'] as $code => $displayProperty)
							{
								?>
								<dt class="text-muted<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' d-none d-sm-block' : '')?>">
									<?=$displayProperty['NAME']?>
								</dt>
								<dd class="text-dark<?=(!isset($item['PROPERTY_CODE_MOBILE'][$code]) ? ' d-none d-sm-block' : '')?>">
									<?=(is_array($displayProperty['DISPLAY_VALUE'])
										? implode(' / ', $displayProperty['DISPLAY_VALUE'])
										: $displayProperty['DISPLAY_VALUE'])?>
								</dd>
								<?
							}
						}

						if ($showProductProps)
						{
							?>
							<span id="<?=$itemIds['DISPLAY_PROP_DIV']?>" style="display: none;"></span>
							<?
						}
						?>
					</dl>
				</div>
				<?
			}

			if ($showSkuBlock)
			{

				$gridSkuBlockClass = $showPropsBlock ? 'col-7 col-sm-8 col-lg-3' : 'col';
				?>
				<div class="<?=$gridSkuBlockClass; ?>">
					<div id="<?=$itemIds['PROP_DIV']?>">
						<?
						foreach ($arParams['SKU_PROPS'] as $skuProperty)
						{
							$propertyId = $skuProperty['ID'];
							$skuProperty['NAME'] = htmlspecialcharsbx($skuProperty['NAME']);
							if (!isset($item['SKU_TREE_VALUES'][$propertyId]))
								continue;
							?>
							<div class="product-item-info-container" data-entity="sku-block">
								<div class="product-item-scu-container" data-entity="sku-line-block">
									<div class="product-item-scu-block-title text-muted"><?=$skuProperty['NAME']?></div>
									<div class="product-item-scu-block">
										<div class="product-item-scu-list">
											<ul class="product-item-scu-item-list">
												<?
												foreach ($skuProperty['VALUES'] as $value)
												{
													if (!isset($item['SKU_TREE_VALUES'][$propertyId][$value['ID']]))
														continue;

													$value['NAME'] = htmlspecialcharsbx($value['NAME']);

													if ($skuProperty['SHOW_MODE'] === 'PICT')
													{
														?>
														<li class="product-item-scu-item-color-container" title="<?=$value['NAME']?>" data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>">
															<div class="product-item-scu-item-color-block">
																<div class="product-item-scu-item-color" title="<?=$value['NAME']?>" style="background-image: url('<?=$value['PICT']['SRC']?>');"></div>
															</div>
														</li>
														<?
													}
													else
													{
														?>
														<li class="product-item-scu-item-text-container" title="<?=$value['NAME']?>"
															data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>">
															<div class="product-item-scu-item-text-block">
																<div class="product-item-scu-item-text"><?=$value['NAME']?></div>
															</div>
														</li>
														<?
													}
												}
												?>
											</ul>
											<div style="clear: both;"></div>
										</div>
									</div>
								</div>
							</div>
							<?
						}
						?>
					</div>
					<?
					foreach ($arParams['SKU_PROPS'] as $skuProperty)
					{
						if (!isset($item['OFFERS_PROP'][$skuProperty['CODE']]))
							continue;

						$skuProps[] = array(
							'ID' => $skuProperty['ID'],
							'SHOW_MODE' => $skuProperty['SHOW_MODE'],
							'VALUES' => $skuProperty['VALUES'],
							'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
						);
					}

					unset($skuProperty, $value);

					if ($item['OFFERS_PROPS_DISPLAY'])
					{
						foreach ($item['JS_OFFERS'] as $keyOffer => $jsOffer)
						{
							$strProps = '';

							if (!empty($jsOffer['DISPLAY_PROPERTIES']))
							{
								foreach ($jsOffer['DISPLAY_PROPERTIES'] as $displayProperty)
								{
									$strProps .= '<dt>'.$displayProperty['NAME'].'</dt><dd>'
										.(is_array($displayProperty['VALUE'])
											? implode(' / ', $displayProperty['VALUE'])
											: $displayProperty['VALUE'])
										.'</dd>';
								}
							}
							$item['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;
						}
						unset($jsOffer, $strProps);
					}
					?>
				</div>
				<?
			}
		}
		?>
		<?

				$gridLastBlockClass = ($showPropsBlock && $showSkuBlock) ? 'col-5 col-sm-4 col-lg-auto text-center' : 'col-auto';
		?>
		<div class=" <?=$gridLastBlockClass; ?>">
			<?
			foreach ($arParams['PRODUCT_BLOCKS_ORDER'] as $blockName)
			{
				switch ($blockName)
				{
					case 'price': ?>
						<div class="product-item-info-container product-item-price-container" data-entity="price-block">
							<span class="product-item-price-current" id="<?=$itemIds['PRICE']?>">
								<?
								if (!empty($price))
								{
									if ($arParams['PRODUCT_DISPLAY_MODE'] === 'N' && $haveOffers)
									{
										echo Loc::getMessage(
											'CT_BCI_TPL_MESS_PRICE_SIMPLE_MODE',
											array(
												'#PRICE#' => $price['PRINT_RATIO_PRICE'],
												'#VALUE#' => $measureRatio,
												'#UNIT#' => $minOffer['ITEM_MEASURE']['TITLE']
											)
										);
									}
									else
									{
										echo $price['PRINT_RATIO_PRICE'];
									}
								}
								?>
							</span>
							<?
							if ($arParams['SHOW_OLD_PRICE'] === 'Y')
							{
								?>
								<br><span class="product-item-price-old" id="<?=$itemIds['PRICE_OLD']?>" <?=($price['RATIO_PRICE'] >= $price['RATIO_BASE_PRICE'] ? 'style="display: none;"' : '')?>>
									<?=$price['PRINT_RATIO_BASE_PRICE']?>
								</span>&nbsp;
								<?
							}
							?>
						</div>
						<?
						break;

					case 'quantityLimit':
						if ($arParams['SHOW_MAX_QUANTITY'] !== 'N')
						{
							if ($haveOffers)
							{
								if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
								{
									?>
									<div class="product-item-info-container product-item-hidden"
										id="<?=$itemIds['QUANTITY_LIMIT']?>"
										style="display: none;"
										data-entity="quantity-limit-block">
										<div class="product-item-info-container-title text-muted">
											<?=$arParams['MESS_SHOW_MAX_QUANTITY']?>:
											<span class="product-item-quantity text-dark" data-entity="quantity-limit-value"></span>
										</div>
									</div>
									<?
								}
							}
							else
							{
								if (
									$measureRatio
									&& (float)$actualItem['CATALOG_QUANTITY'] > 0
									&& $actualItem['CATALOG_QUANTITY_TRACE'] === 'Y'
									&& $actualItem['CATALOG_CAN_BUY_ZERO'] === 'N'
								)
								{
									?>
									<div class="product-item-info-container product-item-hidden" id="<?=$itemIds['QUANTITY_LIMIT']?>">
										<div class="product-item-info-container-title text-muted">
											<?=$arParams['MESS_SHOW_MAX_QUANTITY']?>:
											<span class="product-item-quantity text-dark" data-entity="quantity-limit-value">
												<?
												if ($arParams['SHOW_MAX_QUANTITY'] === 'M')
												{
													if ((float)$actualItem['CATALOG_QUANTITY'] / $measureRatio >= $arParams['RELATIVE_QUANTITY_FACTOR'])
													{
														echo $arParams['MESS_RELATIVE_QUANTITY_MANY'];
													}
													else
													{
														echo $arParams['MESS_RELATIVE_QUANTITY_FEW'];
													}
												}
												else
												{
													echo $actualItem['CATALOG_QUANTITY'].' '.$actualItem['ITEM_MEASURE']['TITLE'];
												}
												?>
											</span>
										</div>
									</div>
									<?
								}
							}
						}

						break;

					case 'quantity':
						if (!$haveOffers)
						{
							if ($actualItem['CAN_BUY'] && $arParams['USE_PRODUCT_QUANTITY'])
							{
								?>
								<div class="product-item-info-container" data-entity="quantity-block">
									<div class="product-item-amount">
										<div class="product-item-amount-field-container">
											<span class="product-item-amount-field-btn-minus no-select" id="<?=$itemIds['QUANTITY_DOWN']?>"></span>
											<div class="product-item-amount-field-block">
												<input class="product-item-amount-field" id="<?=$itemIds['QUANTITY']?>" type="number" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value="<?=$measureRatio?>">
												<div class="product-item-amount-description-container">
													<span id="<?=$itemIds['QUANTITY_MEASURE']?>"><?=$actualItem['ITEM_MEASURE']['TITLE']?></span>
													<span id="<?=$itemIds['PRICE_TOTAL']?>"></span>
												</div>
											</div>
											<span class="product-item-amount-field-btn-plus no-select" id="<?=$itemIds['QUANTITY_UP']?>"></span>
										</div>
									</div>
								</div>
								<?
							}
						}
						elseif ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
						{
							if ($arParams['USE_PRODUCT_QUANTITY'])
							{
								?>
								<div class="product-item-info-container" data-entity="quantity-block">
									<div class="product-item-amount">
										<div class="product-item-amount-field-container">
											<span class="product-item-amount-field-btn-minus no-select" id="<?=$itemIds['QUANTITY_DOWN']?>"></span>
											<div class="product-item-amount-field-block">
												<input class="product-item-amount-field" id="<?=$itemIds['QUANTITY']?>" type="number" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value="<?=$measureRatio?>">
												<div class="product-item-amount-description-container">
													<span id="<?=$itemIds['QUANTITY_MEASURE']?>"></span>
													<span id="<?=$itemIds['PRICE_TOTAL']?>"></span>
												</div>
											</div>
											<span class="product-item-amount-field-btn-plus no-select" id="<?=$itemIds['QUANTITY_UP']?>"></span>
										</div>
									</div>
								</div>
								<?
							}
						}

						break;

					case 'buttons':
						?>
						<div class="product-item-info-container" data-entity="buttons-block">
							<?
							if (!$haveOffers)
							{
								if ($actualItem['CAN_BUY'])
								{
									?>
									<div class="product-item-button-container" id="<?=$itemIds['BASKET_ACTIONS']?>">
										<button class="btn btn-primary <?=$buttonSizeClass?>"
											id="<?=$itemIds['BUY_LINK']?>"
											href="javascript:void(0)"
											rel="nofollow">
											<?=($arParams['ADD_TO_BASKET_ACTION'] === 'BUY' ? $arParams['MESS_BTN_BUY'] : $arParams['MESS_BTN_ADD_TO_BASKET'])?>
										</button>
									</div>
									<?
								}
								else
								{
									?>
									<div class="product-item-button-container">
										<?
										if ($showSubscribe)
										{
											$APPLICATION->IncludeComponent(
												'bitrix:catalog.product.subscribe',
												'',
												array(
													'PRODUCT_ID' => $actualItem['ID'],
													'BUTTON_ID' => $itemIds['SUBSCRIBE_LINK'],
													'BUTTON_CLASS' => 'btn btn-primary '.$buttonSizeClass,
													'DEFAULT_DISPLAY' => true,
													'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
												),
												$component,
												array('HIDE_ICONS' => 'Y')
											);
										}
										?>
										<button class="btn btn-link <?=$buttonSizeClass?>"
											id="<?=$itemIds['NOT_AVAILABLE_MESS']?>"
											href="javascript:void(0)"
											rel="nofollow">
											<?=$arParams['MESS_NOT_AVAILABLE']?>
										</button>
									</div>
									<?
								}
							}
							else
							{
								if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
								{
									?>
									<div class="product-item-button-container">
										<?
										if ($showSubscribe)
										{
											$APPLICATION->IncludeComponent(
												'bitrix:catalog.product.subscribe',
												'',
												array(
													'PRODUCT_ID' => $item['ID'],
													'BUTTON_ID' => $itemIds['SUBSCRIBE_LINK'],
													'BUTTON_CLASS' => 'btn btn-primary '.$buttonSizeClass,
													'DEFAULT_DISPLAY' => !$actualItem['CAN_BUY'],
													'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
												),
												$component,
												array('HIDE_ICONS' => 'Y')
											);
										}
										?>
										<button class="btn btn-link <?=$buttonSizeClass?>"
											id="<?=$itemIds['NOT_AVAILABLE_MESS']?>" href="javascript:void(0)" rel="nofollow"
											<?=($actualItem['CAN_BUY'] ? 'style="display: none;"' : '')?>>
											<?=$arParams['MESS_NOT_AVAILABLE']?>
										</button>
										<div id="<?=$itemIds['BASKET_ACTIONS']?>" <?=($actualItem['CAN_BUY'] ? '' : 'style="display: none;"')?>>
											<a class="btn btn-primary <?=$buttonSizeClass?>" id="<?=$itemIds['BUY_LINK']?>"
												href="javascript:void(0)" rel="nofollow">
												<?=($arParams['ADD_TO_BASKET_ACTION'] === 'BUY' ? $arParams['MESS_BTN_BUY'] : $arParams['MESS_BTN_ADD_TO_BASKET'])?>
											</a>
										</div>
									</div>
									<?
								}
								else
								{
									?>
									<div class="product-item-button-container">
										<a class="btn btn-primary <?=$buttonSizeClass?>" href="<?=$item['DETAIL_PAGE_URL']?>">
											<?=$arParams['MESS_BTN_DETAIL']?>
										</a>
									</div>
									<?
								}
							}
							?>
						</div>
						<?
						break;

					case 'compare':
						if (
							$arParams['DISPLAY_COMPARE']
							&& (!$haveOffers || $arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
						)
						{
							?>
							<div class="product-item-compare-container">
								<div class="product-item-compare">
									<div class="checkbox">
										<label id="<?=$itemIds['COMPARE_LINK']?>">
											<input type="checkbox" data-entity="compare-checkbox">
											<span data-entity="compare-title"><?=$arParams['MESS_BTN_COMPARE']?></span>
										</label>
									</div>
								</div>
							</div>
							<?
						}

						break;
				}
			}
			?>
		</div>
	</div>
</div>