<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(
	$arParams["arUserField"]["ENTITY_VALUE_ID"] <= 0
	&& $arParams["arUserField"]["SETTINGS"]["DEFAULT_VALUE"] > 0
)
{
	$arResult['VALUE'] = array($arParams["arUserField"]["SETTINGS"]["DEFAULT_VALUE"]);
}
else
{
	$arResult['VALUE'] = array_filter($arResult["VALUE"]);
}
if($arParams['arUserField']["SETTINGS"]["DISPLAY"] != "CHECKBOX")
{
	if($arParams["arUserField"]["MULTIPLE"] == "Y")
	{
		?>
		<select multiple="multiple" name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>" size="<?echo $arParams["arUserField"]["SETTINGS"]["LIST_HEIGHT"]?>" <?=($arParams["arUserField"]["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ':'')?> >
		<?
		foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val)
		{
			$bSelected = in_array($key, $arResult["VALUE"]);
			?>
			<option value="<?echo $key?>" <?echo ($bSelected? "selected" : "")?> title="<?echo trim($val, " .")?>"><?echo $val?></option>
			<?
		}
		?>
		</select>
		<?
	}
	else
	{
		?>
		<select name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>" size="<?echo $arParams["arUserField"]["SETTINGS"]["LIST_HEIGHT"]?>" <?=($arParams["arUserField"]["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ':'')?> >
		<?
		$bWasSelect = false;
		foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val)
		{
			if($bWasSelect)
				$bSelected = false;
			else
				$bSelected = in_array($key, $arResult["VALUE"]);

			if($bSelected)
				$bWasSelect = true;
			?>
			<option value="<?echo $key?>" <?echo ($bSelected? "selected" : "")?> title="<?echo trim($val, " .")?>"><?echo $val?></option>
			<?
		}
		?>
		</select>
		<?
	}
}
else
{
	if($arParams["arUserField"]["MULTIPLE"] == "Y")
	{
		?>
		<input type="hidden" value="" name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>">
		<?
		foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val)
		{
			$id = $arParams["arUserField"]["FIELD_NAME"]."_".$key;

			$bSelected = in_array($key, $arResult["VALUE"]);
			?>
			<input type="checkbox" value="<?echo $key?>" name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>" <?echo ($bSelected? "checked" : "")?> id="<?echo $id?>"><label for="<?echo $id?>"><?echo $val?></label><br />
			<?
		}
	}
	else
	{
		if($arParams["arUserField"]["MANDATORY"] != "Y")
		{
			$id = $arParams["arUserField"]["FIELD_NAME"]."_no";
			?>
			<input type="radio" value="" name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>" id="<?echo $id?>"><label for="<?echo $id?>"><?echo GetMessage("MAIN_NO")?></label><br />
			<?
		}

		$bWasSelect = false;
		foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val)
		{
			$id = $arParams["arUserField"]["FIELD_NAME"]."_".$key;

			if($bWasSelect)
				$bSelected = false;
			else
				$bSelected = in_array($key, $arResult["VALUE"]);

			if($bSelected)
				$bWasSelect = true;
			?>
			<input type="radio" value="<?echo $key?>" name="<?echo $arParams["arUserField"]["FIELD_NAME"]?>" <?echo ($bSelected? "checked" : "")?> id="<?echo $id?>"><label for="<?echo $id?>"><?echo $val?></label><br />
			<?
		}
	}
}
?>