<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_COUNT"] = intval($arParams["MESSAGE_COUNT"])>0 ? intval($arParams["MESSAGE_COUNT"]): 20;
$arParams["SORT_BY1"] = ($arParams["SORT_BY1"] <> '' ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = ($arParams["SORT_ORDER1"] <> '' ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = ($arParams["SORT_BY2"] <> '' ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = ($arParams["SORT_ORDER2"] <> '' ? $arParams["SORT_ORDER2"] : "DESC");

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["YEAR"] = (intval($arParams["YEAR"])>0 ? intval($arParams["YEAR"]) : false);
$arParams["MONTH"] = (intval($arParams["MONTH"])>0 ? intval($arParams["MONTH"]) : false);
$arParams["DAY"] = (intval($arParams["DAY"])>0 ? intval($arParams["DAY"]) : false);
$arParams["CATEGORY_ID"] = (intval($arParams["CATEGORY_ID"])>0 ? intval($arParams["CATEGORY_ID"]) : false);
$arParams["NAV_TEMPLATE"] = ($arParams["NAV_TEMPLATE"] <> '' ? $arParams["NAV_TEMPLATE"] : "");
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	$arParams["CACHE_TIME_LONG"] = intval($arParams["CACHE_TIME_LONG"]);
	if(intval($arParams["CACHE_TIME_LONG"]) <= 0 && intval($arParams["CACHE_TIME"]) > 0)
		$arParams["CACHE_TIME_LONG"] = $arParams["CACHE_TIME"];

}
else
{
	$arParams["CACHE_TIME"] = 0;
	$arParams["CACHE_TIME_LONG"] = 0;

}
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);

CpageOption::SetOptionString("main", "nav_page_in_session", "N");
if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_BLOG_BLOG_TITLE"));

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

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if($arParams["PATH_TO_BLOG_CATEGORY"] == '')
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if($arParams["PATH_TO_POST_EDIT"] == '')
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);

$arParams["IMAGE_MAX_WIDTH"] = intval($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = intval($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
if(!is_array($arParams["POST_PROPERTY_LIST"]))
	$arParams["POST_PROPERTY_LIST"] = Array("UF_BLOG_POST_DOC");
else
	$arParams["POST_PROPERTY_LIST"][] = "UF_BLOG_POST_DOC";

if($arParams["FILTER_NAME"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/i", $arParams["FILTER_NAME"]))
{
	$arFilter = array();
}
else
{
	global ${$arParams["FILTER_NAME"]};
	$arFilter = ${$arParams["FILTER_NAME"]};
	if(!is_array($arFilter))
		$arFilter = array();
}


$arResult["ERROR_MESSAGE"] = Array();
$arResultNFCache["OK_MESSAGE"] = Array();
$arResultNFCache["ERROR_MESSAGE"] = Array();

if($arParams["BLOG_URL"] <> '')
{
	$user_id = intval($USER->GetID());

	//Message delete
	if (intval($_GET["del_id"]) > 0)
	{
		if($arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]))
		{
			if($_GET["success"] == "Y")
			{
				$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DELED");
			}
			else
			{
				if (check_bitrix_sessid() && CBlogPost::CanUserDeletePost(intval($_GET["del_id"]), $user_id))
				{
					$DEL_ID = intval($_GET["del_id"]);
					if(CBlogPost::GetByID($DEL_ID))
					{
						if (CBlogPost::Delete($DEL_ID))
						{
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/pages/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/calendar/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/post/".$DEL_ID."/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
							BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
							BXClearCache(True, "/".SITE_ID."/blog/groups/".$arResult["BLOG"]["GROUP_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/trackback/".$DEL_ID."/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_out/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_all/");
							BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
							BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");

							LocalRedirect($APPLICATION->GetCurPageParam("del_id=".$DEL_ID."&success=Y", Array("del_id", "sessid", "success")));
						}
						else
							$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR");
					}
				}
				else
					$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS");
			}
		}
		else
		{
			$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
			CHTTP::SetStatus("404 Not Found");
		}

	}
	elseif (intval($_GET["hide_id"]) > 0)
	{
		if($arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]))
		{
			if($_GET["success"] == "Y")
			{
				$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_HIDED");
			}
			else
			{
				if (check_bitrix_sessid())
				{
					$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arResult["BLOG"]["ID"], $user_id);
					$hide_id = intval($_GET["hide_id"]);
					if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE)
					{
						if(CBlogPost::GetByID($hide_id))
						{
							if(CBlogPost::Update($hide_id, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
							{
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/first_page/");
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/pages/");
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/calendar/");
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/post/".$hide_id."/");
								BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
								BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
								BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
								BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
								BXClearCache(True, "/".SITE_ID."/blog/groups/".$arResult["BLOG"]["GROUP_ID"]."/");
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/trackback/".$hide_id."/");
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_out/");
								BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_all/");
								BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
								BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
								BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");

								LocalRedirect($APPLICATION->GetCurPageParam("hide_id=".$hide_id."&success=Y", Array("del_id", "sessid", "success", "hide_id")));
							}
							else
								$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR");
						}
					}
					else
						$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS");
				}
			}
		}
		else
		{
			$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
			CHTTP::SetStatus("404 Not Found");
		}

	}


	if($_GET["become_friend"] <> '')
	{
		if($USER->IsAuthorized())
		{
			if(check_bitrix_sessid())
			{
				$frnd_er = 0;
				$frnd_ok = 0;
				if(empty($arResult["BLOG"]))
					$arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
				if(!empty($arResult["BLOG"]))
				{
					if($_GET["become_friend"]=="Y")
					{
						$dbCandidate = CBlogCandidate::GetList(Array(), Array("BLOG_ID"=>$arResult["BLOG"]["ID"], "USER_ID"=>$user_id));
						if($arCandidate = $dbCandidate->Fetch())
						{
							$frnd_ok = 1;
						}
						else
						{
							if(CBlog::IsFriend($arResult["BLOG"]["ID"], $user_id))
								$frnd_ok = 2;
							else
							{
								if(CBlogCandidate::Add(Array("BLOG_ID"=>$arResult["BLOG"]["ID"], "USER_ID"=>$user_id)))
								{
									$frnd_ok = 3;
									$BlogUser = CBlogUser::GetByID($user_id, BLOG_BY_USER_ID);
									$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);

									$dbUser = CUser::GetByID($user_id);
									$arUser = $dbUser->GetNext();
									$AuthorName = CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]);
									$dbUser = CUser::GetByID($arResult["BLOG"]["OWNER_ID"]);
									$arUserBlog = $dbUser->GetNext();
									if ($serverName == '')
									{
										if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
											$serverName = SITE_SERVER_NAME;
										else
											$serverName = COption::GetOptionString("main", "server_name", "");
										if ($serverName == '')
											$serverName = $_SERVER["SERVER_NAME"];
									}

									$arMailFields = Array(
											"BLOG_ID" => $arResult["BLOG"]["ID"],
											"BLOG_NAME" => $arResult["BLOG"]["NAME"],
											"BLOG_URL" => $arResult["BLOG"]["URL"],
											"BLOG_ADR" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_BLOG"]), array("blog" => $arResult["BLOG"]["URL"])),
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
									$frnd_er = 1;
							}
						}

						if($arOwnBlog = CBlog::GetByOwnerID($user_id, $arParams["GROUP_ID"]))
						{
							$dbCandidate = CBlogCandidate::GetList(Array(), Array("BLOG_ID"=>$arOwnBlog["ID"], "USER_ID"=>$arResult["BLOG"]["OWNER_ID"]));
							if($arCandidate = $dbCandidate->Fetch())
							{
								$frnd_ok = 4;
							}
							else
							{
								if(CBlog::IsFriend($arOwnBlog["ID"], $arResult["BLOG"]["OWNER_ID"]))
								{
									$frnd_ok = 5;
								}
								else
								{
									if(CBlogCandidate::Add(Array("BLOG_ID"=>$arOwnBlog["ID"], "USER_ID"=>$arResult["BLOG"]["OWNER_ID"])))
									{
										$frnd_ok = 6;

										$BlogUser = CBlogUser::GetByID($arResult["BLOG"]["OWNER_ID"], BLOG_BY_USER_ID);
										$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);

										$dbUser = CUser::GetByID($arResult["BLOG"]["OWNER_ID"]);
										$arUser = $dbUser->GetNext();
										$AuthorName = CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]);
										$dbUser = CUser::GetByID($user_id);
										$arUserBlog = $dbUser->GetNext();

										if ($serverName == '')
										{
											if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
												$serverName = SITE_SERVER_NAME;
											else
												$serverName = COption::GetOptionString("main", "server_name", "");
											if ($serverName == '')
												$serverName = $_SERVER["SERVER_NAME"];
										}

										$arMailFields = Array(
												"BLOG_ID" => $arOwnBlog["ID"],
												"BLOG_NAME" => $arOwnBlog["NAME"],
												"BLOG_URL" => $arOwnBlog["URL"],
												"BLOG_ADR" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_BLOG"]), array("blog" => $arOwnBlog["URL"])),
												"USER_ID" => $user_id,
												"USER" => $AuthorName,
												"USER_URL" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_USER"]), array("user_id" => $arUser["ID"])),
												"EMAIL_FROM" => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
											);
										$arF1 = $arF2 = $arMailFields;
										$arF1["EMAIL_TO"] = $arUserBlog["EMAIL"];
										$arF2["EMAIL_TO"] = $arUser["EMAIL"];
										CEvent::Send("BLOG_YOUR_BLOG_TO_USER", SITE_ID, $arF1);
										CEvent::Send("BLOG_BLOG_TO_YOU", SITE_ID, $arF2);
									}
									else
										$frnd_er = 2;
								}
							}
						}
					}
					elseif($_GET["become_friend"]=="N")
					{
						CBlogUser::DeleteFromUserGroup($user_id, $arResult["BLOG"]["ID"], BLOG_BY_USER_ID);

						$dbCandidate = CBlogCandidate::GetList(
							array(),
							array("BLOG_ID" => $arResult["BLOG"]["ID"], "USER_ID" => $user_id)
						);
						if ($arCandidate = $dbCandidate->Fetch())
							CBlogCandidate::Delete($arCandidate["ID"]);

						$frnd_ok = 7;
						
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
					CHTTP::SetStatus("404 Not Found");
				}

				if(intval($frnd_er) > 0)
					LocalRedirect($APPLICATION->GetCurPageParam("frnd_res_er=".$frnd_er, Array("frnd_res_er", "sessid", "frnd_res_ok")));
				elseif(intval($frnd_ok) > 0)
					LocalRedirect($APPLICATION->GetCurPageParam("frnd_res_ok=".$frnd_ok, Array("frnd_res_er", "sessid", "frnd_res_ok")));
			}
			elseif(intval($_GET["frnd_res_er"]) > 0 || intval($_GET["frnd_res_ok"]) > 0)
			{
				if(intval($_GET["frnd_res_er"]) > 0)
				{
					switch (intval($_GET["frnd_res_er"]))
					{
						case 1:
							$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ERROR");
							break;
						case 2:
							$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ERROR_2");
							break;
						case 3:
							$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
							break;
						case 4:
							$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
							break;
					}
				
				}
				if(intval($_GET["frnd_res_ok"]) > 0)
				{
					switch (intval($_GET["frnd_res_ok"]))
					{
						case 1:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY");
							break;
						case 2:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY_3");
							break;
						case 3:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ADDED");
							break;
						case 4:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY_2");
							break;
						case 5:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY_4");
							break;
						case 6:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ADDED_2");
							break;
						case 7:
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_LEAVED");
							break;
					}
				}
			}
			else
				$arResultNFCache["ERROR_MESSAGE"][] = $_GET["frnd_res_ok"].GetMessage("BLOG_BLOG_SESSID_WRONG");
		}
		else
			$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_NEED_AUTH");
	}

	if($GLOBALS["USER"]->IsAuthorized())
		$arUserGroups = CBlogUser::GetUserGroups($user_id, $arParams["BLOG_URL"], "Y", BLOG_BY_USER_ID, "URL");
	else
		$arUserGroups = Array(1);

	$numUserGroups = count($arUserGroups);
	for ($i = 0; $i < $numUserGroups - 1; $i++)
	{
		for ($j = $i + 1; $j < $numUserGroups; $j++)
		{
			if ($arUserGroups[$i] > $arUserGroups[$j])
			{
				$tmpGroup = $arUserGroups[$i];
				$arUserGroups[$i] = $arUserGroups[$j];
				$arUserGroups[$j] = $tmpGroup;
			}
		}
	}

	$strUserGroups = "";
	for ($i = 0; $i < $numUserGroups; $i++)
		$strUserGroups .= "_".$arUserGroups[$i];

	if(empty($arResult["BLOG"]))
		$arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
	if(!empty($arResult["BLOG"]) && $arResult["PostPerm"] == '')
		$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arResult["BLOG"]["ID"], $user_id);
	$cache = new CPHPCache;
	$cache_id = "blog_blog_message_".serialize($arParams)."_".CDBResult::NavStringForCache($arParams["MESSAGE_COUNT"])."_".$strUserGroups."_".$arResult["PostPerm"];
	if(($tzOffset = CTimeZone::GetOffset()) <> 0)
		$cache_id .= "_".$tzOffset;
	if($arResult["PostPerm"] == BLOG_PERMS_WRITE)
		$cache_id .= "_".$user_id;

	if(!empty($arResult["BLOG"]))
	{
		$arBlog = $arResult["BLOG"];
		if($arBlog["ACTIVE"] == "Y")
		{
			if(!isset($_GET["PAGEN_1"]) || intval($_GET["PAGEN_1"])<1)
			{
				$CACHE_TIME = $arParams["CACHE_TIME"];
				$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/";
			}
			else
			{
				$CACHE_TIME = $arParams["CACHE_TIME_LONG"];
				$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/pages/".intval($_GET["PAGEN_1"])."/";
			}

			if ($CACHE_TIME > 0 && $cache->InitCache($CACHE_TIME, $cache_id, $cache_path))
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
				if ($CACHE_TIME > 0)
					$cache->StartDataCache($CACHE_TIME, $cache_id, $cache_path);

				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] == SITE_ID)
				{
					$arResult["BLOG"]["Group"] = $arGroup;
					if($arResult["PostPerm"] >= BLOG_PERMS_READ)
					{
						$arResult["enable_trackback"] = COption::GetOptionString("blog","enable_trackback", "Y");

						$arFilter["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_PUBLISH;
						$arFilter[">PERMS"] = BLOG_PERMS_DENY;
						$arFilter["BLOG_ID"] = $arBlog["ID"];

						if($arParams["YEAR"] && $arParams["MONTH"] && $arParams["DAY"])
						{
							$from = mktime(0, 0, 0, $arParams["MONTH"], $arParams["DAY"], $arParams["YEAR"]);
							$to = mktime(0, 0, 0, $arParams["MONTH"], ($arParams["DAY"]+1), $arParams["YEAR"]);
							if($to > ($t = time()+$tzOffset))
								$to = $t;
							$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
							$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
						}
						elseif($arParams["YEAR"] && $arParams["MONTH"])
						{
							$from = mktime(0, 0, 0, $arParams["MONTH"], 1, $arParams["YEAR"]);
							$to = mktime(0, 0, 0, ($arParams["MONTH"]+1), 1, $arParams["YEAR"]);
							if($to > ($t = time()+$tzOffset))
								$to = $t;
							$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
							$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
						}
						elseif($arParams["YEAR"])
						{
							$from = mktime(0, 0, 0, 1, 1, $arParams["YEAR"]);
							$to = mktime(0, 0, 0, 1, 1, ($arParams["YEAR"]+1));
							if($to > ($t = time()+$tzOffset))
								$to = $t;
							$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
							$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
						}
						else
							$arFilter["<=DATE_PUBLISH"] = ConvertTimeStamp(time()+$tzOffset, "FULL");

						if(intval($arParams["CATEGORY_ID"])>0)
						{
							$arFilter["CATEGORY_ID_F"] = $arParams["CATEGORY_ID"];
							if($arParams["SET_TITLE"] == "Y")
							{
								$arCat = CBlogCategory::GetByID($arFilter["CATEGORY_ID_F"]);
								$arResult["title"]["category"] = CBlogTools::htmlspecialcharsExArray($arCat);
							}

						}

						$arResult["filter"] = $arFilter;
						
//						prefind post IDs by perms and filter
						$dbPostIds = CBlogPost::GetList(
							$SORT,
							$arFilter,
							array(
								"DATE_PUBLISH", "ID", "MAX" => "PERMS"
							),
							array("bDescPageNumbering"=>true, "nPageSize"=>$arParams["MESSAGE_COUNT"], "bShowAll" => false)
						);
						$postIds = [];
						while($post = $dbPostIds->GetNext())
						{
							$postIds[] = $post['ID'];
						}
//						save navchain
						$arResult["NAV_STRING"] = $dbPostIds->GetPageNavString(
							GetMessage("MESSAGE_COUNT"),
							$arParams["NAV_TEMPLATE"],
							false,
							$component
						);

						if(!empty($postIds))
						{
							//get all posts data by prefinded IDs
							$dbPost = CBlogPost::GetList(
								$SORT,
								['ID' => $postIds],
								false
							);
							$arResult["POST"] = [];
							$arResult["IDS"] = [];
							$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
							$arParserParams = array(
								"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
								"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
							);

							$blogUser = new \Bitrix\Blog\BlogUser($CACHE_TIME);
							$blogUser->setBlogId($arBlog["ID"]);
							$postsUsers = $blogUser->getUsers(
								\Bitrix\Blog\BlogUser::getPostAuthorsIdsByBlogId($arBlog["ID"])
							);

							while ($curPost = $dbPost->GetNext())
							{
								$curPost["DATE_PUBLISHED"] = new \DateTime($curPost["DATE_PUBLISH"]) < new \DateTime()
									? "Y" : "N";

								if ($curPost["AUTHOR_ID"] == $arBlog["OWNER_ID"])
								{
									$curPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate(
										$arParams["PATH_TO_BLOG"],
										array("blog" => $arBlog["URL"])
									);
								}
								else
								{
									if ($arOwnerBlog = CBlog::GetByOwnerID(
										$curPost["AUTHOR_ID"],
										$arParams["GROUP_ID"]
									))
									{
										$curPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate(
											$arParams["PATH_TO_BLOG"],
											array("blog" => $arOwnerBlog["URL"])
										);
									}
									else
									{
										$curPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate(
											$arParams["PATH_TO_BLOG"],
											array("blog" => $arBlog["URL"])
										);
									}
								}
								$curPost["urlToPost"] = CComponentEngine::MakePathFromTemplate(
									$arParams["PATH_TO_POST"],
									array(
										"blog" => $arBlog["URL"],
										"post_id" => CBlogPost::GetPostID(
											$curPost["ID"],
											$curPost["CODE"],
											$arParams["ALLOW_POST_CODE"]
										)
									)
								);
								$curPost["urlToAuthor"] = CComponentEngine::MakePathFromTemplate(
									$arParams["PATH_TO_USER"],
									array("user_id" => $curPost["AUTHOR_ID"])
								);

								$arImages = array();
								$res = CBlogImage::GetList(
									array("ID" => "ASC"),
									array(
										"POST_ID" => $curPost['ID'],
										"BLOG_ID" => $arBlog['ID'],
										"IS_COMMENT" => "N"
									)
								);
								while ($arImage = $res->Fetch())
								{
									$arImages[$arImage['ID']] = $arImage['FILE_ID'];
									$curPost["arImages"][$arImage['ID']] = array(
										"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".
												   $arImage['ID'].
												   "&width=70&height=70&type=square",
										"full" => "/bitrix/components/bitrix/blog/show_file.php?fid=".
												  $arImage['ID'].
												  "&width=".
												  blogTextParser::IMAGE_MAX_SHOWING_WIDTH.
												  "&height=".
												  blogTextParser::IMAGE_MAX_SHOWING_HEIGHT
									);
								}

								if ($curPost["DETAIL_TEXT_TYPE"] == "html" &&
									COption::GetOptionString("blog", "allow_html", "N") == "Y")
								{
									$arAllow = array(
										"HTML" => "Y",
										"ANCHOR" => "Y",
										"IMG" => "Y",
										"SMILES" => "Y",
										"NL2BR" => "N",
										"VIDEO" => "Y",
										"QUOTE" => "Y",
										"CODE" => "Y"
									);
									if (COption::GetOptionString("blog", "allow_video", "Y") != "Y")
									{
										$arAllow["VIDEO"] = "N";
									}
									$curPost["TEXT_FORMATED"] = $p->convert(
										$curPost["~DETAIL_TEXT"],
										true,
										$arImages,
										$arAllow,
										$arParserParams
									);
								}
								else
								{
									$arAllow = array(
										"HTML" => "N",
										"ANCHOR" => "Y",
										"BIU" => "Y",
										"IMG" => "Y",
										"QUOTE" => "Y",
										"CODE" => "Y",
										"FONT" => "Y",
										"LIST" => "Y",
										"SMILES" => "Y",
										"NL2BR" => "N",
										"VIDEO" => "Y"
									);
									if (COption::GetOptionString("blog", "allow_video", "Y") != "Y")
									{
										$arAllow["VIDEO"] = "N";
									}
									$curPost["TEXT_FORMATED"] = $p->convert(
										$curPost["~DETAIL_TEXT"],
										true,
										$arImages,
										$arAllow,
										$arParserParams
									);
								}
								$curPost["IMAGES"] = $arImages;
								if (!empty($p->showedImages))
								{
									foreach ($p->showedImages as $val)
									{
										if (!empty($curPost["arImages"][$val]))
										{
											unset($curPost["arImages"][$val]);
										}
									}
								}

								$curPost["BlogUser"] = $postsUsers[$curPost["AUTHOR_ID"]]["BlogUser"];
								if ($curPost["BlogUser"]["AVATAR_file"] !== false)
								{
									//								get only size for post
									$curPost["BlogUser"]["Avatar_resized"] = $curPost["BlogUser"]["Avatar_resized"]["100_100"];
									$curPost["BlogUser"]["AVATAR_img"] = $curPost["BlogUser"]["AVATAR_img"]["100_100"];
								}
								$curPost["arUser"] = $postsUsers[$curPost["AUTHOR_ID"]]["arUser"];
								$curPost["AuthorName"] = $postsUsers[$curPost["AUTHOR_ID"]]["AUTHOR_NAME"];

								if ($arResult["PostPerm"] >= BLOG_PERMS_FULL ||
									($arResult["PostPerm"] >= BLOG_PERMS_PREMODERATE &&
									 $curPost["AUTHOR_ID"] == $user_id))
								{
									$curPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate(
										$arParams["PATH_TO_POST_EDIT"],
										array("blog" => $arBlog["URL"], "post_id" => $curPost["ID"])
									);
								}

								if ($arResult["PostPerm"] >= BLOG_PERMS_MODERATE)
								{
									$curPost["urlToHide"] = urlencode(
										$APPLICATION->GetCurPageParam(
											"hide_id=".$curPost["ID"],
											array("del_id", "sessid", "success", "hide_id")
										)
									);
									$curPost["urlToHide"] = htmlspecialcharsbx($curPost["urlToHide"]);
								}

								if ($arResult["PostPerm"] >= BLOG_PERMS_FULL)
								{
									$curPost["urlToDelete"] = urlencode(
										$APPLICATION->GetCurPageParam(
											"del_id=".$curPost["ID"],
											array("del_id", "sessid", "success", "hide_id")
										)
									);
									$curPost["urlToDelete"] = htmlspecialcharsbx($curPost["urlToDelete"]);
								}

								if (preg_match("/(\[CUT\])/i", $curPost['DETAIL_TEXT']) ||
									preg_match("/(<CUT>)/i", $curPost['DETAIL_TEXT']))
								{
									$curPost["CUT"] = "Y";
								}

								if ($curPost["CATEGORY_ID"] <> '')
								{
									$arCategory = explode(",", $curPost["CATEGORY_ID"]);
									foreach ($arCategory as $v)
									{
										if (intval($v) > 0)
										{
											$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
											$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate(
												$arParams["PATH_TO_BLOG_CATEGORY"],
												array("blog" => $arBlog["URL"], "category_id" => $v)
											);
											$curPost["CATEGORY"][] = $arCatTmp;
										}
									}
								}
								$curPost["POST_PROPERTIES"] = array("SHOW" => "N");

								if (!empty($arParams["POST_PROPERTY_LIST"]))
								{
									$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields(
										"BLOG_POST",
										$curPost["ID"],
										LANGUAGE_ID
									);

									if (count($arParams["POST_PROPERTY_LIST"]) > 0)
									{
										foreach ($arPostFields as $FIELD_NAME => $arPostField)
										{
											if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY_LIST"]))
											{
												continue;
											}
											$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> ''
												? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
											$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx(
												$arPostField["EDIT_FORM_LABEL"]
											);
											$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
											$curPost["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
										}
									}
									if (!empty($curPost["POST_PROPERTIES"]["DATA"]))
									{
										$curPost["POST_PROPERTIES"]["SHOW"] = "Y";
									}
								}
								$curPost["DATE_PUBLISH_FORMATED"] = FormatDate(
									$arParams["DATE_TIME_FORMAT"],
									MakeTimeStamp(
										$curPost["DATE_PUBLISH"],
										CSite::GetDateFormat("FULL")
									)
								);
								$curPost["DATE_PUBLISH_DATE"] = ConvertDateTime($curPost["DATE_PUBLISH"], FORMAT_DATE);
								$curPost["DATE_PUBLISH_TIME"] = ConvertDateTime($curPost["DATE_PUBLISH"], "HH:MI");
								$curPost["DATE_PUBLISH_D"] = ConvertDateTime($curPost["DATE_PUBLISH"], "DD");
								$curPost["DATE_PUBLISH_M"] = ConvertDateTime($curPost["DATE_PUBLISH"], "MM");
								$curPost["DATE_PUBLISH_Y"] = ConvertDateTime($curPost["DATE_PUBLISH"], "YYYY");
								$arResult["POST"][] = $curPost;
								$arResult["IDS"][] = $curPost["ID"];
							}
						}
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
					CHTTP::SetStatus("404 Not Found");
				}

				if ($CACHE_TIME > 0)
					$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
			}
		}
		else
		{
			$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
			CHTTP::SetStatus("404 Not Found");
		}
	}
	else
	{
		$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
		CHTTP::SetStatus("404 Not Found");
	}

	if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
		$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["IDS"]);

	if($arParams["SET_TITLE"]=="Y")
	{
		$title = $arResult["BLOG"]["NAME"];

		if($arParams["SET_NAV_CHAIN"]=="Y")
			$APPLICATION->AddChainItem($arResult["BLOG"]["NAME"], CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_BLOG"]), array("blog" => $arResult["BLOG"]["URL"])));

		if(isset($arResult["filter"][">=DATE_PUBLISH"]))
		{
			$title .= " - ".GetMessage("BLOG_BLOG_BLOG_MES_FOR");
			if($arParams["YEAR"] && $arParams["MONTH"] && $arParams["DAY"])
				$title .= ConvertTimeStamp(mktime(0, 0, 0, $arParams["MONTH"], $arParams["DAY"], $arParams["YEAR"]));
			elseif($arParams["YEAR"] && $arParams["MONTH"])
				$title .= GetMessage("BLOG_BLOG_BLOG_M_".$arParams["MONTH"])." ".$arParams["YEAR"]." ".GetMessage("BLOG_BLOG_BLOG_MES_YEAR");
			elseif($arParams["YEAR"])
				$title .= $arParams["YEAR"]." ".GetMessage("BLOG_BLOG_BLOG_MES_YEAR_ONE");
		}

		if(isset($arResult["filter"]["CATEGORY_ID_F"]))
		{
			$title .= " - ".GetMessage("BLOG_BLOG_BLOG_MES_CAT").' "';

			$title .= $arResult["title"]["category"]["NAME"].'"';
		}

		$APPLICATION->SetTitle($title);
	}

	if($_GET["become_friend"]!="Y" && !empty($arResult["BLOG"]) && $arResult["PostPerm"] < BLOG_PERMS_READ)
	{
		$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_FRIENDS_ONLY");
		if($USER->IsAuthorized())
			$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_U_CAN").' <a href="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam('become_friend=Y&'.bitrix_sessid_get(), Array("become_friend", "sessid"))).'">'.GetMessage("BLOG_BLOG_BLOG_U_CAN1").'</a> '.GetMessage("BLOG_BLOG_BLOG_U_CAN2");
		else
			$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NEED_AUTH");
	}
}
else
{
	$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
	CHTTP::SetStatus("404 Not Found");
}


if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $val)
	{
		if(!in_array($val, $arResultNFCache["ERROR_MESSAGE"]))
			$arResultNFCache["ERROR_MESSAGE"][] = $val;
	}
}
if(!empty($arResult["OK_MESSAGE"]))
{
	foreach($arResult["OK_MESSAGE"] as $val)
	{
		if(!in_array($val, $arResultNFCache["OK_MESSAGE"]))
			$arResultNFCache["OK_MESSAGE"][] = $val;
	}
}
$arResult = array_merge($arResult, $arResultNFCache);

$this->IncludeComponentTemplate();
?>