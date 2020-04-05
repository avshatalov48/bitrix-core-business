<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!CModule::IncludeModule("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"]))
	return ShowError(GetMessage("P_GALLERY_EMPTY"));

if (empty($arParams["INDEX_URL"]) && !empty($arParams["SECTIONS_TOP_URL"]))
	$arParams["INDEX_URL"] = $arParams["SECTIONS_TOP_URL"];

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["BX_PHOTO_AJAX"] = isset($_REQUEST["bx_photo_ajax"]);
	$arParams["ACTION_URL"] = CHTTP::urlDeleteParams(htmlspecialcharsback(POST_FORM_ACTION_URI), array("clear_cache", "bitrix_include_areas", "bitrix_show_mode", "back_url_admin", "bx_photo_ajax", "change_view_mode_data", "sessid"));
	$arParams["ACTION_URL"] = $arParams["ACTION_URL"].(strpos($arParams["ACTION_URL"], "?") === false ? "?" : "&")."bx_photo_ajax=Y&sessid=".bitrix_sessid();

	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$iblockId = $arParams["IBLOCK_ID"];
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["ACTION"] = (empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? "EDIT" : $arParams["ACTION"]);

	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
	$arResult["JSID"] = '1';

	$arParams["USE_PHOTO_TITLE"] = ($arParams["USE_PHOTO_TITLE"] == "Y" ? "Y" : "N");
	$arParams["PAGE_ELEMENTS"] = (intVal($arParams["PAGE_ELEMENTS"]) > 0 ? intVal($arParams["PAGE_ELEMENTS"]) : 20);
	$arParams["THUMBNAIL_SIZE"] = (intVal($arParams["THUMBNAIL_SIZE"]) > 0 ? intVal($arParams["THUMBNAIL_SIZE"]) : 100);
	$arParams["ALBUM_PHOTO_THUMBS_WIDTH"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) : 110);

	$arParams['AFTER_UPLOAD_IDS'] = array();
	if (isset($_REQUEST['uploader_redirect']) && $_REQUEST['uploader_redirect'] == "Y" && isset($_SESSION['arUploadedPhotos']))
	{
		foreach($_SESSION['arUploadedPhotos'] as $uplId)
			if (intVal($uplId) > 0)
				$arParams['AFTER_UPLOAD_IDS'][] = intVal($uplId);
	}
	//$arParams['AFTER_UPLOAD_MODE'] = count($arParams['AFTER_UPLOAD_IDS']) == 0 ? 'N' : 'Y';
	$arParams['AFTER_UPLOAD_MODE'] = 'N';

	$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#",
		"section_edit" => "PAGE_NAME=section_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).	"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"section_edit_icon" => "PAGE_NAME=section_edit_icon".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"section_drop" => "PAGE_NAME=section_drop".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#"
	);

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}

/***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
$oPhoto = new CPGalleryInterface(
	array(
		"IBlockID" => $iblockId,
		"GalleryID" => $arParams["USER_ALIAS"],
		"Permission" => $arParams["PERMISSION_EXTERNAL"]
	),
	array(
		"cache_time" => $arParams["CACHE_TIME"],
		"set_404" => $arParams["SET_STATUS_404"]
	)
);

$bError = true;
if ($oPhoto)
{
	$bError = false;
	$arResult["GALLERY"] = $oPhoto->Gallery;
	$arParams["PERMISSION"] = $oPhoto->User["Permission"];

	if ($arParams["PERMISSION"] < "U")
	{
		ShowError(GetMessage("P_ACCESS_DENIED"));
		$bError = true;
	}
	elseif (!$ajaxAction && $arParams["SECTION_ID"] > 0 && ($oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]) > 300))
	{
		$bError = true;
	}
}

if ($bError)
	return false;

$strWarning = "";
$bVarsFromForm = false;

/********************************************************************
				Actions
********************************************************************/
if (isset($_REQUEST["cancel"]))
{
	LocalRedirect(CHTTP::urlDeleteParams(CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"])), array("sessid", "edit"), true));
}
elseif($_REQUEST["save_edit"] == "Y" || $_REQUEST["edit"] == "Y")
{
	$multiAction = $_POST['multiple_action'];
	$bMultipleAction = in_array($multiAction, array('delete', 'move'));

	$arError = array();
	if(!check_bitrix_sessid())
	{
		$strWarning = GetMessage("IBLOCK_WRONG_SESSION")."<br>";
		$bVarsFromForm = true;
	}
	// Edit album
	elseif ($arParams["ACTION"] != "NEW" && $arParams["ACTION"] != "DROP")
	{
		if ($arParams["AJAX_CALL"] == "Y")
			CUtil::JSPostUnEscape();

		if (!$bMultipleAction)
		{
			$arFields = array("IBLOCK_ID" => $iblockId);
			if (isset($_REQUEST["UF_DATE"]))
			{
				$arFields["UF_DATE"] = $_REQUEST["UF_DATE"];
				$arFields["DATE"] = $_REQUEST["UF_DATE"];
			}

			if (isset($_REQUEST["NAME"]))
				$arFields["NAME"] = trim($_REQUEST["NAME"]);
			if (isset($_REQUEST["DESCRIPTION"]))
				$arFields["DESCRIPTION"] = trim($_REQUEST["DESCRIPTION"]);
			if (isset($_REQUEST["ACTIVE"]))
				$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];

			if ($_REQUEST["DROP_PASSWORD"] == "Y")
			{
				$arFields["UF_PASSWORD"] = "";
				$GLOBALS["UF_PASSWORD"] = "";
				$_REQUEST["DROP_PASSWORD"] = "Y";
			}
			elseif ($_REQUEST["USE_PASSWORD"] == "Y" && !empty($_REQUEST["PASSWORD"]))
			{
				$arFields["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
				$GLOBALS["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
			}
			else
			{
				$arFields["UF_PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
				$GLOBALS["UF_PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
			}

			foreach ($_REQUEST as $key => $val)
			{
				if (substr($key, 0, 3) == "UF_")
					$GLOBALS[$key] = $val;
			}

			$bs = new CIBlockSection;
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$iblockId."_SECTION", $arFields);
			if ($bs->Update($arResult["SECTION"]["ID"], $arFields))
			{
				foreach(GetModuleEvents("photogallery", "OnAfterSectionEdit", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array($arFields, $arParams, $arResult));

				$rsSection = CIBlockSection::GetList(
					array(),
					array("ID" => $arResult["SECTION"]["ID"], "IBLOCK_ID" => $iblockId),
					false,
					array("UF_DATE", "UF_PASSWORD"));
				$arResultSection = $rsSection->GetNext();
				$arResultFields = Array(
					"IBLOCK_ID" => $iblockId,
					"DATE"=>PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResultSection["UF_DATE"], CSite::GetDateFormat())),
					"PASSWORD" => $arResultSection["UF_PASSWORD"],
					"NAME"=>$arResultSection["NAME"],
					"DESCRIPTION"=>$arResultSection["DESCRIPTION"],
					"ID" => $arResult["SECTION"]["ID"],
					"error" => "");
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
			}
			elseif ($bs->LAST_ERROR)
			{
				$strWarning .= $bs->LAST_ERROR;
				$bVarsFromForm = true;
			}
			else
			{
				$err = $GLOBALS['APPLICATION']->GetException();
				if ($err)
				{
					$strWarning .= $err->GetString();
					$bVarsFromForm = true;
				}
			}
		}

		// Save photos information && handling multiple actions
		if (is_array($_POST['ITEMS']) && count($_POST['ITEMS']) > 0)
		{
			$arChangedId = array();
			foreach ($_POST['ITEMS'] as $itemID => $item)
			{
				if (($bMultipleAction && $item['checked'] == 'Y') || (!$bMultipleAction && ($item['changed'] == 'Y' || $item['deleted'] == "Y")))
					$arChangedId[] = $itemID;
			}

			$arFilter = array("IBLOCK_ID" => $iblockId, "SECTION_ID" => $arParams["SECTION_ID"], "ID" => $arChangedId, "CHECK_PERMISSIONS" => "Y");
			$arSelect = array("ID", "NAME", "DETAIL_PICTURE", "PREVIEW_PICTURE", "PREVIEW_TEXT", "DETAIL_TEXT", "TAGS", "PROPERTY_BLOG_POST_ID", "PROPERTY_FORUM_TOPIC_ID", "PROPERTY_REAL_PICTURE");

			$db_res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			$arItems = array();
			while ($arRes = $db_res->Fetch())
				$arItems[$arRes['ID']] = $arRes;

			if (empty($arResult["URL"]))
			{
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
					array(
						"USER_ALIAS" => $arParams["USER_ALIAS"],
						"SECTION_ID" => $arResult["SECTION"]["ID"],
						"ACTION" => "edit"
					)
				);
			}

			foreach ($_POST['ITEMS'] as $itemID => $item)
			{
				if (isset($arItems[$itemID]))
				{
					if ($item['deleted'] == "Y" || $multiAction == 'delete') // Delete item
					{
						if (!CIBlockElement::Delete($itemID))
						{
							$sError = GetMessage("P_DELETE_ERROR");
							if($ex = $APPLICATION->GetException())
								$sError = $ex->GetString();
							$arError[] = array(
								"id" => "drop error",
								"text" => PhotoShowError(array("code" => "NOT_DELETED", "title" => $sError, "DATA" => $arRes))
							);
							break;
						}
						else
						{
							$arEventFields = array(
								"ID" => $itemID,
								"IBLOCK_ID" => $iblockId,
								"SECTION_ID" => $arResult["SECTION"]["ID"]
							);
							foreach(GetModuleEvents("photogallery", "OnAfterPhotoDrop", true) as $arEvent)
								ExecuteModuleEventEx($arEvent, array($arEventFields, $arParams));
						}
						if ($multiAction == 'delete')
							continue;
					}

					$arFields = Array(
						"MODIFIED_BY" => $USER->GetID(),
						"PREVIEW_TEXT" => $item['desc'],
						"DETAIL_TEXT" => $item['desc'],
						"DETAIL_TEXT_TYPE" => "text",
						"PREVIEW_TEXT_TYPE" => "text"
					);

					if ($multiAction == 'move' && intVal($_POST["move_to"] > 0))
					{
						$arFields["IBLOCK_SECTION"] = intVal($_POST["move_to"]);
					}

					if ($arParams['USE_PHOTO_TITLE'] != 'N')
					{
						$arFields["NAME"] = $item['title'];
						if ($arFields["NAME"] == "")
							$arFields["NAME"] = " ";
					}
					if ($arParams['SHOW_TAGS'] != 'N')
					{
						$arFields["TAGS"] = $item['tags'];
					}

					if ($item['angle'] > 0) // Rotate item
					{
						// Preview
						if ($arItems[$itemID]["PREVIEW_PICTURE"] > 0)
						{
							$arImg = CFile::MakeFileArray($arItems[$itemID]["PREVIEW_PICTURE"]);
							CFile::ImageRotate($arImg['tmp_name'], $item['angle']);
							$arFields["PREVIEW_PICTURE"] = CFile::MakeFileArray($arImg['tmp_name']);
						}

						// Detail
						if ($arItems[$itemID]["DETAIL_PICTURE"] > 0)
						{
							$arImg = CFile::MakeFileArray($arItems[$itemID]["DETAIL_PICTURE"]);
							CFile::ImageRotate($arImg['tmp_name'], $item['angle']);
							$arFields["DETAIL_PICTURE"] = CFile::MakeFileArray($arImg['tmp_name']);
						}

						// Real
						if ($arItems[$itemID]["PROPERTY_REAL_PICTURE_VALUE"] > 0)
						{
							$arImg = CFile::MakeFileArray($arItems[$itemID]["PROPERTY_REAL_PICTURE_VALUE"]);
							CFile::ImageRotate($arImg['tmp_name'], $item['angle']);
							CIBlockElement::SetPropertyValues($itemID, $iblockId, array(
								"REAL_PICTURE" => CFile::MakeFileArray($arImg['tmp_name'])
							));
						}
					}

					$bs = new CIBlockElement;
					if (!$bs->Update($itemID, $arFields))
					{
						$arError[] = array(
							"id" => "update",
							"text" => $bs->LAST_ERROR);
					}
				}
			}
		}
	}
	elseif ($arParams["ACTION"] == "NEW")
	{
		if ($arParams["AJAX_CALL"] == "Y")
			CUtil::JSPostUnEscape();
		$arFields = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $iblockId,
			"DATE" => $_REQUEST["UF_DATE"],
			"UF_DATE" => $_REQUEST["UF_DATE"],
			"NAME"=> trim($_REQUEST["NAME"]),
			"DESCRIPTION" => trim($_REQUEST["DESCRIPTION"])
		);
		if (isset($_REQUEST["ACTIVE"]))
			$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];

		if ($arParams["BEHAVIOUR"] == "USER")
		{
			if ($_REQUEST["IBLOCK_SECTION_ID"] > 0)
			{
				$db_res = CIBlockSection::GetByID($_REQUEST["IBLOCK_SECTION_ID"]);
				if ($db_res && $res = $db_res->Fetch())
				{
					if ($res["LEFT_MARGIN"] > $arResult["GALLERY"]["LEFT_MARGIN"] &&
						$res["RIGHT_MARGIN"] < $arResult["GALLERY"]["RIGHT_MARGIN"])
					$arFields["IBLOCK_SECTION_ID"] = $_REQUEST["IBLOCK_SECTION_ID"];
				}
			}
			if (empty($arFields["IBLOCK_SECTION_ID"]))
			{
				$arFields["IBLOCK_SECTION_ID"] = $arResult["GALLERY"]["ID"];
			}
		}
		elseif (intVal($_REQUEST["IBLOCK_SECTION_ID"]) > 0)
		{
			$arFields["IBLOCK_SECTION_ID"] = $_REQUEST["IBLOCK_SECTION_ID"];
		}

		if (!empty($_REQUEST["PASSWORD"]))
		{
			$arFields["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
			$GLOBALS["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
		}

		$bs = new CIBlockSection();
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$iblockId."_SECTION", $arFields);
		$ID = $bs->Add($arFields);

		if ($ID > 0)
		{
			$rsSection = CIBlockSection::GetList(Array(), array("ID" => $ID), false);
			$arResultSection = $rsSection->GetNext();
			$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $ID));
			$arResultFields = Array(
				"IBLOCK_ID" => $iblockId,
				"DATE" => PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($_REQUEST["UF_DATE"], CSite::GetDateFormat())),
				"NAME" => $arResultSection["NAME"],
				"DESCRIPTION" => $arResultSection["DESCRIPTION"],
				"PASSWORD" => $arResultSection["UF_PASSWORD"],
				"ID" => $ID,
				"error" => "",
				"url" => $arResult["URL"]
			);
		}
		elseif ($bs->LAST_ERROR)
		{
			$strWarning .= $bs->LAST_ERROR;
			$bVarsFromForm = true;
		}
		else
		{
			$err = $GLOBALS['APPLICATION']->GetException();
			if ($err)
			{
				$strWarning .= $err->GetString();
				$bVarsFromForm = true;
			}
		}
	}
	elseif ($arParams["ACTION"] == "DROP")
	{
		@set_time_limit(1000);

		foreach(GetModuleEvents("photogallery", "OnBeforeSectionDrop", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($arResult["SECTION"]["ID"], $arParams, $arResult, &$arTreeSectionID, &$arTreeElementID));

		if (CIBlockSection::Delete($arResult["SECTION"]["ID"]))
		{
			$arEventFields = array(
				"ID" => $arResult["SECTION"]["ID"],
				"SECTIONS_IN_TREE" => $arTreeSectionID,
				"ELEMENTS_IN_TREE" => $arTreeElementID
			);
			foreach(GetModuleEvents("photogallery", "OnAfterSectionDrop", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arResult["SECTION"]["ID"], $arEventFields, $arParams, $arResult));

			// /Must Be deleted
			if ($arParams["BEHAVIOUR"] == "USER" && intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) == intVal($arResult["GALLERY"]["ID"]))
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"]));
			elseif (intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) > 0)
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["IBLOCK_SECTION_ID"]));
			else
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"],
				array());
			$arResultFields = Array(
				"ID" => $arResult["SECTION"]["ID"],
				"error" => "",
				"url" => $arResult["URL"]);
		}
		elseif ($e = $APPLICATION->GetException())
		{
			$strWarning .= $e->GetString();
			$bVarsFromForm = true;
		}
		else
		{
			$strWarning .= GetMessage("IBSEC_A_DELERR_REFERERS");
			$bVarsFromForm = true;
		}
	}

	if (!$bVarsFromForm)
	{
		CIBlockSection::ReSort($iblockId);

		$sectionsIds = array(0);
		$arGalleriesIds = array(0);
		$arUsers = array();

		if ($ID > 0) // Add
			$sectionsIds[] = $ID;
		if($arParams['SECTION_ID']) // Del, edit
			$sectionsIds[] = $arParams['SECTION_ID'];

		if ($arFields && $arFields['IBLOCK_SECTION_ID'])
			$sectionsIds[] = $arFields['IBLOCK_SECTION_ID'];

		if (isset($arResult) && isset($arResult['SECTION']['IBLOCK_SECTION_ID']))
			$sectionsIds[] = $arResult['SECTION']['IBLOCK_SECTION_ID'];

		if (isset($arResult['GALLERY']['CODE']))
		{
			$sectionsIds[] = $arResult['GALLERY']['ID'];
			$arGalleriesIds[] = $arResult["GALLERY"]["CODE"];

			if ($arResult["GALLERY"]["CREATED_BY"])
				$arUsers[] = $arResult["GALLERY"]["CREATED_BY"];
		}

		PClearComponentCacheEx($iblockId, $sectionsIds, $arGalleriesIds, $arUsers);

		if ($arParams["AJAX_CALL"] == "Y")
		{
			$APPLICATION->RestartBuffer();
			?><?=CUtil::PhpToJSObject($arResultFields);?><?
			die();
		}
		else
		{
			LocalRedirect(CHTTP::urlDeleteParams($arResult["URL"], array("sessid", "edit"), true));
		}
	}
	$arResult["ERROR_MESSAGE"] = $strWarning;
}
/********************************************************************
				/Actions
********************************************************************/

if ($arParams["AJAX_CALL"] == "Y" || $arParams["BX_PHOTO_AJAX"])
	$GLOBALS['APPLICATION']->RestartBuffer();

if ($arParams["BX_PHOTO_AJAX"])
	$ajaxAction = $_REQUEST['bx_photo_action'];
else
	$ajaxAction = false;
$arParams["AJAX_ACTION"] = $ajaxAction;

if ($oPhoto)
{
	// Get subsections for this album
	if (!$ajaxAction && $arParams["SECTION_ID"] > 0 && !$bError && $arParams['AFTER_UPLOAD_MODE'] != "Y")
	{
		$arFilter = array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $iblockId,
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => intVal($arParams["SECTION_ID"])
		);

		// GALLERY INFO
		if ($arParams["BEHAVIOUR"] == "USER" && ($arFilter["SECTION_ID"] != $arResult["GALLERY"]["ID"]))
		{
			$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
			$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
		}

		$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), $arFilter, false, array("UF_DATE", "UF_PASSWORD"));

		if ($db_res)
		{
			while ($res = $db_res->GetNext())
			{
				$res["DATE"] = $res["UF_DATE"];
				$res["~DATE"] = $res["~UF_DATE"];
				if (!empty($res["~DATE"]))
					$res["DATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~DATE"], CSite::GetDateFormat()));

				$res["PASSWORD"] = $res["UF_PASSWORD"];
				$res["~PASSWORD"] = $res["~UF_PASSWORD"];

				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
				$res["DETAIL_PICTURE"] = CFile::GetFileArray($res["DETAIL_PICTURE"]);
				//$res["PREVIEW_PICTURE"] = CFile::GetFileArray($res["PREVIEW_PICTURE"]);

				$res["SECTIONS_CNT"] = intVal(CIBlockSection::GetCount(array("IBLOCK_ID" => $iblockId, "SECTION_ID" => $res["ID"])));

				$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ACTIVE" => "Y")));
				if ($arParams["PERMISSION"] >= "U")
					$res["ELEMENTS_CNT_ALL"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ALL"=>"Y")));

				if ($arParams["PERMISSION"] < "U" && $res["ELEMENTS_CNT"] <= 0)
					continue;

				$res["~LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"]));
				$res["LINK"] = htmlspecialcharsbx($res["~LINK"]);
				if ($arParams["PERMISSION"] >= "W")
				{
					$res["~NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
						array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "new"));
					$res["NEW_LINK"] = htmlspecialcharsbx($res["~NEW_LINK"]);
					$res["~EDIT_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
						array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "edit"));
					$res["EDIT_LINK"] = htmlspecialcharsbx($res["~EDIT_LINK"]);
					if ($res["ELEMENTS_CNT_ALL"] > 0)
					{
						$res["~EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_ICON_URL"],
							array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "edit"));
						$res["EDIT_ICON_LINK"] = htmlspecialcharsbx($res["~EDIT_ICON_LINK"]);
					}
					$res["~DROP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
						array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "drop")).
						(strpos($arParams["~SECTION_EDIT_URL"], "?") === false ? "?" : "&").bitrix_sessid_get()."&edit=Y";
					$res["DROP_LINK"] = htmlspecialcharsbx($res["~DROP_LINK"]);
				}
				$arResult["SECTIONS"][$res["ID"]] = $res;
			}
		}
	}

	// Select section items
	//$arResult["PHOTOS"] = array();
	$arResult["PHOTOS_JS"] = array();
	$arResult["SHOW_MORE_PHOTOS"] = false;
	if ($arParams["SECTION_ID"] > 0 && !$bError && (!$ajaxAction || $ajaxAction == 'load_items'))
	{
		if ($arParams['AFTER_UPLOAD_MODE'] == "Y")
		{
			$arNavParams = array();
		}
		else
		{
			//PAGENAVIGATION
			$arNavParams = false;
			$arNavigation = false;
			if ($arParams["PAGE_ELEMENTS"] > 0)
			{
				CPageOption::SetOptionString("main", "nav_page_in_session", "N");
				$arNavParams = array(
					"iNumPage" => (isset($_REQUEST['bx_photo_nav_page']) ? intVal($_REQUEST['bx_photo_nav_page']) : 1),
					"nPageSize" => $arParams["PAGE_ELEMENTS"],
					"bDescPageNumbering" => ($arParams["USE_DESC_PAGE"] == "N" ? false : true),
					"bShowAll" => false
				);
				$arNavigation = CDBResult::GetNavParams($arNavParams);
			}
		}

		$arSort["SORT"] = "ASC";
		// Select fields
		$arSelect = array("ID", "IBLOCK_ID", "NAME", "ACTIVE", "DETAIL_TEXT", "PREVIEW_PICTURE", "TAGS");
		// Filter for section items
		$arFilter = array(
			"IBLOCK_ID" => $iblockId,
			"CHECK_PERMISSIONS" => "Y",
			"SECTION_ID" => $arParams["SECTION_ID"]
		);
		if ($arParams["PERMISSION"] < "U")
			$arFilter["ACTIVE"] = "Y";

		if ($arParams['AFTER_UPLOAD_MODE'] == "Y")
		{
			$arFilter["ID"] = $arParams['AFTER_UPLOAD_IDS'];
		}

		$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);
		if ($rsElements)
		{
			$arResult["NAV_RESULT"] = $rsElements;
			$arResult["NAV_PAGE_COUNT"] = $rsElements->NavPageCount;
			$arResult["NAV_SELECTED_COUNT"] = $rsElements->nSelectedCount;
			$arResult["NAV_PAGE_SIZE"] = $rsElements->NavPageSize;
			if ($arResult["NAV_PAGE_COUNT"] > 1)
				$arResult["SHOW_MORE_PHOTOS"] = true;

			while ($obElement = $rsElements->GetNextElement())
			{
				$arElement = $obElement->GetFields();
				$arElement["PREVIEW_PICTURE"] = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
				//URL
				$arElement["~URL"] = CComponentEngine::MakePathFromTemplate(
					$arParams["~DETAIL_URL"],
					array(
						"USER_ALIAS" => $arGallery["CODE"],
						"SECTION_ID" => $arParams["SECTION_ID"],
						"ELEMENT_ID" => $arElement["ID"],
						"USER_ID" => $arGallery["CREATED_BY"],
						"GROUP_ID" => $arGallery["SOCNET_GROUP_ID"]
					)
				);

				if ($arElement["DETAIL_TEXT"] == "" && $arElement["NAME"] != "" && !preg_match('/\d{3,}/', $arElement["NAME"]))
				{
					$arElement["~NAME"] = preg_replace(array('/\.jpg/i','/\.jpeg/i','/\.gif/i','/\.png/i','/\.bmp/i'), '', $arElement["~NAME"]);
					$arElement["~DETAIL_TEXT"] = $arElement["~NAME"];
				}

				$arElement["URL"] = htmlspecialcharsbx($arElement["~URL"]);
				$arResult["PHOTOS_JS"][$arElement["ID"]] = array(
					"id" => intVal($arElement["ID"]),
					"src" => $arElement["PREVIEW_PICTURE"]["SRC"],
					"width" => intVal($arElement["PREVIEW_PICTURE"]["WIDTH"]),
					"height" => intVal($arElement["PREVIEW_PICTURE"]["HEIGHT"]),
					"title" => $arElement["NAME"],
					"description" => $arElement["~DETAIL_TEXT"],
					"tags" => $arElement["~TAGS"],
					"url" => $arElement["~URL"]
				);
			}
		}
	}

	if ($ajaxAction == 'multi_delete' || $ajaxAction == 'multi_move')
		die();

	// Get sections tree list
	$arResult["SECTIONS_LIST"] = array();
	if (count($arResult["PHOTOS_JS"]) > 0 && $arParams["PERMISSION"] >= "U" && !$ajaxAction)
	{
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $iblockId,
			"IBLOCK_ACTIVE" => "Y"
		);
		if ($arParams["BEHAVIOUR"] == "USER")
		{
			$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
			$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
			$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
		}
		$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
		$iDiff = ($arParams["BEHAVIOUR"] == "USER" ? 2 : 1);
		while ($arSection = $rsIBlockSectionList->GetNext())
		{
			$arResult["SECTIONS_LIST"][] = array(
				"ID" => $arSection["ID"],
				"NAME" => $arSection["NAME"],
				"DEPTH" => $arSection["DEPTH_LEVEL"] - $iDiff
			);
		}
	}

	if ($ajaxAction == 'load_items')
	{
		?><script>window.bx_load_items_res = <?= CUtil::PhpToJSObject($arResult["PHOTOS_JS"])?>;</script><?
		die();
	}
}

if ($bError)
{
	if ($arParams["AJAX_CALL"] == "Y")
		die();
	return false;
}
/********************************************************************
				Data
********************************************************************/
/************** PROPERTIES *****************************************/
$arUserFields = $arResult["USER_FIELDS"];
if (empty($arUserFields) || empty($arUserFields["UF_DATE"]))
{
	$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION", "FIELD_NAME" => "UF_DATE"));
	if (!($db_res && $res = $db_res->GetNext()))
	{
		$arFields = Array(
			"ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION",
			"FIELD_NAME" => "UF_DATE",
			"USER_TYPE_ID" => "datetime",
			"MULTIPLE" => "N",
			"MANDATORY" => "N");
		$arFieldName = array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch()):
			$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_DATE");
			$arFieldName[$arLanguage["LID"]] = (empty($arFieldName[$arLanguage["LID"]]) ? "Date" : $arFieldName[$arLanguage["LID"]]);
		endwhile;
		$arFields["EDIT_FORM_LABEL"] = $arFieldName;
		$obUserField  = new CUserTypeEntity;
		$obUserField->Add($arFields);
		$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
	}

}

if (empty($arUserFields) || empty($arUserFields["UF_PASSWORD"]))
{
	$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION", "FIELD_NAME" => "UF_PASSWORD"));
	if (!($db_res && $res = $db_res->GetNext()))
	{
		$arFields = Array(
			"ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION",
			"FIELD_NAME" => "UF_PASSWORD",
			"USER_TYPE_ID" => "string",
			"MULTIPLE" => "N",
			"MANDATORY" => "N");
		$arFieldName = array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch()):
			$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_PASSWORD");
			$arFieldName[$arLanguage["LID"]] = (empty($arFieldName[$arLanguage["LID"]]) ? "Password" : $arFieldName[$arLanguage["LID"]]);
		endwhile;
		$arFields["EDIT_FORM_LABEL"] = $arFieldName;
		$obUserField  = new CUserTypeEntity;
		$obUserField->Add($arFields);
		$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
	}
}

if ((empty($arUserFields) || empty($arUserFields["UF_DATE"]) || empty($arUserFields["UF_PASSWORD"])))
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$iblockId."_SECTION", $arResult["SECTION"]["ID"], LANGUAGE_ID);
}
$arResult["SECTION"]["~DATE"] = $arUserFields["UF_DATE"];
$arResult["SECTION"]["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
/********************************************************************
				/Data
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["bVarsFromForm"] = false;
if ($arParams["ACTION"] != "NEW")
{
	$arResult["FORM"]["ACTIVE"] = $arResult["SECTION"]["ACTIVE"];
	$arResult["FORM"]["NAME"] = htmlspecialcharsEx($arResult["SECTION"]["~NAME"]);
	$arResult["FORM"]["DESCRIPTION"] = htmlspecialcharsEx($arResult["SECTION"]["~DESCRIPTION"]);
	$arResult["FORM"]["~DATE"] = $arResult["SECTION"]["~DATE"];
	$arResult["FORM"]["~PASSWORD"] = $arResult["SECTION"]["~PASSWORD"];
}
else
{
	$arResult["FORM"]["ACTIVE"] = "";
	$arResult["FORM"]["NAME"] = "";
	$arResult["FORM"]["DESCRIPTION"] = "";
	$arResult["FORM"]["IBLOCK_SECTION_ID"] = ($arParams["SECTION_ID"] > 0 && $arParams["SECTION_ID"] != $arResult["GALLERY"]["ID"] ? $arParams["SECTION_ID"] : 0);
	$arResult["FORM"]["~DATE"] = $arResult["SECTION"]["~DATE"];
	$arResult["FORM"]["~DATE"]["VALUE"] = GetTime(time());
	$arResult["FORM"]["~PASSWORD"] = $arResult["SECTION"]["~PASSWORD"];
	$arResult["FORM"]["~PASSWORD"]["VALUE"] = "";
}

if ($bVarsFromForm)
{
	$arResult["bVarsFromForm"] = true;
	$arResult["FORM"]["ACTIVE"] = ($_REQUEST["ACTIVE"] == "Y" ? "Y" : "N");
	$arResult["FORM"]["NAME"] = htmlspecialcharsbx($_REQUEST["NAME"]);
	$arResult["FORM"]["DESCRIPTION"] = htmlspecialcharsbx($_REQUEST["DESCRIPTION"]);
	$arResult["FORM"]["DATE"] = $arResult["SECTION"]["~DATE"];
	$arResult["FORM"]["DATE"]["VALUE"] =  htmlspecialcharsbx($_REQUEST["UF_DATE"]);
}

if (intVal($arResult["SECTION"]["ID"]) > 0)
{
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["SECTION_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
}
elseif ($arParams["BEHAVIOUR"] == "USER")
{
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["GALLERY_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"]));
}
else
{
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array());
}

$arResult["SECTION"] = (is_array($arResult["SECTION"]) ? $arResult["SECTION"] : array());
$arResult["SECTION"]["~EDIT_LINK"] = CComponentEngine::MakePathFromTemplate(
	$arParams["~SECTION_EDIT_URL"],
	array(
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"ACTION" => "edit"
	)
);

$arResult["SECTION"]["~EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate(
	$arParams["~SECTION_EDIT_ICON_URL"],
	array(
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"ACTION" => "edit"
	));

$arResult["SECTION"]["~DROP_LINK"] = CComponentEngine::MakePathFromTemplate(
	$arParams["~SECTION_EDIT_URL"],
	array(
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"ACTION" => "drop"
	));

	$arResult["SECTION"]["~DROP_LINK"] .= (strpos($arResult["SECTION"]["~DROP_LINK"], "?") === false ? "?" : "&").bitrix_sessid_get()."&edit=Y";

$arResult["SECTION"]["EDIT_ICON_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~EDIT_ICON_LINK"]);
$arResult["SECTION"]["DROP_LINK"] = htmlspecialcharsbx($arResult["SECTION"]["~DROP_LINK"]);

/********************************************************************
				/Data
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arParams["ACTION"] == "NEW" ? GetMessage("IBLOCK_NEW") : $arResult["SECTION"]["~NAME"].GetMessage("IBLOCK_EDIT_TITLE"));
$arResult["SECTION"]["PATH"] = (is_array($arResult["SECTION"]["PATH"]) ? $arResult["SECTION"]["PATH"] : array());
/************** Chain Items ****************************************/
if ($arParams["SET_NAV_CHAIN"] != "N")
{
	$bFound = ($arParams["BEHAVIOUR"] != "USER");
	foreach ($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFound)
		{
			$bFound = ($arResult["GALLERY"]["ID"] == $arPath["ID"]);
			continue;
		}
		$APPLICATION->AddChainItem($arPath["NAME"],
			CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
	}
	$APPLICATION->AddChainItem($arParams["ACTION"] == "NEW" ? GetMessage("IBLOCK_NEW") : GetMessage("IBLOCK_EDIT"));
}
/********************************************************************
				/Standart
********************************************************************/
?>