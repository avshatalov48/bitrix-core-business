<?php

use Bitrix\Main\Web\Uri;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($this->InitComponentTemplate())
	$template = &$this->GetTemplate();
else
	return;

if (
	!array_key_exists("ALIGN", $arParams)
	|| trim($arParams["ALIGN"]) == ''
	|| !in_array($arParams["ALIGN"], array("left", "right"))
)
	$arParams["ALIGN"] = "left";

$arResult["COUNTER"] = $this->__currentCounter;

$arResult["FOLDER_PATH"] = $folderPath = $template->GetFolder();
$path2Handlers = $_SERVER["DOCUMENT_ROOT"].$folderPath."/handlers/";
CheckDirPath($path2Handlers);

$arHandlers = array();
if ($handle = opendir($path2Handlers))
{
	while (($file = readdir($handle)) !== false)
	{
		if ($file == "." || $file == "..")
			continue;

		if (is_file($path2Handlers.$file) && mb_strtoupper(mb_substr($file, mb_strlen($file) - 4)) == ".PHP")
		{
			$name = $title = $icon_url_template = $charset = "";
			$sort = 0;
			$charsBack = false;

			include($path2Handlers.$file);

			if ($name <> '')
			{
				$arHandlers[$name] = array(
					"TITLE" => $title,
					"ICON" => $icon_url_template,
					"SORT" => intval($sort),
				);
				if ($charset <> '')
					$arHandlers[$name]["CHARSET"] = $charset;
				if ($charsBack)
					$arHandlers[$name]["CHARSBACK"] = true;
					
			}
		}
	}
}

$arResult["HANDLERS_ALL"] = $arHandlers;
if(!is_array($arParams["HANDLERS"]))
{
	if (LANGUAGE_ID != 'ru')
	{
		unset($arHandlers["vk"]);
		unset($arHandlers["mailru"]);
	}
	$arParams["HANDLERS"] = array_keys($arHandlers);
}

$arResult["BOOKMARKS"] = array();
$arResult["PAGE_URL"] = (new Uri($arParams["PAGE_URL"]))->toAbsolute()->getUri();
$arResult["PAGE_TITLE"] = $arParams["PAGE_TITLE"];

foreach ($arResult["HANDLERS_ALL"] as $name => $arHandler)
{
	if (in_array($name, $arParams["HANDLERS"]))
	{
		$PageTitle = $arResult["PAGE_TITLE"];
		if (array_key_exists("CHARSBACK", $arHandler) && $arHandler["CHARSBACK"])
			$PageTitleBack = htmlspecialcharsback($PageTitle);

		$arHandler["ICON"] = str_replace("#PAGE_URL#", $arResult["PAGE_URL"], $arHandler["ICON"]);
		$arHandler["ICON"] = str_replace("#PAGE_URL_ENCODED#", urlencode($arResult["PAGE_URL"]), $arHandler["ICON"]);

		if (array_key_exists("CHARSBACK", $arHandler) && $arHandler["CHARSBACK"])
		{
			$arHandler["ICON"] = str_replace("#PAGE_TITLE#", CUtil::JSEscape($PageTitleBack), $arHandler["ICON"]);
			$arHandler["ICON"] = str_replace("#PAGE_TITLE_ENCODED#", urlencode($PageTitleBack), $arHandler["ICON"]);
			$arHandler["ICON"] = str_replace("#PAGE_TITLE_ORIG#", CUtil::addslashes($PageTitle), $arHandler["ICON"]);
			$utfTitle = $PageTitleBack;
			$arHandler["ICON"] = str_replace("#PAGE_TITLE_UTF_ENCODED#", urlencode($utfTitle), $arHandler["ICON"]);
		}
		else
		{
			$arHandler["ICON"] = str_replace("#PAGE_TITLE#", CUtil::addslashes($PageTitle), $arHandler["ICON"]);
			$arHandler["ICON"] = str_replace("#PAGE_TITLE_ENCODED#", urlencode($PageTitle), $arHandler["ICON"]);
			$utfTitle = $PageTitle;
			$arHandler["ICON"] = str_replace("#PAGE_TITLE_UTF_ENCODED#", urlencode($utfTitle), $arHandler["ICON"]);
		}

		$arResult["BOOKMARKS"][$name]["ICON"] = $arHandler["ICON"];
		$arResult["BOOKMARKS"][$name]["SORT"] = $arHandler["SORT"];
	}
}

sortByColumn($arResult["BOOKMARKS"], "SORT");

CUtil::InitJSCore();
$this->IncludeComponentTemplate();
?>