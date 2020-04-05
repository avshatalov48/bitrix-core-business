<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<b><?= GetMessage("SALE_UNAVAIL_TITLE")?></b><br /><br />
<table class="sale_basket_basket data-table">
	<tr>
		<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_NAME")?></th>
		<?endif;?>
		<?if (in_array("PROPS", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_PROPS")?></th>
		<?endif;?>
		<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_PRICE")?></th>
		<?endif;?>
		<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_PRICE_TYPE")?></th>
		<?endif;?>
		<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_QUANTITY")?></th>
		<?endif;?>
		<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_DELETE")?></th>
		<?endif;?>
		<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
			<th align="center"><?echo GetMessage("SALE_WEIGHT")?></th>
		<?endif;?>
	</tr>
	<?
	foreach($arResult["ITEMS"]["nAnCanBuy"] as $arBasketItems)
	{
		?>
		<tr>
			<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
				<td><?
				if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):
					?><a href="<?=$arBasketItems["DETAIL_PAGE_URL"] ?>"><?
				endif;
				?><b><?=$arBasketItems["NAME"]?></b><?
				if (strlen($arBasketItems["DETAIL_PAGE_URL"])>0):
					?></a><?
				endif;
				?>
				</td>
			<?endif;?>
			<?if (in_array("PROPS", $arParams["COLUMNS_LIST"])):?>
				<td>
				<?
				foreach($arBasketItems["PROPS"] as $val)
				{
					echo $val["NAME"].": ".$val["VALUE"]."<br />";
				}
				?>
				</td>
			<?endif;?>
			<?if (in_array("PRICE", $arParams["COLUMNS_LIST"])):?>
				<td align="right"><?=$arBasketItems["PRICE_FORMATED"]?></td>
			<?endif;?>
			<?if (in_array("TYPE", $arParams["COLUMNS_LIST"])):?>
				<td><?=$arBasketItems["NOTES"]?></td>
			<?endif;?>
			<?if (in_array("QUANTITY", $arParams["COLUMNS_LIST"])):?>
				<td align="center"><?=$arBasketItems["QUANTITY"]?></td>
			<?endif;?>
			<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
				<td align="center"><input type="checkbox" name="DELETE_<?echo $arBasketItems["ID"] ?>" value="Y"></td>
			<?endif;?>
			<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
				<td align="right"><?=$arBasketItems["WEIGHT_FORMATED"] ?></td>
			<?endif;?>
		</tr>
		<?
	}
	?>
</table>

<br />
<div width="30%">
	<input type="submit" value="<?= GetMessage("SALE_REFRESH") ?>" name="BasketRefresh"><br />
	<small><?= GetMessage("SALE_REFRESH_DESCR") ?></small>
</div>
<br />
<?