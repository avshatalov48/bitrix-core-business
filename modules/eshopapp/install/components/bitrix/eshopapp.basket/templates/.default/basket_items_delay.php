<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->SetPageProperty("BodyClass", "cart");?>
<div class="cart_item_list" id="id-shelve-list" style="display:none;">
	<div class="cart_item_list_top_container">
		<a href="javascript:void(0)" class="bedit cart_item_list_filter_button" onclick="changeMode()"></a>
		<ul>
			<li ontouchstart="ShowBasketItems(1);"><a href="javascript:void(0)"><?=GetMessage("SALE_PRD_IN_BASKET_ACT")?> <span class="cart-item-title">(<?=count($arResult["ITEMS"]["AnDelCanBuy"])?>)</span></a></li>
			<li class="current"><a href="javascript:void(0)"><?=GetMessage("SALE_PRD_IN_BASKET_SHELVE")?> <span class="delay-item-title">(<?=count($arResult["ITEMS"]["DelDelCanBuy"])?>)</span></a></li>
		</ul>
		<div class="clb"></div>
	</div>
	<ul id="id-shelve-ul">
<?if(count($arResult["ITEMS"]["DelDelCanBuy"]) > 0):?>
	<?
	foreach($arResult["ITEMS"]["DelDelCanBuy"] as $arBasketItems)
	{
	?>
		<li <?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>onclick="if (!BX.hasClass(BX('body'), 'edit')) app.loadPageBlank({url:'<?=htmlspecialcharsback($arBasketItems["DETAIL_PAGE_URL"])?>'});"<?endif;?>>
		<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
			<a class="cart_item_list_img" href="<?=$arBasketItems["DETAIL_PAGE_URL"]?>">
		<?endif;?>
		<?if (!empty($arResult["ITEMS_IMG"][$arBasketItems["ID"]]["SRC"])) :?>
			<img src="<?=$arResult["ITEMS_IMG"][$arBasketItems["ID"]]["SRC"]?>" alt="<?=$arBasketItems["NAME"] ?>"/>
		<?endif?>
		<?if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):?>
			</a>
		<?endif;?>

		<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
			<div class="cart_item_list_title">
				<span><?=$arBasketItems["NAME"] ?></span>
			</div>
		<?endif;?>

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

			<?/*if (in_array("VAT", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-price"><?=$arBasketItems["VAT_RATE_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-type"><?=$arBasketItems["NOTES"]?></td>
			<?endif;?>
			<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-discount"><?=$arBasketItems["DISCOUNT_PRICE_PERCENT_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
				<td class="cart-item-weight"><?=$arBasketItems["WEIGHT_FORMATED"]?></td>
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
				<a href="javascript:void(0)" class="count_minus" ontouchstart="if (BX('QUANTITY_<?=$arBasketItems["ID"]?>').value > 1) BX('QUANTITY_<?=$arBasketItems["ID"]?>').value--;" style="display:none"><span></span></a>
				<input maxlength="18" min="1" type="number" class="quantity_input" name="QUANTITY_<?=$arBasketItems["ID"]?>" value="<?=$arBasketItems["QUANTITY"]?>" size="3" id="QUANTITY_<?=$arBasketItems["ID"]?>" readonly>
				<a href="javascript:void(0)" class="count_plus" ontouchstart="BX('QUANTITY_<?=$arBasketItems["ID"]?>').value++;" style="display:none"><span></span></a>
			</div>
			<?endif;?>

			<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
			<a class="cart_item_remove" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>" onclick="return DeleteFromCart(this);" title="<?=GetMessage("SALE_DELETE_PRD")?>"></a>
			<?endif;?>
			<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
				<a class="cart_item_delayed" href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["add"])?>" onclick="return Add2Order(this)"></a>
			<?endif;?>
		</li>
		<?
	}
	?>
<?endif;?>
	</ul>
</div>