<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage('B_B_DRAFT_TITLE'));
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	
$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$arResult["OK_MESSAGE"] = Array();
$arResult["ERROR_MESSAGE"] = Array();

if(!is_array($arParams["POST_PROPERTY_LIST"]))
	$arParams["POST_PROPERTY_LIST"] = Array("UF_BLOG_POST_DOC");
else
	$arParams["POST_PROPERTY_LIST"][] = "UF_BLOG_POST_DOC";

$user_id = IntVal($USER->GetID());

$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $user_id);

if(!empty($arBlog) && $arBlog["ACTIVE"] == "Y")
{
		$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
		if($arGroup["SITE_ID"] == SITE_ID)
		{
			$arResult["BLOG"] = $arBlog;
			
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(str_replace("#NAME#", $arBlog["NAME"], GetMessage("B_B_DRAFT_TITLE_BLOG")));
			if($arParams["SET_NAV_CHAIN"]=="Y")
				$APPLICATION->AddChainItem($arBlog["NAME"], CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"])));

			if($arResult["PostPerm"]>=BLOG_PERMS_WRITE)
			{
				$errorMessage = "";
				$okMessage = "";
				if (IntVal($_GET["del_id"]) > 0)
				{
					if (check_bitrix_sessid() && CBlogPost::CanUserDeletePost(IntVal($_GET["del_id"]), $user_id))
					{
						$DEL_ID = IntVal($_GET["del_id"]);
						if (CBlogPost::Delete($DEL_ID))
						{
							$okMessage = GetMessage("B_B_DRAFT_M_DEL");
						}
						else
							$errorMessage = GetMessage("B_B_DRAFT_M_DEL_ERR");
					}
					else
						$errorMessage = GetMessage("B_B_DRAFT_M_DEL_RIGHTS");
				}
				elseif (IntVal($_GET["show_id"]) > 0)
				{
					if($_GET["success"] == "Y") 
					{
						$okMessage = GetMessage("BLOG_BLOG_BLOG_MES_SHOWED");
					}
					else
					{
						if (check_bitrix_sessid())
						{
							$show_id = IntVal($_GET["show_id"]);
							{
								if(CBlogPost::GetByID($show_id))
								{
									if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE)
									{
										if(CBlogPost::Update($show_id, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH)))
										{
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/first_page/");
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/pages/");
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/calendar/");
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/post/".$show_id."/");
											BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
											BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
											BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
											BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
											BXClearCache(True, "/".SITE_ID."/blog/groups/".$arResult["BLOG"]["GROUP_ID"]."/");
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/trackback/".$show_id."/");
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_out/");
											BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_all/");
											BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
											BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
											BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");

											LocalRedirect($APPLICATION->GetCurPageParam("show_id=".$show_id."&success=Y", Array("del_id", "sessid", "success", "show_id")));
										}
										else
											$errorMessage = GetMessage("BLOG_BLOG_BLOG_MES_SHOW_ERROR");
									}
									elseif($arResult["PostPerm"] == BLOG_PERMS_PREMODERATE)
									{
										if(!CBlogPost::Update($show_id, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
											$errorMessage = GetMessage("BLOG_BLOG_BLOG_MES_SHOW_ERROR");
									}
									else
										$errorMessage = GetMessage("BLOG_BLOG_BLOG_MES_SHOW_NO_RIGHTS");
								}
							}
						}
					}
				}

				if (StrLen($errorMessage) > 0)
					$arResult["ERROR_MESSAGE"][] = $errorMessage;
				if (StrLen($okMessage) > 0)
					$arResult["OK_MESSAGE"][] = $okMessage;			
				
				$arResult["POST"] = Array();
				$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
				$arPostColl1 = Array();
				$arPostColl2 = Array();

				$dbPost = CBlogPost::GetList(
					array("DATE_PUBLISH" => "DESC"),
					Array(
							"BLOG_ID" => $arBlog["ID"],
							"AUTHOR_ID" => $user_id,
							"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_DRAFT
					),
					false,
					false,
					Array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "BLOG_ACTIVE", "BLOG_URL", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "BLOG_USER_ALIAS", "BLOG_OWNER_ID", "VIEWS", "NUM_COMMENTS", "ATTACH_IMG", "BLOG_SOCNET_GROUP_ID", "DETAIL_TEXT_TYPE", "CATEGORY_ID")
				);
				while($arPost = $dbPost->Fetch())
				{
					$arPostColl1[] = $arPost;
				}
				
				$dbPost = CBlogPost::GetList(
					array("DATE_PUBLISH" => "DESC"),
					Array(
							"BLOG_ID" => $arBlog["ID"],
							"AUTHOR_ID" => $user_id,
							">DATE_PUBLISH" => ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL"),
							"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH
					),
					false,
					false,
					Array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "BLOG_ACTIVE", "BLOG_URL", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "BLOG_USER_ALIAS", "BLOG_OWNER_ID", "VIEWS", "NUM_COMMENTS", "ATTACH_IMG", "BLOG_SOCNET_GROUP_ID", "DETAIL_TEXT_TYPE", "CATEGORY_ID")
				);
				while($arPost = $dbPost->Fetch())
				{
					$arPostColl1[] = $arPost;
				}

				function CompareBlogDatePublish($ar1, $ar2)
				{
					$ts_ar1 = MakeTimeStamp($ar1["DATE_PUBLISH"]);

					$ts_ar2 = MakeTimeStamp($ar2["DATE_PUBLISH"]);

					if (MakeTimeStamp($ar1["DATE_PUBLISH"]) < MakeTimeStamp($ar2["DATE_PUBLISH"]))
						return 1;
					return -1;
				}

				uasort($arPostColl1, 'CompareBlogDatePublish');
				
				$arParserParams = Array(
					"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
					"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
				);
				
				foreach($arPostColl1 as $arPost)
				{
					$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
					$arImages = array();
					$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "N"));
					while ($arImage = $res->Fetch())
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

					if($arResult["PostPerm"] >= BLOG_PERMS_MODERATE || ($arResult["PostPerm"] >= BLOG_PERMS_PREMODERATE && $arPost["AUTHOR_ID"] == $user_id))
					{
						$arPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"], "user_id" => $arBlog["OWNER_ID"]));
						if($arResult["PostPerm"] >= BLOG_PERMS_FULL)
						{
							$arPost["urlToDelete"] = urlencode($APPLICATION->GetCurPageParam("del_id=".$arPost["ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid", "success")));
							$arPost["urlToDelete"] = htmlspecialcharsbx($arPost["urlToDelete"]);
						}
						if($arResult["PostPerm"] >= BLOG_PERMS_MODERATE)
						{
							$arPost["urlToShow"] = urlencode($APPLICATION->GetCurPageParam("show_id=".$arPost["ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid", "show_id", "success")));
							$arPost["urlToShow"] = htmlspecialcharsbx($arPost["urlToShow"]);
						}

					}
					if(strlen($arPost["CATEGORY_ID"])>0)
					{
						$arCategory = explode(",",$arPost["CATEGORY_ID"]);
						foreach($arCategory as $v)
						{
							if(IntVal($v)>0)
							{
								$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
								$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v, "user_id" => $arParams["USER_ID"]));
								$arPost["CATEGORY"][] = $arCatTmp;
							}
						}
					}

					$arPost["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
					$arPost["DATE_PUBLISH_DATE"] = ConvertDateTime($arPost["DATE_PUBLISH"], FORMAT_DATE);
					$arPost["DATE_PUBLISH_TIME"] = ConvertDateTime($arPost["DATE_PUBLISH"], "HH:MI");
					$arPost["DATE_PUBLISH_D"] = ConvertDateTime($arPost["DATE_PUBLISH"], "DD");
					$arPost["DATE_PUBLISH_M"] = ConvertDateTime($arPost["DATE_PUBLISH"], "MM");
					$arPost["DATE_PUBLISH_Y"] = ConvertDateTime($arPost["DATE_PUBLISH"], "YYYY");

					$arResult["POST"][] = $arPost;
				}
			}
			else
				$arResult["FATAL_ERROR"] = GetMessage("B_B_DRAFT_NO_R_CR");
		}
		else
			$arResult["FATAL_ERROR"] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
}
else
{
	$arResult["FATAL_ERROR"] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
	CHTTP::SetStatus("404 Not Found");
}
	
$this->IncludeComponentTemplate();
?>