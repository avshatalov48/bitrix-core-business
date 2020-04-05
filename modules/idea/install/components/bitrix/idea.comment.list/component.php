<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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

$arRemoveUriParams = Array("sessid", "delete_comment_id", "hide_comment_id", "success", "show_comment_id", "commentId", "bind_comment_id", "unbind_comment_id", "sessid", "success", "commentId", "clear_cache");

//@::Idea
$arParams['RATING_TEMPLATE'] = (strlen($arParams['RATING_TEMPLATE'])>0 && in_array($arParams['RATING_TEMPLATE'], array("standart", "like")))
	?$arParams['RATING_TEMPLATE']
	:"standart";

if(!is_array($arParams["POST_BIND_USER"]))
	$arParams["POST_BIND_USER"] = array();

$arResult["IDEA_MODERATOR"] = false;
if((!empty($arParams["POST_BIND_USER"]) && array_intersect($USER->GetUserGroupArray(), $arParams["POST_BIND_USER"]))
	||$USER->IsAdmin()
)
	$arResult["IDEA_MODERATOR"] = true;


//Prepare Extra Filter 2 ways
if(is_array($arParams["FILTER"]))
	$extFilter = $arParams["FILTER"];
else
{
	global ${$arParams["FILTER"]};
	$extFilter = array();
	if(is_array(${$arParams["FILTER"]}))
		$extFilter = ${$arParams["FILTER"]};
}

$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);
$bSoNet = false;

$arParams["ID"] = trim($arParams["ID"]);
$bIDbyCode = false;
if(!is_numeric($arParams["ID"]))
{
	$arParams["ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["~ID"]));
	$bIDbyCode = true;
}
else
	$arParams["ID"] = IntVal($arParams["ID"]);
	
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["NAV_PAGE_VAR"])<=0)
	$arParams["NAV_PAGE_VAR"] = "pagen";
if(strLen($arParams["COMMENT_ID_VAR"])<=0)
	$arParams["COMMENT_ID_VAR"] = "commentId";
if(IntVal($_GET[$arParams["NAV_PAGE_VAR"]])>0)
	$pagen = IntVal($_REQUEST[$arParams["NAV_PAGE_VAR"]]);
else
	$pagen = 1;

if(IntVal($arParams["COMMENTS_COUNT"])<=0)
	$arParams["COMMENTS_COUNT"] = 25;
	
if($arParams["USE_ASC_PAGING"] != "Y")
	$arParams["USE_DESC_PAGING"] = "Y";

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#"."&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

#if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
#	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
#if (!array_key_exists("PATH_TO_MESSAGES_CHAT", $arParams))
#	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
#if (!array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
#	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";	
$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$bUseTitle = true;
$arParams["NOT_USE_COMMENT_TITLE"] = ($arParams["NOT_USE_COMMENT_TITLE"] != "Y") ? "N" : "Y";
if($arParams["NOT_USE_COMMENT_TITLE"] == "Y")
	$bUseTitle = false;

$arParams["SMILES_COUNT"] = IntVal($arParams["SMILES_COUNT"]);
if(IntVal($arParams["SMILES_COUNT"])<=0)
	$arParams["SMILES_COUNT"] = 4;

$arParams["SMILES_COLS"] = IntVal($arParams["SMILES_COLS"]);
if(IntVal($arParams["SMILES_COLS"]) <= 0)
	$arParams["SMILES_COLS"] = 0;

$commentUrlID = IntVal($_REQUEST[$arParams["COMMENT_ID_VAR"]]);

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");

$arParams["EDITOR_RESIZABLE"] = $arParams["EDITOR_RESIZABLE"] !== "N";
$arParams["EDITOR_CODE_DEFAULT"] = $arParams["EDITOR_CODE_DEFAULT"] === "Y";
$arParams["EDITOR_DEFAULT_HEIGHT"] = intVal($arParams["EDITOR_DEFAULT_HEIGHT"]);
if(IntVal($arParams["EDITOR_DEFAULT_HEIGHT"]) <= 0)
	$arParams["EDITOR_DEFAULT_HEIGHT"] = 200;
$arParams["ALLOW_VIDEO"] = ($arParams["ALLOW_VIDEO"] == "Y" ? "Y" : "N");
if(COption::GetOptionString("blog","allow_video", "Y") == "Y" && $arParams["ALLOW_VIDEO"] == "Y")
	$arResult["allowVideo"] = true;

$user_id = $USER->GetID();
$arResult["canModerate"] = false;

$blogModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("blog");

if($arParams["NO_URL_IN_COMMENTS"] == "L")
{
	$arResult["NoCommentUrl"] = true;
	$arResult["NoCommentReason"] = GetMessage("B_B_PC_MES_NOCOMMENTREASON_L");
}
if(!$USER->IsAuthorized() && $arParams["NO_URL_IN_COMMENTS"] == "A")
{
	$arResult["NoCommentUrl"] = true;
	$arResult["NoCommentReason"] = GetMessage("B_B_PC_MES_NOCOMMENTREASON_A");
}

if(is_numeric($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]))
{
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY"] = floatVal($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]);
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] = "Y";
	if($USER->IsAuthorized())
	{
		$authorityRatingId = CRatings::GetAuthorityRating();
		$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $user_id);
		if($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
		{
			$arResult["NoCommentUrl"] = true;
			$arResult["NoCommentReason"] = GetMessage("B_B_PC_MES_NOCOMMENTREASON_R");
		}
	}
}

if(empty($arBlog))
{
	$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
	$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
}
$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
$arResult["Blog"] = $arBlog;

if($bIDbyCode)
	$arParams["ID"] = CBlogPost::GetID($arParams["ID"], $arBlog["ID"]);

$arPost = CBlogPost::GetByID($arParams["ID"]);
if(empty($arPost) && !$bIDbyCode)
{
	$arParams["ID"] = CBlogPost::GetID($arParams["ID"], $arBlog["ID"]);
	$arPost = CBlogPost::GetByID($arParams["ID"]);
}

if(!$bSoNet)
{
	if(IntVal($arParams["ID"])>0)
		$arResult["Perm"] = CBlogPost::GetBlogUserCommentPerms($arParams["ID"], $user_id);
	else
		$arResult["Perm"] = CBlog::GetBlogUserCommentPerms($arBlog["ID"], $user_id);
}

if(((!empty($arPost) && ($arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $arResult["IDEA_MODERATOR"]) && $arPost["ENABLE_COMMENTS"] == "Y") || $simpleComment) && (($arBlog["ACTIVE"] == "Y" && $arGroup["SITE_ID"] == SITE_ID) || $simpleComment) )
{
	$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
	$arResult["Post"] = $arPost;
	
	if($arPost["BLOG_ID"] == $arBlog["ID"] || $simpleComment)
	{
		//Comment delete
		if(IntVal($_GET["delete_comment_id"])>0 && $arResult["IDEA_MODERATOR"])
		{
			if($_GET["success"] == "Y")
							$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_DELED");
			else
			{
				$arComment = CBlogComment::GetByID(IntVal($_GET["delete_comment_id"]));
				if($arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
				{
					if(check_bitrix_sessid())
					{
						if(CBlogComment::Delete(IntVal($_GET["delete_comment_id"])))
						{
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arComment["POST_ID"]."/");

							$Notify = CIdeaManagment::getInstance()->Notification(
								array("TYPE" => "IDEA_COMMENT", "ID" => IntVal($_GET["delete_comment_id"]))
							)->getSonetNotify()
							->Remove();

							LocalRedirect($APPLICATION->GetCurPageParam("", $arRemoveUriParams));
						}
					}
					else
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
				$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_DELETE");
			}
		}
		elseif(IntVal($_GET["show_comment_id"])>0)
		{
			$arComment = CBlogComment::GetByID(IntVal($_GET["show_comment_id"]));
			if($arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
			{
				if($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_READY)
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
				}
				else
				{
					if(check_bitrix_sessid())
					{
						if($commentID = CBlogComment::Update($arComment["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH)))
						{
							$dbLogComment = CSocNetLogComments::GetList(
								array("ID" => "DESC"),
								array(
									"EVENT_ID"	=> array("idea_comment"),
									"SOURCE_ID" => $commentID
								),
								false,
								false,
								array("ID")
							);
							$arLogComment = $dbLogComment->Fetch();
							if (!$arLogComment)
							{
								$Notify = CIdeaManagment::getInstance()->Notification(
									array(
										"TYPE" => 'IDEA_COMMENT',
										"ACTION" => 'ADD',
										"ID" => $commentID,
										"POST_ID" => $arPost["ID"],
										"AUTHOR_ID" => $arComment["AUTHOR_ID"],
										"PATH" => $arComment["PATH"],
										"POST_TEXT" => $arComment["POST_TEXT"],
										"LOG_DATE" => $arComment["DATE_CREATE"],
									)
								)->getSonetNotify()
								->Send();
							}

							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arComment["POST_ID"]."/");

							LocalRedirect($APPLICATION->GetCurPageParam("", $arRemoveUriParams));
						}
					}
					else
					{
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
					}
				}
			}
			$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
		}
		elseif(IntVal($_GET["hide_comment_id"])>0 && $arResult["IDEA_MODERATOR"])
		{
			$arComment = CBlogComment::GetByID(IntVal($_GET["hide_comment_id"]));
			if($arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
			{
				if($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
				}
				else
				{
					if(check_bitrix_sessid())
					{
						if($commentID = CBlogComment::Update($arComment["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
						{
							$Notify = CIdeaManagment::getInstance()->Notification(
								array("TYPE" => "IDEA_COMMENT", "ID" => IntVal($_GET["hide_comment_id"]))
							)->getSonetNotify()
							->Remove();

							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arComment["POST_ID"]."/");

							LocalRedirect($APPLICATION->GetCurPageParam("", $arRemoveUriParams));
						}
					}
					else
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
			$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_HIDE");
		}
		elseif(IntVal($_GET["hidden_add_comment_id"])>0 && $arResult["IDEA_MODERATOR"])
		{
					$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
		}//Bind official
		elseif(IntVal($_GET["bind_comment_id"])>0 && $arResult["IDEA_MODERATOR"])
		{
			$arComment = CBlogComment::GetByID(IntVal($_GET["bind_comment_id"]));
			if($arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
			{
				if(check_bitrix_sessid())
				{
					$bBind = CIdeaManagment::getInstance()->IdeaComment($arComment["ID"])->Bind();
					if($bBind)
					{
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arComment["POST_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arComment["POST_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
						LocalRedirect($APPLICATION->GetCurPageParam("", $arRemoveUriParams));
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
		}//UnBind official
		elseif(IntVal($_GET["unbind_comment_id"])>0 && $arResult["IDEA_MODERATOR"])
		{
			$arComment = CBlogComment::GetByID(IntVal($_GET["unbind_comment_id"]));
			if($arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
			{
				if(check_bitrix_sessid())
				{
					$bBind = CIdeaManagment::getInstance()->IdeaComment($arComment["ID"])->UnBind();
					if($bBind)
					{
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arComment["POST_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arComment["POST_ID"]."/");
						LocalRedirect($APPLICATION->GetCurPageParam("", $arRemoveUriParams));
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
		}

		//Comments output
		if($arResult["Perm"]>=BLOG_PERMS_READ)
		{
			$arResult["CanUserComment"] = false;
			$arResult["canModerate"] = false;
			if($arResult["Perm"] >= BLOG_PERMS_PREMODERATE)
				$arResult["CanUserComment"] = true;
			if($arResult["Perm"] >= BLOG_PERMS_MODERATE)
				$arResult["canModerate"] = true;
				
			if(IntVal($user_id)>0)
			{
				$arResult["BlogUser"] = CBlogUser::GetByID($user_id, BLOG_BY_USER_ID);
				$arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["BlogUser"]);
				$dbUser = CUser::GetByID($user_id);
				$arResult["arUser"] = $dbUser->GetNext();
				$arResult["User"]["NAME"] = CBlogUser::GetUserName($arResult["BlogUser"]["ALIAS"], $arResult["arUser"]["NAME"], $arResult["arUser"]["LAST_NAME"], $arResult["arUser"]["LOGIN"], $arResult["arUser"]["SECOND_NAME"]);
				$arResult["User"]["ID"] = $user_id;
			}

			if(!$USER->IsAuthorized())
			{
				$useCaptcha = COption::GetOptionString("blog", "captcha_choice", "U");
				if(empty($arBlog))
				{
					$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
					$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
					$arResult["Blog"] = $arBlog;
				}
				if($useCaptcha == "U")
					$arResult["use_captcha"] = ($arBlog["ENABLE_IMG_VERIF"]=="Y")? true : false;
				elseif($useCaptcha == "A")
					$arResult["use_captcha"] = true;
				else
					$arResult["use_captcha"] = false;
			}
			else
			{
				$arResult["use_captcha"] = false;
			}

			/////////////////////////////////////////////////////////////////////////////////////
			if(strlen($arPost["ID"])>0 && $_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["post"]) > 0 && strlen($_POST["preview"]) <= 0)
			{
				if($arResult["Perm"] >= BLOG_PERMS_PREMODERATE)
				{
					if(check_bitrix_sessid())
					{
						$strErrorMessage = '';
						if(empty($arResult["Blog"]))
						{
							$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
							$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
							$arResult["Blog"] = $arBlog;
						}

						if($_POST["act"] != "edit")
						{
							if ($arResult["use_captcha"])
							{
								include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
								$captcha_code = $_POST["captcha_code"];
								$captcha_word = $_POST["captcha_word"];
								$cpt = new CCaptcha();
								$captchaPass = COption::GetOptionString("main", "captcha_password", "");
								if (strlen($captcha_code) > 0)
								{
									if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
										$strErrorMessage .= GetMessage("B_B_PC_CAPTCHA_ERROR")."<br />";
								}
								else
									$strErrorMessage .= GetMessage("B_B_PC_CAPTCHA_ERROR")."<br />";
							}

							$UserIP = CBlogUser::GetUserIP();
							$arFields = Array(
															"POST_ID" => $arPost["ID"],
															"BLOG_ID" => $arBlog["ID"],
															"TITLE" => trim($_POST["subject"]),
															"POST_TEXT" => trim($_POST["comment"]),
															"DATE_CREATE" => ConvertTimeStamp(false, "FULL"),
															"AUTHOR_IP" => $UserIP[0],
															"AUTHOR_IP1" => $UserIP[1],
														);
							if($arResult["Perm"] == BLOG_PERMS_PREMODERATE)
								$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;

							if(!$bUseTitle)
								unset($arFields["TITLE"]);
								
							if(IntVal($user_id)>0)
								$arFields["AUTHOR_ID"] = $user_id;
							else
							{
								$arFields["AUTHOR_NAME"] = trim($_POST["user_name"]);
								if(strlen(trim($_POST["user_email"]))>0)
									$arFields["AUTHOR_EMAIL"] = trim($_POST["user_email"]);
								if(strlen($arFields["AUTHOR_NAME"])<=0)
									$strErrorMessage .= GetMessage("B_B_PC_NO_ANAME")."<br />";
								$_SESSION["blog_user_name"] = $_POST["user_name"];
								$_SESSION["blog_user_email"] = $_POST["user_email"];
							}
							
							if(IntVal($_POST["parentId"])>0)
								$arFields["PARENT_ID"] = IntVal($_POST["parentId"]);
							else 
								$arFields["PARENT_ID"] = false;
							if(strlen($_POST["comment"])<=0)
								$strErrorMessage .= GetMessage("B_B_PC_NO_COMMENT")."<br />";

							if(strlen($strErrorMessage)<=0)
							{
								$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
								
								$arFields["PATH"] = $commentUrl;
								if(strpos($arFields["PATH"], "?") !== false)
									$arFields["PATH"] .= "&";
								else
									$arFields["PATH"] .= "?";
								$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=#comment_id###comment_id#";

								if($commentID = CBlogComment::Add($arFields))
								{
									//start custom, use UF binding ::@Idea
									if (
										$arResult["IDEA_MODERATOR"] 
										&& $_REQUEST["BIND_OFFICIAL_ANSWER"] == "Y"
									)
									{
										// Bind offical answer									
										CIdeaManagment::getInstance()->IdeaComment($commentID)->Bind();
									}

									if($arResult["IDEA_MODERATOR"] && $_REQUEST["BIND_IDEA_STATUS"] != "") // Set Status of Idea
									{
										CIdeaManagment::getInstance()->Idea($arPost["ID"])->SetStatus($_REQUEST["BIND_IDEA_STATUS"]);
										BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
									}
									/*end*/

									BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arPost["ID"]."/");
									BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arPost["ID"]."/");
									BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
									BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");

									$AuthorName = CBlogUser::GetUserName($arResult["BlogUser"]["~ALIAS"], $arResult["arUser"]["~NAME"], $arResult["arUser"]["~LAST_NAME"], $arResult["arUser"]["~LOGIN"], $arResult["arUser"]["~SECOND_NAME"]); 
									
									$arSite = CSite::GetByID(SITE_ID)->Fetch();
									$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
									if (strlen($serverName) <=0)
									{
										if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
											$serverName = SITE_SERVER_NAME;
										else
											$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
									}

									if(strpos($commentUrl, "?") !== false)
										$commentUrl .= "&";
									else
										$commentUrl .= "?";
									if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
										$commentAddedUrl = $commentUrl.$arParams["COMMENT_ID_VAR"]."=".$commentID."&hidden_add_comment_id=".$commentID;
									$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;
									
									if(strlen($AuthorName)<=0)
										$AuthorName = $arFields["AUTHOR_NAME"];
									
									$IdeaParser = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);

									$arNotifyFields = array_merge(
										$arFields,
										array(
											"TYPE"=>'IDEA_COMMENT',
											"ACTION" => 'ADD',
											"ID" => $commentID,
											"AUTHOR" => $AuthorName,
											"FULL_PATH" => "http://".$serverName.$commentUrl,
											"IDEA_COMMENT_TEXT" => $IdeaParser->convert4mail($arFields["POST_TEXT"]),
											"IDEA_TITLE" => $arPost['~TITLE']
										)
									);

									//Notifications
									$Notify = CIdeaManagment::getInstance()->Notification($arNotifyFields);
									//Socialnetwork notification
									$Notify->getSonetNotify()->Send();
									//Email notification
									$Notify->getEmailNotify()->Send();
									//END -> Notifications

									if($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || strlen($arFields["PUBLISH_STATUS"]) <= 0)
									{
										if(!$bSoNet && $arFields["PARENT_ID"] > 0) // In case the is an comment before - we'll notice author
										{
											$arPrev = CBlogComment::GetByID($arFields["PARENT_ID"]);
											$arPrev = CBlogTools::htmlspecialcharsExArray($arPrev);
											if ($user_id != $arPrev['AUTHOR_ID']) 
											{
												$email = '';

												$res = CUser::GetByID($arPrev['AUTHOR_ID']);
												if ($arOwner = $res->GetNext()) 
												{
													$arPrevBlog = CBlog::GetByOwnerID($arPrev['AUTHOR_ID'], $arParams["GROUP_ID"]);
													if ($arPrevBlog['EMAIL_NOTIFY']!='N') 
														$email = $arOwner['EMAIL'];
												}
												elseif($arPrev['AUTHOR_EMAIL'])
													$email = $arPrev['AUTHOR_EMAIL'];

												if ($email && $email != $arMailFields["EMAIL_TO"] && $email != $arOwnerBlog['EMAIL'])
												{
													$arMailFields["EMAIL_TO"] = $email;
													$text4mail1 = $parserBlog->convert4mail($arPrev["~POST_TEXT"]);
													$arMailFields["PARENT_COMMENT_TEXT"] = $text4mail1;
													$arMailFields["PARENT_COMMENT_TITLE"] = $arPrev["~TITLE"];
													$arMailFields["PARENT_COMMENT_DATE"] = $arPrev["DATE_CREATE"];

													/*CEvent::Send(
														($bUseTitle) ? "NEW_BLOG_COMMENT2COMMENT" : "NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE",
														SITE_ID,
														$arMailFields
													);*/
												}
											}
										}
									}
									if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
										LocalRedirect($commentAddedUrl);
									else
										LocalRedirect($commentUrl);
								}
								else
								{
									if ($e = $APPLICATION->GetException())
										$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$e->GetString();
								}
							}
							else
							{
								if ($e = $APPLICATION->GetException())
									$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$e->GetString();
								if(strlen($strErrorMessage)>0)
									$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$strErrorMessage;
							}
						}
						else //update comment
						{
							$commentID = IntVal($_POST["edit_id"]);
							$arOldComment = CBlogComment::GetByID($commentID);
							if($commentID <= 0 || empty($arOldComment))
								$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_COM_ERROR_LOST");
							elseif($arOldComment["AUTHOR_ID"] == $user_id || ($blogModulePermissions >= "W" && $arResult["IDEA_MODERATOR"]))
							{
								$arFields = Array(
										"TITLE" => $_POST["subject"],
										"POST_TEXT" => $_POST["comment"],
									);
								if(!$bUseTitle)
									unset($arFields["TITLE"]);
								if($arResult["Perm"] == BLOG_PERMS_PREMODERATE)
									$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;

								$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
								
								$arFields["PATH"] = $commentUrl;
								if(strpos($arFields["PATH"], "?") !== false)
									$arFields["PATH"] .= "&";
								else
									$arFields["PATH"] .= "?";
								$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;

								$dbComment = CBlogComment::GetList(array(), Array("POST_ID" => $arPost["ID"], "BLOG_ID" => $arBlog["ID"], "PARENT_ID" => $commentID));
								if($arComment = $dbComment->Fetch() && $blogModulePermissions < "W")
								{
									$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_EDIT_ALREADY_COMMENTED");
								}
								else
								{
									if($commentID = CBlogComment::Update($commentID, $arFields))
									{
										//Notifications
										$arCommentNotifyFields = array(
											"TYPE" => 'IDEA_COMMENT',
											"ACTION" => 'UPDATE',
											"AUTHOR_ID" => $user_id,
											"ID" => $commentID,
											"POST_ID" => $arPost["ID"],
										);
										
										if (
											$arResult["IDEA_MODERATOR"] 
											&& $_REQUEST["BIND_OFFICIAL_ANSWER"] == "Y"
										)
										{
											$arCommentNotifyFields["LOG_DATE"] = $arOldComment["DATE_CREATE"];
										}

										$Notify = CIdeaManagment::getInstance()->Notification(array_merge($arFields, $arCommentNotifyFields));
										//Socialnetwork notification
										$Notify->getSonetNotify()->Send();
										//Email notification
										//$Notify->getEmailNotify()->Send();
										//END -> Notifications

										//start custom, use UF binding ::@Idea
										if (
											$arResult["IDEA_MODERATOR"] 
											&& $_REQUEST["BIND_OFFICIAL_ANSWER"] == "Y"
										)
										{
											// Bind offical answer
											CIdeaManagment::getInstance()->IdeaComment($commentID)->Bind();
										}
										if($arResult["IDEA_MODERATOR"] && $_REQUEST["BIND_IDEA_STATUS"] != "") // Set Status of Idea
										{
											CIdeaManagment::getInstance()->Idea($arPost["ID"])->SetStatus($_REQUEST["BIND_IDEA_STATUS"]);
											BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
										}
										/*end*/

										BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arOldComment["POST_ID"]."/");
										BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$arOldComment["POST_ID"]."/");
										BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
										BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");

										$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
										if(strpos($commentUrl, "?") !== false)
											$commentUrl .= "&";
										else
											$commentUrl .= "?";

										if($_REQUEST["redirect_type"] == 'list') //bx redirect for idea list
											LocalRedirect($APPLICATION->GetCurPageParam());
										elseif(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
										{
											$commentAddedUrl = $commentUrl.$arParams["COMMENT_ID_VAR"]."=".$commentID."&hidden_add_comment_id=".$commentID;
											LocalRedirect($commentAddedUrl);
										}
										else
										{
											$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;
											LocalRedirect($commentUrl);
										}
									}
									else
									{
										if ($e = $APPLICATION->GetException())
											$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".$e->GetString();
									}
								}
							}
							else
							{
								$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_NO_RIGHTS_EDIT");
							}
						}
					}
					else
						$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
				else
					$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_NO_RIGHTS");
			}
			elseif(strlen($_POST["preview"]) > 0)
			{
				$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
				$arParserParams = Array(
						"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
						"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
					);
				$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
				if(COption::GetOptionString("blog","allow_video", "Y") != "Y" || $arParams["ALLOW_VIDEO"] != "Y")
					$arAllow["VIDEO"] = "N";
				
				if($arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && !$arResult["NoCommentUrl"] && $USER->IsAuthorized())
				{
					$authorityRatingId = CRatings::GetAuthorityRating();
					$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $user_id);
					if($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
						$arResult["NoCommentUrl"] = true;
				}
				
				if($arResult["NoCommentUrl"])
					$arAllow["CUT_ANCHOR"] = "Y";

				$_POST["commentFormated"] = $p->convert($_POST["comment"], false, array(), $arAllow, $arParserParams);
			}
			/////////////////////////////////////////////////////////////////////////////////////
			if($USER->IsAdmin())
				$arResult["ShowIP"] = "Y";
			else
				$arResult["ShowIP"] = COption::GetOptionString("blog", "show_ip", "Y");
			
			$cache = new CPHPCache;
			$cache_id = "blog_comment_".serialize($arParams)."_".$arResult["Perm"]."_".$USER->IsAuthorized().intval($arResult["IDEA_MODERATOR"]);
			$cache_path = "/".SITE_ID."/idea/".$arBlog["ID"]."/comment/".$arParams["ID"]."/";
			
			$tmp = Array();
			$tmp["MESSAGE"] = $arResult["MESSAGE"];
			$tmp["ERROR_MESSAGE"] = $arResult["ERROR_MESSAGE"];

			if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
			{
				$Vars = $cache->GetVars();
				foreach($Vars["arResult"] as $k=>$v)
				{
					if(!array_key_exists($k, $arResult))
						$arResult[$k] = $v;
				}
				CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
				$cache->Output();
			}
			else
			{
			
				if ($arParams["CACHE_TIME"] > 0)
					$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

				$arResult["CommentsResult"] = Array();
				$arResult["Smiles"] = CBlogSmile::getSmiles(CSmile::TYPE_SMILE, LANGUAGE_ID);
				foreach($arResult["Smiles"] as $key => $value)
				{
					$arResult["Smiles"][$key]["LANG_NAME"] = $value["NAME"];
					$arResult["Smiles"][$key]["~LANG_NAME"] = htmlspecialcharsback($value["NAME"]);
					list($type) = explode(" ", $value["TYPING"]);
					$arResult["Smiles"][$key]["TYPE"] = str_replace("'", "\'", $type);
					$arResult["Smiles"][$key]["TYPE"] = str_replace("\\", "\\\\", $arResult["Smiles"][$key]["TYPE"]);
				}

				if(IntVal($arParams["ID"]) > 0)
				{
					$arOrder = Array("ID" => "ASC", "DATE_CREATE" => "ASC");
					$arFilter = Array("POST_ID"=>$arParams["ID"]);
					
					if($arResult["Perm"] < BLOG_PERMS_MODERATE || !$arResult["IDEA_MODERATOR"])
						$arFilter["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_PUBLISH;

					$arSelectedFields = Array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS");
					$dbComment = CBlogComment::GetList($arOrder, array_merge($extFilter,$arFilter), false, false, $arSelectedFields);
					$resComments = Array();
					
					$arResult["firstLevel"] = 0;
					
					if($arComment = $dbComment->GetNext())
					{
						$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
						$arParserParams = Array(
							"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
							"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
						);

						do
						{
							$arResult["Comments"][$arComment["ID"]] = Array(
									"ID" => $arComment["ID"],
									"PARENT_ID" => $arComment["PARENT_ID"],
								);
							$arComment["ShowIP"] = $arResult["ShowIP"];
							if(empty($resComments[IntVal($arComment["PARENT_ID"])]))
							{
								$resComments[IntVal($arComment["PARENT_ID"])] = Array();
								if(strlen($arResult["firstLevel"])<=0)
									$arResult["firstLevel"] = IntVal($arComment["PARENT_ID"]);
							}
							
							if(IntVal($arComment["AUTHOR_ID"])>0)
							{
								
								if(empty($arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]))
								{
									$arUsrTmp = Array();
									$arUsrTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComment["AUTHOR_ID"]));
									$arUsrTmp["BlogUser"] = CBlogUser::GetByID($arComment["AUTHOR_ID"], BLOG_BY_USER_ID); 
									$arUsrTmp["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arUsrTmp["BlogUser"]);
									$dbUser = CUser::GetByID($arComment["AUTHOR_ID"]);
									$arUsrTmp["arUser"] = $dbUser->GetNext();
									$arUsrTmp["AuthorName"] = CBlogUser::GetUserName($arUsrTmp["BlogUser"]["ALIAS"], $arUsrTmp["arUser"]["NAME"], $arUsrTmp["arUser"]["LAST_NAME"], $arUsrTmp["arUser"]["LOGIN"], $arUsrTmp["arUser"]["SECOND_NAME"]);
									
									if(IntVal($arUsrTmp["BlogUser"]["AVATAR"]) > 0)
									{
										$arUsrTmp["AVATAR_file"] = CFile::ResizeImageGet(
													$arUsrTmp["BlogUser"]["AVATAR"],
													array("width" => 30, "height" => 30),
													BX_RESIZE_IMAGE_EXACT,
													false
												);
									}
									elseif($arResult["bSoNet"] && IntVal($arUsrTmp["arUser"]["PERSONAL_PHOTO"]) > 0)
									{
										$arUsrTmp["AVATAR_file"] = CFile::ResizeImageGet(
														$arUsrTmp["arUser"]["PERSONAL_PHOTO"],
														array("width" => 30, "height" => 30),
														BX_RESIZE_IMAGE_EXACT,
														false
													);
									}
									if ($arUsrTmp["AVATAR_file"] !== false)
										$arUsrTmp["AVATAR_img"] = CFile::ShowImage($arUsrTmp["AVATAR_file"]["src"], 30, 30, "border=0 align='right'");

									$arUsrTmp["Blog"] = CBlog::GetByOwnerID(IntVal($arComment["AUTHOR_ID"]), $arParams["GROUP_ID"]);
								
									if($arUsrTmp["AUTHOR_ID"] == $arUsrTmp["AUTHOR_ID"])
										$arUsrTmp["AuthorIsPostAuthor"] = "Y";
										
									$arResult["USER_CACHE"][$arComment["AUTHOR_ID"]] = $arUsrTmp;
								}
								
								$arComment["urlToAuthor"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["urlToAuthor"];
								$arComment["BlogUser"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["BlogUser"];
								$arComment["arUser"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["arUser"];
								$arComment["AuthorName"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["AuthorName"];
								$arComment["AVATAR_file"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["AVATAR_file"];
								$arComment["AVATAR_img"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["AVATAR_img"];
								$arComment["Blog"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["Blog"];
								$arComment["AuthorIsPostAuthor"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["AuthorIsPostAuthor"];
								
								if(!empty($arComment["Blog"]))
								{
									$arComment["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arComment["Blog"]["URL"], "user_id" => $arComment["Blog"]["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
								}
							}
							else
							{
								$arComment["AuthorName"]  = $arComment["AUTHOR_NAME"];
								$arComment["AuthorEmail"]  = $arComment["AUTHOR_EMAIL"];
							}
							
							if($arResult["canModerate"])
							{
								if($arResult["IDEA_MODERATOR"] && $arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
																	$arComment["urlToHide"] = $APPLICATION->GetCurPageParam("hide_comment_id=".$arComment["ID"], $arRemoveUriParams);
								elseif($arResult["IDEA_MODERATOR"])
																	$arComment["urlToShow"] = $APPLICATION->GetCurPageParam("show_comment_id=".$arComment["ID"], $arRemoveUriParams);
								if($arResult["IDEA_MODERATOR"] && $arResult["Perm"]>=BLOG_PERMS_FULL)
																	$arComment["urlToDelete"] = $APPLICATION->GetCurPageParam("delete_comment_id=".$arComment["ID"], $arRemoveUriParams);
								if($arResult["IDEA_MODERATOR"] && $arResult["Perm"]>=BLOG_PERMS_FULL)
																	$arComment["urlToBind"] = $APPLICATION->GetCurPageParam("bind_comment_id=".$arComment["ID"], $arRemoveUriParams);
								if($arResult["IDEA_MODERATOR"] && $arResult["Perm"]>=BLOG_PERMS_FULL)
																	$arComment["urlToUnBind"] = $APPLICATION->GetCurPageParam("unbind_comment_id=".$arComment["ID"], $arRemoveUriParams);
							}
							
							$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
							if(COption::GetOptionString("blog","allow_video", "Y") != "Y" || $arParams["ALLOW_VIDEO"] != "Y")
								$arAllow["VIDEO"] = "N";
							
							if($arParams["NO_URL_IN_COMMENTS"] == "L" || (IntVal($arComment["AUTHOR_ID"]) <= 0  && $arParams["NO_URL_IN_COMMENTS"] == "A"))
								$arAllow["CUT_ANCHOR"] = "Y";
									
							if($arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && $arAllow["CUT_ANCHOR"] != "Y" && IntVal($arComment["AUTHOR_ID"]) > 0)
							{
								$authorityRatingId = CRatings::GetAuthorityRating();
								$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComment["AUTHOR_ID"]);
								if($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
									$arAllow["CUT_ANCHOR"] = "Y";
							}

							$arComment["TextFormated"] = $p->convert($arComment["~POST_TEXT"], false, array(), $arAllow, $arParserParams);

							$arComment["DateFormated"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arComment["DATE_CREATE"], CSite::GetDateFormat("FULL")));

							if($bUseTitle)
							{
							
								if(strlen($arComment["TITLE"])>0)
									$arComment["TitleFormated"] = $p->convert($arComment["TITLE"], false);
								if(strpos($arComment["TITLE"], "RE")===false)
									$subj = "RE: ".$arComment["TITLE"];
								else
								{
									if(strpos($arComment["TITLE"], "RE")==0)
									{
										if(strpos($arComment["TITLE"], "RE:")!==false)
										{
											$count = substr_count($arComment["TITLE"], "RE:");
											$subj = substr($arComment["TITLE"], (strrpos($arComment["TITLE"], "RE:")+4));
										}
										else
										{
											if(strpos($arComment["TITLE"], "[")==2)
											{
												$count = substr($arComment["TITLE"], 3, (strpos($arComment["TITLE"], "]: ")-3));
												$subj = substr($arComment["TITLE"], (strrpos($arComment["TITLE"], "]: ")+3));
											}
										}
										$subj = "RE[".($count+1)."]: ".$subj;
									}
									else
										$subj = "RE: ".$arComment["TITLE"];
								}
								$arComment["CommentTitle"] = str_replace(array("\\", "\"", "'"), array("\\\\", "\\"."\"", "\\'"), $subj);
							}
							$resComments[IntVal($arComment["PARENT_ID"])][] = $arComment;
						}
						while($arComment = $dbComment->GetNext());
						$arResult["CommentsResult"] = $resComments;
					}
				}
				unset($arResult["MESSAGE"]);
				unset($arResult["ERROR_MESSAGE"]);

				if ($arParams["CACHE_TIME"] > 0)
					$cache->EndDataCache(array("templateCachedData"=>$this-> GetTemplateCachedData(), "arResult"=>$arResult));
			}
			$arResult["MESSAGE"] = $tmp["MESSAGE"];
			$arResult["ERROR_MESSAGE"] = $tmp["ERROR_MESSAGE"];

			if($arResult["use_captcha"])
			{
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
				$cpt = new CCaptcha();
				$captchaPass = COption::GetOptionString("main", "captcha_password", "");
				if (strlen($captchaPass) <= 0)
				{
					$captchaPass = randString(10);
					COption::SetOptionString("main", "captcha_password", $captchaPass);
				}
				$cpt->SetCodeCrypt($captchaPass);
				$arResult["CaptchaCode"] = htmlspecialcharsbx($cpt->GetCodeCrypt());
			}
		}

		if(is_array($arResult["CommentsResult"]) && count($arResult["CommentsResult"][0]) > $arParams["COMMENTS_COUNT"])
		{
			$arResult["PAGE"] = $pagen;
			if($arParams["USE_DESC_PAGING"] == "Y")
			{
				$v1 = floor(count($arResult["CommentsResult"][0]) / $arParams["COMMENTS_COUNT"]);
				$firstPageCount = count($arResult["CommentsResult"][0]) - ($v1 - 1) * $arParams["COMMENTS_COUNT"];
			}
			else
			{
				$v1 = ceil(count($arResult["CommentsResult"][0]) / $arParams["COMMENTS_COUNT"]);
				$firstPageCount = $arParams["COMMENTS_COUNT"];
			}
			$arResult["PAGE_COUNT"] = $v1;
			if($arResult["PAGE"] > $arResult["PAGE_COUNT"])
				$arResult["PAGE"] = $arResult["PAGE_COUNT"];		
			if($arResult["PAGE_COUNT"] > 1)
			{
				if(IntVal($commentUrlID) > 0)
				{
										if(!function_exists("BXBlogSearchParentID"))
										{
											function BXBlogSearchParentID($commentID, $arComments)
											{
													if(IntVal($arComments[$commentID]["PARENT_ID"]) > 0)
													{
															return BXBlogSearchParentID($arComments[$commentID]["PARENT_ID"], $arComments);
													}
													else
															return $commentID;
											}
										}
					$parentCommentId = BXBlogSearchParentID($commentUrlID, $arResult["Comments"]);
					
					if(IntVal($parentCommentId) > 0)
					{
						foreach($arResult["CommentsResult"][0] as $k => $v)
						{
							if($v["ID"] == $parentCommentId)
							{
								if($k < $firstPageCount)
									$arResult["PAGE"] = 1;
								else
									$arResult["PAGE"] = ceil(($k + 1 - $firstPageCount) / $arParams["COMMENTS_COUNT"]) + 1;
								break;
							}
						}
					}
				}
				
				foreach($arResult["CommentsResult"][0] as $k => $v)
				{
					
					if($arResult["PAGE"] == 1)
					{
						if($k > ($firstPageCount-1))
							unset($arResult["CommentsResult"][0][$k]);
					}
					else
					{
						
						if($k >= ($firstPageCount + ($arResult["PAGE"]-1)*$arParams["COMMENTS_COUNT"]) || 
							$k < ($firstPageCount + ($arResult["PAGE"]-2)*$arParams["COMMENTS_COUNT"]))
							unset($arResult["CommentsResult"][0][$k]);
					}
				}
				$arResult["NEED_NAV"] = "Y";
				$arResult["PAGES"] = Array();

				for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
				{
					if($i != $arResult["PAGE"])
					{
						if($i == 1)
							$arResult["PAGES"][] = '<a href="'.$APPLICATION->GetCurPageParam("", Array($arParams["NAV_PAGE_VAR"], "commentID")).'#comments">'.$i.'</a>&nbsp;&nbsp;';
						else
							$arResult["PAGES"][] = '<a href="'.$APPLICATION->GetCurPageParam($arParams["NAV_PAGE_VAR"].'='.$i, array($arParams["NAV_PAGE_VAR"], "commentID")).'#comments">'.$i.'</a>&nbsp;&nbsp;';
					}
					else
						$arResult["PAGES"][] = "<b>".$i."</b>&nbsp;&nbsp;";
				}

			}
		}

		$arResult["IDS"] = Array();
		if(is_array($arResult["CommentsResult"]))
		{
			foreach($arResult["CommentsResult"] as $k1 => $v1)
			{
		
				foreach($v1 as $k => $v)
				{
					$arResult["IDS"][] = $v["ID"];
					if($arResult["Perm"] >= BLOG_PERMS_MODERATE || $blogModulePermissions >= "W")
						$arResult["Comments"][$v["ID"]]["SHOW_SCREENNED"] = "Y";

					if(IntVal($v["PARENT_ID"]) > 0 && $blogModulePermissions < "W")
					{
						$arResult["Comments"][$v["PARENT_ID"]]["CAN_EDIT"] = "N";
						if($arResult["Perm"] < BLOG_PERMS_MODERATE)
						{
							if($arResult["Comments"][$v["PARENT_ID"]]["SHOW_AS_HIDDEN"] != "Y" && $v["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
								$arResult["Comments"][$v["PARENT_ID"]]["SHOW_AS_HIDDEN"] = "Y";
							else
								$arResult["Comments"][$v["PARENT_ID"]]["SHOW_AS_HIDDEN"] = "N";
						}
					}
					
					if(IntVal($v["AUTHOR_ID"])>0)
					{
						if($v["AUTHOR_ID"] == $user_id || ($blogModulePermissions >= "W" && $arResult["IDEA_MODERATOR"]))
							$arResult["Comments"][$v["ID"]]["CAN_EDIT"] = "Y";
					}
					else
					{
						if($blogModulePermissions >= "W")
							$arResult["Comments"][$v["ID"]]["CAN_EDIT"] = "Y";
					}
				}
			}
			if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
				$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_COMMENT', $arResult["IDS"]);

		}
		if($USER->IsAuthorized())
		{
			if(IntVal($commentUrlID) > 0 && empty($arResult["Comments"][$commentUrlID]))
			{
				$arComment = CBlogComment::GetByID($commentUrlID);
				if($arComment["AUTHOR_ID"] == $user_id && $arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
					$arResult["MESSAGE"] = GetMessage("B_B_PC_HIDDEN_POSTED");
			}
		}

		$this->IncludeComponentTemplate();
	}
}

if(!is_array($arResult["CommentsResult"][0]))
	return 0;

$PublishedComments = 0;
foreach($arResult["CommentsResult"][0] as $arComment)
	if($arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
		$PublishedComments++;

return $PublishedComments;
?>