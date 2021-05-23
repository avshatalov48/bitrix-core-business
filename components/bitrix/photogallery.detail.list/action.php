<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->IncludeComponentLang("action.php");
if ($_REQUEST["detail_list_edit"] == "Y" && !empty($_REQUEST["ACTION"]) && !empty($_REQUEST["items"]))
{
	$arError = array();
	$bVarsFromForm = false;

	CModule::IncludeModule("iblock");
	$_REQUEST["TO_SECTION_ID"] = intval($_REQUEST["TO_SECTION_ID"]);

	if (!check_bitrix_sessid()): // SESSION
		$arError[] = array(
			"id" => "bad sessid",
			"text" => PhotoShowError(array("code" => "100")));
	elseif ($arParams["SECTION_ID"] <= 0): // SECTION_ID must be
		$arError[] = array(
			"id" => "empty section",
			"text" => PhotoShowError(array("code" => "102")));
	elseif ($arParams["PERMISSION"] < "U"):
		$arError[] = array(
			"id" => "bad permission",
			"text" => GetMessage("P_DENIED_ACCESS"));
	elseif ($_REQUEST["ACTION"] == "move"):
		if ($_REQUEST["TO_SECTION_ID"] <= 0):
			$arError[] = array(
				"id" => "BAD_SECTION_TO_MOVE",
				"text" => GetMessage("P_SECTION_EMPTY_TO_MOVE"));
		elseif ($_REQUEST["TO_SECTION_ID"] == $arParams["SECTION_ID"]):
			$arError[] = array(
				"id" => "BAD_SECTION_TO_MOVE",
				"text" => GetMessage("P_SECTION_THIS_TO_MOVE"));
		elseif ($arParams["BEHAVIOUR"] == "USER"):
			$arResult["SECTION_TO_MOVE"] = array();
			$db_res = CIBlockSection::GetList(array(), array(
				"ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_ACTIVE" => "Y", "ID" => $_REQUEST["TO_SECTION_ID"]));
			if (!($db_res && $res = $db_res->Fetch())):
				$arError[] = array(
					"id" => "bad section",
					"text" => PhotoShowError(array("code" => 102)));
			elseif ($arResult["GALLERY"]["LEFT_MARGIN"] >= $res["LEFT_MARGIN"] ||
					$arResult["GALLERY"]["RIGHT_MARGIN"] <= $res["RIGHT_MARGIN"]):
				$arError[] = array(
					"id" => "BAD_SECTION_TO_MOVE",
					"text" => GetMessage("P_SECTION_IS_NOT_IN_GALLERY"));
			else:
				$arResult["SECTION_TO_MOVE"] = $res;
			endif;
		endif;
	endif;

	if (empty($arError))
	{
		$iFileSize = 0;
		$bClearCacheDetailAll = false;
		@set_time_limit(0);

		foreach ($_REQUEST["items"] as $itemID)
		{
			$arFilter = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "SECTION_ID" => $arParams["SECTION_ID"], "ID" => $itemID, "CHECK_PERMISSIONS" => "Y");
			$arSelect = array(
				"ID",
				"CODE",
				"IBLOCK_ID",
				"IBLOCK_SECTION_ID",
				"SECTION_PAGE_URL",
				"NAME",
				"DETAIL_PICTURE",
				"PREVIEW_PICTURE",
				"PREVIEW_TEXT",
				"DETAIL_TEXT",
				"DETAIL_PAGE_URL",
				"PREVIEW_TEXT_TYPE",
				"DETAIL_TEXT_TYPE",
				"TAGS",
				"DATE_CREATE",
				"CREATED_BY",
				"PROPERTY_REAL_PICTURE",
				"PROPERTY_PUBLIC_ELEMENT",
				"PROPERTY_BLOG_POST_ID",
				"PROPERTY_FORUM_TOPIC_ID");

			$db_res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			if ($arRes = $db_res->Fetch())
			{
				$arRes["REAL_PICTURE"] = intval($arRes["PROPERTY_REAL_PICTURE_VALUE"]);
				$arRes["BLOG_POST_ID"] = intval($arRes["PROPERTY_BLOG_POST_ID_VALUE"]);
				$arRes["FORUM_TOPIC_ID"] = intval($arRes["PROPERTY_FORUM_TOPIC_ID_VALUE"]);

				$bClearCacheDetailAll = ($arRes["PROPERTY_PUBLIC_ELEMENT_VALUE"] == "Y" ? true : $bClearCacheDetailAll);

				if ($_REQUEST["ACTION"] == "drop"):
					$arRes["REAL_PICTURE"] = CFile::GetFileArray($arRes["REAL_PICTURE"]);
				endif;
			}
			else
			{
				$arError[] = array(
	   				"id" => "103",
	   				"text" => PhotoShowError(array("code" => "103", "data" => array("ID" => $itemID))));
	   			continue;
	   		}

			$APPLICATION->ResetException();
			$sectionsIds = array(0, $arParams["SECTION_ID"]);
			$arGalleriesIds = array(0);
			$arUsers = array();

			switch ($_REQUEST["ACTION"])
			{
				case "drop":
					if (!CIBlockElement::Delete($itemID))
					{
						$sError = GetMessage("P_DELETE_ERROR");
						if($ex = $APPLICATION->GetException())
							$sError = $ex->GetString();
			   			$arError[] = array(
			   				"id" => "drop error",
			   				"text" => PhotoShowError(array("code" => "NOT_DELETED", "title" => $sError, "DATA" => $arRes)));
			   			continue;
					}

					$iFileSize += intval($arRes["REAL_PICTURE"]["FILE_SIZE"]);

					if ($arRes["BLOG_POST_ID"] > 0)
					{
						CModule::IncludeModule("blog");
						$arPost = CBlogPost::GetByID($arRes["BLOG_POST_ID"]);
						$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
						CBlogPost::Delete($arRes["BLOG_POST_ID"]);
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]);
					}
					if ($arRes["FORUM_TOPIC_ID"] > 0)
					{
						CModule::IncludeModule("forum");
						ForumDeleteTopic($arRes["FORUM_TOPIC_ID"]);
					}

					$events = GetModuleEvents("photogallery", "OnAfterPhotoDrop");
					$arEventFields = array("ID" => $arRes["ID"], "SECTION_ID" => $arRes["IBLOCK_SECTION_ID"]);
					$sectionsIds[] = $arRes["IBLOCK_SECTION_ID"];

					while ($arEvent = $events->Fetch())
						ExecuteModuleEventEx($arEvent, array($arEventFields, $arParams));

					break;
				case "move":
					$bs = new CIBlockElement;
					$itemID = $bs->Update($itemID, array("MODIFIED_BY" => $USER->GetID(), "IBLOCK_SECTION" => $_REQUEST["TO_SECTION_ID"]));

					if ($itemID <= 0)
			   			$arError[] = array(
			   				"id" => "move error",
			   				"text" => PhotoShowError(array("ID" => $itemID, "code" => "NOT_UPDATED", "title" => $bs->LAST_ERROR, "DATA" => $arRes)));
					else
						$sectionsIds[] = $arRes["TO_SECTION_ID"];

					break;
			}
		}

		PClearComponentCacheEx($arParams["IBLOCK_ID"], $sectionsIds, $arGalleriesIds);
	}

	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	}
	elseif (!empty($_REQUEST["REDIRECT_URL"]))
	{
		LocalRedirect($_REQUEST["REDIRECT_URL"]);
	}
	$arResult["bVarsFromForm"] = ($bVarsFromForm ? "Y" : "N");
}
?>