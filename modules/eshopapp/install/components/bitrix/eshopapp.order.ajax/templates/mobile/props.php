<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
function PrintPropsForm($arSource=Array(), $locationTemplate = ".default")
{
	if (!empty($arSource))
	{
		?>

		<?
		foreach($arSource as $arProperties)
		{
			/*if($arProperties["SHOW_GROUP_NAME"] == "Y")
			{
				?>
				<tr>
					<td colspan="2">
						<b><?= $arProperties["GROUP_NAME"] ?></b>
					</td>
				</tr>
				<?
			}    */
			?>

					<span class="inputtext">
					<?= $arProperties["NAME"] ?>:<?
					if($arProperties["REQUIED_FORMATED"]=="Y")
					{
						?><span class="sof-req">*</span><?
					}
					?>
					</span>

					<?
					if($arProperties["TYPE"] == "CHECKBOX")
					{
						?>

						<input type="hidden" name="<?=$arProperties["FIELD_NAME"]?>" value="">
						<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if ($arProperties["CHECKED"]=="Y") echo " checked";?>>
						<?
					}
					elseif($arProperties["TYPE"] == "TEXT")
					{
						?>
						<input type="text" maxlength="250" size="<?=$arProperties["SIZE1"]?>" value="<?=$arProperties["VALUE"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>">
						<?
					}
					elseif($arProperties["TYPE"] == "SELECT")
					{
						?>
						<select name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
						<?
						foreach($arProperties["VARIANTS"] as $arVariants)
						{
							?>
							<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
							<?
						}
						?>
						</select>
						<?
					}
					elseif ($arProperties["TYPE"] == "MULTISELECT")
					{
						?>
						<select multiple name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
						<?
						foreach($arProperties["VARIANTS"] as $arVariants)
						{
							?>
							<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
							<?
						}
						?>
						</select>
						<?
					}
					elseif ($arProperties["TYPE"] == "TEXTAREA")
					{
						?>
						<textarea style="max-height:100px" rows="<?=$arProperties["SIZE2"]?>" cols="<?=$arProperties["SIZE1"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>"><?=$arProperties["VALUE"]?></textarea>
						<?
					}
					elseif ($arProperties["TYPE"] == "LOCATION")
					{
						//_print_r('L: '.$arProperties["VALUE"].' ------------ '.rand(99, 9999));

						$value = 0;
						if (is_array($arProperties["VARIANTS"]) && count($arProperties["VARIANTS"]) > 0)
						{
							foreach ($arProperties["VARIANTS"] as $arVariant)
							{
								if ($arVariant["SELECTED"] == "Y")
								{
									$value = $arVariant["ID"];
									break;
								}
							}
						}

						CSaleLocation::proxySaleAjaxLocationsComponent(
							array(
								"AJAX_CALL" => "N",
								"COUNTRY_INPUT_NAME" => "COUNTRY",//.$arProperties["FIELD_NAME"],
								"REGION_INPUT_NAME" => "REGION",//.$arProperties["FIELD_NAME"],
								"CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
								"CITY_OUT_LOCATION" => "Y",
								"SHOW_QUICK_CHOOSE" => "N",
								"LOCATION_VALUE" => $value,
								"ORDER_PROPS_ID" => $arProperties["ID"],
								"ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitForm()" : "",
								"SIZE1" => $arProperties["SIZE1"],
							),
							array(
								"CODE" => "",
								"ID" => $arProperties["VALUE"],
								"JS_CALLBACK" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitFormProxy" : ""
							),
							$locationTemplate,
							true,
							'locationpro-selector-wrapper'
						);

					}
					elseif ($arProperties["TYPE"] == "RADIO")
					{
						foreach($arProperties["VARIANTS"] as $arVariants)
						{
							?>
							<input type="radio" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>" value="<?=$arVariants["VALUE"]?>"<?if($arVariants["CHECKED"] == "Y") echo " checked";?>> <label for="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"><?=$arVariants["NAME"]?></label><br />
							<?
						}
					}

					if (strlen($arProperties["DESCRIPTION"]) > 0)
					{
						?><br /><small><?echo $arProperties["DESCRIPTION"] ?></small><?
					}
					?>
			<?
		}
		?>

		<?
		return true;
	}
	return false;
}

$classClose = false;
if (!empty($arResult["ORDER_PROP"]["USER_PROFILES"]))
{
	foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $profile)
	{
		if ($profile["CHECKED"] == "Y")
			$classClose = true;
	}
}
?>

<div class="order_item_description <?if ($classClose) echo "close"?>">
	<h3 onclick="OpenClose(this.parentNode);"><?=GetMessage("SOA_TEMPL_PROP_INFO")?><span class="order_item_arrow"></span></h3>
	<div class="ordering_container">
<?
if(!empty($arResult["ORDER_PROP"]["USER_PROFILES"]))
{
	$noChoosenProfile = true;
	foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles)
	{
		if ($arUserProfiles["CHECKED"]=="Y")
			$noChoosenProfile = false;
	}
	?>
	<p class="p_small"><?=GetMessage("SOA_TEMPL_PROP_CHOOSE")?></p>
	<ul>
		<li>
			<div class="ordering_li_container <?if ($noChoosenProfile) echo "checked"?>">
				<table>
					<tr>
						<td><span class="inputradio"><input name="PROFILE_ID" value="0" type="radio" id="new_profile" onclick="SetContact(this.value)" <?if ($noChoosenProfile) echo "checked"?>></span></td>
						<td><label for="new_profile"><span><?=GetMessage("SOA_TEMPL_PROP_NEW_PROFILE")?></span></label></td>
					</tr>
				</table>
			</div>
		</li>
		<?
		foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles)
		{
		?>
		<li>
			<div class="ordering_li_container <?if ($arUserProfiles["CHECKED"]=="Y") echo "checked"?>">
				<table>
					<tr>
						<td><span class="inputradio"><input name="PROFILE_ID" value="<?= $arUserProfiles["ID"] ?>" type="radio" id="profile_<?= $arUserProfiles["ID"] ?>" onclick="SetContact(this.value)" <?if ($arUserProfiles["CHECKED"]=="Y") echo " checked";?>></span></td>
						<td><label for="profile_<?= $arUserProfiles["ID"]?>"><span><?=$arUserProfiles["NAME"]?></span></label></td>
					</tr>
				</table>
			</div>
		</li>
		<?
		}
		?>
	</ul>

	<!--<select name="PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
		<option value="0"><?=GetMessage("SOA_TEMPL_PROP_NEW_PROFILE")?></option>
		<?
		foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles)
		{
			?>
			<option value="<?= $arUserProfiles["ID"] ?>"<?if ($arUserProfiles["CHECKED"]=="Y") echo " selected";?>><?=$arUserProfiles["NAME"]?></option>
			<?
		}
		?>
	</select> -->

	<?
}
/*
?>
<div style="display:none;">
<?
	$APPLICATION->IncludeComponent(
		"bitrix:sale.ajax.locations",
		".default",
		array(
			"AJAX_CALL" => "N",
			"COUNTRY_INPUT_NAME" => "COUNTRY_tmp",
			"REGION_INPUT_NAME" => "REGION_tmp",
			"CITY_INPUT_NAME" => "tmp",
			"CITY_OUT_LOCATION" => "Y",
			"LOCATION_VALUE" => "",
			"ONCITYCHANGE" => "",
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
?>
</div>
<?
*/
?>
<div class="ordering_li_container">
<?
PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_N"], $arParams["TEMPLATE_LOCATION"]);
PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_Y"], $arParams["TEMPLATE_LOCATION"]);
?>
	</div>
	</div>
</div>