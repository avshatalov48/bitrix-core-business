<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arParams
 * @var array $arResult
 */

foreach (GetModuleEvents("main", "system.field.edit.file", true) as $arEvent)
{
	if (ExecuteModuleEventEx($arEvent, array($arResult, $arParams)))
		return;
}

?>
<div id="main_<?=$arParams["arUserField"]["FIELD_NAME"]?>">
<?
$postFix = ($arParams["arUserField"]["MULTIPLE"] == "Y" ? "[]" : "");

if($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N" && $arParams["bVarsFromForm"])
{
	//multiple - we have additional hidden to clone, need to remove it
	array_pop($arResult["VALUE"]);
}

foreach ($arResult["VALUE"] as $res):
	?>
	<div class="fields files">
		<input type="hidden" name="<?=$arParams["arUserField"]["~FIELD_NAME"]?>_old_id<?=$postFix?>" value="<?=$res?>" />
		<?=CFile::InputFile($arParams["arUserField"]["FIELD_NAME"], 0, $res, false, 0, "", "", 0, "", ' value="'.$res.'"', true, isset($arParams['SHOW_FILE_PATH']) ? $arParams['SHOW_FILE_PATH'] : true);?>
		<br>
<?
$arFile = CFile::GetFileArray($res);
if($arFile)
{
	if(CFile::IsImage($arFile["SRC"], $arFile["CONTENT_TYPE"]))
	{
		echo CFile::ShowImage(
			$arFile,
			isset($arParams["FILE_MAX_WIDTH"]) ? (int)$arParams["FILE_MAX_WIDTH"] : 0,
			isset($arParams["FILE_MAX_HEIGHT"]) ? (int)$arParams["FILE_MAX_HEIGHT"] : 0,
			null,
			'',
			false,
			0,
			0,
			0,
			!empty($arParams['FILE_URL_TEMPLATE']) ? $arParams['FILE_URL_TEMPLATE'] : ''
		);
	}
	else
	{
		if($arParams['FILE_URL_TEMPLATE'] <> '')
		{
			$src = CComponentEngine::MakePathFromTemplate($arParams['FILE_URL_TEMPLATE'], array('file_id' => $arFile["ID"]));
		}
		else
		{
			$src = $arFile["SRC"];
		}
		echo '<a href="'.htmlspecialcharsbx($src).'">'.htmlspecialcharsbx($arFile["FILE_NAME"]).'</a> ('.CFile::FormatSize($arFile["FILE_SIZE"]).')';
	}
}
?>
	</div>
	<?
endforeach;
?>
</div>
<?if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"):?>
<div style="display:none" id="main_add_<?=$arParams["arUserField"]["FIELD_NAME"]?>" class="fields files">
	<input type="hidden" name="<?=$arParams["arUserField"]["~FIELD_NAME"]?>_old_id[]" value="" />
	<?=CFile::InputFile($arParams["arUserField"]["FIELD_NAME"], 0, "")?>
</div>
<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElementFile('<?=$arParams["arUserField"]["FIELD_NAME"]?>', this)">
<?endif;?>
