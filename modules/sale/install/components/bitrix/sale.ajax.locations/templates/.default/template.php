<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$disabled = false;
if ($arParams["AJAX_CALL"] != "Y"
	&& count($arParams["LOC_DEFAULT"]) > 0
	&& $arParams["PUBLIC"] != "N"
	&& $arParams["SHOW_QUICK_CHOOSE"] == "Y"):
	$isChecked = "";
	$onCityChange = htmlspecialcharsbx(CUtil::JSEscape($arResult["ONCITYCHANGE"]));

	foreach ($arParams["LOC_DEFAULT"] as $val):
		$checked = "";

		if ((($val["ID"] == intval($_REQUEST["NEW_LOCATION_".$arParams["ORDER_PROPS_ID"]])) || ($val["ID"] == $arParams["CITY"])) && (!isset($_REQUEST["CHANGE_ZIP"]) || $_REQUEST["CHANGE_ZIP"] != "Y"))
		{
			$checked = "checked";
			$isChecked = "Y";
			$disabled = true;
		}?>

		<div><input onChange="if(window['<?=$onCityChange?>'] && typeof window['<?=$onCityChange?>'] === 'function') window['<?=$onCityChange?>']();" <?=$checked?> type="radio" name="NEW_LOCATION_<?=$arParams["ORDER_PROPS_ID"]?>" value="<?=$val["ID"]?>" id="loc_<?=$val["ID"]?>" /><label for="loc_<?=$val["ID"]?>"><?=$val["LOC_DEFAULT_NAME"]?></label></div>
	<?endforeach;?>

	<input <? if($isChecked!="Y") echo 'checked';?> type="radio" onclick="newlocation(<?=$arParams["ORDER_PROPS_ID"]?>);" name="NEW_LOCATION_<?=$arParams["ORDER_PROPS_ID"]?>" value="0" id="loc_0" /><label for="loc_0"><?=GetMessage("LOC_DEFAULT_NAME_NULL")?></label>
<?endif;?>

<?
if (isset($_REQUEST["NEW_LOCATION_".$arParams["ORDER_PROPS_ID"]]) && intval($_REQUEST["NEW_LOCATION_".$arParams["ORDER_PROPS_ID"]]) > 0)
	$disabled = true;
?>

<?if ($arParams["AJAX_CALL"] != "Y"):?><div id="LOCATION_<?=$arParams["CITY_INPUT_NAME"];?>"><?endif?>

<?
$countryName = "";
if (count($arResult["COUNTRY_LIST"]) == 1)
	$cDisabled = true;
else
	$cDisabled = false;
?>

<?if (count($arResult["COUNTRY_LIST"]) > 0):?>
	<?
	$onCityChange = htmlspecialcharsbx(CUtil::JSEscape($arResult["ONCITYCHANGE"]));

	if ($arResult["EMPTY_CITY"] == "Y" && $arResult["EMPTY_REGION"] == "Y")
		$change = "if(window['{$onCityChange}'] && typeof window['{$onCityChange}'] === 'function') window['{$onCityChange}']();";
	else
		$change = "getLocation(this.value, '', '', ".$arResult["JS_PARAMS"].", '".CUtil::JSEscape($arParams["SITE_ID"])."', '".$arParams['ADMIN_SECTION']."')";
	?>
	<?if($cDisabled):?>
		<div style="display:none">
	<?endif?>
	<select <?if($disabled || $cDisabled) echo "disabled";?> id="<?=$arParams["COUNTRY_INPUT_NAME"].$arParams["CITY_INPUT_NAME"]?>" name="<?=$arParams["COUNTRY_INPUT_NAME"].$arParams["CITY_INPUT_NAME"]?>" onChange="<?=$change?>" type="location">
		<option><?echo GetMessage('SAL_CHOOSE_COUNTRY')?></option>
		<?foreach ($arResult["COUNTRY_LIST"] as $arCountry):?>
			<option value="<?=$arCountry["ID"]?>"<?if ($arCountry["ID"] == $arParams["COUNTRY"]):?> selected="selected"<?endif;?>><?=$arCountry["NAME_LANG"]?></option>
			<?if ($arCountry["ID"] == $arParams["COUNTRY"]) $countryName = $arCountry["NAME_LANG"];?>
		<?endforeach;?>
	</select>
	<?if($cDisabled):?>
		</div>
		<?if($countryName <> ''):?>
			<div class="sale_locations_fixed"><?=GetMessage("SAL_LOC_COUNTRY").": ".$countryName."<br>"?></div>
		<?endif;?>
	<?endif?>

<?endif;?>

<?
$regionName = "";
if (count($arResult["REGION_LIST"]) == 1)
	$rDisabled = true;
else
	$rDisabled = false;

if (count($arResult["COUNTRY_LIST"]) <= 0 && count($arResult["REGION_LIST"]) <= 0)
{
	$idAttrValue = $arParams["COUNTRY_INPUT_NAME"];
}
else
{
	$idAttrValue = $arParams["CITY_INPUT_NAME"];
}
?>
<?if (count($arResult["REGION_LIST"]) > 0):?>
	<?
	$id = "";
	if (count($arResult["COUNTRY_LIST"]) <= 0):
		$id = "id=\"".$arParams["COUNTRY_INPUT_NAME"].$arParams["CITY_INPUT_NAME"]."\"";
	endif;?>

	<?
	$onCityChange = htmlspecialcharsbx(CUtil::JSEscape($arResult["ONCITYCHANGE"]));

	if ($arResult["EMPTY_CITY"] == "Y")
		$change = "if(window['{$onCityChange}'] && typeof window['{$onCityChange}'] === 'function') window['{$onCityChange}']();";
	else
		$change = "decideRegionOrCity(".$arParams["COUNTRY"].", this.value, '', ".$arResult["JS_PARAMS"].", '".CUtil::JSEscape($arParams["SITE_ID"])."', '".$arParams['ADMIN_SECTION']."', '".$idAttrValue."')";
	?>

	<?if($rDisabled):?>
		<div style="display:none">
	<?endif?>
	<select <?=$id?> <?if($disabled || $rDisabled) echo "disabled";?> name="<?=$arParams["REGION_INPUT_NAME"].$arParams["CITY_INPUT_NAME"]?>" onChange="<?=$change?>" type="location">
		<option><?echo GetMessage('SAL_CHOOSE_REGION')?></option>
		<?foreach ($arResult["REGION_LIST"] as $arRegion):?>
			<option value="<?=$arRegion["ID"]?>"<?if ($arRegion["ID"] == $arParams["REGION"]):?> selected="selected"<?endif;?>><?=$arRegion["NAME_LANG"]?></option>
			<?if ($arRegion["ID"] == $arParams["REGION"]) $regionName = $arRegion["NAME_LANG"];?>
		<?endforeach;?>
	</select>
	<?if($rDisabled):?>
		</div>
		<?if($regionName <> ''):?>
			<div class="sale_locations_fixed"><?=GetMessage("SAL_LOC_REGION").": ".$regionName?></div>
		<?endif;?>
	<?endif?>
<?endif;?>

<?if (count($arResult["CITY_LIST"]) > 0):?>
	<?

	$cityName = "";
	$id = "";

	if (count($arResult["CITY_LIST"]) == 1)
		$cDisabled = true;
	else
		$cDisabled = false;

	$id = "id=\"".$idAttrValue."\"";
	?>

	<?if($cDisabled):?>
		<div style="display:none">
	<?endif?>

	<?if(isset($arResult["ONCITYCHANGE"]) && (string)$arResult["ONCITYCHANGE"] !== ''):?>
		<?$onCityChange = htmlspecialcharsbx(CUtil::JSEscape($arResult["ONCITYCHANGE"]));?>
		<?$change = "if(window['{$onCityChange}'] && typeof window['{$onCityChange}'] === 'function') window['{$onCityChange}']();";?>
	<?else:?>
		<?$change = "";?>
	<?endif?>

	<select <?=$id?> <?if($disabled) echo "disabled";?> name="<?=$arParams["CITY_INPUT_NAME"]?>"<?if($change <> ''):?> onchange="<?=$change?>"<?endif;?> type="location">
		<option><?echo GetMessage('SAL_CHOOSE_CITY')?></option>
		<?foreach ($arResult["CITY_LIST"] as $arCity):?>
			<option value="<?=$arCity["ID"]?>"<?if ($arCity["ID"] == $arParams["CITY"]):?> selected="selected"<?endif;?>><?=($arCity['CITY_ID'] > 0 ? $arCity["CITY_NAME"] : GetMessage('SAL_CHOOSE_CITY_OTHER'))?></option>
			<?if($arCity["ID"] == $arParams["CITY"]) $cityName = $arCity["CITY_NAME"];?>
		<?endforeach;?>
	</select>
	<?
	if($cDisabled):?>
		</div>
		<?if($cityName <> ''):?>
			<div class="sale_locations_fixed"><?=GetMessage("SAL_LOC_CITY").": ".$cityName?></div>
		<?endif;?>
	<?endif?>

<?endif;?>

<?if ($arParams["AJAX_CALL"] != "Y"):?></div><div id="wait_container_<?=$arParams["CITY_INPUT_NAME"]?>" style="display: none;"></div><?endif;?>

<?if ($arParams["AJAX_CALL"] != "Y" && $arParams["PUBLIC"] != "N"):?>
<script>
	function newlocation(orderPropId)
	{
		var select = document.getElementById("LOCATION_ORDER_PROP_" + orderPropId);

		arSelect = select.getElementsByTagName("select");
		if (arSelect.length > 0)
		{
			for (var i in arSelect)
			{
				var elem = arSelect[i];
				elem.disabled = false;
			}
		}
	}

	function decideRegionOrCity(c, value, hz, jsParams, siteId, admin, idAttrValue)
	{
		if(value > 0)
		{
			getLocation.apply(window, arguments);
		}
		else if(<?=mb_strlen($arResult['ONCITYCHANGE'])?> > 0)
		{
			var citySelector = BX(idAttrValue);

			if(BX.type.isElementNode(citySelector))
			{
				var firstOpt = citySelector.querySelector('option');
				if(BX.type.isElementNode(firstOpt))
				{
					firstOpt.value = -1*value;
					firstOpt.selected = 'selected';

					BX.hide(citySelector);
					var onCityChangeCallback = '<?=CUtil::JSEscape($arResult["ONCITYCHANGE"])?>';

					if(window[onCityChangeCallback] && typeof window[onCityChangeCallback] === 'function')
					{
						window[onCityChangeCallback]();
					}
				}
			}
		}
	}
</script>
<?endif;?>