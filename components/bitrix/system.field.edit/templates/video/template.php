<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
if ($arParams["arUserField"]["MULTIPLE"] == "Y")
{
	$tmpName = "bx_tmp_field_div_name[]";
	echo "<div class='bx-tmp-field-div' style='display: none;'>".CUserTypeVideo::GetEditFormHTML(array("SETTINGS" => $arParams['arUserField']["SETTINGS"]),	array("NAME" => $tmpName, "VALUE" => ""))."</div>";

	for($i = 0, $l = count($arParams['arUserField']["VALUE"]); $i < $l; $i++)
	{
		$val = $arParams['arUserField']["VALUE"][$i];
		$name = str_replace("[]", "[".$i."]", $arParams['arUserField']["FIELD_NAME"]);
		if ($val != "")
		{
			echo CUserTypeVideo::GetEditFormHTML(
				array(
					"SETTINGS" => $arParams['arUserField']["SETTINGS"]
				),
				array(
					"NAME" => $name,
					"VALUE" => $val
				)
			);
			echo "\n<br />\n";
		}
	}

	if ($arParams["SHOW_BUTTON"] != "N"):?>
		<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElementVideo('<?=$arParams["arUserField"]["FIELD_NAME"]?>', this, '<?= $tmpName?>')" />
		<input type="hidden" value="<?= count($arParams['arUserField']["VALUE"]) - 1?>" />
	<?endif;
}
else
{
	echo CUserTypeVideo::GetEditFormHTML(
		array(
			"SETTINGS" => $arParams['arUserField']["SETTINGS"]
		),
		array(
			"NAME" => $arParams['arUserField']["FIELD_NAME"],
			"VALUE" => $arParams['arUserField']["VALUE"]
		)
	);
}
?>