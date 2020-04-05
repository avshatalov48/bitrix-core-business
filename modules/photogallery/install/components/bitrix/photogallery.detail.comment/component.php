<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));

/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["ELEMENT_ID"] = intVal($arParams["ELEMENT_ID"]);

	$arParams["COMMENTS_TYPE"] = ($arParams["COMMENTS_TYPE"] == "forum" ? "forum" : "blog");
	$arParams["IS_SOCNET"] = ($arParams["IS_SOCNET"] == "Y" ? "Y" : "N");

	// For blog
	$arParams["BLOG_URL"] = trim($arParams["BLOG_URL"]);
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array("detail" => "PAGE_NAME=detail&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}

	$arParams["DETAIL_URL"] = CComponentEngine::MakePathFromTemplate($arParams["DETAIL_URL"], array("USER_ALIAS" => isset($arParams["USER_ALIAS"]) ? $arParams["USER_ALIAS"] : 'empty'));

//***************** CACHE ******************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	if (intVal($_REQUEST['ELEMENT_ID']) > 0 && $_REQUEST['save_photo_comment'] == 'Y')
		PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]));
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default values
********************************************************************/
if (!IsModuleInstalled($arParams["COMMENTS_TYPE"]))
	return ShowError("Module is not installed (".$arParams["COMMENTS_TYPE"].")");
elseif ($arParams["COMMENTS_TYPE"] == "blog" && empty($arParams["BLOG_URL"]))
	return ShowError(GetMessage("P_EMPTY_BLOG_URL"));
elseif ($arParams["ELEMENT_ID"] <= 0)
	return;

$cache_path = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["IBLOCK_ID"]);

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
/********************************************************************
				/Default values
********************************************************************/
if ($arParams["COMMENTS_TYPE"] == "forum")
{
	if ($arParams["IS_SOCNET"] == "Y")
	{
		$cache = new CPHPCache;
		$cache_id = serialize(
			array(
				"TYPE" => $arParams["COMMENTS_TYPE"],
				"ELEMENT_ID" => $arParams["ELEMENT_ID"],
				"USER_ALIAS" => $arParams["USER_ALIAS"]
			)
		);

		if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache(3600*24, $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			if (intval($res["FORUM_ID"]) > 0)
				$arParams["FORUM_ID"] = $res["FORUM_ID"];
		}
		else
		{
			CModule::IncludeModule("iblock");

			//SELECT
			$arSelect = array(
				"ID",
				"IBLOCK_ID",
				"PROPERTY_FORUM_TOPIC_ID",
			);

			//WHERE
			$arFilter = array(
				"ID" => $arParams["ELEMENT_ID"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			);

			//EXECUTE
			$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			if ($obElement = $rsElement->GetNextElement())
			{
				$arElement = $obElement->GetFields();
				if (intval($arElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]) > 0 && CModule::IncludeModule("forum"))
					if ($arForumTopic = CForumTopic::GetByID($arElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]))
						$arParams["FORUM_ID"] = $arForumTopic["FORUM_ID"];
			}

			$cache->StartDataCache(3600*24, $cache_id, $cache_path);
			$cache->EndDataCache(array("FORUM_ID" => $arParams["FORUM_ID"]));
		}
	}

	if (class_exists('CSocNetPhotoCommentEvent'))
	{
		$obPhotoCommentEventHandler = new CSocNetPhotoCommentEvent;
		$obPhotoCommentEventHandler->SetVars($arParams, $arResult);
		if (method_exists($obPhotoCommentEventHandler, "OnAfterPhotoCommentAddForum"))
			AddEventHandler("forum", "onAfterMessageAdd", array($obPhotoCommentEventHandler, "OnAfterPhotoCommentAddForum"));
	}
}
elseif ($arParams["COMMENTS_TYPE"] == "blog")
{
	/*************************************************************************
				Caching
	*************************************************************************/
	/*************************************************************************
			Before caching
	*************************************************************************/
	// Clear cache.
	if (isset($_REQUEST["parentId"]) || $_REQUEST["save_product_review"] == "Y" || isset($_REQUEST["delete_comment_id"]))
		PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]), array($arParams["USER_ALIAS"]));

	/*************************************************************************
			/Before caching
	*************************************************************************/
	$arResult["ELEMENT"] = array();
	$cache = new CPHPCache;
	$cache_id = serialize(
		array(
			"TYPE" => $arParams["COMMENTS_TYPE"],
			"USER" => $USER->GetGroups(),
			"ELEMENT_ID" => $arParams["ELEMENT_ID"],
			"USER_ALIAS" => $arParams["USER_ALIAS"]
		)
	);

	if(($tzOffset = CTimeZone::GetOffset()) <> 0)
		$cache_id .= "_".$tzOffset;

	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["ELEMENT"] = $res["ELEMENT"];
		$arResult["COMMENT_ID"] = $res["COMMENT_ID"];
	}
	else
	{
		CModule::IncludeModule("iblock");
		CModule::IncludeModule("blog");

		//SELECT
		$arSelect = array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"SECTION_PAGE_URL",
			"NAME",
			"ACTIVE",
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
			"PROPERTY_BLOG_POST_ID",
			"PROPERTY_BLOG_COMMENTS_CNT"
		);
		//WHERE
		$arFilter = array(
			"ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "Y"
		);

		//EXECUTE
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
		if (!$obElement = $rsElement->GetNextElement())
		{
			ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
			@define("ERROR_404", "Y");
			CHTTP::SetStatus("404 Not Found");
			return false;
		}

		$arResult["ELEMENT"] = $obElement->GetFields();
		if ($arResult["ELEMENT"]["ACTIVE"] != "Y")
			return false;

		$arResult["ELEMENT"]["PROPETIES"] = array();
		foreach ($arResult["ELEMENT"] as $key => $val)
		{
			if ((substr($key, 0, 9) == "PROPERTY_" && substr($key, -6, 6) == "_VALUE"))
				$arResult["ELEMENT"]["PROPERTIES"][substr($key, 9, intVal(strLen($key)-15))] = array("VALUE" => $val);
		}

		$arGallery = array("CODE" => "");
		if (strpos($arParams["~DETAIL_URL"], "#USER_ALIAS#") !== false)
		{
			CModule::IncludeModule("iblock");
			$arFilter = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ID" => $arResult["ELEMENT"]["IBLOCK_SECTION_ID"]);
			$db_res = CIBlockSection::GetList(
				array(),
				$arFilter,
				false,
				array("ID", "ACTIVE", "CODE", "RIGHT_MARGIN", "LEFT_MARGIN")
			);
			if ($db_res && $arSection = $db_res->Fetch())
			{
				$db_res = CIBlockSection::GetList(
					array(),
					array(
						"IBLOCK_ID" => $arSection["IBLOCK_ID"],
						"SECTION_ID" => 0,
						"!LEFT_MARGIN" => $arSection["LEFT_MARGIN"],
						"!RIGHT_MARGIN" => $arSection["RIGHT_MARGIN"],
						"!ID" => $arSection["ID"]),
					false,
					array("ID", "CODE", "RIGHT_MARGIN", "LEFT_MARGIN"));
				if ($db_res)
				{
					$arGallery = $db_res->Fetch();
				}
			}
		}
		$arResult["ELEMENT"]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], array("USER_ALIAS" => $arGallery["CODE"],"SECTION_ID" => $arResult["ELEMENT"]["IBLOCK_SECTION_ID"], "ELEMENT_ID" =>$arResult["ELEMENT"]["ID"]));
		$arResult["ELEMENT"]["DETAIL_PAGE_URL"] = htmlspecialcharsbx($arResult["ELEMENT"]["~DETAIL_PAGE_URL"]);

		$obProperty = false;
		$iCommentID = 0;

		/************** BLOG *****************************************************/
		$obProperty = new CIBlockProperty;
		if (is_set($arResult["ELEMENT"]["PROPERTIES"], "BLOG_POST_ID"))
		{
			$iCommentID = intVal($arResult["ELEMENT"]["PROPERTIES"]["BLOG_POST_ID"]["VALUE"]);
		}
		else
		{
			$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_BLOG_POST_ID")) <= 0 ? "BLOG_POST_ID" : GetMessage("P_BLOG_POST_ID")),
					"CODE" => "BLOG_POST_ID"
				)
			);
		}

		if (!is_set($arResult["ELEMENT"], "PROPERTY_BLOG_COMMENTS_CNT_VALUE"))
		{
			$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_BLOG_COMMENTS_CNT")) <= 0 ? "P_BLOG_COMMENTS_CNT" : GetMessage("P_BLOG_COMMENTS_CNT")),
					"CODE" => "BLOG_COMMENTS_CNT"
				)
			);
		}

		if ($iCommentID > 0)
		{
			$arPost = CBlogPost::GetByID($iCommentID);
			if (!$arPost)
				$iCommentID = 0;
			elseif (intVal($arPost["NUM_COMMENTS"]) > 0 && $arPost["NUM_COMMENTS"] != $arResult["ELEMENT"]["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"])
				CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], intVal($arPost["NUM_COMMENTS"]), "BLOG_COMMENTS_CNT");
		}

		if (!$iCommentID && isset($_REQUEST["parentId"]))
		{
			$arCategory = array();
			$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
			if (!empty($arResult["ELEMENT"]["TAGS"]))
			{
				$arCategoryVal = explode(",", $arResult["ELEMENT"]["TAGS"]);
				foreach($arCategoryVal as $k => $v)
				{
					if ($id = CBlogCategory::Add(array("BLOG_ID"=>$arBlog["ID"],"NAME"=>$v)))
						$arCategory[] = $id;
				}
			}

			$arResult["ELEMENT"]["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["DETAIL_PICTURE"]);
			$arResult["ELEMENT"]["REAL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"]["VALUE"]);

			$arFields=array(
				"TITLE"			=> $arResult["ELEMENT"]["NAME"],
				"DETAIL_TEXT"		=>
					"[IMG]http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["DETAIL_PICTURE"]["SRC"]."[/IMG]\n".
					"[URL=http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["~DETAIL_PAGE_URL"]."]".$arResult["ELEMENT"]["NAME"]."[/URL]\n".
					(!empty($arResult["ELEMENT"]["TAGS"]) ? $arResult["ELEMENT"]["TAGS"]."\n" : "").
					$arResult["ELEMENT"]["~DETAIL_TEXT"]."\n".
					"[URL=http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["REAL_PICTURE"]["SRC"]."]".GetMessage("P_ORIGINAL")."[/URL]",
				"CATEGORY_ID"		=> implode(",", $arCategory),
				"PUBLISH_STATUS"	=> "P",
				"PERMS_POST"	=> array(),
				"PERMS_COMMENT"	=> array(),
				"=DATE_CREATE"	=> $DB->GetNowFunction(),
				"=DATE_PUBLISH"	=> $DB->GetNowFunction(),
				"AUTHOR_ID"	=>	(!empty($arResult["ELEMENT"]["CREATED_BY"]) ? $arResult["ELEMENT"]["CREATED_BY"] : 1),
				"BLOG_ID"	=> $arBlog["ID"],
				"ENABLE_TRACKBACK" => "N");

			$newID = CBlogPost::Add($arFields);
			if ($newID > 0)
			{
				foreach($arCategory as $key)
					CBlogPostCategory::Add(Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $newID, "CATEGORY_ID"=>$key));

					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]);
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
				$iCommentID = $newID;
				CIBlockElement::SetPropertyValues($arResult["ELEMENT"]["ID"], $arParams["IBLOCK_ID"], $iCommentID, "BLOG_POST_ID");
			}
		}
		$arResult["COMMENT_ID"] = $iCommentID;
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("COMMENT_ID" => $arResult["COMMENT_ID"], "ELEMENT" => $arResult["ELEMENT"]));
		}
	}
	/*************************************************************************
					/Caching
	*************************************************************************/
	if (class_exists('CSocNetPhotoCommentEvent'))
	{
		$obPhotoCommentEventHandler = new CSocNetPhotoCommentEvent;
		$obPhotoCommentEventHandler->SetVars($arParams, $arResult);
		if (method_exists($obPhotoCommentEventHandler, "OnAfterPhotoCommentAddBlog"))
			AddEventHandler("blog", "OnCommentAdd", array($obPhotoCommentEventHandler, "OnAfterPhotoCommentAddBlog"));
		if (method_exists($obPhotoCommentEventHandler, "OnAfterPhotoCommentDeleteBlog"))
			AddEventHandler("blog", "OnCommentDelete", array($obPhotoCommentEventHandler, "OnAfterPhotoCommentDeleteBlog"));
	}
}

CUtil::InitJSCore(array('window', 'ajax'));
$this->IncludeComponentTemplate();
?>