<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $areaId
 * @var array $itemIds
 * @var array $item
 * @var array $jsParams
 * @var array $messages
 */

if (isset($item))
{
	$actualItem = $item;

	$morePhoto = null;
	$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
	$measureRatio = $price['MIN_QUANTITY'];
	if (isset($actualItem['MORE_PHOTO']))
	{
		$morePhoto = $actualItem['MORE_PHOTO'];
	}

	//$showSlider = is_array($morePhoto) && count($morePhoto) > 1;
	$photosExist = is_array($morePhoto);
	$activePhoto = 0;
	if ($photosExist)
	{
		$activePhoto = (isset($actualItem['MORE_PHOTO_SELECTED']) ? (int)$actualItem['MORE_PHOTO_SELECTED'] : 0);
		if ($activePhoto < 0 || $activePhoto >= count($morePhoto))
		{
			$activePhoto = 0;
		}
	}
	?>
	<div class="catalog-section-item" id="<?=$areaId?>" data-entity="item">
		<?//region COVER
		$percent = '';
		if ($photosExist)
		{
			$firstPhoto = $morePhoto[$activePhoto];
			$percent = 'padding-top: '.(($firstPhoto['HEIGHT']/$firstPhoto['WIDTH'])*100).'%;';
		}
		?>
		<div class="catalog-section-item-slider-container">
			<span class="catalog-section-item-slider-close" data-entity="close-popup"></span>
			<div class="catalog-section-item-slider-block" data-entity="images-slider-block" style="<?=$percent; ?>">
				<?
				//region LABEL
				if (($item['LABEL']) && (!empty($item['LABEL_ARRAY_VALUE'])))
				{
					?><div class="catalog-section-item-label-container"><?php
					foreach ($item['LABEL_ARRAY_VALUE'] as $code => $value)
					{
						?><span class="catalog-section-item-label-text"><?=$value?></span><?php
					}
					?></div><?php
				}
				//endregion
				?>
				<div class="catalog-section-item-slider-images-container" data-entity="images-container" id="<?=$itemIds['PICT_SLIDER']?>">
					<?
					if ($photosExist)
					{
						foreach ($morePhoto as $key => $photo)
						{
						?>
							<a href="<?=$item["DETAIL_PAGE_URL"]; ?>"
								class="catalog-section-item-slider-image<?= ($key == $activePhoto ? ' active' : '') ?>"
								data-entity="image"
								data-id="<?= $photo['ID'] ?>">
								<img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>" title="<?= $title ?>">
							</a>
							<div class="d-none d-sm-block catalog-section-item-slider-image-overlay" style="background-image: url(<?= $photo['SRC'] ?>);"></div>
							<?
						}
					}
					?>
				</div>
				<? //region SLIDER PAGER ?>
				<a href="<?=$item["DETAIL_PAGE_URL"]; ?>" class="catalog-section-item-slider-images-slider-pager d-none d-sm-flex" id="<?=$itemIds['PICT_SLIDER']?>_pager">
					<?
					if ($photosExist)
					{
						foreach ($morePhoto as $key => $photo)
						{
						?><div class="catalog-section-item-slider-images-slider-pager-item" data-entity="slider-control" data-value="<?=$photo['ID']?>" data-go-to="<?=$key; ?>"></div><?
						}
					}
					?>
				</a>
				<? //endregion ?>
			</div>
			<? //region SLIDER CONTROLS ?>
			<div class="catalog-section-item-slider-controls-block" id="<?=$itemIds['PICT_SLIDER']?>_indicator" <?=($photosExist && count($morePhoto) > 1 ? '' : 'style="display: none;"')?>>
				<?
				if ($photosExist)
				{
					foreach ($morePhoto as $key => $photo)
					{
						?>
						<div class="catalog-section-item-slider-controls-image<?=($key == $activePhoto ? ' active' : '')?>" data-entity="slider-control" data-value="<?=$photo['ID']?>" data-go-to="<?=$key; ?>">
							<div class="catalog-section-item-slider-controls-dot" data-entity="slider-control-dot" data-go-to="<?=$key; ?>"></div>
						</div>
						<?
					}
				}
				?>
			</div>
			<? //endregion ?>
		</div>
		<?
		//endregion
		?>
		<div class="catalog-section-item-description">
			<?php
			//region NAME
			?>
			<h3 class="catalog-section-item-name">
				<a class="catalog-section-item-name-link" href="<?=$item["DETAIL_PAGE_URL"]?>"><?=$item["NAME"]?></a>
			</h3>
			<?php
			//endregion
			?>
			<div class="catalog-section-item-offers-container d-flex justify-content-between align-items-center">
				<div>
					<?php
					//region PRICE
					if ($arParams['SHOW_OLD_PRICE'] === 'Y' && $price['RATIO_PRICE'] < $price['RATIO_BASE_PRICE'])
					{
						?>
						<div class="catalog-section-item-price-discount-container d-flex justify-content-between align-items-center">
							<span class="catalog-section-item-price-discount"><?=$price['PRINT_RATIO_BASE_PRICE']?></span>
							<span class="catalog-section-item-price-discount-diff"><?=$price['PRINT_RATIO_DISCOUNT']?></span>
						</div>
						<?php
					}
					?><div class="catalog-section-item-price" id="<?=$itemIds['PRICE_TWIN']; ?>"><?php
						if (!empty($price))
						{
							echo $price['PRINT_RATIO_PRICE'];
						}
					?></div><?php
					//endregion
					?>
				</div>
				<div>
					<?php
					//region BUTTONS
					if ($actualItem['CAN_BUY'])
					{
						?>
						<button class="catalog-section-item-buy-btn btn btn-primary btn-md rounded-pill" id="<?=$itemIds['PREBUY_OPEN_BTN']?>">
							<?=($arParams['ADD_TO_BASKET_ACTION'] === 'BUY'
								? $arParams['MESS_BTN_BUY']
								: $arParams['MESS_BTN_ADD_TO_BASKET']
							)?>
						</button>
						<?php
					}
					else
					{
						?>
						<button class="catalog-section-item-buy-btn btn btn-primary btn-md rounded-pill disabled" id="<?=$itemIds['NOT_AVAILABLE_MESS']?>" disabled>
							<?=$arParams['MESS_NOT_AVAILABLE']?>
						</button>
						<?php
					}
					//endregion
					?>
				</div>
			</div>

			<?//region DETAIL POPUP?>
				<div class="catalog-section-item-detail-wrapper closed" data-attr-simple id="<?=$itemIds['PREBUY']?>" >
					<div class="catalog-section-item-detail-cover" id="<?=$itemIds['PREBUY_OVERLAY']?>"></div>
					<div class="catalog-section-item-detail-container" id="<?=$itemIds['PREBUY_CONTAINER']?>">
						<div class="catalog-section-item-detail-header d-flex justify-content-between align-items-center">
							<div class="catalog-section-item-detail-header-separate"></div>
							<div class="catalog-section-item-detail-swipe-btn-container" id="<?=$itemIds['PREBUY_SWIPE_BTN']?>">
								<div class="catalog-section-item-detail-swipe-btn"></div>
							</div>
							<div class="catalog-section-item-detail-close-btn-container">
								<span class="catalog-section-item-detail-close-btn" id="<?=$itemIds['PREBUY_CLOSE_BTN']?>">
									<span class="catalog-section-item-detail-close-btn-text"><?=$messages['CLOSE_BTN']; ?></span>
								</span>
							</div>
						</div>

						<div class="catalog-section-item-detail">
							<section class="catalog-section-item-detail-props-container">
								<div class="d-flex align-items-center justify-content-start" style="margin-bottom: 19px;">
									<?
									if (isset($item['PREVIEW_PICTURE']['SRC']))
									{
										?>
										<div style="padding-right: 15px;">
											<img src="<?=$item['PREVIEW_PICTURE']['SRC']?>"
												id="<?=$itemIds['PREBUY_PICT']; ?>"
												alt="<?=$item["NAME"]?>"
												class="catalog-section-item-detail-preview-image"
											/>
										</div>
										<?
									}
									?>
									<div class="catalog-section-item-detail-title" id="<?=$itemIds['PREBUY_NAME']?>"><?=$item["NAME"]?></div>
								</div><?
								if (!empty($item['DISPLAY_PROPERTIES']) || ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !empty($item['PRODUCT_PROPERTIES'])))
								{
								?><div class="catalog-section-item-detail-props-container-inner">
									<?
									//region PROPS
									if (!empty($item['DISPLAY_PROPERTIES']))
									{
										?>
										<div class="catalog-section-item-info-container" data-entity="props-block">
											<div class="catalog-section-item-properties">
												<?
												foreach ($item['DISPLAY_PROPERTIES'] as $code => $displayProperty)
												{
													?>
													<div class="catalog-section-item-propertie-item">
														<span class="text-muted"><?=$displayProperty['NAME']?>:</span>
														<span class="text-dark">
															<?=(is_array($displayProperty['DISPLAY_VALUE'])
																? implode(' / ', $displayProperty['DISPLAY_VALUE'])
																: $displayProperty['DISPLAY_VALUE'])?>
														</span>
													</div>
													<?
												}
												?>
											</div>
										</div>
										<?
									}

									if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !empty($item['PRODUCT_PROPERTIES']))
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
									//endregion
									?>
								</div>
								<?
								}
								?>
							</section>
						</div>

						<div class="catalog-section-item-detail-offers">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<?php
									//region PRICE
									if ($arParams['SHOW_OLD_PRICE'] === 'Y' && $price['RATIO_PRICE'] < $price['RATIO_BASE_PRICE'])
									{
										?>
										<div class="catalog-section-item-price-discount-container d-flex justify-content-between align-items-center">
											<span class="catalog-section-item-price-discount" id="<?=$itemIds['PRICE_OLD']; ?>"><?=$price['PRINT_RATIO_BASE_PRICE']?></span>
											<span class="catalog-section-item-price-discount-diff"><?=$price['PRINT_RATIO_DISCOUNT']?></span>
										</div>
										<?php
									}

									?>
									<div class="catalog-section-item-price" id="<?=$itemIds['PRICE']; ?>">
										<?php
										if (!empty($price))
										{
											echo $price['PRINT_RATIO_PRICE'];
										}
										?>
									</div>
									<?php
									//endregion
									?>
								</div>
								<div>
									<?
									//region QUANTITY
									if ($actualItem['CAN_BUY'] && $arParams['USE_PRODUCT_QUANTITY'])
									{
										?>
										<div class="catalog-section-item-quantity-container" data-entity="quantity-block">
											<div class="catalog-section-item-quantity-field-container">
												<?//.product-item-detail-quantity-btn-disabled ?>
												<div class="catalog-section-item-quantity-btn-minus no-select" id="<?=$itemIds['QUANTITY_DOWN']?>"></div>
												<div class="catalog-section-item-quantity-field-block">
													<input class="catalog-section-item-quantity-field" id="<?=$itemIds['QUANTITY']?>" type="number" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value="<?=$measureRatio?>">
													<div class="catalog-section-item-quantity-field" id="<?=$itemIds['QUANTITY_COUNTER']?>" contentEditable="true" inputmode="numeric" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value=""><?=$measureRatio?></div>
												</div>
												<div class="catalog-section-item-quantity-btn-plus no-select" id="<?=$itemIds['QUANTITY_UP']?>"></div>
											</div>
											<span class="catalog-section-item-quantity-description" id="<?=$itemIds['QUANTITY_MEASURE_CONTAINER']?>">
												<span class="catalog-item-quantity-description-text"  id="<?=$itemIds['QUANTITY_MEASURE']?>"><?=$actualItem['ITEM_MEASURE']['TITLE']?></span>
												<span class="catalog-item-quantity-description-price" id="<?=$itemIds['PRICE_TOTAL']?>"></span>
											</span>
										</div>
										<?
									}
									//endregion
									?>
								</div>
							</div>
							<div class="d-flex mt-3 flex-column justify-content-center">
								<?
								//region BUTTONS
								if ($actualItem['CAN_BUY'])
								{
									?>
									<div id="<?=$itemIds['BASKET_ACTIONS']?>" class="mb-2" <?=($actualItem['CAN_BUY'] ? '' : 'style="display: none;"')?>>
										<button class="catalog-section-item-popup-buy-btn btn btn-primary btn-md rounded-pill" id="<?=$itemIds['BUY_LINK']?>"
												href="javascript:void(0)" rel="nofollow">
											<?=$arParams['BTN_MESSAGE_CREATE_ORDER']; ?>
										</button>
										<button class="catalog-section-item-popup-buy-btn btn border btn-md rounded-pill mt-2" id="<?=$itemIds['ADD_BASKET_LINK']?>">
											<?=$arParams['BTN_MESSAGE_CONTINUE_SHOPPING']; ?>
										</button>
									</div>
									<?php
								}
								else
								{
									?>
									<button class="catalog-section-item-popup-buy-btn btn btn-primary btn-md rounded-pill"
											id="<?=$itemIds['NOT_AVAILABLE_MESS']?>">
										<?=$arParams['MESS_NOT_AVAILABLE']?>
									</button>
									<?php
								}
								//endregion
								?>
							</div>
						</div>

					</div>
				</div>
			<?//endregion?>
		</div>
	</div>
	<?
	$arrayData = array(
		"@context" => "https://schema.org/",
		"@type" => "Product",
	);

	$arrayData["name"] = "SIMPLE ".$item["NAME"];

	//region PREVIEW_TEXT
	if (isset($item['PREVIEW_TEXT']) && $item['PREVIEW_TEXT'] !== "")
	{
		$arrayData["description"] = $item['PREVIEW_TEXT'];
	}

	//endregion

	//region category
	//todo: need to add category
	if(isset($item['CATEGORY_PATH']) && false)
	{
		$arrayData['category'] = $item['CATEGORY_PATH'];
	}

	//endregion

	//region link
	if (isset($item['DETAIL_PAGE_URL']) && $item['DETAIL_PAGE_URL'] !== "")
	{
		$arrayData['link'] = $item['DETAIL_PAGE_URL'];
	}

	//endregion

	//region MORE_PHOTO
	$arrayData['image'] = [];
	if ($photosExist)
	{
		foreach ($morePhoto as $key => $photo)
		{
			$arrayData['image'][] = $photo['SRC'];
		}
	}
	else if(isset($item['PREVIEW_PICTURE']))
	{
		$arrayData['image'][] = $item['PREVIEW_PICTURE']['SRC'];
	}
	else if(isset($item['DETAIL_PICTURE']))
	{
		$arrayData['image'][] = $item['DETAIL_PICTURE']['SRC'];
	}

	//endregion

	if (!empty($price))
	{
		$arrayDataOffers[] = array(
			"@type" => "Offer",
			"price" => $price['PRICE'],
			"priceCurrency" => $price['CURRENCY'],
			"availability" => ($actualItem['CAN_BUY'] ? 'InStock' : 'OutOfStock')
		);
		$arrayData["offers"] = $arrayDataOffers;
	}

	?><script data-type="text/html" type="application/ld+json"><?=json_encode($arrayData, JSON_UNESCAPED_UNICODE ), "";?></script><?php

	if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y')
	{
		if (!empty($item['PRODUCT_PROPERTIES']))
		{
			$jsParams['VISUAL']['BASKET_PROP_DIV'] = $itemIds['BASKET_PROP_DIV'];
		}

		$jsParams['BASKET']['EMPTY_PROPS'] = empty($item['PRODUCT_PROPERTIES']);
	}

	$jsParams['PRODUCT'] = $jsParams['PRODUCT'] +
		[
			'PICT' => $item['SECOND_PICT'] ? $item['PREVIEW_PICTURE_SECOND'] : $item['PREVIEW_PICTURE'],
			'CAN_BUY' => $item['CAN_BUY'],
			'CHECK_QUANTITY' => $item['CHECK_QUANTITY'],
			'MAX_QUANTITY' => $item['CATALOG_QUANTITY'],
			'STEP_QUANTITY' => $item['ITEM_MEASURE_RATIOS'][$item['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
			'QUANTITY_FLOAT' => is_float($item['ITEM_MEASURE_RATIOS'][$item['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
			'ITEM_PRICE_MODE' => $item['ITEM_PRICE_MODE'],
			'ITEM_PRICES' => $item['ITEM_PRICES'],
			'ITEM_PRICE_SELECTED' => $item['ITEM_PRICE_SELECTED'],
			'ITEM_QUANTITY_RANGES' => $item['ITEM_QUANTITY_RANGES'],
			'ITEM_QUANTITY_RANGE_SELECTED' => $item['ITEM_QUANTITY_RANGE_SELECTED'],
			'ITEM_MEASURE_RATIOS' => $item['ITEM_MEASURE_RATIOS'],
			'ITEM_MEASURE_RATIO_SELECTED' => $item['ITEM_MEASURE_RATIO_SELECTED'],
		];
}