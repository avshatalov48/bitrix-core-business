<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$sticker = "";
if (array_key_exists("PROPERTIES", $arResult) && is_array($arResult["PROPERTIES"]))
{
	foreach (Array("SPECIALOFFER", "NEWPRODUCT", "SALELEADER") as $propertyCode)
		if (array_key_exists($propertyCode, $arResult["PROPERTIES"]) && intval($arResult["PROPERTIES"][$propertyCode]["PROPERTY_VALUE_ID"]) > 0)
		{
			$sticker = toLower($arResult["PROPERTIES"][$propertyCode]["NAME"]);
			break;
		}
}
?>
<div class="detail_item">
	<?if(is_array($arResult["PREVIEW_PICTURE"]) || is_array($arResult["DETAIL_PICTURE"])):?>
		<div class="detail_item_img_container" <?if (!empty($arResult["PHOTO_GALLERY"])):?>onclick="showPhoto(<?=CUtil::PhpToJsObject($arResult["PHOTO_GALLERY"])?>, '<?=$arResult["NAME"]?>')"<?endif?>>
			<a class="detail_item_img" href="javascript:void(0)">
				<?if(is_array($arResult["DETAIL_PICTURE_SMALL"])):?>
					<img id="catalog_detail_image" src="<?=$arResult["DETAIL_PICTURE_SMALL"]["SRC"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" />
				<?elseif(is_array($arResult["PREVIEW_PICTURE"])):?>
					<img id="catalog_detail_image" src="<?=$arResult["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" />
				<?endif?>
			</a>
			<!-- <span class="detail_item_img_lupe"></span> -->
		</div>
	<?endif;?>
	<h2 class="detail_item_title">
		<a href="<?=$arResult["DETAIL_PAGE_URL"]?>" title="<?=$arResult["NAME"]?>"><?=$arResult["NAME"]?></a>
		<?if ($sticker):?><br/><span style="color:#9b0000; font-size: 14px;"><?=$sticker?></span><?endif?>
	</h2>

	<?if(!is_array($arResult["OFFERS"]) || empty($arResult["OFFERS"])):?>
		<?foreach($arResult["PRICES"] as $code=>$arPrice):?>
			<?if($arPrice["CAN_ACCESS"]):?>
				<?//=$arResult["CAT_PRICES"][$code]["TITLE"];?>
				<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
					<div class="detail_price_container oldprice">
						<span class="item_price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span><br />
						<span class="item_price_old"><?=$arPrice["PRINT_VALUE"]?></span>
					</div>
				<?else:?>
					<div class="detail_price_container">
						<span class="item_price"><?=$arPrice["PRINT_VALUE"]?></span>
					</div>
				<?endif;?>
			<?endif;?>
		<?endforeach;?>

		<?if($arResult["CAN_BUY"]):?>
			<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
			<div class="clb"></div>
			<div class="detail_item_buy_container">
				<form action="<?=POST_FORM_ACTION_URI?>" id="quantity_form" method="post" enctype="multipart/form-data"  >
					<div class="detail_item_count">
						<a href="javascript:void(0)" class="count_minus" id="count_minus" ontouchstart="if (BX('item_quantity').value > 1) BX('item_quantity').value--;"><span></span></a>
							<input type="number" id="item_quantity" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1">
						<a href="javascript:void(0)" class="count_plus" id="count_plus" ontouchstart="BX('item_quantity').value++;"><span></span></a>
					</div>
					<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="ADD2BASKET">
					<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arResult["ID"]?>">
					<a class="detail_item_buykey button_red_medium" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" href="javascript:void(0)" onclick="
							BX.addClass(BX.findParent(this, {class : 'detail_item'}, false), 'add2cart');
							app.onCustomEvent('onItemBuy', {});
							BX.ajax({
								timeout:   30,
								method:   'POST',
								url:       '<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
								processData: false,
								data: {
									<?echo $arParams["ACTION_VARIABLE"]?>: 'ADD2BASKET',
									<?echo $arParams["PRODUCT_ID_VARIABLE"]?>: '<?echo $arResult["ID"]?>',
									<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>: BX('quantity_form').elements['<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>'].value
								},
								onsuccess: function(reply){
								},
								onfailure: function(){
								}
							});
							return BX.PreventDefault(event);
					"><?echo GetMessage("CATALOG_BUY")?></a>
					<a class="detail_item_buykey_cartlink button_yellow_small" href="<?echo $arParams["BASKET_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_IN_CART")?></a>
				</form>
			</div>

			<?else:?>
			<div class="detail_item_buy_container">
				<noindex>
					<a class="detail_item_buykey button_red_medium" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" href="<?echo $arResult["ADD_URL"]?>" onclick="
						BX.addClass(BX.findParent(this, {class : 'detail_item'}, false), 'add2cart');
						return addItemToCart(this);" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
					<a class="detail_item_buykey_cartlink button_yellow_small" href="<?echo $arParams["BASKET_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_IN_CART")?></a>
				</noindex> 
			</div>
			<?endif;?>


		<?if(count($arResult["MORE_PHOTO"])>0):?>
		<div class="detail_item_gallery" onclick="showPhoto(<?=CUtil::PhpToJsObject($arResult["PHOTO_GALLERY"])?>, '<?=$arResult["NAME"]?>')">
			<div class="detail_item_gallery_topborder"></div>
			<span class="detail_item_gallery_left"></span>
			<div class="detail_item_gallery_tcontainer">
				<ul>
				<?foreach($arResult["MORE_PHOTO"] as $photo):?>
					<li><a href="javascript:void(0)"><span><img src="<?=$photo["SRC"]?>" alt=""></span></a></li>
				<?endforeach?>
				</ul>
			</div>
			<div class="clb"></div>
			<span class="detail_item_gallery_right"></span>
		</div>
		<?endif?>


		<?/*elseif((count($arResult["PRICES"]) > 0) || is_array($arResult["PRICE_MATRIX"])):?>
			<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
			<?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
					"NOTIFY_ID" => $arResult['ID'],
					"NOTIFY_PRODUCT_ID" => $arParams['PRODUCT_ID_VARIABLE'],
					"NOTIFY_ACTION" => $arParams['ACTION_VARIABLE'],
					"NOTIFY_URL" => htmlspecialcharsback($arResult["SUBSCRIBE_URL"]),
					"NOTIFY_USE_CAPTHA" => "N"
				),
				$component
			);?>
		<?*/endif?>
	<?endif;?>
</div>

	<?/*if(is_array($arResult["OFFERS"]) && !empty($arResult["OFFERS"])):/*?>
		<?foreach($arResult["OFFERS"] as $arOffer):?>
			<?foreach($arParams["OFFERS_FIELD_CODE"] as $field_code):?>
				<small><?echo GetMessage("IBLOCK_FIELD_".$field_code)?>:&nbsp;<?
						echo $arOffer[$field_code];?></small><br />
			<?endforeach;?>
			<?foreach($arOffer["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
				<small><?=$arProperty["NAME"]?>:&nbsp;<?
					if(is_array($arProperty["DISPLAY_VALUE"]))
						echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
					else
						echo $arProperty["DISPLAY_VALUE"];?></small><br />
			<?endforeach?>
			<?foreach($arOffer["PRICES"] as $code=>$arPrice):?>
				<?if($arPrice["CAN_ACCESS"]):?>
					<p><?=$arResult["CAT_PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
					<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
						<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
					<?else:?>
						<span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
					<?endif?>
					</p>
				<?endif;?>
			<?endforeach;?>
			<p>
			<?if($arParams["DISPLAY_COMPARE"]):?>
				<noindex>
				<a href="<?echo $arOffer["COMPARE_URL"]?>" rel="nofollow"><?echo GetMessage("CT_BCE_CATALOG_COMPARE")?></a>&nbsp;
				</noindex>
			<?endif?>
			<?if($arOffer["CAN_BUY"]):?>
				<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
					<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
					<table border="0" cellspacing="0" cellpadding="2">
						<tr valign="top">
							<td><?echo GetMessage("CT_BCE_QUANTITY")?>:</td>
							<td>
								<input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5">
							</td>
						</tr>
					</table>
					<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY">
					<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arOffer["ID"]?>">
					<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>">
					<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CT_BCE_CATALOG_ADD")?>">
					</form>
				<?else:?>
					<noindex>
					<a href="<?echo $arOffer["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
					&nbsp;<a href="<?echo $arOffer["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CT_BCE_CATALOG_ADD")?></a>
					</noindex>
				<?endif;?>
			<?elseif(count($arResult["CAT_PRICES"]) > 0):?>
				<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
				<?$APPLICATION->IncludeComponent("bitrix:sale.notice.product", ".default", array(
					"NOTIFY_ID" => $arOffer['ID'],
					"NOTIFY_URL" => htmlspecialcharsback($arOffer["SUBSCRIBE_URL"]),
					"NOTIFY_USE_CAPTHA" => "N"
					),
					$component
				);?>
			<?endif?>
			</p>
		<?endforeach;?>
	<?else:?>

	<?endif*/?>

<?if ($arResult["DETAIL_TEXT"] || $arResult["PREVIEW_TEXT"]):?>
<div class="detail_item_description <?if (!CMobile::getInstance()->isLarge()) echo "close";?>" >
	<h3 onclick="OpenClose(BX(this).parentNode)"><?=GetMessage("CATALOG_FULL_DESC")?> <span class="detail_item_arrow"></span></h3>
	<div class="detail_item_description_text">
		<?if($arResult["DETAIL_TEXT"]):?>
			<br /><?=$arResult["DETAIL_TEXT"]?><br />
		<?elseif($arResult["PREVIEW_TEXT"]):?>
			<br /><?=$arResult["PREVIEW_TEXT"]?><br />
		<?endif;?>
	</div>
</div>
<?endif?>

<?if (is_array($arResult['DISPLAY_PROPERTIES']) && count($arResult['DISPLAY_PROPERTIES']) > 0)
{
	$arPropertyRecommend = $arResult["DISPLAY_PROPERTIES"]["RECOMMEND"];
	unset($arResult["DISPLAY_PROPERTIES"]["RECOMMEND"]);
	if (is_array($arResult['DISPLAY_PROPERTIES']) && count($arResult['DISPLAY_PROPERTIES']) > 0):?>
	<div class="detail_item_description info <?if (!CMobile::getInstance()->isLarge()) echo "close";?>">
		<h3 onclick="OpenClose(BX(this).parentNode)"><?=GetMessage("CATALOG_PROPERTIES")?> <span class="detail_item_arrow"></span></h3>
		<div class="detail_item_description_text">
			<ul>
			<?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
				<li>
					<table>
						<tr>
							<td class="detail_item_feature"><span><?=$arProperty["NAME"]?>:</span></td>
							<td class="detail_item__featurevalue"><span>
							<?if(is_array($arProperty["DISPLAY_VALUE"])):
								echo implode(" / ", $arProperty["DISPLAY_VALUE"]);
							elseif($pid=="MANUAL"):
								?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
							else:
								echo $arProperty["DISPLAY_VALUE"];?>
							<?endif?>
							</span></td>
						</tr>
					</table>
				</li>
			<?endforeach?>
			</ul>
		</div>
	</div>
	<?endif;
}
?>
<script type="text/javascript">
	app.setPageTitle({"title" : "<?=CUtil::JSEscape(htmlspecialcharsback($arResult["NAME"]))?>"});
	function showPhoto(arPhotos, descr)
	{
		var photos = [];
		for (var i=0; i<arPhotos.length; i++)
		{
			photos[i] = {url : arPhotos[i], description : descr};
		}
		app.openPhotos({
			"photos": photos
		});
	}
</script>


