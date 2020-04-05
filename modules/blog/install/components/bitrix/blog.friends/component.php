<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);
if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("B_B_FR_TITLE"));

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&calegory=#category#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
if(!is_array($arParams["POST_PROPERTY_LIST"]))
	$arParams["POST_PROPERTY_LIST"] = Array("UF_BLOG_POST_DOC");
else
	$arParams["POST_PROPERTY_LIST"][] = "UF_BLOG_POST_DOC";

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

if(IntVal($arParams["ID"])>0)
{
	$arBlogUser = CBlogUser::GetByID($arParams["ID"], BLOG_BY_USER_ID);
	$arBlogUser = CBlogTools::htmlspecialcharsExArray($arBlogUser);

	if ($arBlogUser)
	{
		if ($USER->IsAuthorized()
			&& $USER->GetID() == $arBlogUser["USER_ID"])
		{
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(GetMessage("B_B_FR_TITLES"));
		}
		else
		{
			$dbUser = CUser::GetByID($arBlogUser["USER_ID"]);
			$arUser = $dbUser->GetNext();

			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(str_replace("#NAME#", CBlogUser::GetUserName($arBlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]), GetMessage("B_B_FR_TITLE_OF")));
		}

		$dbList = CBlogUser::GetUserFriendsList($arParams["ID"], $USER->GetID(), $USER->IsAuthorized(), $arParams["MESSAGE_COUNT"], $arParams["GROUP_ID"]);
		$arResult["FRIENDS_POSTS"] = Array();
		$arResult["IDS"] = Array();
		$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
		$arParserParams = Array(
			"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
			"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
		);

		while($arList = $dbList->Fetch())
		{
			$arResult["IDS"][] = $arList["ID"];
			$arPost = CBlogPost::GetByID($arList["ID"]);

			$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
			$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
			$arPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"])));
			$arPost["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));
			if($arPost["AUTHOR_ID"] == $arBlog["OWNER_ID"])
			{
				$arPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
			}
			else
			{
				if($arOwnerBlog = CBlog::GetByOwnerID($arPost["AUTHOR_ID"], $arParams["GROUP_ID"]))
					$arPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"]));
			}

			$arImages = array();
			$dbImage = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "N"));
			while ($arImage = $dbImage->Fetch())
			{
				$arImages[$arImage['ID']] = $arImage['FILE_ID'];
				$arPost["arImages"][$arImage['ID']] = Array(
									"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=70&height=70&type=square",
									"full" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=1000&height=1000"
								);
			}

			if($arPost["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
			{
				$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
				if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
					$arAllow["VIDEO"] = "N";
				$arPost["TEXT_FORMATED"] = $p->convert($arPost["~DETAIL_TEXT"], true, $arImages, $arAllow, $arParserParams);
			}
			else
			{
				$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
				if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
					$arAllow["VIDEO"] = "N";
				$arPost["TEXT_FORMATED"] = $p->convert($arPost["~DETAIL_TEXT"], true, $arImages, $arAllow, $arParserParams);
			}

			$arPost["IMAGES"] = $arImages;
			if(!empty($p->showedImages))
			{
				foreach($p->showedImages as $val)
				{
					if(!empty($arPost["arImages"][$val]))
						unset($arPost["arImages"][$val]);
				}
			}

			$arPost["POST_PROPERTIES"] = array("SHOW" => "N");
			if (!empty($arParams["POST_PROPERTY_LIST"]))
			{
				$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arPost["ID"], LANGUAGE_ID);

				if (count($arParams["POST_PROPERTY_LIST"]) > 0)
				{
					foreach ($arPostFields as $FIELD_NAME => $arPostField)
					{
						if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY_LIST"]))
							continue;
						$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
						$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
						$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
						$arPost["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
					}
				}
				if (!empty($arPost["POST_PROPERTIES"]["DATA"]))
					$arPost["POST_PROPERTIES"]["SHOW"] = "Y";
			}

			$arPost["BlogUser"] = CBlogUser::GetByID($arPost["AUTHOR_ID"], BLOG_BY_USER_ID);
			$arPost["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arPost["BlogUser"]);
			$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
			$arPost["arUser"] = $dbUser->GetNext();
			$arPost["AuthorName"] = CBlogUser::GetUserName($arPost["BlogUser"]["ALIAS"], $arPost["arUser"]["NAME"], $arPost["arUser"]["LAST_NAME"], $arPost["arUser"]["LOGIN"]);

			if (preg_match("/(\[CUT\])/i",$arPost["DETAIL_TEXT"]) || preg_match("/(<CUT>)/i",$arPost["DETAIL_TEXT"]))
				$arPost["CUT"] = "Y";

			if(strlen($arPost["CATEGORY_ID"])>0)
			{
				$arCategory = explode(",",$arPost["CATEGORY_ID"]);
				foreach($arCategory as $v)
				{
					if(IntVal($v)>0)
					{
						$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
						$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v));
						$arPost["Category"][] = $arCatTmp;
					}
				}
			}

			$arPost["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
			$arResult["FRIENDS_POSTS"][] = array("POST" => $arPost, "BLOG" => $arBlog);
		}
		if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
			$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["IDS"]);
	}
	else
	{
		$arResult["FATAL_MESSAGE"] = GetMessage("B_B_FR_NO_USER");
		CHTTP::SetStatus("404 Not Found");
	}
}
else
{
	$arResult["FATAL_MESSAGE"] = GetMessage("B_B_FR_NO_USER");
	CHTTP::SetStatus("404 Not Found");
}

$this->IncludeComponentTemplate();
?>