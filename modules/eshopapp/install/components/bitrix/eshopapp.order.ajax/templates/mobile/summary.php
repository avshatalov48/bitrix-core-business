<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="order_infoblock close" onclick="OpenClose(this);">
	<div class="order_infoblock_title"><?=GetMessage("SOA_TEMPL_SUM_TITLE")?><span></span></div>
	<?
	foreach($arResult["BASKET_ITEMS"] as $arBasketItems)
	{
		?>
	<div class="order_detail_container">
		<div class="order_infoblock_title"><?=$arBasketItems["NAME"]?></div>
		<table class="order_detail_table">
			<tr>
				<td class="order_detail_table_td_img">
					<img src="<?=$arBasketItems["DETAIL_PICTURE"]["SRC"]?>" alt="">
				</td>
				<td>
					<table class="order_detail_table_td_table">
						<thead>
							<tr>
								<!--<td><?=GetMessage("SOA_TEMPL_SUM_PROPS")?></td>
								<td><?=GetMessage("SOA_TEMPL_SUM_PRICE_TYPE")?></td>-->
								<td><?=GetMessage("SOA_TEMPL_SUM_DISCOUNT")?></td>
								<!--<td><?=GetMessage("SOA_TEMPL_SUM_WEIGHT")?></td>-->
								<td><?=GetMessage("SOA_TEMPL_SUM_QUANTITY")?></td>
								<td><?=GetMessage("SOA_TEMPL_SUM_PRICE")?></td>
							</tr>
						</thead>

						<tbody>
							<tr>
								<!--<td>
									<?
									foreach($arBasketItems["PROPS"] as $val)
									{
										echo $val["NAME"].": ".$val["VALUE"]."<br />";
									}
									?>
								</td>
								<td><?=$arBasketItems["NOTES"]?></td>-->
								<td><?=$arBasketItems["DISCOUNT_PRICE_PERCENT_FORMATED"]?></td>
								<!--<td><?=$arBasketItems["WEIGHT_FORMATED"]?></td>-->
								<td><?=$arBasketItems["QUANTITY"]?></td>
								<td align="right"><?=$arBasketItems["PRICE_FORMATED"]?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
	</div>
		<?
	}
	?>
	<div class="order_detail_container_itogi">
		<table class="order_detail_container_itogi_table">
			<tr><td><?=GetMessage("SOA_TEMPL_SUM_WEIGHT_SUM")?></td>
			<td align="right"><?=$arResult["ORDER_WEIGHT_FORMATED"]?></td></tr>

			<tr><td><?=GetMessage("SOA_TEMPL_SUM_SUMMARY")?></td>
				<td align="right"><?=$arResult["ORDER_PRICE_FORMATED"]?></td></tr>

			<?
			if (doubleval($arResult["DISCOUNT_PRICE"]) > 0)
			{
				?>
				<tr>
					<td><?=GetMessage("SOA_TEMPL_SUM_DISCOUNT")?><?if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0):?> (<?echo $arResult["DISCOUNT_PERCENT_FORMATED"];?>)<?endif;?>:</td>
					<td align="right"><?echo $arResult["DISCOUNT_PRICE_FORMATED"]?>
					</td>
				</tr>
				<?
			}

			if(!empty($arResult["arTaxList"]))
			{
			foreach($arResult["arTaxList"] as $val)
			{
			?>
			<tr>
				<td align="right"><?=$val["NAME"]?> <?=$val["VALUE_FORMATED"]?>:</td>
				<td align="right" ><?=$val["VALUE_MONEY_FORMATED"]?></td>
			</tr>
			<?
			}
			}
			if (doubleval($arResult["DELIVERY_PRICE"]) > 0)
			{
				?>
				<tr>
					<td align="right">
						<?=GetMessage("SOA_TEMPL_SUM_DELIVERY")?>
					</td>
					<td align="right"><?=$arResult["DELIVERY_PRICE_FORMATED"]?></td>
				</tr>
				<?
			}
			?>
			<tr class="order_detail_container_itogi_table_td_green">
				<td align="right"><b><?=GetMessage("SOA_TEMPL_SUM_IT")?></b></td>
				<td align="right"><b><?=$arResult["ORDER_TOTAL_PRICE_FORMATED"]?></b>
				</td>
			</tr>
			<?
			if (strlen($arResult["PAYED_FROM_ACCOUNT_FORMATED"]) > 0)
			{
				?>
				<tr>
					<td align="right"><b><?=GetMessage("SOA_TEMPL_SUM_PAYED")?></b></td>
					<td align="right"><?=$arResult["PAYED_FROM_ACCOUNT_FORMATED"]?></td>
				</tr>
				<?
			}
			?>
		</table>
	</div>
</div>

<div class="order_item_description close" >
	<h3 onclick="OpenClose(this.parentNode);"><?=GetMessage("SOA_TEMPL_SUM_ADIT_INFO")?> <span class="order_item_arrow"></span></h3>
	<div class="ordering_container">
		<div class="ordering_li_container">
			<p><?=GetMessage("SOA_TEMPL_SUM_COMMENTS")?></p>
			<textarea rows="4" cols="40" name="ORDER_DESCRIPTION"><?=htmlspecialcharsbx($arResult["USER_VALS"]["ORDER_DESCRIPTION"])?></textarea>
		</div>
	</div>
</div>