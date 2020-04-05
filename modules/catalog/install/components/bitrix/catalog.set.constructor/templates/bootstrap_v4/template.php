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

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';

$templateData = array(
	'CURRENCIES' => CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)
);
$curJsId = $this->randString();
?>
<!---->
<div class="row mt-4">
	<div class="col">
		<div id="bx-set-const-<?=$curJsId?>" class="catalog-set-constructor 11 <?=$themeClass;?>">
			<h4 class="catalog-set-constructor-title mb-4"><?=GetMessage("CATALOG_SET_BUY_SET")?></h4>
			<div class="row">

				<div class="col-md-3">
					<div class="catalog-set-constructor-product-item-container mb-4">
						<?if ($arResult["ELEMENT"]["DETAIL_PICTURE"]["src"]):?>
							<img src="<?=$arResult["ELEMENT"]["DETAIL_PICTURE"]["src"]?>" class="catalog-set-constructor-product-item-image mb-2 float-none float-sm-left float-md-none mr-0 mr-sm-2 mr-md-0" alt="">
						<?else:?>
							<img src="<?=$this->GetFolder().'/images/no_foto.png'?>" class="catalog-set-constructor-product-item-image mb-2 float-none float-sm-left float-md-none mr-0 mr-sm-2 mr-md-0" alt="">
						<?endif?>

						<div class="catalog-set-constructor-product-info">
							<div class="catalog-set-constructor-product-name"><?=$arResult["ELEMENT"]["NAME"]?></div>
							<span class="catalog-set-constructor-product-new-price">
								<strong><?=$arResult["ELEMENT"]["PRICE_PRINT_DISCOUNT_VALUE"]?></strong>
								* <?=$arResult["ELEMENT"]["BASKET_QUANTITY"];?> <?=$arResult["ELEMENT"]["MEASURE"]["SYMBOL_RUS"];?>
							</span>
							<?if (!($arResult["ELEMENT"]["PRICE_VALUE"] == $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"])):?>
								<span class="catalog-set-constructor-product-old-price">
									<strong><?=$arResult["ELEMENT"]["PRICE_PRINT_VALUE"]?></strong>
								</span>
							<?endif?>
						</div>
					</div>
				</div>

				<div class="col-md-9">
					<div class="catalog-set-constructor-items-list">

						<table class="catalog-set-constructor-items-list-table">
							<tbody data-role="set-items">
							<?foreach($arResult["SET_ITEMS"]["DEFAULT"] as $key => $arItem):?>
								<tr
									data-id="<?=htmlspecialcharsbx($arItem["ID"])?>"
									data-img="<?=htmlspecialcharsbx($arItem["DETAIL_PICTURE"]["src"])?>"
									data-url="<?=htmlspecialcharsbx($arItem["DETAIL_PAGE_URL"])?>"
									data-name="<?=htmlspecialcharsbx($arItem["NAME"])?>"
									data-price="<?=htmlspecialcharsbx($arItem["PRICE_DISCOUNT_VALUE"])?>"
									data-print-price="<?=htmlspecialcharsbx($arItem["PRICE_PRINT_DISCOUNT_VALUE"])?>"
									data-old-price="<?=htmlspecialcharsbx($arItem["PRICE_VALUE"])?>"
									data-print-old-price="<?=htmlspecialcharsbx($arItem["PRICE_PRINT_VALUE"])?>"
									data-diff-price="<?=htmlspecialcharsbx($arItem["PRICE_DISCOUNT_DIFFERENCE_VALUE"])?>"
									data-measure="<?=htmlspecialcharsbx($arItem["MEASURE"]["SYMBOL_RUS"])?>"
									data-quantity="<?=htmlspecialcharsbx($arItem["BASKET_QUANTITY"])?>"
								>
									<td class="catalog-set-constructor-items-list-table-cell-img">
										<?if ($arItem["DETAIL_PICTURE"]["src"]):?>
											<img src="<?=$arItem["DETAIL_PICTURE"]["src"]?>" class="img-responsive" alt="">
										<?else:?>
											<img src="<?=$this->GetFolder().'/images/no_foto.png'?>" class="img-responsive" alt="">
										<?endif?>
									</td>
									<td class="catalog-set-constructor-items-list-table-cell-name">
										<a class="" target="_blank" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
									</td>
									<td class="catalog-set-constructor-items-list-table-cell-price">
										<span class="catalog-set-constructor-product-new-price">
											<?=$arItem["PRICE_PRINT_DISCOUNT_VALUE"]?> * <?=$arItem["BASKET_QUANTITY"];?> <?=$arItem["MEASURE"]["SYMBOL_RUS"];?>
										</span>
										<?if ($arItem["PRICE_VALUE"] != $arItem["PRICE_DISCOUNT_VALUE"]):?>
											<span class="catalog-set-constructor-product-old-price"><?=$arItem["PRICE_PRINT_VALUE"]?></span>
										<?endif?>
									</td>
									<td class="catalog-set-constructor-items-list-table-cell-del">
										<span class="catalog-set-constructor-items-list-table-cell-delete-btn" data-role="set-delete-btn"></span>
									</td>
								</tr>
							<?endforeach?>
							</tbody>
						</table>
						<div style="display: none;margin:20px;" data-set-message="empty-set"></div>

					</div>
				</div>

			</div>
			<div class="row" data-role="slider-parent-container"<?=(empty($arResult["SET_ITEMS"]["OTHER"]) ? 'style="display:none;"' : '')?>>
				<div class="col">
					<div class="catalog-set-constructor-slider">
						<div class="catalog-set-constructor-slider-box">
							<div class="catalog-set-constructor-slider-container">
								<div class="catalog-set-constructor-slider-slide catalog-set-constructor-slider-slide-<?=$curJsId?>" data-role="set-other-items">
									<?
									$first = true;
									foreach($arResult["SET_ITEMS"]["OTHER"] as $key => $arItem):?>
										<div class="catalog-set-constructor-slider-item-container catalog-set-constructor-slider-item-container-<?=$curJsId?>"
											data-id="<?=$arItem["ID"]?>"
											data-img="<?=$arItem["DETAIL_PICTURE"]["src"]?>"
											data-url="<?=$arItem["DETAIL_PAGE_URL"]?>"
											data-name="<?=$arItem["NAME"]?>"
											data-price="<?=$arItem["PRICE_DISCOUNT_VALUE"]?>"
											data-print-price="<?=$arItem["PRICE_PRINT_DISCOUNT_VALUE"]?>"
											data-old-price="<?=$arItem["PRICE_VALUE"]?>"
											data-print-old-price="<?=$arItem["PRICE_PRINT_VALUE"]?>"
											data-diff-price="<?=$arItem["PRICE_DISCOUNT_DIFFERENCE_VALUE"]?>"
											data-measure="<?=$arItem["MEASURE"]["SYMBOL_RUS"];?>"
											data-quantity="<?=$arItem["BASKET_QUANTITY"];?>"<?
										if (!$arItem['CAN_BUY'] && $first)
										{
											echo 'data-not-avail="yes"';
											$first = false;
										}
										?>
										>
											<div class="catalog-set-constructor-slider-item">
												<div class="catalog-set-constructor-slider-item-img">
													<div class="catalog-set-constructor-slider-item-img-container">
														<?if ($arItem["DETAIL_PICTURE"]["src"]):?>
															<img src="<?=$arItem["DETAIL_PICTURE"]["src"]?>" class="img-responsive" alt=""/>
														<?else:?>
															<img src="<?=$this->GetFolder().'/images/no_foto.png'?>" class="img-responsive"/>
														<?endif?>
													</div>
												</div>
												<div class="catalog-set-constructor-slider-item-title">
													<a target="_blank" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
												</div>
												<div class="catalog-set-constructor-slider-item-price">
													<div class="catalog-set-constructor-product-new-price"><?=$arItem["PRICE_PRINT_DISCOUNT_VALUE"]?> * <?=$arItem["BASKET_QUANTITY"];?> <?=$arItem["MEASURE"]["SYMBOL_RUS"];?></div>
													<?if ($arItem["PRICE_VALUE"] != $arItem["PRICE_DISCOUNT_VALUE"]):?>
														<div class="catalog-set-constructor-product-old-price"><?=$arItem["PRICE_PRINT_VALUE"]?></div>
													<?endif?>
												</div>
												<div class="catalog-set-constructor-slider-item-add-btn">
													<?
													if ($arItem['CAN_BUY'])
													{
														?><a href="javascript:void(0)" data-role="set-add-btn" class="btn btn-primary btn-sm"><?=GetMessage("CATALOG_SET_BUTTON_ADD")?></a><?
													}
													else
													{
														?><span class="catalog-set-constructor-slider-item-notavailable"><?=GetMessage('CATALOG_SET_MESS_NOT_AVAILABLE');?></span><?
													}
													?>
												</div>
											</div>
										</div>
									<?endforeach?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row align-items-center">
				<div class="col-sm-8">
					<table class="catalog-set-constructor-result-table">
						<tr style="display: <?=($arResult['SHOW_DEFAULT_SET_DISCOUNT'] ? 'table-row' : 'none'); ?>;">
							<td class="catalog-set-constructor-result-table-title"><?=GetMessage("CATALOG_SET_PRODUCTS_PRICE")?>:</td>
							<td class="catalog-set-constructor-result-table-value">
								<strong data-role="set-old-price"><?=$arResult["SET_ITEMS"]["OLD_PRICE"]?></strong>
							</td>
						</tr>
						<tr>
							<td class="catalog-set-constructor-result-table-title"><?=GetMessage("CATALOG_SET_SET_PRICE")?>:</td>
							<td class="catalog-set-constructor-result-table-value">
								<strong data-role="set-price"><?=$arResult["SET_ITEMS"]["PRICE"]?></strong>
							</td>
						</tr>
						<tr style="display: <?=($arResult['SHOW_DEFAULT_SET_DISCOUNT'] ? 'table-row' : 'none'); ?>;">
							<td class="catalog-set-constructor-result-table-title"><?=GetMessage("CATALOG_SET_ECONOMY_PRICE")?>:</td>
							<td class="catalog-set-constructor-result-table-value">
								<strong data-role="set-diff-price"><?=$arResult["SET_ITEMS"]["PRICE_DISCOUNT_DIFFERENCE"]?></strong>
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-4" style="text-align: right;">
					<div class="catalog-set-constructor-result-btn-container">
						<span class="catalog-set-constructor-result-price border-primary" data-role="set-price-duplicate">
							<?=$arResult["SET_ITEMS"]["PRICE"]?>
						</span>
					</div>
					<div class="catalog-set-constructor-result-btn-container">
						<a href="javascript:void(0)" data-role="set-buy-btn" class="btn btn-primary btn-sm"
							<?=($arResult["ELEMENT"]["CAN_BUY"] ? '' : 'style="display: none;"')?>>
							<?=GetMessage("CATALOG_SET_BUY")?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?
		$arJsParams = array(
			"numSliderItems" => count($arResult["SET_ITEMS"]["OTHER"]),
			"numSetItems" => count($arResult["SET_ITEMS"]["DEFAULT"]),
			"jsId" => $curJsId,
			"parentContId" => "bx-set-const-".$curJsId,
			"ajaxPath" => $this->GetFolder().'/ajax.php',
			"canBuy" => $arResult["ELEMENT"]["CAN_BUY"],
			"currency" => $arResult["ELEMENT"]["PRICE_CURRENCY"],
			"mainElementPrice" => $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"],
			"mainElementOldPrice" => $arResult["ELEMENT"]["PRICE_VALUE"],
			"mainElementDiffPrice" => $arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"],
			"mainElementBasketQuantity" => $arResult["ELEMENT"]["BASKET_QUANTITY"],
			"lid" => SITE_ID,
			"iblockId" => $arParams["IBLOCK_ID"],
			"basketUrl" => $arParams["BASKET_URL"],
			"setIds" => $arResult["DEFAULT_SET_IDS"],
			"offersCartProps" => $arParams["OFFERS_CART_PROPERTIES"],
			"itemsRatio" => $arResult["BASKET_QUANTITY"],
			"noFotoSrc" => $this->GetFolder().'/images/no_foto.png',
			"messages" => array(
				"EMPTY_SET" => GetMessage('CT_BCE_CATALOG_MESS_EMPTY_SET'),
				"ADD_BUTTON" => GetMessage("CATALOG_SET_BUTTON_ADD")
			)
		);
		?>
		<script type="text/javascript">
			BX.ready(function(){
				new BX.Catalog.SetConstructor(<?=CUtil::PhpToJSObject($arJsParams, false, true, true)?>);
			});
		</script>
	</div>
</div>
<!--/-->