<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;

global $USER_FIELD_MANAGER, $CACHE_MANAGER;

/** @var SocialnetworkBlogPostComment $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
/** @global CMain $APPLICATION */

if (!Loader::includeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!Loader::includeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);
$arResult["bIntranetInstalled"] = ModuleManager::isModuleInstalled("intranet");
$arResult["bTasksAvailable"] = (
	(!isset($arParams["bPublicPage"]) || !$arParams["bPublicPage"])
	&& IsModuleInstalled("tasks")
	&& (
		!CModule::IncludeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($USER->GetID(), "tasks")
	)
);

$arParams["ID"] = trim($arParams["ID"]);
if(preg_match("/^[1-9][0-9]*\$/", $arParams["ID"]))
{
	$arParams["ID"] = IntVal($arParams["ID"]);
	$bIDbyCode = false;
}
else
{
	$arParams["ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arParams["~ID"]);
	$bIDbyCode = true;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arParams["BLOG_URL"]);
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

$pagen = (
	IntVal($_GET[$arParams["NAV_PAGE_VAR"]]) > 0
		? IntVal($_REQUEST[$arParams["NAV_PAGE_VAR"]])
		: 1
);

if (intval($_REQUEST["LAST_LOG_TS"]) > 0)
{
	$arParams["LAST_LOG_TS"] = intval($_REQUEST["LAST_LOG_TS"]);
	if ($arParams["MOBILE"] != "Y")
	{
		$arParams["MARK_NEW_COMMENTS"] = "Y";
	}
}

if(IntVal($arParams["COMMENTS_COUNT"])<=0)
	$arParams["COMMENTS_COUNT"] = 25;

if($arParams["USE_ASC_PAGING"] != "Y")
	$arParams["USE_DESC_PAGING"] = "Y";

$applicationPage = $APPLICATION->GetCurPage();

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($applicationPage."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($applicationPage."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
{
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($applicationPage."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#"."&".$arParams["POST_VAR"]."=#post_id#");
}
$arParams["PATH_TO_POST_CURRENT"] = $arParams["PATH_TO_POST"];
if ($arParams["bPublicPage"])
{
	$arParams["PATH_TO_POST"] = COption::GetOptionString("socialnetwork", "userblogpost_page", "/company/personal/user/#user_id#/blog/#post_id#/", SITE_ID)."";
}

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

if (!isset($arParams["PATH_TO_CONPANY_DEPARTMENT"]) || $arParams["PATH_TO_CONPANY_DEPARTMENT"] == "")
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
if (!isset($arParams["PATH_TO_MESSAGES_CHAT"]) || $arParams["PATH_TO_MESSAGES_CHAT"] == "")
	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
if (!isset($arParams["PATH_TO_VIDEO_CALL"]) || $arParams["PATH_TO_VIDEO_CALL"] == "")
	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";
$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] = (IntVal($arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"]) > 0 ? IntVal($arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"]) : 70);
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] = (IntVal($arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"]) > 0 ? IntVal($arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"]) : 70);
$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] = (IntVal($arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]) > 0 ? IntVal($arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]) : 1000);
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] = (IntVal($arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]) > 0 ? IntVal($arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]) : 1000);

$commentUrlID = IntVal($_REQUEST[$arParams["COMMENT_ID_VAR"]]);

$arParams["DATE_TIME_FORMAT_S"] = $arParams["DATE_TIME_FORMAT"];

CSocNetLogComponent::processDateTimeFormatParams($arParams);
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["SEF"] = (isset($arParams["SEF"]) && $arParams["SEF"] == "N" ? "N" : "Y");

$arParams["CAN_USER_COMMENT"] = (!isset($arParams["CAN_USER_COMMENT"]) || $arParams["CAN_USER_COMMENT"] == 'Y' ? 'Y' : 'N');

$arParams["ALLOW_VIDEO"] = ($arParams["ALLOW_VIDEO"] == "N" ? "N" : "Y");
$arResult["allowVideo"] = COption::GetOptionString("blog","allow_video", "Y");
if($arParams["ALLOW_VIDEO"] == "N")
	$arResult["allowVideo"] = "N";

if($arParams["ALLOW_IMAGE_UPLOAD"] == "A" || ($arParams["ALLOW_IMAGE_UPLOAD"] == "R" && $USER->IsAuthorized()) || empty($arParams["ALLOW_IMAGE_UPLOAD"]))
	$arResult["allowImageUpload"] = true;

$arResult["userID"] = $user_id = $USER->GetID();
$arResult["canModerate"] = false;
$arResult["ajax_comment"] = 0;
$arResult["is_ajax_post"] = "N";

$arResult["TZ_OFFSET"] = CTimeZone::GetOffset();

$a = new CAccess;
$a->UpdateCodes();

$arParams["PAGE_SIZE"] = intval($arParams["PAGE_SIZE"]);
if($arParams["PAGE_SIZE"] <= 0)
	$arParams["PAGE_SIZE"] = 20;

$arParams["PAGE_SIZE_MIN"] = 3;

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
$arParams["COMMENT_PROPERTY"] = array("UF_BLOG_COMMENT_DOC");
if(CModule::IncludeModule("webdav") || CModule::IncludeModule("disk"))
{
	$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_FILE";
	$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_FH";
}

$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMM_URL_PRV";

$arBlog = $arParams["BLOG_DATA"];
$arPost = $arParams["POST_DATA"];

$arResult["Perm"] = BLOG_PERMS_DENY;
$arResult["PostPerm"] = BLOG_PERMS_DENY;
$arResult["PermBySG"] = false;

if(IntVal($_REQUEST["comment_post_id"]) > 0)
{
	$arParams["ID"] = IntVal($_REQUEST["comment_post_id"]);
	$arPost = CBlogPost::GetById($arParams["ID"]);
	$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
	$arBlog = CBlog::GetById($arPost["BLOG_ID"]);
	$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);

	if($arPost["AUTHOR_ID"] == $user_id)
	{
		$arResult["Perm"] = BLOG_PERMS_FULL;
		$arResult["PostPerm"] = BLOG_PERMS_FULL;
	}
	else
	{
		$arResult["PostPerm"] = (
			strlen($arParams["POST_DATA"]["perms"]) <= 0
				? CBlogPost::GetSocNetPostPerms($arParams["ID"])
				: $arParams["POST_DATA"]["perms"]
		);

		if ($arResult["PostPerm"] > BLOG_PERMS_DENY)
		{
			$arResult["Perm"] = CBlogComment::GetSocNetUserPerms($arParams["ID"], $arPost["AUTHOR_ID"]);
		}
	}

	$arResult["is_ajax_post"] = "Y";
}
else
{
	$arResult["PostPerm"] = (
		strlen($arParams["POST_DATA"]["perms"]) <= 0
			? CBlogPost::GetSocNetPostPerms($arParams["ID"])
			: $arParams["POST_DATA"]["perms"]
	);

	if($arResult["PostPerm"] > BLOG_PERMS_DENY)
	{
		$arResult["Perm"] = (
			$arResult["bIntranetInstalled"]
			&& IsModuleInstalled("bitrix24")
			&& $arParams["POST_DATA"]["HAVE_ALL_IN_ADR"] == "Y"
				? ($arPost["AUTHOR_ID"] == $user_id ? BLOG_PERMS_FULL : BLOG_PERMS_WRITE)
				: CBlogComment::GetSocNetUserPermsNew($arParams["ID"], $arPost["AUTHOR_ID"], $USER->getID(), $arResult["PermBySG"])
		);
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
{
	CBlogImage::AddImageResizeHandler(array("width" => 400, "height" => 400));
}

if(
	!empty($arPost)
	&& $arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
	&& $arPost["ENABLE_COMMENTS"] == "Y"
)
{
	//Comment delete
	if(IntVal($_GET["delete_comment_id"])>0)
	{
		if($_GET["success"] == "Y")
		{
			$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_DELED");
		}
		else
		{
			$arComment = CBlogComment::GetByID(IntVal($_GET["delete_comment_id"]));
			if (
				(
					$arResult["Perm"] >= BLOG_PERMS_MODERATE
					|| (
						IntVal($user_id) > 0
						&& $arComment["AUTHOR_ID"] == $user_id
					)
				)
				&& !empty($arComment)
			)
			{
				if(check_bitrix_sessid())
				{
					if(CBlogComment::Delete(IntVal($_GET["delete_comment_id"])))
					{
						BXClearCache(true, "/blog/comment/".intval($arParams["ID"] / 100)."/".$arParams["ID"]."/");
						CBlogComment::DeleteLog(IntVal($_GET["delete_comment_id"]));

						$arResult["ajax_comment"] = IntVal($_GET["delete_comment_id"]);
						$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_DELED");
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
			if (
				IntVal($arResult["ajax_comment"]) <= 0
				&& strlen($arResult["ERROR_MESSAGE"]) <= 0
			)
			{
				$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_DELETE");
				if ($ex = $APPLICATION->GetException())
				{
					$arResult["ERROR_MESSAGE"] .= ": ".$ex->GetString();
				}
			}
		}
	}
	elseif (IntVal($_GET["show_comment_id"]) > 0)
	{
		$arComment = CBlogComment::GetByID(IntVal($_GET["show_comment_id"]));
		$arTagInline = \Bitrix\Socialnetwork\Util::detectTags($arComment, array('POST_TEXT'));

		if (
			$arResult["Perm"] >= BLOG_PERMS_MODERATE
			&& !empty($arComment)
		)
		{
			if($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_READY)
			{
				$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
			}
			else
			{
				if(check_bitrix_sessid())
				{
					if($commentID = CBlogComment::Update($arComment["ID"], Array(
						"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
						"SEARCH_GROUP_ID" => \Bitrix\Main\Config\Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID)
					)))
					{
						BXClearCache(true, "/blog/comment/".intval($arParams["ID"] / 100)."/".$arParams["ID"]."/");
						$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arParams["bPublicPage"]));

						$dbRes = CSocNetLog::GetList(
							array("ID" => "DESC"),
							array(
								"EVENT_ID" => "blog_post",
								"SOURCE_ID" => $arComment["POST_ID"]
							),
							false,
							false,
							array("ID", "TMP_ID")
						);
						if ($arRes = $dbRes->Fetch())
						{
							$log_id = $arRes["TMP_ID"];

							$arParserParams = Array(
								"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
								"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
							);

							$arImages = Array();
							$res = CBlogImage::GetList(array("ID"=>"ASC"), array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arPost["BLOG_ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => $arComment["ID"]));
							while ($arImage = $res->Fetch())
							{
								$arImages[$arImage["ID"]] = $arImage["FILE_ID"];
							}

							$arAllow = array(
								"HTML" => "N",
								"ANCHOR" => "N",
								"BIU" => "N",
								"IMG" => "N",
								"QUOTE" => "N",
								"CODE" => "N",
								"FONT" => "N",
								"TABLE" => "N",
								"LIST" => "N",
								"SMILES" => "N",
								"NL2BR" => "N",
								"VIDEO" => "N"
							);
							$text4message = $parserBlog->convert($arComment["POST_TEXT"], false, $arImages, $arAllow, array("isSonetLog"=>true));

							$text4mail = $parserBlog->convert4mail($arComment["POST_TEXT"]);
							$postUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));

							$commentUrl = $postUrl;
							$commentUrl .= (strpos($commentUrl, "?") !== false ? "&" : "?");
							$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$arComment["ID"]."#com".$arComment["ID"];

							$arFieldsForSocnet = array(
								"ENTITY_TYPE" => SONET_ENTITY_USER,
								"ENTITY_ID" => $arPost["AUTHOR_ID"],
								"EVENT_ID" => "blog_comment",
								"=LOG_DATE" => $DB->CurrentTimeFunction(),
								"MESSAGE" => $text4message,
								"TEXT_MESSAGE" => $text4mail,
								"URL" => $commentUrl,
								"MODULE_ID" => false,
								"SOURCE_ID" => $arComment["ID"],
								"LOG_ID" => $log_id,
								"RATING_TYPE_ID" => "BLOG_COMMENT",
								"RATING_ENTITY_ID" => intval($arComment["ID"])
							);

							if (intval($arComment["AUTHOR_ID"]) > 0)
							{
								$arFieldsForSocnet["USER_ID"] = $arComment["AUTHOR_ID"];
							}
							if (!empty($arTagInline))
							{
								$arFieldsForSocnet["TAG"] = $arTagInline;
							}

							$log_comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false);
							CSocNetLog::CounterIncrement(
								$log_comment_id,
								false,
								false,
								"LC",
								CSocNetLogRights::CheckForUserAll($log_id)
							);

							CBlogPost::NotifyImPublish(array(
								"TYPE" => "COMMENT",
								"TITLE" => $arPost["TITLE"],
								"TO_USER_ID" => $arComment["AUTHOR_ID"],
								"POST_URL" => $postUrl,
								"COMMENT_URL" => $commentUrl,
								"POST_ID" => $arPost["ID"],
								"COMMENT_ID" => $arComment["ID"],
							));
						}
						$arResult["ajax_comment"] = $arComment["ID"];
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
		}
		if (IntVal($arResult["ajax_comment"]) <= 0)
		{
			$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
		}
	}
	elseif(IntVal($_GET["hide_comment_id"])>0)
	{
		$arComment = CBlogComment::GetByID(IntVal($_GET["hide_comment_id"]));
		if (
			$arResult["Perm"] >= BLOG_PERMS_MODERATE
			&& !empty($arComment)
		)
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
						BXClearCache(true, "/blog/comment/".intval($arParams["ID"] / 100)."/".$arParams["ID"]."/");
						CBlogComment::DeleteLog($arComment["ID"]);
						$arResult["ajax_comment"] = $arComment["ID"];
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
		}
		if (
			IntVal($arResult["ajax_comment"]) <= 0
			&& strlen($arResult["ERROR_MESSAGE"]) <= 0
		)
		{
			$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_HIDE");
		}
	}
	elseif(IntVal($_GET["hidden_add_comment_id"])>0)
	{
		$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
	}

	$arResult["CanUserComment"] = false;
	$arResult["canModerate"] = false;
	if (
		$arResult["Perm"] >= BLOG_PERMS_PREMODERATE
		&& $arParams["CAN_USER_COMMENT"] == 'Y'
	)
	{
		$arResult["CanUserComment"] = true;
	}

	if ($arResult["Perm"] >= BLOG_PERMS_MODERATE)
	{
		$arResult["canModerate"] = true;
	}

	if(IntVal($user_id)>0)
	{
		$arResult["User"]["ID"] = $user_id;
	}

	$arResult["use_captcha"] = false;
	if($arResult["CanUserComment"])
	{
		if(!$USER->IsAuthorized())
		{
			if(!empty($arBlog))
			{
				CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
			}
			else
			{
				if (!empty(CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]]))
				{
					$arBlog = CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]];
				}
				else
				{
					if (empty($arBlog))
					{
						$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
					}
					CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
				}
			}

			$useCaptcha = COption::GetOptionString("blog", "captcha_choice", "U");
			if($useCaptcha == "U")
			{
				$arResult["use_captcha"] = ($arBlog["ENABLE_IMG_VERIF"] == "Y") ? true : false;
			}
			elseif($useCaptcha == "A")
			{
				$arResult["use_captcha"] = true;
			}
			else
			{
				$arResult["use_captcha"] = false;
			}
		}
	}

	if(strlen($arPost["ID"])>0 && $_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["post"]) > 0)
	{
		if ($_POST["decode"] == "Y")
		{
			CUtil::JSPostUnescape();
		}

		if($arResult["Perm"] >= BLOG_PERMS_PREMODERATE)
		{
			if(check_bitrix_sessid())
			{
				if(!empty($arBlog))
				{
					CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
				}
				else
				{
					if (!empty(CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]]))
					{
						$arBlog = CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]];
					}
					else
					{
						if (empty($arBlog))
						{
							$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
						}
						CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
					}
				}
				$strErrorMessage = '';
				if ($_POST["blog_upload_image_comment"] == "Y")
				{
					if ($_FILES["BLOG_UPLOAD_FILE"]["size"] > 0)
					{
						$arResult["imageUploadFrame"] = "Y";
						$APPLICATION->RestartBuffer();
						header("Pragma: no-cache");

						$arFields = array(
							"MODULE_ID" => "blog",
							"BLOG_ID"	=> $arBlog["ID"],
							"POST_ID"	=> $arPost["ID"],
							"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
							"TITLE"		=> "",
							"IMAGE_SIZE"	=> $_FILES["BLOG_UPLOAD_FILE"]["size"],
							"IS_COMMENT" => "Y",
							"URL" => $arBlog["URL"],
							"USER_ID" => IntVal($user_id),
						);
						$arFields["FILE_ID"] = array_merge(
								$_FILES["BLOG_UPLOAD_FILE"],
								array(
									"MODULE_ID" => "blog",
									"del" => "Y",
								)
							);

						if ($imgID = CBlogImage::Add($arFields))
						{
							$aImg = CBlogImage::GetByID($imgID);
							$aImg["PARAMS"] = CFile::_GetImgParams($aImg["FILE_ID"]);
							$arResult["Image"] = Array("ID" => $aImg["ID"], "SRC" => $aImg["PARAMS"]["SRC"], "WIDTH" => $aImg["PARAMS"]["WIDTH"], "HEIGHT" => $aImg["PARAMS"]["HEIGHT"]);
						}
						else
						{
							if ($ex = $APPLICATION->GetException())
							{
								$arResult["ERROR_MESSAGE"] = $ex->GetString();
							}
						}
						$this->IncludeComponentTemplate();
						return;
					}
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
							{
								$strErrorMessage .= GetMessage("B_B_PC_CAPTCHA_ERROR")."<br />";
							}
						}
						else
						{
							$strErrorMessage .= GetMessage("B_B_PC_CAPTCHA_ERROR")."<br />";
						}
					}

					if(
						isset($_POST['webdav_history'])
						&& $_POST['webdav_history'] == 'Y'
						&& isset($_POST['comment'])
						&& strlen($_POST['comment']) > 0
					)
					{
						$_POST["comment"] = $APPLICATION->convertCharset($_POST["comment"], 'UTF-8', LANG_CHARSET);
					}

					$UserIP = CBlogUser::GetUserIP();
					$arFields = Array(
						"POST_ID" => $arPost["ID"],
						"BLOG_ID" => $arBlog["ID"],
						"TITLE" => trim($_POST["subject"]),
						"POST_TEXT" => trim($_POST["comment"]),
						"DATE_CREATE" => ConvertTimeStamp(time() + $arResult["TZ_OFFSET"], "FULL"),
						"AUTHOR_IP" => $UserIP[0],
						"AUTHOR_IP1" => $UserIP[1],
						"URL" => $arBlog["URL"],
						"PARENT_ID" => false,
						"SEARCH_GROUP_ID" => \Bitrix\Main\Config\Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID)
					);

					if(\Bitrix\Main\Config\Configuration::getValue("utf_mode") === true)
					{
						$conn = \Bitrix\Main\Application::getConnection();
						$table = \Bitrix\Blog\CommentTable::getTableName();

						if ($arFields["POST_TEXT"] <> '')
						{
							if (!$conn->isUtf8mb4($table, 'POST_TEXT'))
							{
								$arFields["POST_TEXT"] = \Bitrix\Main\Text\UtfSafeString::escapeInvalidUtf($arFields["POST_TEXT"]);
							}
						}
					}

					if ($arResult["Perm"] == BLOG_PERMS_PREMODERATE)
					{
						$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
					}

					if (IntVal($user_id) > 0)
					{
						$arFields["AUTHOR_ID"] = $user_id;
					}
					else
					{
						$arFields["AUTHOR_NAME"] = trim($_POST["user_name"]);
						if (strlen(trim($_POST["user_email"])) > 0)
						{
							$arFields["AUTHOR_EMAIL"] = trim($_POST["user_email"]);
						}
						if (strlen($arFields["AUTHOR_NAME"]) <= 0)
						{
							$strErrorMessage .= GetMessage("B_B_PC_NO_ANAME")."<br />";
						}
						$_SESSION["blog_user_name"] = $_POST["user_name"];
						$_SESSION["blog_user_email"] = $_POST["user_email"];
					}

					if (strlen($arFields["POST_TEXT"]) <= 0)
					{
						$strErrorMessage .= GetMessage("B_B_PC_NO_COMMENT")."<br />";
					}

					if (IntVal($_REQUEST["as"]) > 0)
					{
						$arParams["AVATAR_SIZE_COMMENT"] = IntVal($_REQUEST["as"]);
					}

					if(strlen($strErrorMessage) <= 0)
					{
						if (!\Bitrix\Blog\Item\Comment::checkDuplicate(array(
							'MESSAGE' => $arFields["POST_TEXT"],
							'BLOG_ID' => $arBlog["ID"],
							'POST_ID' => $arPost["ID"],
							'AUTHOR_ID' => $arFields["AUTHOR_ID"]
						)))
						{
							$strErrorMessage .= GetMessage("B_B_PC_DUPLICATE_COMMENT");
						}
					}

					if(strlen($strErrorMessage)<=0)
					{
						$fieldName = 'UF_BLOG_COMMENT_DOC';
						if (isset($GLOBALS[$fieldName]) && is_array($GLOBALS[$fieldName]))
						{
							$arAttachedFiles = array();
							foreach($GLOBALS[$fieldName] as $fileID)
							{
								$fileID = intval($fileID);
								if (
									$fileID <= 0
									|| !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]])
								)
								{
									continue;
								}

								$arFile = CFile::GetFileArray($fileID);
								if (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === null)
								{
									$arImgFields = array(
										"BLOG_ID" => $arBlog["ID"],
										"POST_ID" => $arPost["ID"],
										"COMMENT_ID" => 0,
										"=TIMESTAMP_X" => $DB->GetNowFunction(),
										"TITLE" => $arFile["FILE_NAME"],
										"IMAGE_SIZE" => $arFile["FILE_SIZE"],
										"FILE_ID" => $fileID,
										"IS_COMMENT" => "Y",
										"URL" => $arBlog["URL"],
										"USER_ID" => IntVal($user_id),
										"IMAGE_SIZE_CHECK" => "N",
									);
									$imgID = CBlogImage::Add($arImgFields);
									if (intval($imgID) <= 0)
									{
										$APPLICATION->ThrowException("Error Adding file by CBlogImage::Add");
									}
									else
									{
										$arFields["POST_TEXT"] = str_replace("[IMG ID=".$fileID."file", "[IMG ID=".$imgID."", $arFields["POST_TEXT"]);
									}
								}
								else
								{
									$arAttachedFiles[] = $fileID;
								}
							}
							$GLOBALS[$fieldName] = $arAttachedFiles;
						}

						if (count($arParams["COMMENT_PROPERTY"]) > 0)
						{
							$USER_FIELD_MANAGER->EditFormAddFields("BLOG_COMMENT", $arFields);
						}

						$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

						$arFields["PATH"] = $commentUrl;
						$arFields["PATH"] .= (strpos($arFields["PATH"], "?") !== false ? "&" : "?");
						$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=#comment_id##com#comment_id#";

						if(
							$arParams["MOBILE"] == "Y"
							&& in_array("UF_BLOG_COMM_URL_PRV", $arParams["COMMENT_PROPERTY"])
							&& empty($arFields["UF_BLOG_COMM_URL_PRV"])
							&& ($urlPreviewValue = \Bitrix\Socialnetwork\ComponentHelper::getUrlPreviewValue($arFields["POST_TEXT"]))
						)
						{
							$arFields["UF_BLOG_COMM_URL_PRV"] = $urlPreviewValue;
						}
						$arTagInline = \Bitrix\Socialnetwork\Util::detectTags($arFields, array('POST_TEXT'));

						if($commentId = CBlogComment::Add($arFields))
						{
							BXClearCache(true, "/blog/comment/".intval($arParams["ID"] / 100)."/".$arParams["ID"]."/");
							$images = Array();

							$DB->Query("UPDATE b_blog_image SET COMMENT_ID=".IntVal($commentId)." WHERE BLOG_ID=".IntVal($arBlog["ID"])." AND POST_ID=".IntVal($arPost["ID"])." AND IS_COMMENT = 'Y' AND (COMMENT_ID = 0 OR COMMENT_ID is null) AND USER_ID=".IntVal($user_id)."", true);

							$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arParams["bPublicPage"]));
							$arParserParams = Array(
								"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
								"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
							);

							$commentUrl .= (strpos($commentUrl, "?") !== false ? "&" : "?");
							if (
								strlen($arFields["PUBLISH_STATUS"]) > 0
								&& $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
							)
							{
								$commentAddedUrl = $commentUrl.$arParams["COMMENT_ID_VAR"]."=".$commentId."&hidden_add_comment_id=".$commentId;
							}
							$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$commentId."#com".$commentId;

							if (
								$arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
								|| strlen($arFields["PUBLISH_STATUS"]) <= 0
							)
							{
								$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

								$dbRes = CSocNetLog::GetList(
									array("ID" => "DESC"),
									array(
										"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
										"SOURCE_ID" =>$arPost["ID"]
									),
									false,
									false,
									array("ID", "TMP_ID")
								);
								if ($arRes = $dbRes->Fetch())
								{
									$log_id = $arRes["ID"];
								}
								else
								{
									$arParamsNotify = Array(
										"bSoNet" => true,
										"UserID" => $arParams["USER_ID"],
										"allowVideo" => $arResult["allowVideo"],
										"bGroupMode" => $arResult["bGroupMode"],
										"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
										"PATH_TO_POST" => $arParams["PATH_TO_POST"],
										"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
										"user_id" => $user_id,
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
										"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
									);
									$log_id = CBlogPost::Notify($arPost, $arBlog, $arParamsNotify);
								}

								$arImages = Array();
								$res = CBlogImage::GetList(array("ID"=>"ASC"), array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => $commentId));
								while ($arImage = $res->Fetch())
								{
									$arImages[$arImage["ID"]] = $arImage["FILE_ID"];
								}
								$arAllow = array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "TABLE" => "N", "LIST" => "N", "SMILES" => "N", "NL2BR" => "N", "VIDEO" => "N");
								$text4mail = $parserBlog->convert4mail($_POST['comment'], $arImages);
								$text4im = $parserBlog->convert4im($_POST['comment']);

								$arPSR = CBlogPost::GetSocnetPerms($arPost["ID"]);
								$arUsrCode = $arUsrIdToPush = array();
								if(!empty($arPSR["U"]))
								{
									$arUsrId = $arUsrIdToPush = array_keys($arPSR["U"]);
									foreach($arPSR["U"] as $k => $v)
									{
										$arUsrCode[] = "U".$k;
									}

									if (count($arPSR) > 1) // not only users
									{
										$arUsrIdToPush = array();
									}
								}

								if (intval($log_id) > 0)
								{
									$text4message = $parserBlog->convert($_POST['comment'], false, $arImages, $arAllow, array("isSonetLog"=>true));

									$arFieldsForSocnet = array(
										"ENTITY_TYPE" => SONET_ENTITY_USER,
										"ENTITY_ID" => $arBlog["OWNER_ID"],
										"EVENT_ID" => "blog_comment",
										"=LOG_DATE" => $DB->CurrentTimeFunction(),
										"MESSAGE" => $text4message,
										"TEXT_MESSAGE" => $text4mail,
										"URL" => $commentUrl,
										"MODULE_ID" => false,
										"SOURCE_ID" => $commentId,
										"LOG_ID" => $log_id,
										"RATING_TYPE_ID" => "BLOG_COMMENT",
										"RATING_ENTITY_ID" => intval($commentId)
									);

									if (intval($user_id) > 0)
									{
										$arFieldsForSocnet["USER_ID"] = $user_id;
									}
									if (!empty($arTagInline))
									{
										$arFieldsForSocnet["TAG"] = $arTagInline;
									}

									if ($log_comment_id = CSocNetLogComments::Add($arFieldsForSocnet, false, false))
									{
										$bForAll = CSocNetLogRights::CheckForUserAll($log_id);

										CSocNetLog::CounterIncrement(
											$log_comment_id,
											false,
											false,
											"LC",
											$bForAll,
											(
												$bForAll
												|| empty($arUsrIdToPush)
												|| count($arUsrIdToPush) > 20
													? array()
													: $arUsrIdToPush
											)
										);
									}
								}

								preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is".BX_UTF_PCRE_MODIFIER, $_POST['comment'], $arMention);

								$arFieldsIM = Array(
									"TYPE" => "COMMENT",
									"COMMENT_ID" => $commentId,
									"TITLE" => htmlspecialcharsBack($arPost["TITLE"]),
									"URL" => $commentUrl,
									"ID" => $arPost["ID"],
									"FROM_USER_ID" => $user_id,
									"TO_USER_ID" => array($arPost["AUTHOR_ID"]),
									"TO_SOCNET_RIGHTS" => $arUsrCode,
									"TO_SOCNET_RIGHTS_OLD" => array(
										"U" => array(),
										"SG" => array()
									),
									"AUTHOR_ID" => $arPost["AUTHOR_ID"],
									"BODY" => $text4im,
								);

								if (!empty($arMention))
								{
									$arFieldsIM["MENTION_ID"] = $arMention[1];
									if (
										$_POST["act"] != "edit"
										&& is_array($arMention[1])
										&& !empty($arMention[1])
									)
									{
										$arMentionedDestCode = array();
										foreach($arMention[1] as $val)
										{
											$arMentionedDestCode[] = "U".$val;
										}

										\Bitrix\Main\FinderDestTable::merge(array(
											"CONTEXT" => "mention",
											"CODE" => array_unique($arMentionedDestCode)
										));
									}
								}

								$arFieldsIM["EXCLUDE_USERS"] = array();

								$rsUnFollower = CSocNetLogFollow::GetList(
									array(
										"CODE" => "L".$log_id,
										"TYPE" => "N"
									),
									array("USER_ID")
								);

								while ($arUnFollower = $rsUnFollower->Fetch())
								{
									$arFieldsIM["EXCLUDE_USERS"][$arUnFollower["USER_ID"]] = $arUnFollower["USER_ID"];
								}

								CBlogPost::NotifyIm($arFieldsIM);

								if(!empty($arUsrId))
								{
									CBlogPost::NotifyMail(array(
										"type" => "COMMENT",
										"siteId" => SITE_ID,
										"userId" => $arUsrId,
										"authorId" => intval($user_id),
										"postId" => $arPost["ID"],
										"commentId" => $commentId,
										"postUrl" => CComponentEngine::MakePathFromTemplate(
											'/pub/post.php?post_id=#post_id#',
											array(
												"post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"])
											)
										)
									));
								}
							}
							elseif ($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
							{
								$arPostCodes = array();
								$arPSR = CBlogPost::GetSocnetPerms($arPost["ID"]);
								if (!empty($arPSR['SG']))
								{
									foreach($arPSR['SG'] as $key => $arCodes)
									{
										$arPostCodes = array_merge($arPostCodes, $arCodes);
									}
								}

								if (!empty($arPostCodes))
								{
									CBlogPost::NotifyImReady(array(
										"TYPE" => "COMMENT",
										"POST_ID" => $arPost["ID"],
										"COMMENT_ID" => $commentId,
										"TITLE" => htmlspecialcharsBack($arPost["TITLE"]),
										"COMMENT_URL" => $commentUrl,
										"FROM_USER_ID" => intval($user_id),
										"TO_SOCNET_RIGHTS" => $arPostCodes
									));
								}
							}

							$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => false, "<=TIMESTAMP_X" => ConvertTimeStamp(AddToTimeStamp(Array("HH" => -3)), "FULL")));
							while ($aImg = $res->Fetch())
							{
								CBlogImage::Delete($aImg["ID"]);
							}

							if (
								strlen($arFields["PUBLISH_STATUS"]) > 0
								&& $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
							)
							{
								$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
							}

							$arResult["ajax_comment"] = $commentId;

							$bHasImg = false;
							$bHasProps = false;

							$dbImg = CBlogImage::GetList(Array(), Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $arPost["ID"], "IS_COMMENT" => "Y"), false, false, Array("ID"));
							if ($dbImg->Fetch())
							{
								$bHasImg = true;
							}

							$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT", $commentId, LANGUAGE_ID);
							foreach ($arPostFields as $FIELD_NAME => $arPostField)
							{
								if(!empty($arPostField["VALUE"]) > 0)
								{
									$bHasProps = true;
									break;
								}
							}

							$arFieldsHave = array(
								"HAS_PROPS" => ($bHasProps ? "Y" : "N"),
							);
							CBlogComment::Update($commentId, $arFieldsHave, false);

							$arFieldsHave = array(
								"HAS_COMMENT_IMAGES" => ($bHasImg ? "Y" : "N"),
							);
							if ($arFieldsHave["HAS_COMMENT_IMAGES"] != $arPost["HAS_COMMENT_IMAGES"])
							{
								CBlogPost::Update($arPost["ID"], $arFieldsHave, false);
							}
						}
						else
						{
							if ($e = $APPLICATION->GetException())
								$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$e->GetString();
						}
					}
					else
					{
						$arResult["COMMENT_ERROR"] = (strlen($strErrorMessage) > 0 ? $strErrorMessage : GetMessage("B_B_PC_COM_ERROR"));
					}
				}
				else //update comment
				{
					$commentID = $_POST["edit_id"];
					$arOldComment = CBlogComment::GetByID($commentID);
					if (
						$commentID <= 0
						|| empty($arOldComment)
					)
					{
						$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_COM_ERROR_EDIT").": ".GetMessage("B_B_PC_COM_ERROR_LOST");
					}
					elseif (
						$arOldComment["AUTHOR_ID"] == $user_id
						|| $arResult["Perm"] >= BLOG_PERMS_FULL
					)
					{
						$arFields = Array(
							"POST_TEXT" => $_POST["comment"],
							"URL" => $arBlog["URL"],
						);
						if($arResult["Perm"] == BLOG_PERMS_PREMODERATE)
						{
							$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
						}

						$fieldName = 'UF_BLOG_COMMENT_DOC';
						$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT", $commentID, LANGUAGE_ID);
						if (isset($GLOBALS[$fieldName]) && is_array($GLOBALS[$fieldName]))
						{
							$checkArray = $_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]];
							$checkArray = array_merge((is_array($checkArray) ? $checkArray : array()),
								(isset($arPostFields["UF_BLOG_COMMENT_DOC"]) ? $arPostFields["UF_BLOG_COMMENT_DOC"]["VALUE"] : array()));

							$arAttachedFiles = array();
							foreach($GLOBALS[$fieldName] as $fileID)
							{
								$fileID = intval($fileID);
								if (
									$fileID <= 0
									|| !in_array($fileID, $checkArray)
								)
								{
									continue;
								}

								$arFile = CFile::GetFileArray($fileID);
								if (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === null)
								{
									$arImgFields = array(
										"BLOG_ID"	=> $arBlog["ID"],
										"POST_ID"	=> $arPost["ID"],
										"USER_ID" => IntVal($user_id),
										"COMMENT_ID" => $commentID,
										"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
										"TITLE"		=> $arFile["FILE_NAME"],
										"IMAGE_SIZE"	=> $arFile["FILE_SIZE"],
										"FILE_ID" => $fileID,
										"IS_COMMENT" => "Y",
										"URL" => $arBlog["URL"],
										"IMAGE_SIZE_CHECK" => "N",
									);
									$imgID = CBlogImage::Add($arImgFields);
									if (intval($imgID) <= 0)
									{
										$APPLICATION->ThrowException("Error Adding file by CBlogImage::Add");
									}
									else
									{
										$arFields["POST_TEXT"] = str_replace("[IMG ID=".$fileID."file", "[IMG ID=".$imgID."", $arFields["POST_TEXT"]);
									}
								}
								else
								{
									$arAttachedFiles[] = $fileID;
								}
							}
							$GLOBALS[$fieldName] = $arAttachedFiles;
						}

						CSocNetLogComponent::checkEmptyUFValue('UF_BLOG_COMMENT_FILE');

						if (count($arParams["COMMENT_PROPERTY"]) > 0)
						{
							$USER_FIELD_MANAGER->EditFormAddFields("BLOG_COMMENT", $arFields);
						}

						$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

						$arFields["PATH"] = $commentUrl;
						$arFields["PATH"] .= (strpos($arFields["PATH"], "?") !== false ? "&" : "?");
						$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;

						$dbComment = CBlogComment::GetList(array(), Array("POST_ID" => $arPost["ID"], "BLOG_ID" => $arBlog["ID"], ">ID" => $commentID));
						if(
							$dbComment->Fetch()
							&& $arResult["Perm"] < BLOG_PERMS_FULL
							&& !$arResult["bIntranetInstalled"]
						)
						{
							$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_COM_ERROR_EDIT").": ".GetMessage("B_B_PC_EDIT_ALREADY_COMMENTED");
						}
						else
						{
							if (
								!empty($_POST["attachedFilesRaw"])
								&& is_array($_POST["attachedFilesRaw"])
							)
							{
								CSocNetLogComponent::saveRawFilesToUF(
									$_POST["attachedFilesRaw"],
									(
										IsModuleInstalled("webdav")
										|| IsModuleInstalled("disk")
											? "UF_BLOG_COMMENT_FILE"
											: "UF_BLOG_COMMENT_DOC"
									),
									$arFields
								);
							}

							$arFields["SEARCH_GROUP_ID"] = \Bitrix\Main\Config\Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID);

							if($commentID = CBlogComment::Update($commentID, $arFields))
							{
								BXClearCache(true, "/blog/comment/".intval($arParams["ID"] / 100)."/".$arParams["ID"]."/");
								$images = Array();
								$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "COMMENT_ID" => $commentID, "IS_COMMENT" => "Y"));
								while ($aImg = $res->Fetch())
								{
									$images[$aImg["ID"]] = $aImg["FILE_ID"];
								}

								$arParamsUpdateLog = array(
									"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
									"IMAGES" => $images,
								);

								if(IntVal($user_id) > 0)
								{
									$arResult["BlogUser"] = CBlogUser::GetByID($user_id, BLOG_BY_USER_ID);
									$arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["BlogUser"]);
									$dbUser = CUser::GetByID($user_id);
									$arResult["arUser"] = $dbUser->GetNext();
									$arResult["User"]["NAME"] = CBlogUser::GetUserName($arResult["BlogUser"]["ALIAS"], $arResult["arUser"]["NAME"], $arResult["arUser"]["LAST_NAME"], $arResult["arUser"]["LOGIN"]);
								}

								CBlogComment::UpdateLog($commentID, $arResult["BlogUser"], $arResult["User"], $arFields, $arPost, $arParamsUpdateLog);

								$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => false, "<=TIMESTAMP_X" => ConvertTimeStamp(AddToTimeStamp(Array("HH" => -3)), "FULL")));
								while($aImg = $res->Fetch())
								{
									CBlogImage::Delete($aImg["ID"]);
								}

								$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"]));
								$commentUrl .= (strpos($commentUrl, "?") !== false ? "&" : "?");

								if (
									strlen($arFields["PUBLISH_STATUS"]) > 0
									&& $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
								)
								{
									$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_EDITED");
								}

								if (
									(
										!empty($arFields["PUBLISH_STATUS"])
										&& $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
									)
									|| (
										empty($arFields["PUBLISH_STATUS"])
										&& !empty($arOldComment["PUBLISH_STATUS"])
										&& $arOldComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
									)
								)
								{
									$arUserIdToShare = $arNewRights = array();

									preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is".BX_UTF_PCRE_MODIFIER, $_POST['comment'], $arMention);

									$arMentionedUserId = array();
									if (
										!empty($arMention)
										&& !empty($arMention[1])
										&& is_array($arMention[1])
									)
									{
										$arMentionedUserId = $arMention[1];
									}

									if (!empty($arMentionedUserId))
									{
										foreach($arMentionedUserId as $val)
										{
											$val = IntVal($val);
											if (
												IntVal($val) > 0
												&& $val != $arOldComment["AUTHOR_ID"]
												&& $val != $arPost["AUTHOR_ID"]
											)
											{
												$postPerm = CBlogPost::getSocNetPostPerms(array(
													"POST_ID" => $arPost["ID"],
													"NEED_FULL" => true,
													"USER_ID" => $val,
													"IGNORE_ADMIN" => true
												));

												if ($postPerm < BLOG_PERMS_PREMODERATE)
												{
													$arUserIdToShare[] = $val;
												}
											}
										}
									}

									$arUserIdToShare = array_unique($arUserIdToShare);

									if (!empty($arUserIdToShare))
									{
										foreach($arUserIdToShare as $val)
										{
											$arNewRights[] = 'U'.$val;
										}

										$arSocnetPerms = CBlogPost::GetSocnetPerms($arPost["ID"]);
										$arSocNetRights = $arNewRights;

										foreach($arSocnetPerms as $entityType => $arEntities)
										{
											foreach($arEntities as $entityId => $arRights)
											{
												$arSocNetRights = array_merge($arSocNetRights, $arRights);
											}
										}
										$arSocNetRights = array_unique($arSocNetRights);

										\Bitrix\Socialnetwork\ComponentHelper::processBlogPostShare(
											array(
												"POST_ID" => $arPost["ID"],
												"BLOG_ID" => $arPost["BLOG_ID"],
												"SITE_ID" => SITE_ID,
												"SONET_RIGHTS" => $arSocNetRights,
												"NEW_RIGHTS" => $arNewRights,
												"USER_ID" => $user_id
											),
											array(
												"PATH_TO_USER" => COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", '/company/personal/user/#user_id#/', SITE_ID),
												"PATH_TO_POST" => COption::GetOptionString("socialnetwork", "userblogpost_page", '/company/personal/user/#user_id#/blog/#post_id#', SITE_ID),
												"NAME_TEMPLATE" => CSite::GetNameFormat(),
												"SHOW_LOGIN" => "Y",
												"LIVE" => "N",
												"MENTION" => "Y"
											)
										);
									}
								}

								$arResult["ajax_comment"] = $commentID;

								$bHasImg = false;
								$bHasProps = false;

								$dbImg = CBlogImage::GetList(Array(), Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $arPost["ID"], "IS_COMMENT" => "Y"), false, false, Array("ID"));
								if ($dbImg->Fetch())
								{
									$bHasImg = true;
								}

								$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT", $commentID, LANGUAGE_ID);

								foreach ($arPostFields as $arPostField)
								{
									if(!empty($arPostField["VALUE"]))
									{
										$bHasProps = true;
										break;
									}
								}
								$arFieldsHave = array(
									"HAS_PROPS" => ($bHasProps ? "Y" : "N"),
								);
								CBlogComment::Update($commentID, $arFieldsHave, false);

								$arFieldsHave = array(
									"HAS_COMMENT_IMAGES" => ($bHasImg ? "Y" : "N"),
								);
								if ($arFieldsHave["HAS_COMMENT_IMAGES"] != $arPost["HAS_COMMENT_IMAGES"])
								{
									CBlogPost::Update($arPost["ID"], $arFieldsHave, false);
								}
							}
							else
							{
								if ($e = $APPLICATION->GetException())
								{
									$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_COM_ERROR_EDIT").": ".$e->GetString();
								}
							}
						}
					}
					else
					{
						$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_COM_ERROR_EDIT").": ".GetMessage("B_B_PC_NO_RIGHTS_EDIT");
					}
				}
			}
			else
			{
				$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
			}
		}
		else
		{
			$arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_NO_RIGHTS");
		}
	}

	//Comments output
	if($arResult["Perm"] >= BLOG_PERMS_READ)
	{
		/////////////////////////////////////////////////////////////////////////////////////

		$tmp = Array();
		$tmp["MESSAGE"] = $arResult["MESSAGE"];
		$tmp["ERROR_MESSAGE"] = $arResult["ERROR_MESSAGE"];
		if((strlen($arResult["COMMENT_ERROR"]) > 0 || strlen($arResult["ERROR_MESSAGE"]) > 0))
		{
			$arResult["is_ajax_post"] = "Y";
		}
		else
		{
			if (IntVal($_REQUEST["new_comment_id"]) > 0) // for push&pull
			{
				$arResult["ajax_comment"] = IntVal($_REQUEST["new_comment_id"]);
			}

			if(
				(
					IntVal($arParams["ID"]) > 0
					&& (
						(isset($arPost["NUM_COMMENTS_ALL"]) && IntVal($arPost["NUM_COMMENTS_ALL"]) > 0)
						|| IntVal($arPost["NUM_COMMENTS"]) > 0
					)
				)
				|| $arResult["ajax_comment"] > 0
			)
			{
				$cache = new CPHPCache;

				$arCacheID = array();
				$arKeys = array(
					"MOBILE",
					"PATH_TO_SMILE",
					"IMAGE_MAX_WIDTH",
					"IMAGE_MAX_HEIGHT",
					"PATH_TO_USER",
					"ATTACHED_IMAGE_MAX_WIDTH_FULL",
					"ATTACHED_IMAGE_MAX_HEIGHT_FULL",
					"AVATAR_SIZE_COMMENT",
					"NAME_TEMPLATE",
					"SHOW_LOGIN",
					"NO_URL_IN_COMMENTS",
					"NO_URL_IN_COMMENTS_AUTHORITY_CHECK",
					"NO_URL_IN_COMMENTS_AUTHORITY",
					"COMMENT_PROPERTY",
					"DATE_TIME_FORMAT",
					"DATE_TIME_FORMAT_S",
					"LAZYLOAD"
				);
				foreach($arKeys as $param_key)
				{
					$arCacheID[$param_key] = (array_key_exists($param_key, $arParams) ? $arParams[$param_key] : false);
				}

				$cache_id = "blog_comment_".$USER->IsAuthorized()."_".md5(serialize($arCacheID))."_".LANGUAGE_ID."_".$arParams["DATE_TIME_FORMAT"]."_".Bitrix\Main\Context::getCurrent()->getCulture()->getDateTimeFormat();
				if ($arResult["TZ_OFFSET"] <> 0)
				{
					$cache_id .= "_".$arResult["TZ_OFFSET"];
				}
				$cache_path = "/blog/comment/".intval($arParams["ID"] / 100)."/".$arParams["ID"]."/";

				if(IntVal($arResult["ajax_comment"]) > 0)
				{
					$arResult["is_ajax_post"] = "Y";
					$cache_id .= $arResult["ajax_comment"];
				}

				if (
					$arParams["CACHE_TIME"] > 0
					&& $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path)
				)
				{
					$Vars = $cache->GetVars();
					$arResult = array_merge($Vars["arResult"], $arResult);

					if (!empty($arResult["Assets"]))
					{
						if (!empty($arResult["Assets"]["CSS"]))
						{
							foreach($arResult["Assets"]["CSS"] as $cssFile)
							{
								\Bitrix\Main\Page\Asset::getInstance()->addCss($cssFile);
							}
						}

						if (!empty($arResult["Assets"]["JS"]))
						{
							foreach($arResult["Assets"]["JS"] as $jsFile)
							{
								\Bitrix\Main\Page\Asset::getInstance()->addJs($jsFile);
							}
						}
					}

					CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);
					$cache->Output();
				}
				else
				{
					if ($arParams["CACHE_TIME"] > 0)
					{
						$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->StartTagCache($cache_path);
						}
					}

					$arResult["Assets"] = array(
						"CSS" => array(),
						"JS" => array()
					);

					$arResult["Images"] = Array();
					$arResult["CommentsResult"] = Array();
					$arResult["IDS"] = Array();

					$arOrder = Array("DATE_CREATE" => "ASC", "ID" => "ASC");
					$arFilter = Array("POST_ID" => $arParams["ID"], "BLOG_ID" => $arPost["BLOG_ID"]);
					if($arResult["is_ajax_post"] == "Y" && IntVal($arResult["ajax_comment"]) > 0)
					{
						$arFilter["ID"] = $arResult["ajax_comment"];
					}
					$arSelectedFields = Array("ID", "BLOG_ID", "POST_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS", "HAS_PROPS", "SHARE_DEST");

					if ($GLOBALS["DB"]->type == "MYSQL")
					{
						$arSelectedFields[] = "DATE_CREATE_TS";
					}

					$dbComment = CBlogComment::GetList($arOrder, $arFilter, false, false, $arSelectedFields);
					$resComments = Array();

					$arCommentsAll = array();
					$arIdToGet = array();

					while ($arComment = $dbComment->Fetch())
					{
						if(IntVal($arComment["AUTHOR_ID"]) > 0)
						{
							$arIdToGet[] = $arComment["AUTHOR_ID"];
						}

						$arCommentsAll[] = $arComment;
					}

					if (!empty($arIdToGet))
					{
						$arResult["userCache"] = CBlogUser::GetUserInfoArray($arIdToGet, $arParams["PATH_TO_USER"], array("AVATAR_SIZE" => (isset($arParams["AVATAR_SIZE_COMMON"]) ? $arParams["AVATAR_SIZE_COMMON"] : $arParams["AVATAR_SIZE"]), "AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"]));

						foreach($arResult["userCache"] as $userId => $arUserCache)
						{
							$arTmpUser = array(
								"NAME" => $arUserCache["~NAME"],
								"LAST_NAME" => $arUserCache["~LAST_NAME"],
								"SECOND_NAME" => $arUserCache["~SECOND_NAME"],
								"LOGIN" => $arUserCache["~LOGIN"],
								"NAME_LIST_FORMATTED" => "",
							);
							$arResult["userCache"][$userId]["NAME_FORMATED"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] != "N" ? true : false));
						}
					}

					$arConvertParams = Array(
						"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
						"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
						"pathToUser" => $arParams["PATH_TO_USER"]
					);

					if (!empty($arParams["LOG_ID"]))
					{
						$arConvertParams["pathToUserEntityType"] = 'LOG_ENTRY';
						$arConvertParams["pathToUserEntityId"] = intval($arParams["LOG_ID"]);
					}

					$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arParams["bPublicPage"]));
					$p->LAZYLOAD = (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] == "Y" ? "Y" : "N");
					$p->bMobile = (isset($arParams["MOBILE"]) && $arParams["MOBILE"] == "Y");

					if ($p->bMobile)
					{
						$arConvertParams["imageWidth"] = 275;
						$arConvertParams["imageHeight"] = 416;
					}

					$i = 0;
					if(!empty($arCommentsAll[$i]))
					{
						$arComment = $arCommentsAll[$i];

						$bHasImg = false;
						if($arPost["HAS_COMMENT_IMAGES"] != "N")
						{
							$res = CBlogImage::GetList(array("ID"=>"ASC"), array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arPost['BLOG_ID'], "IS_COMMENT" => "Y"), false, false, Array("ID", "FILE_ID", "POST_ID", "BLOG_ID", "USER_ID", "TITLE", "COMMENT_ID", "IS_COMMENT"));
							while ($aImg = $res->Fetch())
							{
								$bHasImg = true;
								$arImages[$aImg['ID']] = $aImg['FILE_ID'];
								if($arResult["allowImageUpload"])
								{
									$aImgNew = CFile::ResizeImageGet(
										$aImg["FILE_ID"],
										array("width" => 90, "height" => 90),
										BX_RESIZE_IMAGE_EXACT,
										true
									);
									$aImgNew["source"] = CFile::ResizeImageGet(
										$aImg["FILE_ID"],
										array("width" => $arParams["IMAGE_MAX_WIDTH"], "height" => $arParams["IMAGE_MAX_HEIGHT"]),
										BX_RESIZE_IMAGE_EXACT,
										true
									);
									$aImgNew["ID"] = $aImg["ID"];
									$aImgNew["fileName"] = substr($aImgNew["src"], strrpos($aImgNew["src"], "/")+1);
									$arResult["Images"][$aImg['ID']] = $aImgNew;
								}
								$arResult["arImages"][$aImg["COMMENT_ID"]][$aImg['ID']] = Array(
									"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$aImg['ID']."&width=".$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"]."&height=".$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"]."&type=square"
								);

								$arResult["arImages"][$aImg["COMMENT_ID"]][$aImg['ID']]["full"] = (
									$arParams["MOBILE"] == "Y"
										? SITE_DIR."mobile/log/blog_image.php?bfid=".$aImg['ID']."&fid=".$aImg['FILE_ID']."&width=".$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]."&height=".$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]
										: "/bitrix/components/bitrix/blog/show_file.php?fid=".$aImg['ID']."&width=".$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]."&height=".$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]
								);
							}
						}

						$arFieldsHave = array();
						if ($arPost["HAS_COMMENT_IMAGES"] == "")
						{
							$arFieldsHave["HAS_COMMENT_IMAGES"] = ($bHasImg ? "Y" : "N");
						}

						if (!empty($arFieldsHave))
						{
							CBlogPost::Update($arPost["ID"], $arFieldsHave, false);
						}

						do
						{
							if(IntVal($arComment["AUTHOR_ID"]) > 0)
							{
								if($arComment["AUTHOR_ID"] == $arPost["AUTHOR_ID"])
									$arComment["AuthorIsPostAuthor"] = "Y";

								$p->authorName = $arResult["userCache"][$arComment["AUTHOR_ID"]]["NAME_FORMATED"];

								if ($arParams["CACHE_TIME"] > 0 && defined("BX_COMP_MANAGED_CACHE"))
								{
									$CACHE_MANAGER->RegisterTag("USER_NAME_".$arComment["AUTHOR_ID"]);
								}
							}
							else
							{
								$arComment["AuthorName"]  = htmlspecialcharsbx($arComment["AUTHOR_NAME"]);
								$arComment["AuthorEmail"]  = htmlspecialcharsbx($arComment["AUTHOR_EMAIL"]);
								$p->authorName = $arComment["AuthorName"];
							}
							unset($arComment["AUTHOR_NAME"]);
							unset($arComment["AUTHOR_EMAIL"]);

							$bHasProps = false;
							$urlPreviewText = false;

							if (!empty($arParams["COMMENT_PROPERTY"]))
							{
								if($arComment["HAS_PROPS"] != "N")
								{
									$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT", $arComment["ID"], LANGUAGE_ID);

									if (count($arPostFields) > 0)
									{
										foreach ($arPostFields as $FIELD_NAME => $arPostField)
										{
											if (!in_array($FIELD_NAME, $arParams["COMMENT_PROPERTY"]))
											{
												continue;
											}

											if (!empty($arPostField["VALUE"]))
											{
												$bHasProps = true;
											}

											if (
												$FIELD_NAME == "UF_BLOG_COMM_URL_PRV"
												&& array_key_exists("VALUE", $arPostField)
												&& intval($arPostField["VALUE"]) > 0
											)
											{
												$arCss = $APPLICATION->sPath2css;
												$arJs = $APPLICATION->arHeadScripts;

												$urlPreviewText = \Bitrix\Socialnetwork\ComponentHelper::getUrlPreviewContent($arPostField, array(
													"LAZYLOAD" => $arParams["LAZYLOAD"],
													"MOBILE" => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] == "Y" ? "Y" : "N"),
													"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
													"PATH_TO_USER" => $arParams["~PATH_TO_USER"]
												));

												$arResult["Assets"]["CSS"] = array_merge($arResult["Assets"]["CSS"], array_diff($APPLICATION->sPath2css, $arCss));
												$arResult["Assets"]["JS"] = array_merge($arResult["Assets"]["JS"], array_diff($APPLICATION->arHeadScripts, $arJs));
												$arComment["COMMENT_PROPERTIES"]["HIDDEN_DATA"][$FIELD_NAME] = $arPostField;
											}
											else
											{
												$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
												$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
												$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
												$arComment["COMMENT_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
											}
										}
									}
									if (!empty($arComment["COMMENT_PROPERTIES"]["DATA"]))
									{
										$arComment["COMMENT_PROPERTIES"]["SHOW"] = "Y";
									}
								}
							}
							if (is_array($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]))
							{
								$arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]["~VALUE"] = $arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]["VALUE"];
							}

							if ($commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
								$arComment,
								array(
									"mobile" => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] == "Y"),
									"bPublicPage" => $arParams["bPublicPage"],
									"cache" => true
								)
							))
							{
								$arComment["TextFormated"] = $commentAuxProvider->getText();
								$arComment["AuxType"] = $commentAuxProvider->getType();
							}
							else
							{
								$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "SHORT_ANCHOR" => "Y");
								if (
									COption::GetOptionString("blog","allow_video", "Y") != "Y"
									|| $arParams["ALLOW_VIDEO"] != "Y"
								)
								{
									$arAllow["VIDEO"] = "N";
								}

								if(
									$arParams["NO_URL_IN_COMMENTS"] == "L"
									|| (
										IntVal($arComment["AUTHOR_ID"]) <= 0
										&& $arParams["NO_URL_IN_COMMENTS"] == "A"
									)
								)
								{
									$arAllow["CUT_ANCHOR"] = "Y";
								}

								if($arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && $arAllow["CUT_ANCHOR"] != "Y" && IntVal($arComment["AUTHOR_ID"]) > 0)
								{
									$authorityRatingId = CRatings::GetAuthorityRating();
									$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComment["AUTHOR_ID"]);
									if($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
										$arAllow["CUT_ANCHOR"] = "Y";
								}

								if (is_array($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]))
								{
									$p->arUserfields = array("UF_BLOG_COMMENT_FILE" => array_merge($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"], array("TAG" => "DOCUMENT ID")));
								}

								$arComment["TextFormated"] = $p->convert($arComment["POST_TEXT"], false, $arImages, $arAllow, $arConvertParams);

								if (!empty($urlPreviewText))
								{
									$arComment["TextFormated"] .= $urlPreviewText;
								}
								$arComment["showedImages"] = $p->showedImages;
								if(!empty($p->showedImages))
								{
									foreach($p->showedImages as $val)
									{
										if(!empty($arResult["arImages"][$arComment["ID"]][$val]))
										{
											unset($arResult["arImages"][$arComment["ID"]][$val]);
										}
									}
								}
							}

							$arComment["DateFormated"] = FormatDateFromDB($arComment["DATE_CREATE"], $arParams["DATE_TIME_FORMAT"], true);
							$arComment["DATE_CREATE_DATE"] = FormatDateFromDB($arComment["DATE_CREATE"], FORMAT_DATE);
							if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
							{
								$arComment["DateFormated"] = ToLower($arComment["DateFormated"]);
								$arComment["DATE_CREATE_DATE"] = ToLower($arComment["DATE_CREATE_DATE"]);
							}
							// strip current year
							if (!empty($arParams['DATE_TIME_FORMAT_S']) && ($arParams['DATE_TIME_FORMAT_S'] == 'j F Y G:i' || $arParams['DATE_TIME_FORMAT_S'] == 'j F Y g:i a'))
							{
								$arComment["DateFormated"] = ltrim($arComment["DateFormated"], '0');
								$arComment["DATE_CREATE_DATE"] = ltrim($arComment["DATE_CREATE_DATE"], '0');
								$curYear = date('Y');
								$arComment["DateFormated"] = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $arComment["DateFormated"]);
								$arComment["DATE_CREATE_DATE"] = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $arComment["DATE_CREATE_DATE"]);
							}
							if ($arParams["MOBILE"] == "Y")
							{
								$timestamp = MakeTimeStamp($arComment["DATE_CREATE"]);
								$arComment["DATE_CREATE_TIME"] = FormatDate(GetMessage("SONET_SBPC_MOBILE_FORMAT_TIME"), $timestamp);
							}
							else
							{
								$arComment["DATE_CREATE_TIME"] = FormatDateFromDB(
									$arComment["DATE_CREATE"],
									(
										strpos($arParams["DATE_TIME_FORMAT_S"], 'a') !== false
										|| (
											$arParams["DATE_TIME_FORMAT_S"] == 'FULL'
											&& IsAmPmMode()
										) !== false
											? (strpos(FORMAT_DATETIME, 'TT')!==false ? 'G:MI TT': 'G:MI T')
											: 'GG:MI'
									)
								);
							}

							$arResult["CommentsResult"][] = $arComment;
							$arResult["IDS"][] = $arComment["ID"];

							$arFieldsHave = array();
							if($arComment["HAS_PROPS"] == "")
								$arFieldsHave["HAS_PROPS"] = ($bHasProps ? "Y" : "N");

							if(!empty($arFieldsHave))
								CBlogComment::Update($arComment["ID"], $arFieldsHave, false);
							$i++;
						}
						while (
							$i < count($arCommentsAll)
							&& ($arComment = $arCommentsAll[$i])
						);
					}
					unset($arResult["MESSAGE"]);
					unset($arResult["ERROR_MESSAGE"]);

					if ($arParams["CACHE_TIME"] > 0)
					{
						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->EndTagCache();
						}
						$cache->EndDataCache(array(
							"templateCachedData" => $this->GetTemplateCachedData(),
							"arResult" => $arResult
						));
					}
				}
			}

			$arResult["MESSAGE"] = $tmp["MESSAGE"];
			$arResult["ERROR_MESSAGE"] = $tmp["ERROR_MESSAGE"];
		}

		$arResult["commentUrl"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));
		$arResult["commentUrl"] .= (strpos($arResult["commentUrl"], "?") !== false ? "&" : "?").$arParams["COMMENT_ID_VAR"]."=#comment_id###comment_id#";

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

		if(is_array($arResult["CommentsResult"]))
		{
			$arResult["newCount"] = 0;
			$arResult["newCountWOMark"] = 0;

			$arConvertParserParams = Array(
				"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
				"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
				"pathToUser" => $arParams["PATH_TO_USER"],
			);

			$handlerManager = new Bitrix\Socialnetwork\CommentAux\HandlerManager();
			$newFound = false;

			foreach($arResult["CommentsResult"] as $k1 => $v1)
			{
				if(IntVal($commentUrlID) > 0 && $commentUrlID == $v1["ID"] && $v1["AUTHOR_ID"] == $user_id && $v1["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
					$arResult["MESSAGE"] = GetMessage("B_B_PC_HIDDEN_POSTED");

				/** @var bool|object $handler */
				if ($handler = $handlerManager->getHandlerByPostText($v1["POST_TEXT"]))
				{
					if ($handler->checkRecalcNeeded($v1, $arParams))
					{
						$commentAuxFields = $v1;
						if (isset($arParams["POST_DATA"]["SPERM_HIDDEN"]))
						{
							$commentAuxFields["HIDDEN_DEST"] = $arParams["POST_DATA"]["SPERM_HIDDEN"];
						}
						$params = $handler->getParamsFromFields($commentAuxFields);
						if (!empty($params))
						{
							$handler->setParams($params);
						}
						$handler->setOptions(array(
							'mobile' => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] == "Y"),
							'bPublicPage' => (isset($arParams["bPublicPage"]) && $arParams["bPublicPage"]),
							'cache' => false
						));
						$arResult["CommentsResult"][$k1]["TextFormated"]  = $handler->getText();
					}
				}
				else // check for old shares
				{
					if (
						!empty($v1["SHARE_DEST"])
						&& !empty($arParams["POST_DATA"]["SPERM_HIDDEN"])
					)
					{
						$dest = explode(",", $v1["SHARE_DEST"]);
						if(!empty($dest))
						{
							$bDestCut = false;
							foreach($dest as $destId)
							{
								if(in_array($destId, $arParams["POST_DATA"]["SPERM_HIDDEN"]))
								{
									$bDestCut = true;
									break;
								}
							}

							if($bDestCut)
							{
								if ($handler = \Bitrix\Socialnetwork\CommentAux\Base::init(\Bitrix\Socialnetwork\CommentAux\Share::getType(), array(
									'destinationList' => $dest,
									'hiddenDestinationList' => $arParams["POST_DATA"]["SPERM_HIDDEN"]
									),
									array(
										'mobile' => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] == "Y"),
										'cache' => false
									)
								))
								{
									$arResult["CommentsResult"][$k1]["TextFormated"]  = $handler->getText();
								}
							}
						}
					}
				}

				$bAuthor = (
					intval($v1["AUTHOR_ID"]) > 0
					&& $v1["AUTHOR_ID"] == $user_id
				);

				if (
					($arResult["bIntranetInstalled"] && $bAuthor)
					|| ($arResult["Perm"] >= BLOG_PERMS_FULL && !$arResult["bIntranetInstalled"])
					|| CSocNetUser::IsCurrentUserModuleAdmin()
					|| $APPLICATION->GetGroupRight("blog") >= "W"
				)
				{
					$arResult["CommentsResult"][$k1]["CAN_DELETE"] = "Y";
					$arResult["CommentsResult"][$k1]["CAN_EDIT"] = "Y";
				}

				if(
					$bAuthor
					&& $arPost["AUTHOR_ID"] != $v1["AUTHOR_ID"]
					&& strlen($v1["SHARE_DEST"]) > 0
				) // user can't delete his own share from other author post
				{
					$arResult["CommentsResult"][$k1]["CAN_DELETE"] = "N";
					$arResult["CommentsResult"][$k1]["CAN_EDIT"] = "N";
				}

				if(!$arResult["bIntranetInstalled"] & $arResult["Perm"] < BLOG_PERMS_FULL && !empty($arResult["CommentsResult"][$k1-1]))
				{
					$arResult["CommentsResult"][$k1-1]["CAN_EDIT"] = "N";
				}

				if (intval($arParams["CREATED_BY_ID"]) > 0)
				{
					if ($v1["AUTHOR_ID"] != $arParams["CREATED_BY_ID"])
					{
						unset($arResult["CommentsResult"][$k1]);
						unset($arResult["IDS"][$k1]);
					}
					else
					{
						$arResult["newCount"]++;
					}
				}
				else
				{
					$comment_date_create_ts = (
						isset($v1["DATE_CREATE_TS"])
							? ($v1["DATE_CREATE_TS"] + $arResult["TZ_OFFSET"])
							: MakeTimeStamp($v1["DATE_CREATE"])
					);

					if(
						intval($arParams["LAST_LOG_TS"]) > 0
						&& !empty($arResult["CommentsResult"][$k1])
						&& $arParams["LAST_LOG_TS"] < $comment_date_create_ts
					)
					{
						if ($arParams["MARK_NEW_COMMENTS"] == "Y")
						{
							$arResult["newCount"]++;
						}

						$new = ($v1["AUTHOR_ID"] != $user_id);

						if (
							!$newFound
							&& $new
							&& empty($arResult["CommentsResult"][$k1]["AuxType"])
						) // non-aux found
						{
							$newFound = true;
						}

						if (
							$new
							&& (
								empty($arResult["CommentsResult"][$k1]["AuxType"])
								|| $newFound
							)
						) // show only non-aux and aux after non-aux
						{
							if ($arParams["MARK_NEW_COMMENTS"] == "Y")
							{
								$arResult["CommentsResult"][$k1]["NEW"] = "Y";
							}
							else
							{
								$arResult["newCountWOMark"]++;
							}
						}
					}
				}

				if (isset($arResult["CommentsResult"][$k1]))
				{
					if($arResult["Perm"] >= BLOG_PERMS_MODERATE)
					{
						if($v1["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
							$arResult["CommentsResult"][$k1]["CAN_HIDE"] = "Y";
						else
							$arResult["CommentsResult"][$k1]["CAN_SHOW"] = "Y";
					}
					else
					{
						if(
							(
								$user_id <= 0 // anonymous
								|| $v1["AUTHOR_ID"] != $user_id
							)
							&& $v1["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
						)
						{
							unset($arResult["CommentsResult"][$k1]);
							unset($arResult["IDS"][$k1]);
						}
					}
				}

			}

			if($arResult["newCount"] < $arParams["PAGE_SIZE_MIN"]) // 3
			{
				$arResult["newCount"] = $arParams["PAGE_SIZE_MIN"];
			}
			$arResult["~newCount"] = $arResult["newCount"];
			if(IntVal($commentUrlID) > 0)
			{
				$arResult["newCount"] = count($arResult["CommentsResult"]);
			}
			if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
			{
				$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_COMMENT', $arResult["IDS"]);
			}
		}

		$arResult["urlToPost"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST_CURRENT"]), array("post_id" => "#source_post_id#", "user_id" => $arPost["AUTHOR_ID"]));
		$arResult["urlToPost"] .= (strpos($arResult["urlToPost"], "?") === false ? "?" : "&");

		$arResult["urlToDelete"] = $arResult["urlToPost"]."delete_comment_id=#comment_id#&comment_post_id=#post_id#&".bitrix_sessid_get();
		$arResult["urlToHide"] = $arResult["urlToPost"]."hide_comment_id=#comment_id#&comment_post_id=#post_id#&".bitrix_sessid_get();
		$arResult["urlToShow"] = $arResult["urlToPost"]."show_comment_id=#comment_id#&comment_post_id=#post_id#&".bitrix_sessid_get();
		$arResult["urlToAnswer"] = $arResult["urlToPost"]."answer_user_id=#user_id#&answer_post_id=#post_id#&".bitrix_sessid_get();

		$arResult["urlToMore"] = $arResult["urlToPost"]."last_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y&LAST_LOG_TS=#LAST_LOG_TS#";
		$arResult["urlToNew"] = $arResult["urlToPost"]."new_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y&show_new_ans=Y";

		$this->prepareUrls($arResult);

		include_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/component_adit.php");
	}

	$arResult["Post"] = $arPost;

	$this->IncludeComponentTemplate();

	return array(
		"CanUserComment" => $arResult["CanUserComment"],
		"newCountWOMark" => $arResult["newCountWOMark"]
	);
}
?>
