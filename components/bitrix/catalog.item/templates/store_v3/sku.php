<?php

use Bitrix\Main\Localization\Loc;

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
	$skuProps = [];

	$actualItem = $item['OFFERS'][$item['OFFERS_SELECTED']] ?? reset($item['OFFERS']);
	$showedTitle = $item['NAME'];

	$name = !empty($item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
		? $item['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
		: $item['NAME'];
	$title = !empty($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
		? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
		: $item['NAME'];
	$alt = !empty($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
		? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
		: $item['NAME'];

	$firstPhoto = null;
	$morePhoto = null;
	if ($arParams['PRODUCT_DISPLAY_MODE'] === 'N')
	{
		$price = $item['ITEM_START_PRICE'];
		$minOffer = $item['OFFERS'][$item['ITEM_START_PRICE_SELECTED']];
		$measureRatio = $minOffer['ITEM_MEASURE_RATIOS'][$minOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
		if (isset($item['MORE_PHOTO']))
		{
			$morePhoto = $item['MORE_PHOTO'];
		}
	}
	else
	{
		$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
		$measureRatio = $price['MIN_QUANTITY'];
		if (isset($actualItem['MORE_PHOTO']))
		{
			$morePhoto = $actualItem['MORE_PHOTO'];
		}
		if ($arParams['USE_OFFER_NAME'] === 'Y')
		{
			$showedTitle = $actualItem['NAME'];
		}
	}

	//$showSlider = is_array($morePhoto) && count($morePhoto) > 1;
	$photosExist = is_array($morePhoto);
	$activePhoto = 0;
	if ($photosExist && $arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
	{
		$activePhoto = (isset($actualItem['MORE_PHOTO_SELECTED']) ? (int)$actualItem['MORE_PHOTO_SELECTED'] : 0);
		if ($activePhoto < 0 || $activePhoto >= count($morePhoto))
		{
			$activePhoto = 0;
		}
	}
	?>
	<div class="catalog-section-item" id="<?=$areaId?>" data-entity="item">
		<?php //region COVER
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
				<!-- <span class="catalog-section-item-slider-left" data-entity="slider-control-left" style="display: none;"></span>-->
				<!-- <span class="catalog-section-item-slider-right" data-entity="slider-control-right" style="display: none;"></span>-->
				<?php
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
					<?php
					if ($photosExist)
					{
						foreach ($morePhoto as $key => $photo)
						{
							$xResizedImage = \CFile::ResizeImageGet(
								$photo['ID'],
								[
									'width' => 410,
									'height' => 410,
								],
								BX_RESIZE_IMAGE_PROPORTIONAL,
								true
							);

							$x2ResizedImage = \CFile::ResizeImageGet(
								$photo['ID'],
								[
									'width' => 820,
									'height' => 820,
								],
								BX_RESIZE_IMAGE_PROPORTIONAL,
								true
							);

							if (!$xResizedImage || !$x2ResizedImage)
							{
								$xResizedImage = [
									'src' => $photo['SRC'],
								];
								$x2ResizedImage = $xResizedImage;
							}

							$xResizedImage = \Bitrix\Iblock\Component\Tools::getImageSrc([
								'SRC' => $xResizedImage['src']
							]);
							$x2ResizedImage = \Bitrix\Iblock\Component\Tools::getImageSrc([
								'SRC' => $x2ResizedImage['src']
							]);

							$style = "background-image: url('{$xResizedImage}');";
							$style .= "background-image: -webkit-image-set(url('{$xResizedImage}') 1x, url('{$x2ResizedImage}') 2x);";
							$style .= "background-image: image-set(url('{$xResizedImage}') 1x, url('{$x2ResizedImage}') 2x);";
						?>
							<a href="<?=$item["DETAIL_PAGE_URL"]; ?>"
								class="catalog-section-item-slider-image<?= ($key == $activePhoto ? ' active' : '') ?>"
								data-entity="image"
								data-id="<?= $photo['ID'] ?>">
								<img
									src="<?= $xResizedImage ?>"
									srcset="<?= $xResizedImage ?> 1x, <?= $x2ResizedImage ?> 2x"
									alt="<?= $alt ?>"
									title="<?= $title ?>"
								>
							</a>
							<div class="d-none d-sm-block catalog-section-item-slider-image-overlay" style="<?= $style ?>"></div>
			<?php
						}
					}
					?>
				</div>
			<?php //region SLIDER PAGER ?>
				<a href="<?=$item["DETAIL_PAGE_URL"]; ?>" class="catalog-section-item-slider-images-slider-pager d-none d-sm-flex" id="<?=$itemIds['PICT_SLIDER']?>_pager">
					<?php
					if ($photosExist)
					{
						foreach ($morePhoto as $key => $photo)
						{
							?><div class="catalog-section-item-slider-images-slider-pager-item" data-entity="slider-control" data-value="<?=$photo['ID']?>" data-go-to="<?=$key; ?>"></div><?php
						}
					}
					?>
				</a>
			<?php //endregion ?>
			</div>
		<?php //region SLIDER CONTROLS ?>
			<div class="catalog-section-item-slider-controls-block" id="<?=$itemIds['PICT_SLIDER']?>_indicator" <?=($photosExist && count($morePhoto) > 1 ? '' : 'style="display: none;"')?>>
				<?php
				if ($photosExist)
				{
					foreach ($morePhoto as $key => $photo)
					{
						?>
						<div class="catalog-section-item-slider-controls-image<?=($key == $activePhoto ? ' active' : '')?>"
							data-entity="slider-control" data-value="<?=$photo['ID']?>" data-go-to="<?=$key; ?>">
							<div class="catalog-section-item-slider-controls-dot" data-entity="slider-control-dot" data-go-to="<?=$key; ?>"></div>
						</div>
						<?php
					}
				}
				?>
			</div>
		<?php
			//endregion
			?>
		</div>
	<?php

		//endregion
		?>
		<div class="catalog-section-item-description">
			<?php
			//region NAME
			?>
			<h3 class="catalog-section-item-name">
				<a id="<?=$itemIds['NAME']; ?>" class="catalog-section-item-name-link" href="<?=$item["DETAIL_PAGE_URL"]?>"><?=$showedTitle?></a>
			</h3>
			<?php
			//endregion
			?>
			<div class="catalog-section-item-offers-container d-flex justify-content-between align-items-center">
				<div>
					<?php
					//region PRICE
					if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $arParams['SHOW_OLD_PRICE'] === 'Y')
					{
						$showblock = $price['RATIO_PRICE'] < $price['RATIO_BASE_PRICE'];
						?>
						<div class="catalog-section-item-price-discount-container d-flex justify-content-between align-items-center"
							id="<?=$itemIds['BLOCK_PRICE_OLD_TWIN']; ?>" style="display: <?=$showblock ? '' : 'none'; ?>;">
							<span class="catalog-section-item-price-discount" id="<?=$itemIds['PRICE_OLD_TWIN']; ?>"><?=($showblock ? $price['PRINT_RATIO_BASE_PRICE'] : '');?></span>
							<span class="catalog-section-item-price-discount-diff" id="<?=$itemIds['PRICE_DISCOUNT_TWIN']; ?>"><?=($showblock ? $price['PRINT_RATIO_DISCOUNT'] : ''); ?></span>
						</div>
						<?php
						unset($showblock);
					}
					?><div class="catalog-section-item-price" id="<?=$itemIds['PRICE_TWIN']; ?>"><?php
						if (!empty($price))
						{
							if (
								$arParams['PRODUCT_DISPLAY_MODE'] === 'N'
							)
							{
								$unit = $measureRatio !== 1
									? "{$measureRatio} {$minOffer['ITEM_MEASURE']['TITLE']}"
									: $minOffer['ITEM_MEASURE']['TITLE']
								;
								echo Loc::getMessage(
									'CT_BCI_TPL_MESS_PRICE_SIMPLE_MODE',
									[
										'#PRICE#' => $price['PRINT_RATIO_PRICE'],
										'#UNIT#' => $unit,
									]
								);
							}
							else
							{
								echo $price['PRINT_RATIO_PRICE'];
							}
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

			<?php //region DETAIL POPUP ?>
				<div class="catalog-section-item-detail-wrapper closed" data-attr-sku id="<?=$itemIds['PREBUY']?>" >
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
									<?php
									if (!empty($firstPhoto))
									{
										?>
										<div style="padding-right: 15px;">
											<a href="<?=$item["DETAIL_PAGE_URL"]; ?>"><img src="<?=$firstPhoto['SRC']?>"
												id="<?=$itemIds['PREBUY_PICT']; ?>"
												alt="<?=$showedTitle?>"
												class="catalog-section-item-detail-preview-image"
											/></a>
										</div>
										<?php
									}
									?>
									<div class="catalog-section-item-detail-title" id="<?=$itemIds['PREBUY_NAME']?>"><?=$showedTitle?></div>
								</div>
								<?php
								$showProductProps = !empty($item['DISPLAY_PROPERTIES']);
								$showOfferProps = $arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $item['OFFERS_PROPS_DISPLAY'];
								if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && !empty($item['OFFERS_PROP']) || $showProductProps || $showOfferProps)
								{
									?><div class="catalog-section-item-detail-props-container-inner"><?php
										//region PROPS
										$showProductProps = !empty($item['DISPLAY_PROPERTIES']);
										$showOfferProps = $arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $item['OFFERS_PROPS_DISPLAY'];

										if ($showProductProps || $showOfferProps)
										{
											?>
											<div class="catalog-section-item-info-container" data-entity="props-block">
												<div class="catalog-section-item-properties">
													<?php
													if ($showProductProps)
													{
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
															<?php
														}
													}

													if ($showOfferProps)
													{
														?>
														<span id="<?=$itemIds['DISPLAY_PROP_DIV']?>" style="display: none;"></span>
														<?php
													}
													?>
												</div>
											</div>
											<?php
										}
										//endregion

										//region SKU
										if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && !empty($item['OFFERS_PROP']))
										{
											?>
											<div class="catalog-section-item-info-container catalog-section-item-hidden" id="<?=$itemIds['PROP_DIV']?>">
												<?php
												foreach ($arParams['SKU_PROPS'] as $skuProperty)
												{
													$propertyId = $skuProperty['ID'];
													$skuProperty['NAME'] = htmlspecialcharsbx($skuProperty['NAME']);
													if (!isset($item['SKU_TREE_VALUES'][$propertyId]))
														continue;
													?>
													<div data-entity="sku-block">
														<div class="catalog-section-item-scu-container" data-entity="sku-line-block">
															<div class="catalog-section-item-scu-block-title text-muted"><?=$skuProperty['NAME']?></div>
															<div class="catalog-section-item-scu-block">
																<div class="catalog-section-item-scu-list">
																	<ul class="catalog-section-item-scu-item-list">
																		<?php
																		foreach ($skuProperty['VALUES'] as $value)
																		{
																			if (!isset($item['SKU_TREE_VALUES'][$propertyId][$value['ID']]))
																				continue;

																			$value['NAME'] = htmlspecialcharsbx($value['NAME']);

																			if ($skuProperty['SHOW_MODE'] === 'PICT')
																			{
																				?>
																				<li class="catalog-section-item-scu-item-color-container" title="<?=$value['NAME']?>" data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>">
																					<div class="catalog-section-item-scu-item-color-block">
																						<div class="catalog-section-item-scu-item-color" title="<?=$value['NAME']?>" style="background-image: url('<?=$value['PICT']['SRC']?>');"></div>
																					</div>
																				</li>
																				<?php
																			}
																			else
																			{
																				?>
																				<li class="catalog-section-item-scu-item-text-container" title="<?=$value['NAME']?>"
																					data-treevalue="<?=$propertyId?>_<?=$value['ID']?>" data-onevalue="<?=$value['ID']?>">
																					<div class="catalog-section-item-scu-item-text-block">
																						<div class="catalog-section-item-scu-item-text"><?=$value['NAME']?></div>
																					</div>
																				</li>
																				<?php
																			}
																		}
																		?>
																	</ul>
																</div>
															</div>
														</div>
													</div>
													<?php
												}
												?>
											</div>
											<?php
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
										}
										//endregion
									?></div><?php
								}
								?>
							</section>
						</div>

						<div class="catalog-section-item-detail-offers">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<?php
									//region PRICE
									if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && $arParams['SHOW_OLD_PRICE'] === 'Y')
									{
										$showblock = $price['RATIO_PRICE'] < $price['RATIO_BASE_PRICE'];
										?>
										<div class="catalog-section-item-price-discount-container d-flex justify-content-between align-items-center"
											id="<?=$itemIds['BLOCK_PRICE_OLD']; ?>" style="display: <?=$showblock ? '' : 'none'; ?>;">
											<span class="catalog-section-item-price-discount" id="<?=$itemIds['PRICE_OLD']; ?>"><?=($showblock ? $price['PRINT_RATIO_BASE_PRICE'] : '');?></span>
											<span class="catalog-section-item-price-discount-diff" id="<?=$itemIds['PRICE_DISCOUNT']; ?>"><?=($showblock ? $price['PRINT_RATIO_DISCOUNT'] : '');?></span>
										</div>
										<?php
										unset($showblock);
									}
									?>
									<div class="catalog-section-item-price" id="<?=$itemIds['PRICE']; ?>">
										<?php
										if (!empty($price))
										{
											if ($arParams['PRODUCT_DISPLAY_MODE'] === 'N')
											{
												$unit = $measureRatio !== 1
													? "{$measureRatio} {$minOffer['ITEM_MEASURE']['TITLE']}"
													: $minOffer['ITEM_MEASURE']['TITLE']
												;
												echo Loc::getMessage(
													'CT_BCI_TPL_MESS_PRICE_SIMPLE_MODE',
													[
														'#PRICE#' => $price['PRINT_RATIO_PRICE'],
														'#UNIT#' => $unit,
													]
												);
											}
											else
											{
												echo $price['PRINT_RATIO_PRICE'];
											}
										}
										?>
									</div>
									<?php
									//endregion
									?>
								</div>
								<div>
									<?php
									//region QUANTITY
									if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y')
									{
										if ($arParams['USE_PRODUCT_QUANTITY'])
										{
											?>
											<div class="catalog-section-item-quantity-container" data-entity="quantity-block">
												<div class="catalog-section-item-quantity-field-container">
													<span class="catalog-section-item-quantity-btn-minus no-select" id="<?=$itemIds['QUANTITY_DOWN']?>"></span>
													<div class="catalog-section-item-quantity-field-block">
														<input class="catalog-section-item-quantity-field" id="<?=$itemIds['QUANTITY']?>" type="number" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value="<?=$measureRatio?>">
														<div class="catalog-section-item-quantity-field" id="<?=$itemIds['QUANTITY_COUNTER']?>" contentEditable="true" inputmode="numeric" name="<?=$arParams['PRODUCT_QUANTITY_VARIABLE']?>" value=""><?=$measureRatio?></div>
													</div>
													<span class="catalog-section-item-quantity-btn-plus no-select" id="<?=$itemIds['QUANTITY_UP']?>"></span>
												</div>
												<span class="catalog-section-item-quantity-description" id="<?=$itemIds['QUANTITY_MEASURE_CONTAINER']?>">
													<span class="product-item-detail-quantity-description-text"  id="<?=$itemIds['QUANTITY_MEASURE']?>"><?=$actualItem['ITEM_MEASURE']['TITLE']?></span>
													<span class="catalog-item-quantity-description-price" id="<?=$itemIds['PRICE_TOTAL']?>"></span>
												</span>
											</div>
											<?php
										}
									}
									//endregion
									?>
								</div>
							</div>
							<div class="d-flex mt-3 flex-column justify-content-center">
								<?php
								//region BUTTONS
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
								<button class="catalog-section-item-popup-buy-btn btn btn-primary btn-md rounded-pill"
									id="<?=$itemIds['NOT_AVAILABLE_MESS']?>" <?=($actualItem['CAN_BUY'] ? 'style="display: none;"' : ''); ?>>
									<?=$arParams['MESS_NOT_AVAILABLE']?>
								</button>
								<?php
								//endregion
								?>
							</div>
						</div>

					</div>
				</div>
			<?php //endregion ?>
		</div>
	</div>
	<?php
	$arrayData = array(
		"@context" => "https://schema.org/",
		"@type" => "ProductGroup",
	);

	$arrayData["name"] = "SKU | ".$item["NAME"];

	$arrayData["productGroupID"] = $item["ID"];

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

	if (!empty($item['OFFERS_PROP']))
	{
		$arrayData['variesBy'] = [];
		foreach ($item['OFFERS_PROP'] as $key => $offersProp)
		{
			array_push($arrayData['variesBy'], $key);
		}
	}

	//region offers
	foreach ($item['OFFERS'] as $key => $itemOffer)
	{
		$arrayDataOfferItem = array(
			"@context" => "https://schema.org/",
			"@type" => "Product",
			"sku" => $item["ID"]."-".$itemOffer['ID'],
		);

		if (!empty($itemOffer["PROPERTIES"]["SIZES"]))
		{
			$arrayDataOfferItem["size"] = $itemOffer["PROPERTIES"]["SIZES"]["VALUE"];
		}

		if (!empty($itemOffer["PROPERTIES"]["COLOR"]))
		{
			$arrayDataOfferItem["color"] = $itemOffer["PROPERTIES"]["COLOR"]["VALUE"];
		}

		if (!empty($price))
		{
			$arrayOffersPrices = array(
				"@type" => "Offer",
				"price" => $price['PRICE'],
				"priceCurrency" => $price['CURRENCY'],
				"availability" => ($actualItem['CAN_BUY'] ? 'InStock' : 'OutOfStock')
			);

			$arrayDataOfferItem['offers'] = $arrayOffersPrices;

		}

		$arrayDataOffers[] = $arrayDataOfferItem;
	}

	$arrayData["hasVariant"] = $arrayDataOffers;
	//endregion

	?><script data-type="text/html" type="application/ld+json"><?=json_encode($arrayData, JSON_UNESCAPED_UNICODE ), "";?></script><?php

	if ($arParams['PRODUCT_DISPLAY_MODE'] === 'Y' && !empty($item['OFFERS_PROP']))
	{
		$jsParams['SHOW_SKU_PROPS'] = $item['OFFERS_PROPS_DISPLAY'];

		foreach ($item['JS_OFFERS'] as $index => $jsOffer)
		{
			foreach ($jsOffer['MORE_PHOTO'] as $morePhoto)
			{
				$xResizedImage = \CFile::ResizeImageGet(
					$morePhoto['ID'],
					[
						'width' => 410,
						'height' => 410,
					],
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);

				$x2ResizedImage = \CFile::ResizeImageGet(
					$morePhoto['ID'],
					[
						'width' => 820,
						'height' => 820,
					],
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);

				if (!$xResizedImage || !$x2ResizedImage)
				{
					$xResizedImage = [
						'src' => $morePhoto['SRC'],
						'width' => $morePhoto['WIDTH'],
						'height' => $morePhoto['HEIGHT'],
					];
					$x2ResizedImage = $xResizedImage;
				}

				$xResizedImage['src'] = \Bitrix\Iblock\Component\Tools::getImageSrc([
					'SRC' => $xResizedImage['src']
				]);
				$x2ResizedImage['src'] = \Bitrix\Iblock\Component\Tools::getImageSrc([
					'SRC' => $x2ResizedImage['src']
				]);

				$item['JS_OFFERS'][$index]['RESIZED_SLIDER']['X'][] = [
					'ID' => $morePhoto['ID'],
					'SRC' => $xResizedImage['src'],
					'WIDTH' => $xResizedImage['width'],
					'HEIGHT' => $xResizedImage['height'],
				];
				$item['JS_OFFERS'][$index]['RESIZED_SLIDER']['X2'][] = [
					'ID' => $morePhoto['ID'],
					'SRC' => $x2ResizedImage['src'],
					'WIDTH' => $x2ResizedImage['width'],
					'HEIGHT' => $x2ResizedImage['height'],
				];
			}
		}

		$jsParams['OFFERS'] = $item['JS_OFFERS'];
		$jsParams['OFFER_SELECTED'] = $item['OFFERS_SELECTED'];
		$jsParams['TREE_PROPS'] = $skuProps;

		$jsParams['VISUAL']['TREE_ID'] = $itemIds['PROP_DIV'];
		$jsParams['VISUAL']['TREE_ITEM_ID'] = $itemIds['PROP'];

		if ($item['OFFERS_PROPS_DISPLAY'])
		{
			$jsParams['VISUAL']['DISPLAY_PROP_DIV'] = $itemIds['DISPLAY_PROP_DIV'];
		}

		$jsParams['DEFAULT_PICTURE'] = [
			'PICTURE' => $item['PRODUCT_PREVIEW'],
			'PICTURE_SECOND' => $item['PRODUCT_PREVIEW_SECOND'],
		];

		$jsParams['BASKET']['SKU_PROPS'] = $item['OFFERS_PROP_CODES'];
	}
	else
	{
		$jsParams['SHOW_QUANTITY'] = false;

		$jsParams['DISPLAY_COMPARE'] = false;
		$jsParams['PRODUCT_DISPLAY_MODE'] = 'N';

		$jsParams['OFFERS'] = [];
		$jsParams['OFFER_SELECTED'] = 0;
		$jsParams['TREE_PROPS'] = [];
	}

	$templateData['ITEM']['OFFERS_SELECTED'] = $jsParams['OFFER_SELECTED'];
	$templateData['ITEM']['JS_OFFERS'] = $jsParams['OFFERS'];
}