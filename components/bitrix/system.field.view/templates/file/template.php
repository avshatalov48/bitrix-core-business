<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @param array $arParams
 * @param array $arResult
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach(GetModuleEvents("main", "system.field.view.file", true) as $arEvent)
{
	if(ExecuteModuleEventEx($arEvent, array($arResult, $arParams)))
		return;
}

$first = true;
foreach ($arResult["VALUE"] as $res):
	if (!$first):
		?><span class="bx-br-separator"><br /></span><?
	else:
		$first = false;
	endif;
?><span class="fields files"><?
$arFile = CFile::GetFileArray($res);
if($arFile)
{
	if(CFile::IsImage($arFile["SRC"], $arFile["CONTENT_TYPE"]))
	{
		echo CFile::ShowImage($arFile, $arParams["FILE_MAX_WIDTH"], $arParams["FILE_MAX_HEIGHT"], "", "", ($arParams["FILE_SHOW_POPUP"]=="Y"), false, 0, 0, $arParams["~URL_TEMPLATE"]);
	}
	else
	{
		if($arParams["~URL_TEMPLATE"] <> '')
		{
			$src = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATE"], array('file_id' => $arFile["ID"]));
		}
		else
		{
			$src = $arFile["SRC"];
		}
		echo '<a href="'.htmlspecialcharsbx($src).'">'.htmlspecialcharsbx($arFile["FILE_NAME"]).'</a> ('.CFile::FormatSize($arFile["FILE_SIZE"]).')';
	}
}

?></span><?
endforeach;
?>
