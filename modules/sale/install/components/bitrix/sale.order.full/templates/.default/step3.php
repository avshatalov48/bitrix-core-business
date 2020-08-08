<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table border="0" cellspacing="0" cellpadding="5">
<tr>
	<td valign="top" width="60%" align="right">
		<input type="submit" name="contButton" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
	</td>
	<td valign="top" width="5%" rowspan="3">&nbsp;</td>
	<td valign="top" width="35%" rowspan="3">

		<?echo GetMessage("STOF_DELIVERY_NOTES")?><br /><br />
		<?echo GetMessage("STOF_PRIVATE_NOTES")?>

	</td>
</tr>
<tr>
	<td valign="top" width="60%">
		<b><?echo GetMessage("STOF_DELIVERY_PROMT")?></b><br /><br />
		<table class="sale_order_full_table">
			<tr>
				<td colspan="2"><?echo GetMessage("STOF_SELECT_DELIVERY")?><br /><br /></td>
			</tr>
			<?
				foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
				{
					if ($delivery_id !== 0 && intval($delivery_id) <= 0):
				?>
				<tr>
					<td colspan="2">
						<b><?=$arDelivery["TITLE"]?></b><?if ($arDelivery["DESCRIPTION"] <> ''):?><br />
						<?=nl2br($arDelivery["DESCRIPTION"])?><br /><?endif;?>
						<table border="0" cellspacing="0" cellpadding="3">

					<?
						foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
						{
							?>
					<tr>
						<td width="20" nowrap="nowrap">&nbsp;</td>
						<td width="0%" valign="top"><input type="radio" id="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>" name="<?=$arProfile["FIELD_NAME"]?>" value="<?=$delivery_id.":".$profile_id;?>" <?=$arProfile["CHECKED"] == "Y" ? "checked=\"checked\"" : "";?> /></td>
						<td width="50%" valign="top">
							<label for="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>">
								<small><b><?=$arProfile["TITLE"]?></b><?if ($arProfile["DESCRIPTION"] <> ''):?><br />
								<?=nl2br($arProfile["DESCRIPTION"])?><?endif;?></small>
							</label>
						</td>
						<td width="50%" valign="top" align="right">
						<?
							$APPLICATION->IncludeComponent('bitrix:sale.ajax.delivery.calculator', '', array(
								"NO_AJAX" => $arParams["SHOW_AJAX_DELIVERY_LINK"] == 'S' ? 'Y' : 'N',
								"DELIVERY" => $delivery_id,
								"PROFILE" => $profile_id,
								"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
								"ORDER_PRICE" => $arResult["ORDER_PRICE"],
								"LOCATION_TO" => $arResult["DELIVERY_LOCATION"],
								"LOCATION_ZIP" => $arResult['DELIVERY_LOCATION_ZIP'],
								"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
								"ITEMS" => $arResult["BASKET_ITEMS"],
							));
						?>
						<?if ($arParams["SHOW_AJAX_DELIVERY_LINK"] == 'N'):?>
						<script type="text/javascript">deliveryCalcProceed({STEP:1,DELIVERY:'<?=CUtil::JSEscape($delivery_id)?>',PROFILE:'<?=CUtil::JSEscape($profile_id)?>',WEIGHT:'<?=CUtil::JSEscape($arResult["ORDER_WEIGHT"])?>',PRICE:'<?=CUtil::JSEscape($arResult["ORDER_PRICE"])?>',LOCATION:'<?=intval($arResult["DELIVERY_LOCATION"])?>',CURRENCY:'<?=CUtil::JSEscape($arResult["BASE_LANG_CURRENCY"])?>'})</script>
						<?endif;?>
						</td>
					</tr>
							<?
						} // endforeach
					?>
						</table>


					</td>
				</tr>
				<?
					else:
?>
					<tr>
						<td valign="top" width="0%">
							<input type="radio" id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>" name="<?=$arDelivery["FIELD_NAME"]?>" value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y") echo " checked";?>>
						</td>
						<td valign="top" width="100%">
							<label for="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>">
							<b><?= $arDelivery["NAME"] ?></b><br />
							<?
							if ($arDelivery["PERIOD_TEXT"] <> '')
							{
								echo $arDelivery["PERIOD_TEXT"];
								?><br /><?
							}
							?>
							<?=GetMessage("SALE_DELIV_PRICE");?> <?=$arDelivery["PRICE_FORMATED"]?><br />
							<?
							if ($arDelivery["DESCRIPTION"] <> '')
							{
								?>
								<?=$arDelivery["DESCRIPTION"]?><br />
								<?
							}
							?>
							</label>
						</td>
					</tr>
					<?
					endif;

				} // endforeach
			?>
			<?
			//endif;
			?>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" width="60%" align="right">
	<?if(!($arResult["SKIP_FIRST_STEP"] == "Y" && $arResult["SKIP_SECOND_STEP"] == "Y"))
	{
		?>
		<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>">
		<?
	}
	?>
		<input type="submit" name="contButton" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
	</td>
</tr>
</table>