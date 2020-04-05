<?
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = trim($arParams["ID"]);
$bIDbyCode = false;
if(!is_numeric($arParams["ID"]) || strlen(IntVal($arParams["ID"])) != strlen($arParams["ID"]))
{
	$arParams["ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["~ID"]));
	$bIDbyCode = true;
}
else
	$arParams["ID"] = IntVal($arParams["ID"]);
if(strlen($arParams["ID"]) <= 0)
{
	ShowError(GetMessage("B_B_MES_NO_POST"));
	@define("ERROR_404", "Y");
	CHTTP::SetStatus("404 Not Found");
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

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
	$arParams["PATH_TO_BLOG"] = $APPLICATION->GetCurPage()."?".htmlspecialcharsbx($arParams["PAGE_VAR"])."=blog&".htmlspecialcharsbx($arParams["BLOG_VAR"])."=#blog#";

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = $APPLICATION->GetCurPage()."?".htmlspecialcharsbx($arParams["PAGE_VAR"])."=post&".htmlspecialcharsbx($arParams["BLOG_VAR"])."=#blog#&".htmlspecialcharsbx($arParams["POST_VAR"])."=#post_id#";

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = $APPLICATION->GetCurPage()."?".htmlspecialcharsbx($arParams["PAGE_VAR"])."=blog&".htmlspecialcharsbx($arParams["BLOG_VAR"])."=#blog#"."&category=#category_id#";

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = $APPLICATION->GetCurPage()."?".htmlspecialcharsbx($arParams["PAGE_VAR"])."=post_edit&".htmlspecialcharsbx($arParams["BLOG_VAR"])."=#blog#&".htmlspecialcharsbx($arParams["POST_VAR"])."=#post_id#";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = $APPLICATION->GetCurPage()."?".htmlspecialcharsbx($arParams["PAGE_VAR"])."=user&".htmlspecialcharsbx($arParams["USER_VAR"])."=#user_id#";

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
if (!array_key_exists("PATH_TO_MESSAGES_CHAT", $arParams))
	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
if (!array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
$arParams["SMILES_COUNT"] = IntVal($arParams["SMILES_COUNT"]);
if(!is_array($arParams["POST_PROPERTY"]))
	$arParams["POST_PROPERTY"] = Array("UF_BLOG_POST_DOC");
else
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_DOC";

if($arParams["SEO_USE"] != "Y" && $arParams["SEO_USE"] != "D")
	$arParams["SEO_USE"] = "N";

$user_id = $USER->GetID();
$arResult["USER_ID"] = $user_id;

$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
$arResult["Blog"] = $arBlog;
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

	if(!empty($arPost) && $arPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
		unset($arPost);

	if(!empty($arPost) && $arBlog["ID"] == $arPost["BLOG_ID"])
	{
		CBlogPost::CounterInc($arParams["ID"]);

		$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
		$arResult["Post"] = $arPost;

		$arResult["PostPerm"] = CBlogPost::GetBlogUserPostPerms($arParams["ID"], $arResult["USER_ID"]);

		if($arPost["AUTHOR_ID"] == $arBlog["OWNER_ID"])
			$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arPost["AUTHOR_ID"]));
		else
		{
			$arOwnerBlog = CBlog::GetByOwnerID($arPost["AUTHOR_ID"], $arParams["GROUP_ID"]);
			$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"], "user_id" => $arPost["AUTHOR_ID"]));
		}

		$arResult["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>CBlogPost::GetPostID($arResult["Post"]["ID"], $arResult["Post"]["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));
//		add some SEO to preserve collision between ID-link and CODE-link
//		use only if active ALLOW_POST_CODE and if now ID-link
		if($arParams["ALLOW_POST_CODE"] && !$bIDbyCode
			&& !empty($arResult["Post"]["CODE"]) &&!is_numeric($arResult["Post"]["CODE"]))
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$path = $request->isHttps() ? "https://" : "http://";
			$path.= $request->getHttpHost();
			$path.=	CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"],"post_id"=> $arResult["Post"]["CODE"],"user_id" => $arPost["AUTHOR_ID"]));
			Asset::getInstance()->addString('<link rel="canonical" href="'.$path.'" />');
		}
		
		if($_GET["become_friend"]=="Y" && $arResult["PostPerm"]<BLOG_PERMS_READ)
		{
			if($USER->IsAuthorized())
			{
				$dbCandidate = CBlogCandidate::GetList(Array(), Array("BLOG_ID"=>$arBlog["ID"], "USER_ID"=>$arResult["USER_ID"]));
				if($arCandidate = $dbCandidate->Fetch())
				{
					$arResult["MESSAGE"] = GetMessage("B_B_MES_REQUEST_ALREADY")."<br />";
				}
				else
				{
					if(CBlogCandidate::Add(Array("BLOG_ID"=>$arBlog["ID"], "USER_ID"=>$arResult["USER_ID"])))
					{
						$arResult["MESSAGE"] = GetMessage("B_B_MES_REQUEST_ADDED")."<br />";

						$BlogUser = CBlogUser::GetByID($user_id, BLOG_BY_USER_ID);
						$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);

						$dbUser = CUser::GetByID($user_id);
						$arUser = $dbUser->GetNext();
						$AuthorName = CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]);
						$dbUser = CUser::GetByID($arResult["BLOG"]["OWNER_ID"]);
						$arUserBlog = $dbUser->GetNext();
						if (strlen($serverName) <=0)
						{
							if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
								$serverName = SITE_SERVER_NAME;
							else
								$serverName = COption::GetOptionString("main", "server_name", "");
							if (strlen($serverName) <=0)
								$serverName = $_SERVER["SERVER_NAME"];
						}

						$arMailFields = Array(
								"BLOG_ID" => $arBlog["ID"],
								"BLOG_NAME" => $arBlog["NAME"],
								"BLOG_URL" => $arBlog["URL"],
								"BLOG_ADR" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_BLOG"]), array("blog" => $arBlog["URL"])),
								"USER_ID" => $user_id,
								"USER" => $AuthorName,
								"USER_URL" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_USER"]), array("user_id" => $user_id)),
								"EMAIL_FROM" => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
							);
						$arF1 = $arF2 = $arMailFields;
						$arF1["EMAIL_TO"] = $arUser["EMAIL"];
						$arF2["EMAIL_TO"] = $arUserBlog["EMAIL"];
						CEvent::Send("BLOG_YOU_TO_BLOG", SITE_ID, $arF1);
						CEvent::Send("BLOG_USER_TO_YOUR_BLOG", SITE_ID, $arF2);

					}
					else
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_MES_REQUEST_ERROR")."<br />";
				}
			}
			else
				$arResult["ERROR_MESSAGE"] .= GetMessage("B_B_MES_REQUEST_AUTH")."<br />";
		}

		if ($_GET["delete"]=="Y")
		{
			if (check_bitrix_sessid() && CBlogPost::CanUserDeletePost(IntVal($arParams["ID"]), ($USER->IsAuthorized() ? $arResult["USER_ID"] : 0 )))
			{
				if (CBlogPost::Delete($arParams["ID"]))
				{
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/pages/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arParams["ID"]."/");
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
					BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$arParams["ID"]."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
					BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
					BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
					BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");

					$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
					if(strpos($url, "?") === false)
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
		if ($_GET["hide"]=="Y")
		{
			if (check_bitrix_sessid())
			{
				if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE)
				{
					if(CBlogPost::Update($arParams["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
					{
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/pages/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arParams["ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
						BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
						BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
						BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
						BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$arParams["ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
						BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");

						$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
						if(strpos($url, "?") === false)
							$url .= "?";
						else
							$url .= "&";
						$url .= "hide_id=".$arParams["ID"]."&success=Y";

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
					{
						if($arPost["MICRO"] == "Y")
							$APPLICATION->SetTitle(GetMessage("MICROBLOG_TITLE"));
						else
							$APPLICATION->SetTitle($arPost["TITLE"]);
					}

					if($arParams["SET_NAV_CHAIN"]=="Y")
						$APPLICATION->AddChainItem($arBlog["NAME"], CComponentEngine::MakePathFromTemplate(htmlspecialcharsback($arParams["PATH_TO_BLOG"]), array("blog" => $arBlog["URL"], "user_id" => $arPost["AUTHOR_ID"])));

					$cache = new CPHPCache;
					$cache_id = "blog_message_".serialize($arParams)."_".$arResult["PostPerm"];
					if($arPost["AUTHOR_ID"] == $arResult["USER_ID"])
						$cache_id .= "_author";
					if(($tzOffset = CTimeZone::GetOffset()) <> 0)
						$cache_id .= "_".$tzOffset;
					$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arPost["ID"]."/";

					if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
					{
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/wmvplayer/wmvscript.js");
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/wmvplayer/silverlight.js");
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js");
						$APPLICATION->AddHeadScript("/bitrix/components/bitrix/player/mediaplayer/flvscript.js");

						$Vars = $cache->GetVars();
						foreach($Vars["arResult"] as $k=>$v)
							$arResult[$k] = $v;
						CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);
						$cache->Output();
					}
					else
					{
						if ($arParams["CACHE_TIME"] > 0)
							$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

						$arResult["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));

						$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);

						$arImages = Array();
						$arResult["POST_PROPERTIES"] = array("SHOW" => "N");

						if (!empty($arParams["POST_PROPERTY"]))
						{
							$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arPost["ID"], LANGUAGE_ID);
							if (count($arParams["POST_PROPERTY"]) > 0)
							{
								foreach ($arPostFields as $FIELD_NAME => $arPostField)
								{
									if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
										continue;

									$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
									$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
									$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];

									$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
								}
							}
							if (!empty($arResult["POST_PROPERTIES"]["DATA"]))
								$arResult["POST_PROPERTIES"]["SHOW"] = "Y";
						}

						$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arBlog['ID'], "IS_COMMENT" => "N"));
						while ($arImage = $res->Fetch())
						{
							$arImages[$arImage['ID']] = $arImage['FILE_ID'];
							$arResult["images"][$arImage['ID']] = Array(
								"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=70&height=70&type=square",
								"full" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=1000&height=1000"
							);
						}

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
						if(!empty($p->showedImages))
						{
							foreach($p->showedImages as $val)
							{
								if(!empty($arResult["images"][$val]))
									unset($arResult["images"][$val]);
							}
						}

						$arResult["Post"]["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["Post"]["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
						$arResult["Post"]["DATE_PUBLISH_DATE"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], FORMAT_DATE);
						$arResult["Post"]["DATE_PUBLISH_TIME"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "HH:MI");
						$arResult["Post"]["DATE_PUBLISH_D"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "DD");
						$arResult["Post"]["DATE_PUBLISH_M"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "MM");
						$arResult["Post"]["DATE_PUBLISH_Y"] = ConvertDateTime($arResult["Post"]["DATE_PUBLISH"], "YYYY");
						
//						get user data from new class
						$blogUser = new \Bitrix\Blog\BlogUser($CACHE_TIME);
						$blogUser->setBlogId($arBlog["ID"]);
						$blogUserData = $blogUser->getUsers($arPost["AUTHOR_ID"]);
						
						$arResult["BlogUser"] = $blogUserData[$arPost["AUTHOR_ID"]]["BlogUser"];
						$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
						$arResult["arUser"] = $dbUser->GetNext();
						$arResult["AuthorName"] = $blogUserData[$arPost["AUTHOR_ID"]]["AUTHOR_NAME"];
						
						if($arResult["PostPerm"] > BLOG_PERMS_MODERATE || ($arResult["PostPerm"] >= BLOG_PERMS_PREMODERATE && $arPost["AUTHOR_ID"] == $arResult["USER_ID"]))
							$arResult["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"], "user_id" => $arParams["USER_ID"]));
						if($arResult["PostPerm"] >= BLOG_PERMS_MODERATE)
						{
							$arResult["urlToHide"] = urlencode($APPLICATION->GetCurPageParam("hide=Y", Array("del_id", "sessid", "success", "hide", "delete")));
							$arResult["urlToHide"] = htmlspecialcharsbx($arResult["urlToHide"]);
						}
						if($arResult["PostPerm"] >= BLOG_PERMS_FULL)
						{
							$arResult["urlToDelete"] = urlencode($APPLICATION->GetCurPageParam("delete=Y", Array("sessid", "delete", "hide")));
							$arResult["urlToDelete"] = htmlspecialcharsbx($arResult["urlToDelete"]);
						}
						
						if($arResult["BlogUser"]["AVATAR_file"] !== false)
						{
//							get only size for post
							$arResult["BlogUser"]["Avatar_resized"] = $arResult["BlogUser"]["Avatar_resized"]["100_100"];
							$arResult["BlogUser"]["AVATAR_img"] = $arResult["BlogUser"]["AVATAR_img"]["100_100"];
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
									$arResult["Category"][] = $arCatTmp;
								}
							}
						}
						if($arParams["SEO_USE"] == "D")
						{
							$arResult["Post"]["SEO_TITLE"] = $arPost["TITLE"];
							$arResult["Post"]["SEO_DESCRIPTION"] = $p->killAllTags($arPost["~DETAIL_TEXT"]);
							$arResult["Post"]["SEO_TAGS"] = "";
							if(!empty($arResult["Category"]))
							{
								foreach($arResult["Category"] as $v)
								{
									$arResult["Post"]["SEO_TAGS"] .= $v["NAME"].", ";
								}
							}
						}

						if ($arParams["CACHE_TIME"] > 0)
							$cache->EndDataCache(array("templateCachedData"=>$this-> GetTemplateCachedData(), "arResult"=>$arResult));
					}

					if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["Post"]))
						$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["Post"]["ID"]);

					if($arParams["SEO_USE"] != "N")
					{
						if(strlen($arResult["Post"]["SEO_TITLE"]) > 0)
							$APPLICATION->SetPageProperty("title", htmlspecialcharsback($arResult["Post"]["SEO_TITLE"]));
						if(strlen($arResult["Post"]["SEO_TAGS"]) > 0)
							$APPLICATION->SetPageProperty("keywords", htmlspecialcharsback($arResult["Post"]["SEO_TAGS"]));
						if(strlen($arResult["Post"]["SEO_DESCRIPTION"]) > 0)
							$APPLICATION->SetPageProperty("description", htmlspecialcharsback($arResult["Post"]["SEO_DESCRIPTION"]));
					}
				}
				else
					$arResult["ERROR_MESSAGE"] .= GetMessage("B_B_MES_NO_RIGHTS")."<br />";
			}
			else
				$arResult["ERROR_MESSAGE"] .= GetMessage("B_B_MES_NO_MES")."<br />";
		}
		elseif($_GET["become_friend"]!="Y")
		{
			$arResult["NOTE_MESSAGE"] .= GetMessage("B_B_MES_FR_ONLY").'<br />';
			if($USER->IsAuthorized())
				$arResult["NOTE_MESSAGE"] .= GetMessage("B_B_MES_U_CAN").' <a href="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam("become_friend=Y", Array("become_friend"))).'">'.GetMessage("B_B_MES_U_CAN1").'</a> '.GetMessage("B_B_MES_U_CAN2").'</br />';
			else
				$arResult["NOTE_MESSAGE"] .= GetMessage("B_B_MES_U_AUTH").'<br />';
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