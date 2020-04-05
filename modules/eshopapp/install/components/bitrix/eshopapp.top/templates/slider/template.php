<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = unserialize($notifyOption);
?>
<div class="mainspecialoffer_component">
	<table>
		<tr>
	<?foreach($arResult["ITEMS"] as $key => $arItem)
	{
	?>
		<td>
			<div class="main_specialoffer_container">
				<?if (strlen($arItem["DETAIL_PICTURE"]["SRC"])>0):?>
					<a class="main_specialoffer_img" href="<?=$arItem["DETAIL_PAGE_URL"]?>"><span><img src="<?=$arItem["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arItem["NAME"]?>"/></span></a>
				<?endif?>
				<div class="main_specialoffer_label"><?=GetMessage($arParams["FLAG_PROPERTY_CODE"]."_TITLE")?></div>
				<h1 class="main_specialoffer_title"><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" title="<?=$arItem["NAME"]?>"><?=$arItem["NAME"]?></a></h1>
				<div class="main_specialoffer_description"><?=$arItem["PREVIEW_TEXT"]?></div>
				<div class="main_specialoffer_price_container oldprice">

				<?if(!is_array($arItem["OFFERS"]) || empty($arItem["OFFERS"])):?>
					<?$numPrices = count($arParams["PRICE_CODE"]);
					foreach($arItem["PRICES"] as $code=>$arPrice):
						if($arPrice["CAN_ACCESS"]):?>
							<?if ($numPrices>1):?><div style="font-size: 12px;">
								<?=$arResult["PRICES"][$code]["TITLE"];?>:</div>
							<?endif?>
							<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
								<span class="item_price">
									<?=$arPrice["PRINT_DISCOUNT_VALUE"]?><br>
									<span class="item_price_old"><?=$arPrice["PRINT_VALUE"]?></span>
								</span>
							<?else:?>
								<span class="item_price"><?=$arPrice["PRINT_VALUE"]?></span>
							<?endif;
						endif;
					endforeach;?>

					<?if ($arItem["CAN_BUY"]):?>
						<noindex>
							<a href="<?=$arItem["ADD_URL"]?>"
								ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');"
								class="main_specialoffer_buy button_yellow_small"
								rel="nofollow"
								onclick = "	BX.addClass(BX.findParent(this, {class : 'main_specialoffer_container'}, false), 'add2cart');
											return addItemToCart(this);"
								id="catalog_add2cart_link_<?=$arItem['ID']?> ">
								<?=GetMessage("CATALOG_ADD")?>
							</a>
							<a href="<?=$arParams["BASKET_URL"]?>" class="main_catalog_item_cartlink button_yellow_small"><?=GetMessage("CATALOG_IN_CART")?></a>
						</noindex>
					<?/*elseif ($arNotify[SITE_ID]['use'] == 'Y'):?>
						<?if ($USER->IsAuthorized()):?>
							<noindex><a href="<?echo $arItem["SUBSCRIBE_URL"]?>" rel="nofollow" class="bt2" onclick="return addToSubscribe(this, '<?=GetMessage("CATALOG_IN_SUBSCRIBE")?>');" id="catalog_add2cart_link_<?=$arItem['ID']?>"><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
						<?else:?>
							<noindex><a href="javascript:vpid(0)" rel="nofollow" class="bt2" onclick="showAuthForSubscribe(this, <?=$arItem['ID']?>, '<?echo $arItem["SUBSCRIBE_URL"]?>')" id="catalog_add2cart_link_<?=$arItem['ID']?>"><?echo GetMessage("CATALOG_SUBSCRIBE")?></a></noindex>
						<?endif;?>
					<?*/?>
					<?endif?>
				<?endif?>
					<div class="clb"></div>
				</div>

			</div>
			</td>
	<?
	}
	?>
		</tr>
	</table>
</div>