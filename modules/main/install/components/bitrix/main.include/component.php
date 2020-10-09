<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/************************************************************************************************************/
/*  Include Areas Component
/* Params:
/*	AREA_FILE_SHOW => {page | sect} - area to include. Default value - 'page'
/*	AREA_FILE_SUFFIX => string - suffix of file to seek. Default value - 'inc'
/*	AREA_FILE_RECURSIVE => {Y | N} - whether to search area file in parent directories. Used only when AREA_FILE_SHOW='sect'. Default value - 'Y'
/*	EDIT_MODE => {php | html | text} - default edit mode for an area. Default value - 'html'
/*	EDIT_TEMPLATE => string - default template to add new area. Default value - page_inc.php / sect_inc.php
/*
/************************************************************************************************************/

//$arParams["EDIT_MODE"] = in_array($arParams["EDIT_MODE"], array("php", "html", "text")) ? $arParams["EDIT_MODE"] : "html";
$arParams["EDIT_TEMPLATE"] = $arParams["EDIT_TEMPLATE"] <> '' ? $arParams["EDIT_TEMPLATE"] : $arParams["AREA_FILE_SHOW"]."_inc.php";

// check params values
$bHasPath = ($arParams["AREA_FILE_SHOW"] == 'file');
$sRealFilePath = $_SERVER["REAL_FILE_PATH"];

$io = CBXVirtualIo::GetInstance();

if (!$bHasPath)
{
	$arParams["AREA_FILE_SHOW"] = $arParams["AREA_FILE_SHOW"] == "sect" ? "sect" : "page";
	$arParams["AREA_FILE_SUFFIX"] = $arParams["AREA_FILE_SUFFIX"] <> '' ? $arParams["AREA_FILE_SUFFIX"] : "inc";
	$arParams["AREA_FILE_RECURSIVE"] = $arParams["AREA_FILE_RECURSIVE"] == "N" ? "N" : "Y";


	// check file for including
	if ($arParams["AREA_FILE_SHOW"] == "page")
	{
		// if page in SEF mode check real path
		if ($sRealFilePath <> '')
		{
			$slash_pos = mb_strrpos($sRealFilePath, "/");
			$sFilePath = mb_substr($sRealFilePath, 0, $slash_pos + 1);
			$sFileName = mb_substr($sRealFilePath, $slash_pos + 1);
			$sFileName = mb_substr($sFileName, 0, mb_strlen($sFileName) - 4)."_".$arParams["AREA_FILE_SUFFIX"].".php";
		}
		// otherwise use current
		else
		{
			$sFilePath = $APPLICATION->GetCurDir();
			$sFileName = mb_substr($APPLICATION->GetCurPage(true), 0, mb_strlen($APPLICATION->GetCurPage(true)) - 4)."_".$arParams["AREA_FILE_SUFFIX"].".php";
			$sFileName = mb_substr($sFileName, mb_strlen($sFilePath));
		}

		$sFilePathTMP = $sFilePath;
		$bFileFound = $io->FileExists($_SERVER['DOCUMENT_ROOT'].$sFilePath.$sFileName);
	}
	else
	{
		// if page is in SEF mode - check real path
		if ($sRealFilePath <> '')
		{
			$slash_pos = mb_strrpos($sRealFilePath, "/");
			$sFilePath = mb_substr($sRealFilePath, 0, $slash_pos + 1);
		}
		// otherwise use current
		else
		{
			$sFilePath = $APPLICATION->GetCurDir();
		}

		$sFilePathTMP = $sFilePath;
		$sFileName = "sect_".$arParams["AREA_FILE_SUFFIX"].".php";

		$bFileFound = $io->FileExists($_SERVER['DOCUMENT_ROOT'].$sFilePath.$sFileName);

		// if file not found and is set recursive check - start it
		if (!$bFileFound && $arParams["AREA_FILE_RECURSIVE"] == "Y" && $sFilePath != "/")
		{
			$finish = false;

			do
			{
				// back one level
				if (mb_substr($sFilePath, -1) == "/") $sFilePath = mb_substr($sFilePath, 0, -1);
				$slash_pos = mb_strrpos($sFilePath, "/");
				$sFilePath = mb_substr($sFilePath, 0, $slash_pos + 1);

				$bFileFound = $io->FileExists($_SERVER['DOCUMENT_ROOT'].$sFilePath.$sFileName);

				// if we are on the root - finish
				$finish = $sFilePath == "/";
			}
			while (!$finish && !$bFileFound);
		}
	}
}
else
{
	if (mb_substr($arParams['PATH'], 0, 1) != '/')
	{
		// if page in SEF mode check real path
		if ($sRealFilePath <> '')
		{
			$slash_pos = mb_strrpos($sRealFilePath, "/");
			$sFilePath = mb_substr($sRealFilePath, 0, $slash_pos + 1);
		}
		// otherwise use current
		else
		{
			$sFilePath = $APPLICATION->GetCurDir();
		}

		$arParams['PATH'] = Rel2Abs($sFilePath, $arParams['PATH']);
	}

	$slash_pos = mb_strrpos($arParams['PATH'], "/");
	$sFilePath = mb_substr($arParams['PATH'], 0, $slash_pos + 1);
	$sFileName = mb_substr($arParams['PATH'], $slash_pos + 1);

	$bFileFound = $io->FileExists($_SERVER['DOCUMENT_ROOT'].$sFilePath.$sFileName);

	$sFilePathTMP = $sFilePath;
}

if($APPLICATION->GetShowIncludeAreas())
{
	//need fm_lpa for every .php file, even with no php code inside
	$bPhpFile = (!$GLOBALS["USER"]->CanDoOperation('edit_php') && in_array(GetFileExtension($sFileName), GetScriptFileExt()));

	$bCanEdit = $USER->CanDoFileOperation('fm_edit_existent_file', array(SITE_ID, $sFilePath.$sFileName)) && (!$bPhpFile || $GLOBALS["USER"]->CanDoFileOperation('fm_lpa', array(SITE_ID, $sFilePath.$sFileName)));
	$bCanAdd = $USER->CanDoFileOperation('fm_create_new_file', array(SITE_ID, $sFilePathTMP.$sFileName)) && (!$bPhpFile || $GLOBALS["USER"]->CanDoFileOperation('fm_lpa', array(SITE_ID, $sFilePathTMP.$sFileName)));

	if($bCanEdit || $bCanAdd)
	{
		$editor = '&site='.SITE_ID.'&back_url='.urlencode($_SERVER['REQUEST_URI']).'&templateID='.urlencode(SITE_TEMPLATE_ID);

		if ($bFileFound)
		{
			if ($bCanEdit)
			{
				$arMenu = array();
				if($USER->CanDoOperation('edit_php'))
				{
					$arMenu[] = array(
						"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
							array(
								'URL' => "/bitrix/admin/public_file_edit_src.php?lang=".LANGUAGE_ID."&template=".urlencode($arParams["EDIT_TEMPLATE"])."&path=".urlencode($sFilePath.$sFileName).$editor,
								"PARAMS" => array(
									'width' => 770,
									'height' => 570,
									'resize' => true,
									"dialog_type" => 'EDITOR',
									"min_width" => 700,
									"min_height" => 400
								)
							)
						),
						"ICON" => "panel-edit-php",
						"TEXT"=>GetMessage("main_comp_include_edit_php"),
						"TITLE" => GetMessage("MAIN_INCLUDE_AREA_EDIT_".$arParams["AREA_FILE_SHOW"]."_NOEDITOR"),
					);
				}
				$arIcons = array(
					array(
						"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
							array(
								'URL' => "/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&from=main.include&template=".urlencode($arParams["EDIT_TEMPLATE"])."&path=".urlencode($sFilePath.$sFileName).$editor,
								"PARAMS" => array(
									'width' => 770,
									'height' => 570,
									'resize' => true
								)
							)
						),
						"DEFAULT" => $APPLICATION->GetPublicShowMode() != 'configure',
						"ICON" => "bx-context-toolbar-edit-icon",
						"TITLE"=>GetMessage("main_comp_include_edit"),
						"ALT" => GetMessage("MAIN_INCLUDE_AREA_EDIT_".$arParams["AREA_FILE_SHOW"]),
						"MENU" => $arMenu,
					),
				);
			}

			if ($sFilePath != $sFilePathTMP && $bCanAdd)
			{
				$arMenu = array();
				if($USER->CanDoOperation('edit_php'))
				{
					$arMenu[] = array(
						"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
							array(
								'URL' => "/bitrix/admin/public_file_edit_src.php?lang=".LANGUAGE_ID."&new=Y&path=".urlencode($sFilePathTMP.$sFileName)."&new=Y&template=".urlencode($arParams["EDIT_TEMPLATE"]).$editor,
								"PARAMS" => array(
									'width' => 770,
									'height' => 570,
									'resize' => true,
									"dialog_type" => 'EDITOR',
									"min_width" => 700,
									"min_height" => 400
								)
							)
						),
						"ICON" => "panel-edit-php",
						"TEXT"	=> GetMessage("main_comp_include_add_php"),
						"TITLE" => GetMessage("MAIN_INCLUDE_AREA_ADD_".$arParams["AREA_FILE_SHOW"]."_NOEDITOR"),
					);
				}
				$arIcons[] = array(
					"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
						array(
							'URL' => "/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&from=main.include&new=Y&path=".urlencode($sFilePathTMP.$sFileName)."&new=Y&template=".urlencode($arParams["EDIT_TEMPLATE"]).$editor,
							"PARAMS" => array(
								'width' => 770,
								'height' => 570,
								'resize' => true
							)
						)
					),
					"DEFAULT" => $APPLICATION->GetPublicShowMode() != 'configure',
					"ICON" => "bx-context-toolbar-create-icon",
					"TITLE" => GetMessage("main_comp_include_add"),
					"ALT" => GetMessage("MAIN_INCLUDE_AREA_ADD_".$arParams["AREA_FILE_SHOW"]),
					"MENU" => $arMenu,
				);
			}
		}
		elseif ($bCanAdd)
		{
			$arMenu = array();
			if($USER->CanDoOperation('edit_php'))
			{
				$arMenu[] = array(
					"ACTION" => 'javascript:'.$APPLICATION->GetPopupLink(
						array(
							'URL' => "/bitrix/admin/public_file_edit_src.php?lang=".LANGUAGE_ID."&path=".urlencode($sFilePathTMP)."&filename=".urlencode($sFileName)."&new=Y&template=".urlencode($arParams["EDIT_TEMPLATE"]).$editor,
							"PARAMS" => array(
								'width' => 770,
								'height' => 570,
								'resize' => true,
								//"dialog_type" => 'EDITOR',
								"min_width" => 700,
								"min_height" => 400
							)
						)
					),
					"ICON" => "panel-edit-php",
					"TEXT" => GetMessage("main_comp_include_add1_php"),
					"TITLE" => GetMessage("MAIN_INCLUDE_AREA_ADD_".$arParams["AREA_FILE_SHOW"]."_NOEDITOR"),
				);
			}
			$arIcons = array(
				array(
					"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
						array(
							'URL' => "/bitrix/admin/public_file_edit.php?lang=".LANGUAGE_ID."&from=main.include&path=".urlencode($sFilePathTMP.$sFileName)."&new=Y&template=".urlencode($arParams["EDIT_TEMPLATE"]).$editor,
							"PARAMS" => array(
								'width' => 770,
								'height' => 570,
								'resize' => true,
								"dialog_type" => 'EDITOR',
								"min_width" => 700,
								"min_height" => 400
							)
						)
					),
					"DEFAULT" => $APPLICATION->GetPublicShowMode() != 'configure',
					"ICON" => "bx-context-toolbar-create-icon",
					"TITLE" => GetMessage("main_comp_include_add1"),
					"ALT" => GetMessage("MAIN_INCLUDE_AREA_ADD_".$arParams["AREA_FILE_SHOW"]),
					"MENU" => $arMenu,
				),
			);
		}

		if (is_array($arIcons) && count($arIcons) > 0)
		{
			$this->AddIncludeAreaIcons($arIcons);
		}
	}
}

if ($bFileFound)
{
	$arResult["FILE"] = $io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"].$sFilePath.$sFileName);
	$this->IncludeComponentTemplate();
}
?>