<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule("idea"))
{
	ShowError(GetMessage("IDEA_MODULE_NOT_INSTALL"));
	return;
}

if(!is_array($arParams["POST_BIND_USER"]))
	$arParams["POST_BIND_USER"] = array();

$arResult["IDEA_MODERATOR"] = false;
if((!empty($arParams["POST_BIND_USER"]) && array_intersect($USER->GetUserGroupArray(), $arParams["POST_BIND_USER"]))
	||$USER->IsAdmin()
)
	$arResult["IDEA_MODERATOR"] = true;

$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
$bSoNet = false;

$arResult["bSoNet"] = $bSoNet;
$arResult["POST_PROPERTIES"] = array();
$arParams["ID"] = trim($arParams["ID"]);

$bIDbyCode = false;
if(!is_numeric($arParams["ID"]) || mb_strlen(intval($arParams["ID"])) != mb_strlen($arParams["ID"]))
{
	$arParams["ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["~ID"]));
	$bIDbyCode = true;
}
else
	$arParams["ID"] = intval($arParams["ID"]);

if($arParams["ID"] == '')
{
	ShowError(GetMessage("B_B_MES_NO_POST"));
	@define("ERROR_404", "Y");
	CHTTP::SetStatus("404 Not Found");
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
if($arParams["POST_VAR"] == '')
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if($arParams["PATH_TO_BLOG"] == '')
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if($arParams["PATH_TO_BLOG_CATEGORY"] == '')
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if($arParams["PATH_TO_POST_EDIT"] == '')
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	
$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);

if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
if (!array_key_exists("PATH_TO_MESSAGES_CHAT", $arParams))
	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
if (!array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

if (trim($arParams["NAME_TEMPLATE"]) == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";	

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams["IMAGE_MAX_WIDTH"] = intval($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = intval($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
$arParams["SMILES_COUNT"] = intval($arParams["SMILES_COUNT"]);
	
$user_id = $USER->GetID();
$arResult["USER_ID"] = $user_id;

//Get Idea subscribtion
$arResult["USER_IDEA_SUBSCRIBE"] = array();
if($arResult["USER_ID"]>0)
{
	$oIdeaSubscribe = CIdeaManagment::getInstance()->Notification()->getEmailNotify()->GetList(
		array(),
		array("USER_ID" => $arResult["USER_ID"]),
		false,
		false,
		array("ID")
	);
	while($r = $oIdeaSubscribe->Fetch())
		$arResult["USER_IDEA_SUBSCRIBE"][] = $r["ID"];
}
//END -> Get Idea subscribtion

$arBlog = CBlogTools::htmlspecialcharsExArray(
	CBlog::GetByUrl($arParams["BLOG_URL"])
);

$arResult["BLOG"] = $arBlog;
$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);

if(!empty($arBlog) && $arBlog["ACTIVE"] == "Y" && $arGroup["SITE_ID"] == SITE_ID)
{
	if($bIDbyCode)
			$arParams["ID"] = CBlogPost::GetID($arParams["ID"], $arBlog["ID"]);

	$arPost = CBlogPost::GetByID($arParams["ID"]);
	if(empty($arPost) && !$bIDbyCode)
	{
		$arParams["ID"] = CBlogPost::GetID($arParams["ID"], $arBlog["ID"]);
		$arPost = CBlogPost::GetByID($arParams["ID"]);
	}
	
		if(!($arPost
			&& ($arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
				|| ($arResult["IDEA_MODERATOR"] && in_array($arPost["PUBLISH_STATUS"], array(BLOG_PUBLISH_STATUS_PUBLISH, BLOG_PUBLISH_STATUS_READY)))
			))
		)
			unset($arPost);
			
	if(!empty($arPost) && $arBlog["ID"] == $arPost["BLOG_ID"])
	{
		CBlogPost::CounterInc($arParams["ID"]);

		$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
		$arResult["Post"] = $arPost;
		
		if(!$bSoNet)
			$arResult["PostPerm"] = CBlogPost::GetBlogUserPostPerms($arParams["ID"], $arResult["USER_ID"]);
			
		if($arPost["AUTHOR_ID"] == $arBlog["OWNER_ID"])
			$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arPost["AUTHOR_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
		else
		{
			$arOwnerBlog = CBlog::GetByOwnerID($arPost["AUTHOR_ID"], $arParams["GROUP_ID"]);
			$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"], "user_id" => $arPost["AUTHOR_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
		}

		$arResult["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => ($arPost["AUTHOR_ID"] == $arBlog["OWNER_ID"] ? $arBlog["URL"] : $arOwnerBlog["URL"]), "post_id"=>CBlogPost::GetPostID($arResult["Post"]["ID"], $arResult["Post"]["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));

		if ($_GET["delete"]=="Y" && $arResult["IDEA_MODERATOR"]) //Delete message
		{
			if (check_bitrix_sessid() && (!$bSoNet && CBlogPost::CanUserDeletePost(intval($arParams["ID"]), ($USER->IsAuthorized() ? $arResult["USER_ID"] : 0 )) || ($bSoNet && CBlogSoNetPost::CanUserDeletePost(intval($arParams["ID"]), $user_id, $arParams["USER_ID"], $arParams["SOCNET_GROUP_ID"]))))
			{
								//Remove Sonet
								$Notify = CIdeaManagment::getInstance()->Notification(
									array("TYPE" => "IDEA", "ID" => $arParams["ID"])
								)->getSonetNotify()
								->Remove();

				if (CBlogPost::Delete($arParams["ID"]))
				{
					BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
					BXClearCache(True, '/'.SITE_ID.'/idea/tags/');
					BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
					BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
					BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arParams["ID"]."/");
					//RSS
					BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/rss_list");

					$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
					if(mb_strpos("?", $url) === false)
							$url .= "?";
					else
							$url .= "&";
					$url .= "del_id=".$arParams["ID"]."&success=Y";

					LocalRedirect($url);
				}
				else
					$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR").'<br />';
			}
			else
				$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS").'<br />';
		}
		if ($_GET["hide"]=="Y" && $arResult["IDEA_MODERATOR"]) //Hide message
		{
			if($_GET["success"] == "Y"){}
			elseif (check_bitrix_sessid())
			{
				if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE)
				{
					if(CBlogPost::Update($arParams["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
					{
						//Socialnetwork notification
						$Notify = CIdeaManagment::getInstance()->Notification(array("ID" => $arParams["ID"]));
						$Notify->getSonetNotify()->HideMessage();

						BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
						BXClearCache(True, '/'.SITE_ID.'/idea/tags/');
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arParams["ID"]."/");
						//RSS
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/rss_list");

						$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("post_id" => $arParams["ID"], "blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
						if(mb_strpos("?", $url) === false)
							$url .= "?";
						else
							$url .= "&";
						$url .= "hide=Y&success=Y";
						
						LocalRedirect($url);
					}
					else
						$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR").'<br />';
				}
				else
					$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS").'<br />';
			}
		}
		if ($_GET["show"]=="Y" && $arResult["IDEA_MODERATOR"]) //Show message
		{
			if($_GET["success"] == "Y"){}
			elseif (check_bitrix_sessid())
			{
				if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE)
				{
					if(CBlogPost::Update($arParams["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH)))
					{
						//Socialnetwork notification
						$Notify = CIdeaManagment::getInstance()->Notification(array("ID" => $arParams["ID"]));
						$Notify->getSonetNotify()->ShowMessage();

						BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
						BXClearCache(True, '/'.SITE_ID.'/idea/tags/');
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arParams["ID"]."/");

						//RSS
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/rss_list");

						//if ($bSoNet)
						//	CBlogPost::DeleteLog($arParams["ID"]);

						$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("post_id" => $arParams["ID"], "blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
						if(mb_strpos("?", $url) === false)
							$url .= "?";
						else
							$url .= "&";
						$url .= "show=Y&success=Y";
						
						LocalRedirect($url);
					}
					else
						$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR").'<br />';
				}
				else
					$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS").'<br />';
			}
		}

		if($arResult["PostPerm"] > BLOG_PERMS_DENY)
		{
			if(!empty($arPost) && $arPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_DRAFT)
			{
				if($arPost["PUBLISH_STATUS"] == "P" || $arResult["PostPerm"] == BLOG_PERMS_FULL || $arPost["AUTHOR_ID"] == $arResult["USER_ID"])
				{
					if($arParams["SET_TITLE"]=="Y")
						$APPLICATION->SetTitle($arPost["TITLE"]);

					$cache = new CPHPCache;
					$cache_id = "blog_message_".serialize($arParams)."_".$arResult["PostPerm"].intval($arResult["IDEA_MODERATOR"]);
					if($arPost["AUTHOR_ID"] == $arResult["USER_ID"])
						$cache_id .= "_author";
					if(($tzOffset = CTimeZone::GetOffset()) <> 0)
						$cache_id .= "_".$tzOffset;
					$cache_path = "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arPost["ID"]."/";

					if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
					{
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/wmvplayer/wmvscript.js");
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/wmvplayer/silverlight.js");
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js");
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/mediaplayer/flvscript.js");
						$Vars = $cache->GetVars();
						foreach($Vars["arResult"] as $k=>$v)
							$arResult[$k] = $v;

						$template = new CBitrixComponentTemplate();
						$template->ApplyCachedData($Vars["templateCachedData"]);

						$cache->Output();
					}
					else
					{
						if ($arParams["CACHE_TIME"] > 0)
							$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

						$arResult["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

						$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
						
						$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arBlog['ID'], "IS_COMMENT" => "N"));
						while ($arImage = $res->Fetch())
							$arImages[$arImage['ID']] = $arImage['FILE_ID'];

						$arParserParams = Array(
							"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
							"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
						);
						if($arPost["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
						{
							$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
							if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
								$arAllow["VIDEO"] = "N";
						
							$arResult["Post"]["textFormated"] = $p->convert($arPost["~DETAIL_TEXT"], false, $arImages, $arAllow, $arParserParams);
						}
						else
						{
							$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
							if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
								$arAllow["VIDEO"] = "N";
						
							$arResult["Post"]["textFormated"] = $p->convert($arPost["~DETAIL_TEXT"], false, $arImages, $arAllow, $arParserParams);
						}

						$arResult["Post"]["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["Post"]["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
						$arResult["Post"]["DATE_PUBLISH_DATE"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], FORMAT_DATE);
						$arResult["Post"]["DATE_PUBLISH_TIME"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "HH:MI");
						$arResult["Post"]["DATE_PUBLISH_D"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "DD");
						$arResult["Post"]["DATE_PUBLISH_M"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "MM");
						$arResult["Post"]["DATE_PUBLISH_Y"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "YYYY");

						
						$arResult["BlogUser"] = CBlogUser::GetByID($arPost["AUTHOR_ID"], BLOG_BY_USER_ID); 
						$arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["BlogUser"]);
						$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
						$arResult["arUser"] = $dbUser->GetNext();
						$arResult["AuthorName"] = CBlogUser::GetUserName($arResult["BlogUser"]["ALIAS"], $arResult["arUser"]["NAME"], $arResult["arUser"]["LAST_NAME"], $arResult["arUser"]["LOGIN"], $arResult["arUser"]["SECOND_NAME"]);
						
						if(($arResult["PostPerm"] > BLOG_PERMS_MODERATE && $arResult["IDEA_MODERATOR"]) || ($arResult["PostPerm"]>=BLOG_PERMS_WRITE && $arPost["AUTHOR_ID"] == $arResult["USER_ID"]))
							$arResult["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"], "user_id" => $arParams["USER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
						
												if($arResult["IDEA_MODERATOR"] && $arResult["PostPerm"]>=BLOG_PERMS_MODERATE && $arResult["Post"]["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
														$arResult["urlToHide"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("hide=Y", Array("del_id", "sessid", "success", "hide", "show")));
												elseif($arResult["IDEA_MODERATOR"] && $arResult["PostPerm"]>=BLOG_PERMS_MODERATE && $arResult["Post"]["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
														$arResult["urlToShow"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("show=Y", Array("del_id", "sessid", "success", "show", "hide")));
						if($arResult["IDEA_MODERATOR"] && $arResult["PostPerm"] >= BLOG_PERMS_FULL)
							$arResult["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("delete=Y", Array("sessid", "delete", "hide")));
						
						$arResult["BlogUser"]["AVATAR_file"] = CFile::GetFileArray($arResult["BlogUser"]["AVATAR"]);
						if ($arResult["BlogUser"]["AVATAR_file"] !== false)
							$arResult["BlogUser"]["AVATAR_img"] = CFile::ShowImage($arResult["BlogUser"]["AVATAR_file"]["SRC"], 150, 150, "border=0 align='right'");

						if($arPost["CATEGORY_ID"] <> '')
						{
							$arCategory = explode(",",$arPost["CATEGORY_ID"]);
							foreach($arCategory as $v)
							{
								if(intval($v)>0)
								{
									$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
									$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v, "group_id" => $arParams["SOCNET_GROUP_ID"], "user_id" => $arParams["USER_ID"]));
									$arResult["Category"][] = $arCatTmp;
								}
							}
						}
						
						$arResult["POST_PROPERTIES"] = array("SHOW" => "N");
						$arResult["IS_DULICATE"] = false;

						if (!empty($arParams["POST_PROPERTY"]))
						{
							$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arPost["ID"], LANGUAGE_ID);
			
							if (count($arParams["POST_PROPERTY"]) > 0)
							{
								foreach ($arPostFields as $FIELD_NAME => $arPostField)
								{
									if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
										continue;
									$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
									$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
									$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
									$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
								}
							}
							if (!empty($arResult["POST_PROPERTIES"]["DATA"]))
								$arResult["POST_PROPERTIES"]["SHOW"] = "Y";
							//Check duplicate
							$arResult["IS_DUPLICATE"] = false;
							if (array_key_exists(CIdeaManagment::UFOriginalIdField, $arPostFields) && !empty($arPostFields[CIdeaManagment::UFOriginalIdField]["VALUE"]))
							{

								$arResult["IS_DUPLICATE"] = htmlspecialcharsbx($arPostFields[CIdeaManagment::UFOriginalIdField]["VALUE"], ENT_QUOTES);
								if(mb_strpos($arResult["IS_DUPLICATE"], "://") === false) //Link
									$arResult["IS_DUPLICATE"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("post_id" => $arResult["IS_DUPLICATE"]));
							}
						}
						if ($arParams["CACHE_TIME"] > 0)
							$cache->EndDataCache(array("templateCachedData"=>$this->GetTemplateCachedData(), "arResult"=>$arResult));
					}

					if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["Post"]))
											$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["Post"]["ID"]);
				}
				else
					$arResult["ERROR_MESSAGE"] .= GetMessage("B_B_MES_NO_RIGHTS")."<br />";
			}
			else
				$arResult["ERROR_MESSAGE"] .= GetMessage("B_B_MES_NO_MES")."<br />";
		}
		else
			$arResult["FATAL_MESSAGE"] .= GetMessage("B_B_MES_NO_RIGHTS")."<br />";
	}
	else
	{
		$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_POST");
		CHTTP::SetStatus("404 Not Found");
	}
}
else
{
	$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_BLOG");
	CHTTP::SetStatus("404 Not Found");
}

$this->IncludeComponentTemplate();
?>