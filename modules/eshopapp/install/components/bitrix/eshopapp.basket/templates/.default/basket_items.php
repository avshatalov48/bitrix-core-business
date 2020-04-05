<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?$APPLICATION->SetPageProperty("BodyClass", "cart");?>
<div id="id-cart-list">
<div class="cart_item_list">
	<div class="cart_item_list_top_container">
		<a href="javascript:void(0)" class="bedit cart_item_list_filter_button" onclick="changeMode()"></a>
		<ul>
			<li class="current"><a href="javascript:void(0)"><?=GetMessage("SALE_PRD_IN_BASKET_ACT")?> <span class="cart-item-title">(<?=count($arResult["ITEMS"]["AnDelCanBuy"])?>)</span></a></li>
			<li ontouchstart="ShowBasketItems(2);"><a href="javascript:void(0)"><?=GetMessage("SALE_PRD_IN_BASKET_SHELVE")?> <span class="delay-item-title">(<?=count($arResult["ITEMS"]["DelDelCanBuy"])?>)</span></a></li>
			<?/*
			<?if ($countItemsSubscribe=count($arResult["ITEMS"]["ProdSubscribe"])):?><a href="javascript:void(0)" onclick="ShowBasketItems(3);" class="sortbutton"><?=GetMessage("SALE_PRD_IN_BASKET_SUBSCRIBE")?> (<?=$countItemsSubscribe?>)</a><?endif?>
			<?if ($countItemsNotAvailable=count($arResult["ITEMS"]["nAnCanBuy"])):?><a href="javascript:void(0)" onclick="ShowBasketItems(4);" class="sortbutton"><?=GetMessage("SALE_PRD_IN_BASKET_NOTA")?> (<?=$countItemsNotAvailable?>)</a><?endif?>
			*/?>
		</ul>
		<div class="clb"></div>
	</div>

	<ul id="id-cart-ul">
<?if(count($arResult["ITEMS"]["AnDelCanBuy"]) > 0):?>
	<?
	$i=0;
	foreach($arResult["ITEMS"]["AnDelCanBuy"] as $arBasketItems)
	{
		?>
		<li id="basketItemID_<?=$arBasketItems["ID"]?>" <?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>onclick="if (!BX.hasClass(BX('body'), 'edit')) app.loadPageBlank({url:'<?=htmlspecialcharsback($arBasketItems["DETAIL_PAGE_URL"])?>'});"<?endif;?>>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
					<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
						<a class="cart_item_list_img" href="<?=$arBasketItems["DETAIL_PAGE_URL"]?>">
					<?endif;?>
					<?if (!empty($arResult["ITEMS_IMG"][$arBasketItems["ID"]]["SRC"])) :?>
						<img src="<?=$arResult["ITEMS_IMG"][$arBasketItems["ID"]]["SRC"]?>" alt="<?=$arBasketItems["NAME"] ?>"/>
					<?endif?>
					<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
						</a>
					<?endif;?>

					<div class="cart_item_list_title">
						<span><?=$arBasketItems["NAME"] ?></span>
					</div>

					<?if (in_array("PROPS", $arParams["COLUMNS_LIST"]))
					{
					?>
					<div class="cart_item_list_description_text">
						<ul>
					<?
						foreach($arBasketItems["PROPS"] as $val)
						{
							echo "<li>".$val["NAME"].": ".$val["VALUE"]."</li>";
						}
					?>
						</ul>
					</div>
					<?
					}?>
			<?endif;?>
			<?/*if (in_array("VAT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["VAT_RATE_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["NOTES"]?></td>
			<?endif;?>
			<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["DISCOUNT_PRICE_PERCENT_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["WEIGHT_FORMATED"]?></td>
			<?endif;*/?>

			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<?if(doubleval($arBasketItems["FULL_PRICE"]) > 0):?>
					<div class="cart_price_conteiner oldprice whsnw">
						<span class="item_price"><?=$arBasketItems["PRICE_FORMATED"]?></span>
						<span class="item_price_old"><?=$arBasketItems["FULL_PRICE_FORMATED"]?></span>
					</div>
				<?else:?>
					<div class="cart_price_conteiner whsnw">
						<span class="item_price"><?=$arBasketItems["PRICE_FORMATED"]?></span>
					</div>
				<?endif?>
			<?endif;?>

			<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
				<div class="cart_item_count">
					<span><?=GetMessage("SALE_QUANTITY")?>:</span>
					<a href="javascript:void(0)" class="count_minus" ontouchstart="if (BX('QUANTITY_<?=$arBasketItems["ID"]?>').value > 1) BX('QUANTITY_<?=$arBasketItems["ID"]?>').value--;"><span></span></a>
					<input maxlength="18" min="1" type="number" class="quantity_input" name="QUANTITY_<?=$arBasketItems["ID"]?>" value="<?=$arBasketItems["QUANTITY"]?>" size="3" id="QUANTITY_<?=$arBasketItems["ID"]?>">
					<a href="javascript:void(0)" class="count_plus" ontouchstart="BX('QUANTITY_<?=$arBasketItems["ID"]?>').value++;"><span></span></a>
				</div>
			<?endif;?>

			<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
				<a class="cart_item_remove" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>" onclick="/*if (confirm('<?=GetMessage("SALE_DELETE_CONFIRM")?>')) */ return DeleteFromCart(this); //else return false;" title="<?=GetMessage("SALE_DELETE_PRD")?>"></a>
			<?endif;?>
			<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
				<a class="cart_item_delayed" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["shelve"])?>" onclick="return DelayInCart(this);"></a>
			<?endif;?>
			<div class="clb"></div>
		</li>
		<?
		$i++;
	}
	?>
	</ul>
<?endif?>
</div>

<div class="cart_item_bottom" id="cart_item_bottom" <?if(!count($arResult["ITEMS"]["AnDelCanBuy"]) > 0):?>style="display:none"<?endif;?>>
	<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
		<div class="cart_item_total_price" >
		<?echo GetMessage("SALE_ALL_WEIGHT")?>:
		<span id="weight"><?=$arResult["allWeight_FORMATED"]?></span>
		</div>
	<?endif;?>
	<div id="all_discount">
	<?if (doubleval($arResult["DISCOUNT_PRICE"]) > 0):?>
		<div class="cart_item_total_price" >
		<?echo GetMessage("SALE_CONTENT_DISCOUNT")?><?
				if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0)
					echo " (".$arResult["DISCOUNT_PERCENT_FORMATED"].")";?>:
			<?=$arResult["DISCOUNT_PRICE_FORMATED"]?>
		</div>
	<?endif;?>
	</div>
	<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
	<div class="cart_item_total_price" >
		<?echo GetMessage('SALE_VAT_EXCLUDED')?>
		<span id="vat_excluded"><?=$arResult["allNOVATSum_FORMATED"]?></span>
	</div>
	<div class="cart_item_total_price" >
		<?echo GetMessage('SALE_VAT_INCLUDED')?>
		<span id="vat_included"><?=$arResult["allVATSum_FORMATED"]?></span>
	</div>
	<?endif;?>
	<div class="cart_item_total_price">
		<?= GetMessage("SALE_ITOGO")?>: <span class="price"><strong id="all_price"><?=$arResult["allSum_FORMATED"]?></strong></span>
	</div>
	<hr class="cart_item_hr">
	<?if ($arParams["HIDE_COUPON"] != "Y"):?>
	<div class="cart_item_coupon">
		<span><?=GetMessage("SALE_COUPON")?>:</span>
		<div class="cart_item_list_search_input_container">
			<input value="<?if(!empty($arResult["COUPON"])):?><?=$arResult["COUPON"]?><?endif;?>" name="COUPON" type="text">
		</div>
		<div class="clb"></div>
	</div>
	<?endif;?>
	<hr class="cart_item_hr">
	<input type="hidden" value="<?echo GetMessage("SALE_UPDATE")?>" name="BasketRefresh" >
	<a href="javascript:void(0)" class="cart_item_refresh button_gray_medium" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" onclick="
			var data_form = {}, form = BX('basket_form');
			for(var i = 0; i< form.elements.length; i++)
			{
			if (form[i].name != 'BasketOrder')
			data_form[form[i].name] = form[i].value;
			}
			ajaxInCart('<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>', data_form);
			return BX.PreventDefault(event);"><?echo GetMessage("SALE_UPDATE")?></a>
	<a id="basketOrderButton2" class="cart_item_checkout button_red_medium" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" onclick="app.loadPage('<?=$arParams["PATH_TO_ORDER"]?>'); return false;"><?echo GetMessage("SALE_ORDER")?></a>
	<br/>
</div>

<div class="cart-notetext" id="empty_cart_text" <?if(count($arResult["ITEMS"]["AnDelCanBuy"]) > 0):?>style="display:none"<?endif;?>>
	<div class="detail_item tac">
		<span class="empty_cart_text">
			<?=GetMessage("SALE_NO_ACTIVE_PRD");?>
		</span>
	</div>
</div>

</div>