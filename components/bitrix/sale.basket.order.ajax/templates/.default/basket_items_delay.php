<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="cart-items" id="id-shelve-list" style="display:none;">
	<div class="inline-filter cart-filter">
		<label><?=GetMessage("SALE_PRD_IN_BASKET")?></label>&nbsp;
			<a href="javascript:void(0);" onclick="ShowBasketItems(1);"><?=GetMessage("SALE_PRD_IN_BASKET_ACT")?> (<?=count($arResult["ITEMS"]["AnDelCanBuy"])?>)</a>&nbsp;
			<b><?=GetMessage("SALE_PRD_IN_BASKET_SHELVE")?></b>&nbsp;
			<a href="javascript:void(0);" onclick="ShowBasketItems(4);"><?=GetMessage("SALE_NOACTIVE")?> (<?=count($arResult["ITEMS"]["nAnCanBuy"])?>)</a>
			<a href="javascript:void(0);" onclick="ShowBasketItems(3);"><?=GetMessage("SALE_BASKET_NOTIFY")?> (<?=count($arResult["ITEMS"]["AnSubscribe"])?>)</a>
	</div>
	<?if(count($arResult["ITEMS"]["DelDelCanBuy"]) > 0):?>
		<table class="cart-items" cellspacing="0">
		<thead>
			<tr>
				<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-name"><?= GetMessage("SALE_NAME")?></td>
				<?endif;?>
				<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-price"><?= GetMessage("SALE_PRICE")?></td>
				<?endif;?>
				<?if (in_array("VAT", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-price"><?= GetMessage("SALE_VAT")?></td>
				<?endif;?>
				<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-type"><?= GetMessage("SALE_PRICE_TYPE")?></td>
				<?endif;?>
				<?if (in_array("DISCOUNT", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-discount"><?= GetMessage("SALE_DISCOUNT")?></td>
				<?endif;?>
				<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-weight"><?= GetMessage("SALE_WEIGHT")?></td>
				<?endif;?>
				<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-quantity"><?= GetMessage("SALE_QUANTITY")?></td>
				<?endif;?>
				<td class="cart-item-actions">
					<?if (in_array("DELETE", $arParams["COLUMNS_LIST"]) || in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
						<?= GetMessage("SALE_ACTION")?>
					<?endif;?>
				</td>
			</tr>
		</thead>
		
		<tbody>
		<?
		foreach($arResult["ITEMS"]["DelDelCanBuy"] as $arBasketItems)
		{
			?>
			<tr>
				<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-name"><?
					if ($arBasketItems["DETAIL_PAGE_URL"] <> ''):
						?><a href="<?=$arBasketItems["DETAIL_PAGE_URL"] ?>"><?
					endif;
					?><b><?=$arBasketItems["NAME"] ?></b><?
					if ($arBasketItems["DETAIL_PAGE_URL"] <> ''):
						?></a><?
					endif;?>
					<?if (in_array("PROPS", $arParams["COLUMNS_LIST"]))
					{
						foreach($arBasketItems["PROPS"] as $val)
						{
							echo "<br />".$val["NAME"].": ".$val["VALUE"];
						}
					}?>
					</td>
				<?endif;?>
				<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-price"><?=$arBasketItems["PRICE_FORMATED"]?></td>
				<?endif;?>
				<?if (in_array("VAT", $arParams["COLUMNS_LIST"])):?>
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
				<?endif;?>
				<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-quantity"><?=$arBasketItems["QUANTITY"]?></td>
				<?endif;?>
				<td class="cart-item-actions">
					<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
						<a href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>" title="<?=GetMessage("SALE_DELETE_PRD")?>"><?=GetMessage("SALE_DELETE")?></a><br />
					<?endif;?>
					<?if (in_array("DELAY", $arParams["COLUMNS_LIST"])):?>
						<a href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["add"])?>"><?=GetMessage("SALE_ADD_CART")?></a>
					<?endif;?>
				</td>
			</tr>
			<?
		}
		?>
		</tbody>
		</table>
	<?else:
		echo ShowNote(GetMessage("SALE_NO_DELAY_PROD"));
	endif;?>
</div>
