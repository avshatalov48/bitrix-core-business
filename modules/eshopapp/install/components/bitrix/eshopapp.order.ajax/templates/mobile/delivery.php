<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult["DELIVERY"]))
{
	?>

	<div class="order_item_description">
		<h3><?=GetMessage("SOA_TEMPL_DELIVERY")?></h3>
		<div class="ordering_container">
			<ul>
		<?
		foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
		{
			if ($delivery_id !== 0 && intval($delivery_id) <= 0)
			{
				foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
				{
				?>
				<li>
					<table class="postservice">
						<tbody>
							<tr>
								<td>
									<div class="ordering_li_container <?if ($arProfile["CHECKED"] == "Y") echo "checked";?>">
										<table>
										<tr>
											<td>
												<span class="inputradio"><input type="radio" id="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>" name="<?=$arProfile["FIELD_NAME"]?>" value="<?=$delivery_id.":".$profile_id;?>" <?=$arProfile["CHECKED"] == "Y" ? "checked=\"checked\"" : "";?> <?=($arParams["DELIVERY_TO_PAYSYSTEM"]=="d2p")?"onClick=\"submitForm();\"":"";?> /></span>
											</td>
											<td>
												<label for="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>">
													<span class="posttitle"><?=$arDelivery["TITLE"]?></span>
													<span class="posttarif"><?=$arProfile["TITLE"]?></span>
													<?if (strlen($arProfile["DESCRIPTION"]) > 0):?>
														<span class="postdescription"><?=nl2br($arProfile["DESCRIPTION"])?></span>
													<?endif;?>
												</label>
											</td>
										</tr>
										</table>
									</div>
								</td>
								<td width="50%" align="right">
								<?
									$APPLICATION->IncludeComponent('bitrix:eshopapp.ajax.delivery.calculator', '', array(
										"NO_AJAX" => $arParams["DELIVERY_NO_AJAX"],
										"DELIVERY" => $delivery_id,
										"PROFILE" => $profile_id,
										"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
										"ORDER_PRICE" => $arResult["ORDER_PRICE"],
										"LOCATION_TO" => $arResult["USER_VALS"]["DELIVERY_LOCATION"],
										"LOCATION_ZIP" => $arResult["USER_VALS"]["DELIVERY_LOCATION_ZIP"],
										"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
									), null, array('HIDE_ICONS' => 'Y'));
								?>
								</td>
							</tr>
						</tbody>
					</table>
				</li>
				<?
				} // endforeach
			}
			else
			{
				?>
				<li>
					<table class="postservice">
						<tbody>
						<tr>
							<td>
								<div class="ordering_li_container <?if ($arDelivery["CHECKED"]=="Y") echo " checked";?>">
									<table>
										<tr>
											<td>
												<span class="inputradio"><input type="radio" id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>" name="<?=$arDelivery["FIELD_NAME"]?>" value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y") echo " checked";?> <?=($arParams["DELIVERY_TO_PAYSYSTEM"]=="d2p")?"onClick=\"submitForm();\"":"";?>></span>
											</td>
											<td>
												<label for="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>">
													<span class="posttarif"><?= $arDelivery["NAME"] ?></span>
													<?
													if (strlen($arDelivery["DESCRIPTION"])>0)
													{
														?>
														<span class="postdescription"><?=$arDelivery["DESCRIPTION"]?></span>
														<?
													}
													?>
												</label>
											</td>
										</tr>
									</table>
								</div>
							</td>
							<td width="50%" align="right">
								<?
								if (strlen($arDelivery["PERIOD_TEXT"])>0)
								{
									echo $arDelivery["PERIOD_TEXT"];
									?><br /><?
								}
								?>
								<span class="fwb"><?=$arDelivery["PRICE_FORMATED"]?></span>
							</td>
						</tr>
						</tbody>
					</table>
				</li>
				<?
			}
		}
		?>
			</ul>
		</div>
	</div>
	<?
}
?>