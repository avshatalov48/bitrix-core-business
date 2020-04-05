<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(count($arResult["ITEMS"]) > 0): ?>
	<?
	//$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
	//$arNotify = unserialize($notifyOption);
	?>

<div class="main_catalog">
<?foreach($arResult["ITEMS"] as $key => $arItem):
	if(is_array($arItem))
	{
		$bPicture = is_array($arItem["PREVIEW_IMG"]);
		?>
	<div class="main_catalog_item">
		<?/*if($arParams["DISPLAY_COMPARE"]):?>
		<noindex>
			<?if(is_array($arItem["OFFERS"]) && !empty($arItem["OFFERS"])):?>
				<span class="checkbox">
					<a href="javascript:void(0)" onclick="return showOfferPopup(this, 'list', '<?=GetMessage("CATALOG_IN_CART")?>', <?=CUtil::PhpToJsObject($arItem["SKU_ELEMENTS"])?>, <?=CUtil::PhpToJsObject($arItem["SKU_PROPERTIES"])?>, <?=CUtil::PhpToJsObject($arResult["POPUP_MESS"])?>, 'compare');">
						<input type="checkbox" class="addtoCompareCheckbox"/><span class="checkbox_text"><?=GetMessage("CATALOG_COMPARE")?></span>
					</a>
				</span>
			<?else:?>
				<span class="checkbox">
					<a href="<?echo $arItem["COMPARE_URL"]?>" rel="nofollow" onclick="return addToCompare(this, 'list', '<?=GetMessage("CATALOG_IN_COMPARE")?>', '<?=$arItem["DELETE_COMPARE_URL"]?>');" id="catalog_add2compare_link_<?=$arItem['ID']?>">
						<input type="checkbox" class="addtoCompareCheckbox"/><span class="checkbox_text"><?=GetMessage("CATALOG_COMPARE")?></span>
					</a>
				</span>
			<?endif?>
		</noindex>
		<?endif*/?>
		<?if ($bPicture):?>
			<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="main_catalog_item_img"><span><img src="<?=$arItem["PREVIEW_IMG"]["SRC"]?>"  alt="<?=$arElement["NAME"]?>" /></span></a>
		<?endif?>
		<h2><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="<?=$arItem["NAME"]?>"><?=$arItem["NAME"]?></a></h2>

		<div class="main_catalog_item_price">
		<?if(!is_array($arItem["OFFERS"]) || empty($arItem["OFFERS"])):?>
			<?
				$numPrices = count($arParams["PRICE_CODE"]);
				foreach($arItem["PRICES"] as $code=>$arPrice):
					if($arPrice["CAN_ACCESS"]):?>
						<?if ($numPrices>1):?><p style="padding: 0; margin-bottom: 5px;"><?=$arResult["PRICES"][$code]["TITLE"];?>:</p><?endif?>
						<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
							<div class="price">
								<div class="main_price_container oldprice">
									<span class="item_price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br/> 
									<span class="item_price_old"><?=$arPrice["PRINT_VALUE"]?></span>
								</div>
							</div>
						<?else:?>
							<div class="main_price_container">
								<span class="item_price"><?=$arPrice["PRINT_VALUE"]?></span>
							</div>
						<?endif;
					endif;
				endforeach;
			?>

			<?if ($arItem["CAN_BUY"]):?>
				<noindex>
				<a href="<?=$arItem["ADD_URL"]?>"
					class="main_item_buy button_red_small"
					rel="nofollow"
					onclick="
						BX.addClass(BX.findParent(this, {class : 'main_catalog_item'}, false), 'add2cart');//	setTimeout('BX.removeClass(obj, \'add2cart\')', 3000);
						return addItemToCart(this);"
					id="catalog_add2cart_link_<?=$arItem['ID']?>">
					<?=GetMessage("CATALOG_ADD")?>
				</a>
				</noindex>
				<?/*elseif ($arNotify[SITE_ID]['use'] == 'Y'):?>
					<?if ($USER->IsAuthorized()):?>
						<noindex><a href="<?echo $arItem["SUBSCRIBE_URL"]?>" rel="nofollow" class="subscribe_link" onclick="return addToSubscribe(this, '<?=GetMessage("CATALOG_IN_SUBSCRIBE")?>');" id="catalog_add2cart_link_<?=$arItem['ID']?>"><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
					<?else:?>
						<noindex><a href="javascript:void(0)" rel="nofollow" class="subscribe_link" onclick="showAuthForSubscribe(this, <?=$arItem['ID']?>, '<?echo $arItem["SUBSCRIBE_URL"]?>')" id="catalog_add2cart_link_<?=$arItem['ID']?>"><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
					<?endif;?>
				<?*/?>
			<?endif?>
		<?endif?>
		</div>
		<div class="clb"></div>
		<a href="<?=$arParams["BASKET_URL"]?>" class="main_catalog_item_cartlink button_yellow_small" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');"><?=GetMessage("CATALOG_IN_CART")?></a>
	</div>
<?
	}
endforeach;
?>
	<div class="clb"></div>
</div>
<?elseif($USER->IsAdmin()):?>
<h3 class="hitsale"><span></span><?=GetMessage("CR_TITLE_".$arParams["FLAG_PROPERTY_CODE"])?></h3>
<div class="listitem-carousel">
	<?=GetMessage("CR_TITLE_NULL")?>
</div>
<?endif;?>
