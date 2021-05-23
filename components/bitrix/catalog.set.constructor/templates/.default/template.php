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
$this->addExternalCss("/bitrix/css/main/bootstrap.css");

$templateData = array(
	'TEMPLATE_THEME' => $this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css',
	'TEMPLATE_CLASS' => 'bx-'.$arParams['TEMPLATE_THEME'],
	'CURRENCIES' => CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)
);
$curJsId = $this->randString();
?>
<div id="bx-set-const-<?=$curJsId?>" class="bx-set-constructor container-fluid <?=$templateData['TEMPLATE_CLASS'];?>">
	<div class="row">
		<div class="col-xs-12">
			<strong class="bx-modal-small-title"><?=GetMessage("CATALOG_SET_BUY_SET")?></strong>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3">
			<div class="bx-original-item-container">
				<?if ($arResult["ELEMENT"]["DETAIL_PICTURE"]["src"]):?>
					<img src="<?=$arResult["ELEMENT"]["DETAIL_PICTURE"]["src"]?>" class="bx-original-item-image" alt="">
				<?else:?>
					<img src="<?=$this->GetFolder().'/images/no_foto.png'?>" class="bx-original-item-image" alt="">
				<?endif?>

				<div>
					<?=$arResult["ELEMENT"]["NAME"]?> <br>
					<span class="bx-added-item-new-price"><strong><?=$arResult["ELEMENT"]["PRICE_PRINT_DISCOUNT_VALUE"]?></strong> * <?=$arResult["ELEMENT"]["BASKET_QUANTITY"];?> <?=$arResult["ELEMENT"]["MEASURE"]["SYMBOL_RUS"];?></span>
					<?if (!($arResult["ELEMENT"]["PRICE_VALUE"] == $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"])):?><span class="bx-catalog-set-item-price-old"><strong><?=$arResult["ELEMENT"]["PRICE_PRINT_VALUE"]?></strong></span><?endif?>

				</div>
			</div>
		</div>

		<div class="col-sm-9">
			<div class="bx-added-item-table-container">
				<table class="bx-added-item-table">
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
							<td class="bx-added-item-table-cell-img">
								<?if ($arItem["DETAIL_PICTURE"]["src"]):?>
									<img src="<?=$arItem["DETAIL_PICTURE"]["src"]?>" class="img-responsive" alt="">
								<?else:?>
									<img src="<?=$this->GetFolder().'/images/no_foto.png'?>" class="img-responsive" alt="">
								<?endif?>
							</td>
							<td class="bx-added-item-table-cell-itemname">
								<a class="tdn" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
							</td>
							<td class="bx-added-item-table-cell-price">
								<span class="bx-added-item-new-price"><?=$arItem["PRICE_PRINT_DISCOUNT_VALUE"]?> * <?=$arItem["BASKET_QUANTITY"];?> <?=$arItem["MEASURE"]["SYMBOL_RUS"];?></span>
								<?if ($arItem["PRICE_VALUE"] != $arItem["PRICE_DISCOUNT_VALUE"]):?>
									<br><span class="bx-added-item-old-price"><?=$arItem["PRICE_PRINT_VALUE"]?></span>
								<?endif?>
							</td>
							<td class="bx-added-item-table-cell-del"><div class="bx-added-item-delete" data-role="set-delete-btn"></div></td>
						</tr>
					<?endforeach?>
					</tbody>
				</table><div style="display: none;margin:20px;" data-set-message="empty-set"></div>
			</div>
		</div>
	</div>
	<div class="row" data-role="slider-parent-container"<?=(empty($arResult["SET_ITEMS"]["OTHER"]) ? 'style="display:none;"' : '')?>>
		<div class="col-xs-12">
			<div class="bx-catalog-set-topsale-slider">
				<div class="bx-catalog-set-topsale-slider-box">
					<div class="bx-catalog-set-topsale-slider-container">
						<div class="bx-catalog-set-topsale-slids bx-catalog-set-topsale-slids-<?=$curJsId?>" data-role="set-other-items">
							<?
							$first = true;
							foreach($arResult["SET_ITEMS"]["OTHER"] as $key => $arItem):?>
								<div class="bx-catalog-set-item-container bx-catalog-set-item-container-<?=$curJsId?>"
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
									<div class="bx-catalog-set-item">
										<div class="bx-catalog-set-item-img">
											<div class="bx-catalog-set-item-img-container">
												<?if ($arItem["DETAIL_PICTURE"]["src"]):?>
													<img src="<?=$arItem["DETAIL_PICTURE"]["src"]?>" class="img-responsive" alt=""/>
												<?else:?>
													<img src="<?=$this->GetFolder().'/images/no_foto.png'?>" class="img-responsive"/>
												<?endif?>
											</div>
										</div>
										<div class="bx-catalog-set-item-title">
											<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
										</div>
										<div class="bx-catalog-set-item-price">
											<div class="bx-catalog-set-item-price-new"><?=$arItem["PRICE_PRINT_DISCOUNT_VALUE"]?> * <?=$arItem["BASKET_QUANTITY"];?> <?=$arItem["MEASURE"]["SYMBOL_RUS"];?></div>
											<?if ($arItem["PRICE_VALUE"] != $arItem["PRICE_DISCOUNT_VALUE"]):?>
												<div class="bx-catalog-set-item-price-old"><?=$arItem["PRICE_PRINT_VALUE"]?></div>
											<?endif?>
										</div>
										<div class="bx-catalog-set-item-add-btn">
											<?
											if ($arItem['CAN_BUY'])
											{
												?><a href="javascript:void(0)" data-role="set-add-btn" class="btn btn-default btn-sm"><?=GetMessage("CATALOG_SET_BUTTON_ADD")?></a><?
											}
											else
											{
												?><span class="bx-catalog-set-item-notavailable"><?=GetMessage('CATALOG_SET_MESS_NOT_AVAILABLE');?></span><?
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
	<div class="row">
		<div class="col-sm-8">
			<table class="bx-constructor-result-table">
				<tr style="display: <?=($arResult['SHOW_DEFAULT_SET_DISCOUNT'] ? 'table-row' : 'none'); ?>;">
					<td class="bx-constructor-result-table-title"><?=GetMessage("CATALOG_SET_PRODUCTS_PRICE")?>:</td>
					<td class="bx-constructor-result-table-value">
						<strong data-role="set-old-price"><?=$arResult["SET_ITEMS"]["OLD_PRICE"]?></strong>
					</td>
				</tr>
				<tr>
					<td class="bx-constructor-result-table-title"><?=GetMessage("CATALOG_SET_SET_PRICE")?>:</td>
					<td class="bx-constructor-result-table-value">
						<strong data-role="set-price"><?=$arResult["SET_ITEMS"]["PRICE"]?></strong>
					</td>
				</tr>
				<tr style="display: <?=($arResult['SHOW_DEFAULT_SET_DISCOUNT'] ? 'table-row' : 'none'); ?>;">
					<td class="bx-constructor-result-table-title"><?=GetMessage("CATALOG_SET_ECONOMY_PRICE")?>:</td>
					<td class="bx-constructor-result-table-value">
						<strong data-role="set-diff-price"><?=$arResult["SET_ITEMS"]["PRICE_DISCOUNT_DIFFERENCE"]?></strong>
					</td>
				</tr>
			</table>
		</div>
		<div class="col-sm-4" style="text-align: center;">
			<div class="bx-constructor-result-btn-container">
				<span class="bx-constructor-result-price" data-role="set-price-duplicate">
					<?=$arResult["SET_ITEMS"]["PRICE"]?>
				</span>
			</div>
			<div class="bx-constructor-result-btn-container">
				<a href="javascript:void(0)" data-role="set-buy-btn" class="btn btn-default btn-sm"
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
