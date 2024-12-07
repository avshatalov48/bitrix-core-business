<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="cart-items" id="id-sub-list" style="display:none;">
	<div class="inline-filter cart-filter">
		<label><?=GetMessage("SALE_PRD_IN_BASKET")?></label>&nbsp;
			<a href="javascript:void(0);" onclick="ShowBasketItems(1);"><?=GetMessage("SALE_PRD_IN_BASKET_ACT")?> (<?=count($arResult["ITEMS"]["AnDelCanBuy"])?>)</a>&nbsp;
			<a href="javascript:void(0);" onclick="ShowBasketItems(2);"><?=GetMessage("SALE_PRD_IN_BASKET_SHELVE")?> (<?=count($arResult["ITEMS"]["DelDelCanBuy"])?>)</a>
			<a href="javascript:void(0);" onclick="ShowBasketItems(4);"><?=GetMessage("SALE_NOACTIVE")?> (<?=count($arResult["ITEMS"]["nAnCanBuy"])?>)</a>
			<b><?=GetMessage("SALE_BASKET_NOTIFY")?></b>&nbsp;
	</div>
	
	<?if(count($arResult["ITEMS"]["AnSubscribe"]) > 0):?>
		<table class="cart-items" cellspacing="0">
		<thead>
			<tr>
				<?if (in_array("NAME", $arParams["COLUMNS_LIST"])):?>
					<td class="cart-item-name"><?= GetMessage("SALE_NAME")?></td>
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
		foreach($arResult["ITEMS"]["AnSubscribe"] as $arBasketItems)
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
				<td class="cart-item-actions">
					<?if (in_array("DELETE", $arParams["COLUMNS_LIST"])):?>
						<a href="<?=str_replace("#ID#", $arBasketItems["ID"], $arUrlTempl["delete"])?>" title="<?=GetMessage("SALE_DELETE_PRD")?>"><?=GetMessage("SALE_DELETE")?></a><br>
					<?endif;?>
				</td>
			</tr>
			<?
		}
		?>
		</tbody>
		</table>
	<?else:
		ShowNote(GetMessage("SALE_NO_SUBSCRIBE_PROD"));
	endif;?>
</div>
