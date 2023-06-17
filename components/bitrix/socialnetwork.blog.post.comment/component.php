<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Blog\Item\Permissions;
use Bitrix\Socialnetwork\Helper\Mention;
use Bitrix\Main\Text;
use Bitrix\Socialnetwork\CommentAux;

global $USER_FIELD_MANAGER, $CACHE_MANAGER, $DB;

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
	ShowError(Loc::getMessage('BLOG_MODULE_NOT_INSTALL'));
	return;
}
if (!Loader::includeModule("socialnetwork"))
{
	ShowError(Loc::getMessage('SONET_MODULE_NOT_INSTALL'));
	return;
}

$currentUserId = (int)$USER->getID();

$arParams["bPublicPage"] = $arParams["bPublicPage"] ?? false;

$arParams['SOCNET_GROUP_ID'] = (int)$arParams['SOCNET_GROUP_ID'];
$arResult["bIntranetInstalled"] = ModuleManager::isModuleInstalled("intranet");
$arResult["bTasksAvailable"] = (
	(!isset($arParams["bPublicPage"]) || !$arParams["bPublicPage"])
	&& Loader::includeModule("tasks")
	&& (
		!Loader::includeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($currentUserId, "tasks")
	)
	&& \Bitrix\Tasks\Access\TaskAccessController::can($currentUserId, \Bitrix\Tasks\Access\ActionDictionary::ACTION_TASK_CREATE)
);

$arParams["ID"] = (
preg_match("/^[1-9][0-9]*\$/", trim($arParams['ID']))
	? (int)$arParams['ID']
	: preg_replace("/[^a-zA-Z0-9_-]/i", '', $arParams['~ID'])
);

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/i", "", $arParams["BLOG_URL"]);
if (!is_array($arParams["GROUP_ID"]))
{
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
}

foreach ($arParams["GROUP_ID"] as $k => $v)
{
	if ((int)$v <= 0)
	{
		unset($arParams["GROUP_ID"][$k]);
	}
}

$arParams["CACHE_TIME"] = (
$arParams["CACHE_TYPE"] === "Y"
|| (
	$arParams["CACHE_TYPE"] === "A"
	&& Option::get('main', 'component_cache_on', 'Y') === 'Y'
)
	? (int)$arParams["CACHE_TIME"]
	: 0
);

if ($arParams["BLOG_VAR"] == '')
{
	$arParams["BLOG_VAR"] = "blog";
}
if ($arParams["PAGE_VAR"] == '')
{
	$arParams["PAGE_VAR"] = "page";
}
if ($arParams["USER_VAR"] == '')
{
	$arParams["USER_VAR"] = "id";
}
if ($arParams["POST_VAR"] == '')
{
	$arParams["POST_VAR"] = "id";
}
if ($arParams["NAV_PAGE_VAR"] == '')
{
	$arParams["NAV_PAGE_VAR"] = "pagen";
}
if ($arParams["COMMENT_ID_VAR"] == '')
{
	$arParams["COMMENT_ID_VAR"] = "commentId";
}

$pageVar = (int) ($_GET[$arParams["NAV_PAGE_VAR"]] ?? 0);
$pagen = ($pageVar > 0 ? $pageVar : 1);

$arResult["TZ_OFFSET"] = CTimeZone::GetOffset();

if ((int) ($_REQUEST["LAST_LOG_TS"] ?? 0) > 0)
{
	$timeZoneOffset = (
	(
		isset($_REQUEST['AJAX_CALL'])
		&& $_REQUEST['AJAX_CALL'] === 'Y'
	)
	|| (
		isset($_REQUEST['empty_get_comments'])
		&& $_REQUEST['empty_get_comments'] === 'Y'

	)
		? $arResult["TZ_OFFSET"]
		: 0
	);

	$arParams["LAST_LOG_TS"] = (int)$_REQUEST["LAST_LOG_TS"] + $timeZoneOffset; // next mobile livefeed page or get_empty_comments
	if ($arParams["MOBILE"] !== "Y")
	{
		$arParams["MARK_NEW_COMMENTS"] = "Y";
	}
}

if ((int)$arParams['COMMENTS_COUNT'] <= 0)
{
	$arParams['COMMENTS_COUNT'] = 25;
}

if (($arParams['USE_ASC_PAGING'] ?? null) !== 'Y')
{
	$arParams['USE_DESC_PAGING'] = 'Y';
}

$applicationPage = $APPLICATION->GetCurPage();

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if ($arParams["PATH_TO_BLOG"] == '')
{
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($applicationPage."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
{
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($applicationPage."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if ($arParams["PATH_TO_POST"] == '')
{
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($applicationPage."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#"."&".$arParams["POST_VAR"]."=#post_id#");
}
$arParams["PATH_TO_POST_CURRENT"] = $arParams["PATH_TO_POST"];
if ($arParams["bPublicPage"])
{
	$arParams["PATH_TO_POST"] = \Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page');
}

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);

if (!isset($arParams["PATH_TO_CONPANY_DEPARTMENT"]) || $arParams["PATH_TO_CONPANY_DEPARTMENT"] == "")
{
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
}
if (!isset($arParams["PATH_TO_MESSAGES_CHAT"]) || $arParams["PATH_TO_MESSAGES_CHAT"] == "")
{
	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
}
if (!isset($arParams["PATH_TO_VIDEO_CALL"]) || $arParams["PATH_TO_VIDEO_CALL"] == "")
{
	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";
}

if (trim($arParams["NAME_TEMPLATE"]) == '')
{
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
}

$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] !== "N" ? "Y" : "N";
$arParams["IMAGE_MAX_WIDTH"] = (int)$arParams["IMAGE_MAX_WIDTH"];
$arParams["IMAGE_MAX_HEIGHT"] = (int)$arParams["IMAGE_MAX_HEIGHT"];
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] : 70);
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] : 70);
$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] : 1000);
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] : 1000);

$commentUrlId = (int) ($_REQUEST[$arParams['COMMENT_ID_VAR']] ?? 0);

$arParams["NAV_TYPE_NEW"] = (isset($arParams['NAV_TYPE_NEW']) && $arParams['NAV_TYPE_NEW'] === 'Y' ? 'Y' : 'N');
$arResult['firstPage'] = (
	!isset($_REQUEST["last_comment_id"]) // web
	&& empty($_REQUEST["FILTER"]) // mobile
	&& $commentUrlId <= 0
);

$arParams["DATE_TIME_FORMAT_S"] = $arParams["DATE_TIME_FORMAT"];

CSocNetLogComponent::processDateTimeFormatParams($arParams);
CRatingsComponentsMain::getShowRating($arParams);

$arParams["SEF"] = (isset($arParams["SEF"]) && $arParams["SEF"] === "N" ? "N" : "Y");
$arParams["CAN_USER_COMMENT"] = (!isset($arParams["CAN_USER_COMMENT"]) || $arParams["CAN_USER_COMMENT"] === 'Y' ? 'Y' : 'N');

$arParams["ALLOW_VIDEO"] = ($arParams["ALLOW_VIDEO"] === "N" ? "N" : "Y");
$arResult['allowVideo'] = (
	$arParams['ALLOW_VIDEO'] === 'N'
		? 'N'
		: Option::get('blog', 'allow_video', 'Y')
);

if (
	empty($arParams['ALLOW_IMAGE_UPLOAD'])
	|| $arParams['ALLOW_IMAGE_UPLOAD'] === "A"
	|| (
		$arParams["ALLOW_IMAGE_UPLOAD"] === "R"
		&& $USER->IsAuthorized()
	)
)
{
	$arResult["allowImageUpload"] = true;
}

$arResult["userID"] = $currentUserId;
$arResult["canModerate"] = false;
$arResult["ajax_comment"] = 0;
$arResult["is_ajax_post"] = "N";

$a = new CAccess;
$a->UpdateCodes();

$arParams["PAGE_SIZE"] = (int)$arParams["PAGE_SIZE"];
if ($arParams["PAGE_SIZE"] <= 0)
{
	$arParams["PAGE_SIZE"] = 20;
}

$arParams["PAGE_SIZE_MIN"] = 3;

if (($arParams['NO_URL_IN_COMMENTS'] ?? null) === 'L')
{
	$arResult["NoCommentUrl"] = true;
	$arResult["NoCommentReason"] = Loc::getMessage('B_B_PC_MES_NOCOMMENTREASON_L');
}
if (
	($arParams['NO_URL_IN_COMMENTS'] ?? null) === 'A'
	&& !$USER->IsAuthorized()
)
{
	$arResult["NoCommentUrl"] = true;
	$arResult['NoCommentReason'] = Loc::getMessage('B_B_PC_MES_NOCOMMENTREASON_A');
}

if (is_numeric($arParams["NO_URL_IN_COMMENTS_AUTHORITY"] ?? null))
{
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY"] = floatVal($arParams["NO_URL_IN_COMMENTS_AUTHORITY"]);
	$arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] = "Y";

	if ($USER->IsAuthorized())
	{
		$authorityRatingId = CRatings::GetAuthorityRating();
		$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $currentUserId);
		if ($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
		{
			$arResult["NoCommentUrl"] = true;
			$arResult['NoCommentReason'] = Loc::getMessage('B_B_PC_MES_NOCOMMENTREASON_R');
		}
	}
}

$arParams["COMMENT_PROPERTY"] = [ 'UF_BLOG_COMMENT_DOC' ];
if (
	Loader::includeModule('webdav')
	|| Loader::includeModule('disk')
)
{
	$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_FILE";
	$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_FH";
}

$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMM_URL_PRV";

$arBlog = $arParams["BLOG_DATA"];
$arPost = $arParams["POST_DATA"];

$arResult["Perm"] = Permissions::DENY;
$arResult["PostPerm"] = Permissions::DENY;
$arResult["PermBySG"] = false;

if (
	(int) ($_REQUEST['comment_post_id'] ?? 0) > 0
	|| (
		isset($arParams['COMPONENT_AJAX'])
		&& $arParams['COMPONENT_AJAX'] === 'Y'
	)
)
{
	if (
		$arParams > 0
		&& !empty($arParams['POST_DATA'])
	)
	{
		$arPost = $arParams['POST_DATA'];

		$arResult['PostPerm'] = CBlogPost::getSocNetPostPerms(
			$arParams['ID'],
			false,
			$currentUserId,
			$arPost['AUTHOR_ID']
		);
		if ($arResult['PostPerm'] > Permissions::DENY)
		{
			$this->getCommentsPerm([
				'mobile' => ($arParams['MOBILE'] === 'Y'),
				'currentUserId' => $currentUserId,
				'postId' => $arParams['ID'],
				'postAuthorId' => $arPost['AUTHOR_ID'],
				'postHasAllDestination' => ($arPost['HAS_SOCNET_ALL'] === 'Y'),
			], $arResult);
		}
	}

	$arResult['is_ajax_post'] = "Y";
}
else
{
	$arResult["PostPerm"] = (
	(string)$arParams["POST_DATA"]["perms"] === ''
		? CBlogPost::GetSocNetPostPerms($arParams["ID"])
		: $arParams["POST_DATA"]["perms"]
	);

	if ($arResult["PostPerm"] > Permissions::DENY)
	{
		$this->getCommentsPerm([
			'mobile' => ($arParams['MOBILE'] === 'Y'),
			'currentUserId' => $currentUserId,
			'postId' => $arParams['ID'],
			'postAuthorId' => $arPost['AUTHOR_ID'],
			'postHasAllDestination' => ($arParams['POST_DATA']['HAVE_ALL_IN_ADR'] === 'Y'),
		], $arResult);
	}
}

if (
	$_SERVER['REQUEST_METHOD'] === 'POST'
	&& isset($_REQUEST['mfi_mode'])
	&& ($_REQUEST['mfi_mode'] === 'upload')
)
{
	CBlogImage::AddImageResizeHandler([
		'width' => 400,
		'height' => 400,
	]);
}

if (
	!empty($arPost)
	&& $arPost["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_PUBLISH
	&& $arPost["ENABLE_COMMENTS"] === 'Y'
)
{
	$arImages = [];

	//Comment delete
	if ($arResult['deleteCommentId'] > 0)
	{
		if (isset($_GET["success"]) && $_GET["success"] === "Y")
		{
			$arResult["MESSAGE"] = Loc::getMessage('B_B_PC_MES_DELED');
		}
		else
		{
			$arComment = CBlogComment::getById($arResult['deleteCommentId']);
			if (
				(
					$arResult["Perm"] >= Permissions::MODERATE
					|| (
						$currentUserId > 0
						&& (int)$arComment["AUTHOR_ID"] === $currentUserId
					)
				)
				&& !empty($arComment)
				&& CBlogComment::Delete($arResult['deleteCommentId'])
			)
			{
				BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
					'TYPE' => 'post_comments',
					'POST_ID' => $arParams["ID"]
				)));
				CBlogComment::DeleteLog($arResult['deleteCommentId']);

				$arResult["ajax_comment"] = $arResult['deleteCommentId'];
				$arResult["MESSAGE"] = Loc::getMessage('B_B_PC_MES_DELED');
			}

			if (
				(int)$arResult["ajax_comment"] <= 0
				&& (string)$arResult["ERROR_MESSAGE"] === ''
			)
			{
				$arResult["ERROR_MESSAGE"] = Loc::getMessage('B_B_PC_MES_ERROR_DELETE');
				if ($ex = $APPLICATION->GetException())
				{
					$arResult["ERROR_MESSAGE"] .= ": ".$ex->GetString();
				}
			}
		}
	}
	elseif ($arResult['showCommentId'] > 0)
	{
		$arComment = CBlogComment::GetByID($arResult['showCommentId']);
		$arTagInline = \Bitrix\Socialnetwork\Util::detectTags($arComment, array('POST_TEXT'));

		if (
			$arResult["Perm"] >= Permissions::MODERATE
			&& !empty($arComment)
		)
		{
			if ($arComment["PUBLISH_STATUS"] !== BLOG_PUBLISH_STATUS_READY)
			{
				$arResult["ERROR_MESSAGE"] = Loc::getMessage('B_B_PC_MES_ERROR_SHOW');
			}
			elseif ($commentID = CBlogComment::Update($arComment["ID"], [
				"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
				"SEARCH_GROUP_ID" => Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID),
			]))
			{
				BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
					'TYPE' => 'post_comments',
					'POST_ID' => $arParams["ID"]
				)));
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
					$res = CBlogImage::GetList(
						[
							'ID' => 'ASC',
						],
						[
							'POST_ID' => $arPost['ID'],
							'BLOG_ID' => $arPost['BLOG_ID'],
							'IS_COMMENT' => 'Y',
							'COMMENT_ID' => $arComment['ID'],
						]
					);
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
					$commentUrl .= (mb_strpos($commentUrl, "?") !== false ? "&" : "?");
					$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$arComment["ID"]."#com".$arComment["ID"];

					$arFieldsForSocnet = array(
						"ENTITY_TYPE" => SONET_ENTITY_USER,
						"ENTITY_ID" => $arPost["AUTHOR_ID"],
						"EVENT_ID" => "blog_comment",
						"=LOG_DATE" => CDatabase::currentTimeFunction(),
						"MESSAGE" => $text4message,
						"TEXT_MESSAGE" => $text4mail,
						"URL" => $commentUrl,
						"MODULE_ID" => false,
						"SOURCE_ID" => $arComment["ID"],
						"LOG_ID" => $log_id,
						"RATING_TYPE_ID" => "BLOG_COMMENT",
						"RATING_ENTITY_ID" => (int)$arComment["ID"]
					);

					if ((int)$arComment["AUTHOR_ID"] > 0)
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
		if ((int)$arResult["ajax_comment"] <= 0)
		{
			$arResult["ERROR_MESSAGE"] = Loc::getMessage('B_B_PC_MES_ERROR_SHOW');
		}
	}
	elseif ($arResult['hideCommentId'] > 0)
	{
		$arComment = CBlogComment::GetByID($arResult['hideCommentId']);
		if (
			$arResult["Perm"] >= Permissions::MODERATE
			&& !empty($arComment)
		)
		{
			if ($arComment["PUBLISH_STATUS"] !== BLOG_PUBLISH_STATUS_PUBLISH)
			{
				$arResult["ERROR_MESSAGE"] = Loc::getMessage('B_B_PC_MES_ERROR_SHOW');
			}
			elseif ($commentID = CBlogComment::Update($arComment["ID"], [ "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY ]))
			{
				BXClearCache(true, ComponentHelper::getBlogPostCacheDir([
					'TYPE' => 'post_comments',
					'POST_ID' => $arParams["ID"],
				]));
				CBlogComment::DeleteLog($arComment["ID"]);
				$arResult["ajax_comment"] = $arComment["ID"];
			}
		}
		if (
			(int)$arResult["ajax_comment"] <= 0
			&& $arResult["ERROR_MESSAGE"] == ''
		)
		{
			$arResult["ERROR_MESSAGE"] = Loc::getMessage('B_B_PC_MES_ERROR_HIDE');
		}
	}
	elseif ((int) ($_GET["hidden_add_comment_id"] ?? 0) > 0)
	{
		$arResult["MESSAGE"] = Loc::getMessage('B_B_PC_MES_HIDDEN_ADDED');
	}

	$arResult["CanUserComment"] = false;
	$arResult["canModerate"] = false;
	if (
		$arResult["Perm"] >= Permissions::PREMODERATE
		&& $arParams["CAN_USER_COMMENT"] === 'Y'
	)
	{
		$arResult["CanUserComment"] = true;
	}

	if ($arResult["Perm"] >= Permissions::MODERATE)
	{
		$arResult["canModerate"] = true;
	}

	if ($currentUserId >0)
	{
		$arResult["User"]["ID"] = $currentUserId;
	}

	$arResult["use_captcha"] = false;
	if (
		$arResult["CanUserComment"]
		&& !$USER->IsAuthorized()
	)
	{
		if (!empty($arBlog))
		{
			CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
		}
		elseif (!empty(CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]]))
		{
			$arBlog = CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]];
		}
		else
		{
			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
			CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
		}

		$useCaptcha = COption::GetOptionString("blog", "captcha_choice", "U");
		if ($useCaptcha === "U")
		{
			$arResult["use_captcha"] = ($arBlog["ENABLE_IMG_VERIF"] === 'Y');
		}
		elseif ($useCaptcha === "A")
		{
			$arResult["use_captcha"] = true;
		}
		else
		{
			$arResult["use_captcha"] = false;
		}
	}

	if (
		$_SERVER["REQUEST_METHOD"] === "POST"
		&& !empty($arPost["ID"])
		&& isset($_POST["post"])
		&& $_POST["post"] <> ''
	)
	{
		if (($_POST["decode"] ?? null) === "Y")
		{
			CUtil::JSPostUnescape();
		}

		if ($arResult["Perm"] >= Permissions::PREMODERATE)
		{
			if (!empty($arBlog))
			{
				CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
			}
			elseif (!empty(CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]]))
			{
				$arBlog = CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]];
			}
			else
			{
				$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
				CBlogPost::$arBlogPCCache[$arPost["BLOG_ID"]] = $arBlog;
			}

			$strErrorMessage = '';
			if (
				(($_POST["blog_upload_image_comment"] ?? null) === "Y")
				&& $_FILES["BLOG_UPLOAD_FILE"]["size"] > 0
			)
			{
				$arResult["imageUploadFrame"] = "Y";
				$APPLICATION->RestartBuffer();
				header("Pragma: no-cache");

				$arFields = [
					'MODULE_ID' => 'blog',
					'BLOG_ID' => $arBlog['ID'],
					'POST_ID' => $arPost['ID'],
					'=TIMESTAMP_X' => $DB->GetNowFunction(),
					'TITLE' => '',
					'IMAGE_SIZE' => $_FILES['BLOG_UPLOAD_FILE']['size'],
					'IS_COMMENT' => 'Y',
					'URL' => $arBlog['URL'],
					'USER_ID' => $currentUserId,
				];
				$arFields["FILE_ID"] = array_merge(
					$_FILES["BLOG_UPLOAD_FILE"],
					[
						"MODULE_ID" => "blog",
						"del" => "Y",
					]
				);

				if ($imgID = CBlogImage::Add($arFields))
				{
					$aImg = CBlogImage::GetByID($imgID);
					$aImg["PARAMS"] = CFile::_GetImgParams($aImg["FILE_ID"]);
					$arResult["Image"] = Array("ID" => $aImg["ID"], "SRC" => $aImg["PARAMS"]["SRC"], "WIDTH" => $aImg["PARAMS"]["WIDTH"], "HEIGHT" => $aImg["PARAMS"]["HEIGHT"]);
				}
				elseif ($ex = $APPLICATION->GetException())
				{
					$arResult["ERROR_MESSAGE"] = $ex->GetString();
				}

				if (
					!empty($arResult['ERROR_MESSAGE'])
					&& $this->isAjaxRequest()
				)
				{
					$APPLICATION->throwException($arResult['ERROR_MESSAGE']);
				}

				$this->IncludeComponentTemplate();
				return;
			}

			if ($_POST["act"] !== 'edit')
			{
				if ($arResult["use_captcha"])
				{
					include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/captcha.php');
					$captchaCode = (string)$_POST["captcha_code"];
					$captchaWord = (string)$_POST["captcha_word"];
					$cpt = new CCaptcha();
					$captchaPass = Option::get('main', 'captcha_password');
					if ($captchaCode !== '')
					{
						if (!$cpt->CheckCodeCrypt($captchaWord, $captchaCode, $captchaPass))
						{
							$strErrorMessage .= Loc::getMessage('B_B_PC_CAPTCHA_ERROR') . "<br />";
						}
					}
					else
					{
						$strErrorMessage .= Loc::getMessage('B_B_PC_CAPTCHA_ERROR') . "<br />";
					}
				}

				if (
					isset($_POST['webdav_history'], $_POST['comment'])
					&& $_POST['webdav_history'] === 'Y'
					&& $_POST['comment'] <> ''
				)
				{
					$_POST["comment"] = Text\Encoding::convertEncoding($_POST["comment"], 'UTF-8', LANG_CHARSET);
				}

				$UserIP = CBlogUser::GetUserIP();
				$arFields = Array(
					"POST_ID" => $arPost["ID"],
					"BLOG_ID" => $arBlog["ID"],
					"TITLE" => trim($_POST["subject"] ?? ''),
					"POST_TEXT" => trim(preg_replace("/\xe2\x81\xa0/is", ' ', $_POST["comment"])), // INVISIBLE_CURSOR from editor
					"AUTHOR_IP" => $UserIP[0],
					"AUTHOR_IP1" => $UserIP[1],
					"URL" => $arBlog["URL"],
					"PARENT_ID" => false,
					"SEARCH_GROUP_ID" => Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID)
				);

				if (\Bitrix\Main\Config\Configuration::getValue("utf_mode") === true)
				{
					$conn = \Bitrix\Main\Application::getConnection();
					$table = \Bitrix\Blog\CommentTable::getTableName();

					if (
						((string)$arFields['POST_TEXT'] !== '')
						&& !$conn->isUtf8mb4($table, 'POST_TEXT')
					)
					{
						$arFields["POST_TEXT"] = Text\Emoji::encode($arFields["POST_TEXT"]);
					}
				}

				if ($arResult["Perm"] === Permissions::PREMODERATE)
				{
					$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
				}

				if ($currentUserId > 0)
				{
					$arFields["AUTHOR_ID"] = $currentUserId;
				}
				else
				{
					$arFields["AUTHOR_NAME"] = trim($_POST["user_name"]);
					if (trim($_POST["user_email"]) !== '')
					{
						$arFields["AUTHOR_EMAIL"] = trim($_POST["user_email"]);
					}
					if ($arFields["AUTHOR_NAME"] === '')
					{
						$strErrorMessage .= Loc::getMessage('B_B_PC_NO_ANAME')."<br />";
					}
					$_SESSION["blog_user_name"] = $_POST["user_name"];
					$_SESSION["blog_user_email"] = $_POST["user_email"];
				}

				if ($arFields["POST_TEXT"] == '')
				{
					$strErrorMessage .= Loc::getMessage('B_B_PC_NO_COMMENT')."<br />";
				}

				if ((int)$_REQUEST['as'] > 0)
				{
					$arParams['AVATAR_SIZE_COMMENT'] = (int)$_REQUEST['as'];
				}

				$commentId = 0;

				if ($strErrorMessage === '')
				{
					\Bitrix\Blog\Item\Comment::checkDuplicate([
						'MESSAGE' => $arFields['POST_TEXT'],
						'BLOG_ID' => $arBlog['ID'],
						'POST_ID' => $arPost['ID'],
						'AUTHOR_ID' => $arFields['AUTHOR_ID']
					], $commentId);

					if ($commentId <= 0)
					{
						$fieldName = 'UF_BLOG_COMMENT_DOC';
						if (isset($GLOBALS[$fieldName]) && is_array($GLOBALS[$fieldName]))
						{
							$arAttachedFiles = [];
							foreach ($GLOBALS[$fieldName] as $fileID)
							{
								$fileID = (int)$fileID;
								if (
									$fileID <= 0
									|| !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_" . $_POST["blog_upload_cid"]])
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
										"USER_ID" => $currentUserId,
										"IMAGE_SIZE_CHECK" => "N",
									);
									$imgID = CBlogImage::Add($arImgFields);
									if ((int)$imgID <= 0)
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

						if (!empty($arParams['COMMENT_PROPERTY']))
						{
							$USER_FIELD_MANAGER->EditFormAddFields("BLOG_COMMENT", $arFields);
						}

						$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

						$arFields["PATH"] = $commentUrl;
						$arFields["PATH"] .= (mb_strpos($arFields["PATH"], "?") !== false ? "&" : "?");
						$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=#comment_id##com#comment_id#";

						if (
							$arParams['MOBILE'] === "Y"
							&& empty($arFields['UF_BLOG_COMM_URL_PRV'])
							&& in_array('UF_BLOG_COMM_URL_PRV', $arParams['COMMENT_PROPERTY'])
							&& ($urlPreviewValue = ComponentHelper::getUrlPreviewValue($arFields['POST_TEXT']))
						)
						{
							$arFields["UF_BLOG_COMM_URL_PRV"] = $urlPreviewValue;
						}
						$arTagInline = \Bitrix\Socialnetwork\Util::detectTags($arFields, array('POST_TEXT'));

						$log_id = 0;

						$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
						$dbRes = CSocNetLog::GetList(
							array("ID" => "DESC"),
							array(
								"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
								'SOURCE_ID' => $arPost['ID']
							),
							false,
							false,
							array("ID", "TMP_ID")
						);
						if ($arRes = $dbRes->Fetch())
						{
							$log_id = $arRes["ID"];
						}

						if ((int)$log_id > 0)
						{
							$shareCommentId = \Bitrix\Socialnetwork\Integration\Blog\Mention::processCommentShare([
								'commentText' => $_POST['comment'],
								'authorId' => $currentUserId,
								'postId' => $arPost['ID'],
								'blogId' => $arPost['BLOG_ID'],
								'siteId' => SITE_ID,
								'clearCache' => false,
							]);
						}

						$arFields["DATE_CREATE"] = ConvertTimeStamp(time() + $arResult["TZ_OFFSET"], "FULL");

						if ($commentId = CBlogComment::add($arFields))
						{
							BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
								'TYPE' => 'post_comments',
								'POST_ID' => $arParams["ID"],
							)));
							$images = [];

							$DB->Query('UPDATE b_blog_image SET COMMENT_ID=' . (int)$commentId . " WHERE BLOG_ID=" . (int)$arBlog["ID"] . " AND POST_ID = " . (int)$arPost['ID'] . " AND IS_COMMENT = 'Y' AND (COMMENT_ID = 0 OR COMMENT_ID is null) AND USER_ID=" . $currentUserId . '', true);

							$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arParams["bPublicPage"]));
							$arParserParams = Array(
								"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
								"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
							);

							$commentUrl .= (mb_strpos($commentUrl, "?") !== false ? "&" : "?");
							if (
								($arFields["PUBLISH_STATUS"] ?? null) <> ''
								&& ($arFields["PUBLISH_STATUS"] ?? null) !== BLOG_PUBLISH_STATUS_PUBLISH
							)
							{
								$commentAddedUrl = $commentUrl.$arParams["COMMENT_ID_VAR"]."=".$commentId."&hidden_add_comment_id=".$commentId;
							}
							$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$commentId."#com".$commentId;

							if (
								($arFields["PUBLISH_STATUS"] ?? null) === BLOG_PUBLISH_STATUS_PUBLISH
								|| (string) ($arFields["PUBLISH_STATUS"] ?? null) === ''
							)
							{
								if ($log_id <= 0)
								{
									$arParamsNotify = [
										"bSoNet" => true,
										"UserID" => $arParams["USER_ID"],
										"allowVideo" => $arResult["allowVideo"],
										"bGroupMode" => $arResult["bGroupMode"],
										"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
										"PATH_TO_POST" => $arParams["PATH_TO_POST"],
										"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
										"user_id" => $currentUserId,
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
										"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
									];
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

								$arPSR = CBlogPost::getSocnetPerms($arPost["ID"], false);
								$arUsrCode = $arUsrIdToPush = array();
								if (!empty($arPSR["U"]))
								{
									$arUsrId = $arUsrIdToPush = array_keys($arPSR["U"]);
									foreach ($arPSR["U"] as $k => $v)
									{
										$arUsrCode[] = "U".$k;
									}

									if (count($arPSR) > 1) // not only users
									{
										$arUsrIdToPush = array();
									}
								}

								if ((int)$log_id > 0)
								{
									$text4message = $parserBlog->convert($_POST['comment'], false, $arImages, $arAllow, array("isSonetLog" => true));

									$arFieldsForSocnet = [
										"ENTITY_TYPE" => SONET_ENTITY_USER,
										"ENTITY_ID" => $arBlog["OWNER_ID"],
										"EVENT_ID" => "blog_comment",
										'=LOG_DATE' => CDatabase::currentTimeFunction(),
										"MESSAGE" => $text4message,
										"TEXT_MESSAGE" => $text4mail,
										"URL" => $commentUrl,
										"MODULE_ID" => false,
										"SOURCE_ID" => $commentId,
										"LOG_ID" => $log_id,
										"RATING_TYPE_ID" => "BLOG_COMMENT",
										"RATING_ENTITY_ID" => (int)$commentId
									];

									if ($currentUserId > 0)
									{
										$arFieldsForSocnet["USER_ID"] = $currentUserId;
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
													? []
													: $arUsrIdToPush
											)
										);
									}
								}

								$arFieldsIM = Array(
									"TYPE" => "COMMENT",
									"COMMENT_ID" => $commentId,
									"TITLE" => htmlspecialcharsBack($arPost["TITLE"]),
									"URL" => $commentUrl,
									"ID" => $arPost["ID"],
									"FROM_USER_ID" => $currentUserId,
									"TO_USER_ID" => array($arPost["AUTHOR_ID"]),
									"TO_SOCNET_RIGHTS" => $arUsrCode,
									"TO_SOCNET_RIGHTS_OLD" => array(
										"U" => array(),
										"SG" => array()
									),
									"AUTHOR_ID" => $arPost["AUTHOR_ID"],
									"BODY" => $text4im
								);

								$arMention = Mention::getUserIds($_POST['comment']);

								if (!empty($arMention))
								{
									$arFieldsIM['MENTION_ID'] = $arMention;
									if (
										$arParams['MOBILE'] === 'Y'
										&& $_POST['act'] !== 'edit'
									)
									{
										$arMentionedDestCode = [];
										foreach ($arMention as $val)
										{
											$arMentionedDestCode[] = 'U' . $val;
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

								if (!empty($arUsrId))
								{
									CBlogPost::NotifyMail(array(
										"type" => "COMMENT",
										"siteId" => SITE_ID,
										"userId" => $arUsrId,
										"authorId" => $currentUserId,
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
							elseif ($arFields["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_READY)
							{
								$arPostCodes = [];
								$arPSR = CBlogPost::GetSocnetPerms($arPost["ID"]);
								if (!empty($arPSR['SG']))
								{
									foreach ($arPSR['SG'] as $arCodes)
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
										"FROM_USER_ID" => $currentUserId,
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
								($arFields["PUBLISH_STATUS"] ?? null) <> ''
								&& ($arFields["PUBLISH_STATUS"] ?? null) !== BLOG_PUBLISH_STATUS_PUBLISH
							)
							{
								$arResult["MESSAGE"] = Loc::getMessage('B_B_PC_MES_HIDDEN_ADDED');
							}

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
								if (!empty($arPostField['VALUE']))
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
							if (!empty($shareCommentId))
							{
								CBlogComment::delete($shareCommentId);
							}

							if ($e = $APPLICATION->getException())
							{
								$arResult["COMMENT_ERROR"] = '<b>' . Loc::getMessage('B_B_PC_COM_ERROR') . '</b><br />' . $e->getString();
							}
						}
					}

					if ($commentId > 0)
					{
						$arResult['WARNING_CODE'] = 'COMMENT_DUPLICATED';
						$arResult['WARNING_MESSAGE'] = Loc::getMessage('B_B_PC_DUPLICATE_COMMENT');
						$arResult['ajax_comment'] = $commentId;
					}
				}
				else
				{
					$arResult["COMMENT_ERROR"] = ($strErrorMessage <> '' ? $strErrorMessage : Loc::getMessage('B_B_PC_COM_ERROR'));
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
					$arResult["COMMENT_ERROR"] = Loc::getMessage('B_B_PC_COM_ERROR_EDIT') . ": " . Loc::getMessage('B_B_PC_COM_ERROR_LOST');
				}
				elseif (
					(int)$arOldComment['AUTHOR_ID'] === $currentUserId
					|| $arResult["Perm"] >= Permissions::FULL
				)
				{
					$arFields = Array(
						"POST_TEXT" => $_POST["comment"],
						"URL" => $arBlog["URL"],
					);
					if ($arResult["Perm"] === Permissions::PREMODERATE)
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
						foreach ($GLOBALS[$fieldName] as $fileID)
						{
							$fileID = (int)$fileID;
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
									"BLOG_ID" => $arBlog["ID"],
									"POST_ID" => $arPost["ID"],
									"USER_ID" => $currentUserId,
									"COMMENT_ID" => $commentID,
									"=TIMESTAMP_X" => $DB->GetNowFunction(),
									"TITLE" => $arFile["FILE_NAME"],
									"IMAGE_SIZE" => $arFile["FILE_SIZE"],
									"FILE_ID" => $fileID,
									"IS_COMMENT" => "Y",
									"URL" => $arBlog["URL"],
									"IMAGE_SIZE_CHECK" => "N",
								);
								$imgID = CBlogImage::Add($arImgFields);
								if ((int)$imgID <= 0)
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

					if (!empty($arParams['COMMENT_PROPERTY']))
					{
						$USER_FIELD_MANAGER->EditFormAddFields("BLOG_COMMENT", $arFields);
					}

					$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

					$arFields["PATH"] = $commentUrl;
					$arFields["PATH"] .= (mb_strpos($arFields["PATH"], "?") !== false ? "&" : "?");
					$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;

					$dbComment = CBlogComment::GetList([], [
						'POST_ID' => $arPost['ID'],
						'BLOG_ID' => $arBlog['ID'],
						'>ID' => $commentID,
					]);
					if (
						$arResult["Perm"] < Permissions::FULL
						&& !$arResult["bIntranetInstalled"]
						&& $dbComment->Fetch()
					)
					{
						$arResult["COMMENT_ERROR"] = Loc::getMessage('B_B_PC_COM_ERROR_EDIT') . ': ' . Loc::getMessage('B_B_PC_EDIT_ALREADY_COMMENTED');
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

						if (
							$arParams['MOBILE'] === 'Y'
							&& empty($arFields["UF_BLOG_COMM_URL_PRV"])
							&& (
								empty($arPostFields['UF_BLOG_COMM_URL_PRV'])
								|| empty($arPostFields['UF_BLOG_COMM_URL_PRV']['VALUE'])
							)
							&& !empty($arFields["POST_TEXT"])
							&& in_array("UF_BLOG_COMM_URL_PRV", $arParams["COMMENT_PROPERTY"])
							&& ($urlPreviewValue = ComponentHelper::getUrlPreviewValue($arFields["POST_TEXT"]))
						)
						{
							$arFields["UF_BLOG_COMM_URL_PRV"] = $urlPreviewValue;
						}

						$arFields["SEARCH_GROUP_ID"] = Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID);

						if ($commentID = CBlogComment::Update($commentID, $arFields))
						{
							BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
								'TYPE' => 'post_comments',
								'POST_ID' => $arParams["ID"]
							)));
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

							if ($currentUserId > 0)
							{
								$arResult["BlogUser"] = CBlogUser::GetByID($currentUserId, BLOG_BY_USER_ID);
								$arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["BlogUser"]);
								$dbUser = CUser::GetByID($currentUserId);
								$arResult["arUser"] = $dbUser->GetNext();
								$arResult["User"]["NAME"] = CBlogUser::GetUserName($arResult["BlogUser"]["ALIAS"], $arResult["arUser"]["NAME"], $arResult["arUser"]["LAST_NAME"], $arResult["arUser"]["LOGIN"]);
							}

							CBlogComment::UpdateLog($commentID, $arResult["BlogUser"], $arResult["User"], $arFields, $arPost, $arParamsUpdateLog);

							$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => false, "<=TIMESTAMP_X" => ConvertTimeStamp(AddToTimeStamp(Array("HH" => -3)), "FULL")));
							while ($aImg = $res->Fetch())
							{
								CBlogImage::Delete($aImg["ID"]);
							}

							$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"]));
							$commentUrl .= (mb_strpos($commentUrl, "?") !== false ? "&" : "?");

							if (
								($arFields["PUBLISH_STATUS"] ?? '') <> ''
								&& $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
							)
							{
								$arResult["MESSAGE"] = Loc::getMessage('B_B_PC_MES_HIDDEN_EDITED');
							}

							if (
								(
									!empty($arFields["PUBLISH_STATUS"])
									&& $arFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH
								)
								|| (
									empty($arFields["PUBLISH_STATUS"])
									&& !empty($arOldComment["PUBLISH_STATUS"])
									&& $arOldComment['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH
								)
							)
							{
								\Bitrix\Socialnetwork\Integration\Blog\Mention::processCommentShare([
									'commentText' => $_POST['comment'],
									'excludedUserIdList' => [
										(int)$arPost['AUTHOR_ID'],
										(int)$arOldComment['AUTHOR_ID'],
									],
									'authorId' => $currentUserId,
									'postId' => (int)$arPost['ID'],
									'blogId' => (int)$arPost['BLOG_ID'],
									'siteId' => SITE_ID,
								]);
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
								if (!empty($arPostField["VALUE"]))
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
						elseif ($e = $APPLICATION->GetException())
						{
							$arResult["COMMENT_ERROR"] = Loc::getMessage('B_B_PC_COM_ERROR_EDIT') . ': ' . $e->GetString();
						}
					}
				}
				else
				{
					$arResult["COMMENT_ERROR"] = Loc::getMessage('B_B_PC_COM_ERROR_EDIT') . ': ' . Loc::getMessage('B_B_PC_NO_RIGHTS_EDIT');
				}
			}
		}
		else
		{
			$arResult["COMMENT_ERROR"] = Loc::getMessage('B_B_PC_NO_RIGHTS');
		}
	}

	//Comments output
	if ($arResult["Perm"] >= BLOG_PERMS_READ)
	{
		/////////////////////////////////////////////////////////////////////////////////////

		$tmp = Array();
		$tmp["MESSAGE"] = ($arResult["MESSAGE"] ?? '');
		$tmp["ERROR_MESSAGE"] = ($arResult["ERROR_MESSAGE"] ?? '');
		if (
			(
				($arResult["COMMENT_ERROR"] ?? '') <> ''
				|| ($arResult["ERROR_MESSAGE"] ?? '') <> ''
			)
		)
		{
			$arResult["is_ajax_post"] = "Y";
		}
		else
		{
			if ((int) ($_REQUEST["new_comment_id"] ?? 0) > 0) // for push&pull
			{
				$arResult["ajax_comment"] = (int)$_REQUEST["new_comment_id"];
			}

			if (
				(
					(int)$arParams["ID"] > 0
					&& (
						(isset($arPost["NUM_COMMENTS_ALL"]) && (int)$arPost["NUM_COMMENTS_ALL"] > 0)
						|| (int)$arPost["NUM_COMMENTS"] > 0
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
				foreach ($arKeys as $param_key)
				{
					$arCacheID[$param_key] = (array_key_exists($param_key, $arParams) ? $arParams[$param_key] : false);
				}

				$cache_id = "blog_comment_".$USER->IsAuthorized() . "_" . md5(serialize($arCacheID)) . "_" . LANGUAGE_ID . "_" . $arParams["DATE_TIME_FORMAT"] . "_" . Bitrix\Main\Context::getCurrent()->getCulture()->getDateTimeFormat() . ($arParams["NAV_TYPE_NEW"] === 'Y' && $arResult['firstPage'] ? '_' . $arParams["PAGE_SIZE"] : '');
				if ($arResult["TZ_OFFSET"] <> 0)
				{
					$cache_id .= '_' . $arResult["TZ_OFFSET"];
				}
				$cache_path = ComponentHelper::getBlogPostCacheDir(array(
					'TYPE' => 'post_comments',
					'POST_ID' => $arParams["ID"]
				));

				if ((int)$arResult["ajax_comment"] > 0)
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
							foreach ($arResult["Assets"]["CSS"] as $cssFile)
							{
								\Bitrix\Main\Page\Asset::getInstance()->addCss($cssFile);
							}
						}

						if (!empty($arResult["Assets"]["JS"]))
						{
							foreach ($arResult["Assets"]["JS"] as $jsFile)
							{
								\Bitrix\Main\Page\Asset::getInstance()->addJs($jsFile);
							}
						}
					}
					$cache->Output();
				}
				else
				{
					if ($arParams["CACHE_TIME"] > 0)
					{
						$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
						if (defined("BX_COMP_MANAGED_CACHE"))
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

					$arFilter = Array("POST_ID" => $arParams["ID"], "BLOG_ID" => $arPost["BLOG_ID"]);
					if ($arResult["is_ajax_post"] === "Y" && (int)$arResult["ajax_comment"] > 0)
					{
						$arFilter["ID"] = $arResult["ajax_comment"];
					}
					$arSelectedFields = Array("ID", "BLOG_ID", "POST_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS", "HAS_PROPS", "SHARE_DEST");

					if ($DB->type === "MYSQL")
					{
						$arSelectedFields[] = "DATE_CREATE_TS";
					}

					$navParams = (
						$arParams["NAV_TYPE_NEW"] === 'Y'
						&& $arResult['firstPage']
							? array('nTopCount' => $arParams['PAGE_SIZE'])
							: false
					);

					$arOrder = (
						$arParams["NAV_TYPE_NEW"] === 'Y'
						&& $arResult['firstPage']
							? array("DATE_CREATE" => "DESC", "ID" => "DESC")
							: array("DATE_CREATE" => "ASC", "ID" => "ASC")
					);

					$dbComment = CBlogComment::GetList(
						$arOrder,
						$arFilter,
						false,
						$navParams,
						$arSelectedFields
					);
					$resComments = Array();

					$arCommentsAll = array();
					$arIdToGet = array();

					while ($arComment = $dbComment->Fetch())
					{
						if ((int)$arComment["AUTHOR_ID"] > 0)
						{
							$arIdToGet[] = $arComment["AUTHOR_ID"];
						}

						$arCommentsAll[] = $arComment;
					}
					if (
						$arParams["NAV_TYPE_NEW"] === 'Y'
						&& $arResult['firstPage']
					)
					{
						$arCommentsAll = array_reverse($arCommentsAll);
					}

					if (!empty($arIdToGet))
					{
						$arResult["userCache"] = CBlogUser::GetUserInfoArray(
							$arIdToGet,
							$arParams["PATH_TO_USER"],
							[
								"AVATAR_SIZE" => ($arParams["AVATAR_SIZE_COMMON"] ?? $arParams["AVATAR_SIZE"]),
								"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"] ?? null,
							]
						);

						foreach ($arResult["userCache"] as $userId => $arUserCache)
						{
							$arTmpUser = array(
								"NAME" => $arUserCache["~NAME"],
								"LAST_NAME" => $arUserCache["~LAST_NAME"],
								"SECOND_NAME" => $arUserCache["~SECOND_NAME"],
								"LOGIN" => $arUserCache["~LOGIN"],
								"NAME_LIST_FORMATTED" => "",
							);
							$arResult["userCache"][$userId]["NAME_FORMATED"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] !== 'N'));
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
						$arConvertParams["pathToUserEntityId"] = (int)$arParams["LOG_ID"];
					}

					$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arParams["bPublicPage"]));
					$p->LAZYLOAD = (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] === "Y" ? "Y" : "N");
					$p->bMobile = (isset($arParams["MOBILE"]) && $arParams["MOBILE"] === "Y");

					if ($p->bMobile)
					{
						$arConvertParams["imageWidth"] = 275;
						$arConvertParams["imageHeight"] = 416;
					}

					$i = 0;
					if (!empty($arCommentsAll[$i]))
					{
						$arComment = $arCommentsAll[$i];

						$bHasImg = false;
						if ($arPost["HAS_COMMENT_IMAGES"] !== "N")
						{
							$res = CBlogImage::GetList(array("ID"=>"ASC"), array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arPost['BLOG_ID'], "IS_COMMENT" => "Y"), false, false, Array("ID", "FILE_ID", "POST_ID", "BLOG_ID", "USER_ID", "TITLE", "COMMENT_ID", "IS_COMMENT"));
							while ($aImg = $res->Fetch())
							{
								$bHasImg = true;
								$arImages[$aImg['ID']] = $aImg['FILE_ID'];
								if ($arResult["allowImageUpload"])
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
									$aImgNew["fileName"] = mb_substr($aImgNew["src"], mb_strrpos($aImgNew["src"], "/") + 1);

									$resizedImageData = CFile::ResizeImageGet(
										$aImg['FILE_ID'],
										[
											'width' => $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'],
											'height' => $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'],
										],
										BX_RESIZE_IMAGE_EXACT,
										true
									);

									$resizedWidth = (int)$resizedImageData['width'];
									$resizedHeight = (int)$resizedImageData['height'];

									if (
										(int)$resizedImageData['width'] > $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL']
										|| (int)$resizedImageData['height'] > $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL']
									)
									{
										if ((int)$resizedImageData['width'] > (int)$resizedImageData['height'])
										{
											$coeff = $resizedImageData['width'] / $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'];
											$resizedWidth = $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'];
											$resizedHeight = ($resizedImageData['height'] / $coeff);
										}
										else
										{
											$coeff = $resizedImageData['height'] / $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'];
											$resizedHeight = $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'];
											$resizedWidth = (int)($resizedImageData['width'] / $coeff);
										}
									}

									$aImgNew['resizedWidth'] = $resizedWidth;
									$aImgNew['resizedHeight'] = $resizedHeight;

									$arResult["Images"][$aImg['ID']] = $aImgNew;
								}

								$arResult["arImages"][$aImg["COMMENT_ID"]][$aImg['ID']] = Array(
									"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$aImg['ID']."&width=".$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"]."&height=".$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"]."&type=square",
									"full" => (
										$arParams["MOBILE"] === "Y"
											? SITE_DIR."mobile/log/blog_image.php?bfid=".$aImg['ID']."&fid=".$aImg['FILE_ID']."&width=".$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]."&height=".$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]
											: "/bitrix/components/bitrix/blog/show_file.php?fid=".$aImg['ID']."&width=".$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]."&height=".$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]
									),
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
							if ((int)$arComment["AUTHOR_ID"] > 0)
							{
								if ($arComment["AUTHOR_ID"] == $arPost["AUTHOR_ID"])
								{
									$arComment["AuthorIsPostAuthor"] = "Y";
								}

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
							unset($arComment['AUTHOR_NAME'], $arComment['AUTHOR_EMAIL']);

							$bHasProps = false;
							$urlPreviewText = false;

							if (
								!empty($arParams["COMMENT_PROPERTY"])
								&& $arComment["HAS_PROPS"] !== "N"
							)
							{
								$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_COMMENT", $arComment["ID"], LANGUAGE_ID);

								if (!empty($arPostFields))
								{
									foreach ($arPostFields as $FIELD_NAME => $arPostField)
									{
										if (!in_array($FIELD_NAME, $arParams['COMMENT_PROPERTY'], true))
										{
											continue;
										}

										if (!empty($arPostField["VALUE"]))
										{
											$bHasProps = true;
										}

										if (
											$FIELD_NAME === "UF_BLOG_COMM_URL_PRV"
											&& array_key_exists("VALUE", $arPostField)
											&& (int)$arPostField["VALUE"] > 0
										)
										{
											$arCss = $APPLICATION->sPath2css;
											$arJs = $APPLICATION->arHeadScripts;

											$urlPreviewText = ComponentHelper::getUrlPreviewContent($arPostField, array(
												"LAZYLOAD" => $arParams["LAZYLOAD"],
												"MOBILE" => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] === "Y" ? "Y" : "N"),
												"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
												"PATH_TO_USER" => $arParams["~PATH_TO_USER"]
											));

											$arResult["Assets"]["CSS"] = array_merge($arResult["Assets"]["CSS"], array_diff($APPLICATION->sPath2css, $arCss));
											$arResult["Assets"]["JS"] = array_merge($arResult["Assets"]["JS"], array_diff($APPLICATION->arHeadScripts, $arJs));
											$arComment["COMMENT_PROPERTIES"]["HIDDEN_DATA"][$FIELD_NAME] = $arPostField;
										}
										else
										{
											$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
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

							if (
								!empty($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"])
								&& is_array($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"])
							)
							{
								$arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]["~VALUE"] = $arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]["VALUE"];
							}

							if ($commentAuxProvider = CommentAux\Base::findProvider(
								array_merge($arComment, (!empty($arParams["LOG_ID"]) ? array('PATH_ENTITY_TYPE' => 'LOG_ENTRY', 'PATH_ENTITY_ID' => (int)$arParams["LOG_ID"]) : array())),
								array(
									"mobile" => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] === "Y"),
									"bPublicPage" => $arParams["bPublicPage"],
									"cache" => true
								)
							))
							{
								$arComment["TextFormated"] = $commentAuxProvider->getText();
								if (!empty($urlPreviewText))
								{
									$arComment["TextFormated"] .= $urlPreviewText;
								}
								$arComment["AuxType"] = $commentAuxProvider->getType();
							}
							else
							{
								$arAllow = [
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
									"VIDEO" => "Y",
									"SHORT_ANCHOR" => "Y"
								];
								if (
									$arParams['ALLOW_VIDEO'] !== 'Y'
									|| Option::get('blog', 'allow_video', 'Y') !== 'Y'
								)
								{
									$arAllow["VIDEO"] = "N";
								}

								if (
									($arParams["NO_URL_IN_COMMENTS"] ?? null) === "L"
									|| (
										(int)$arComment["AUTHOR_ID"] <= 0
										&& $arParams["NO_URL_IN_COMMENTS"] === "A"
									)
								)
								{
									$arAllow["CUT_ANCHOR"] = "Y";
								}

								if (
									($arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] ?? '')=== "Y"
									&& $arAllow["CUT_ANCHOR"] !== "Y"
									&& (int) $arComment["AUTHOR_ID"] > 0
								)
								{
									$authorityRatingId = CRatings::GetAuthorityRating();
									$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComment["AUTHOR_ID"]);
									if ($arRatingResult["CURRENT_VALUE"] < $arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
									{
										$arAllow["CUT_ANCHOR"] = "Y";
									}
								}

								if (
									isset($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"])
									&& is_array($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"])
								)
								{
									$p->arUserfields = array("UF_BLOG_COMMENT_FILE" => array_merge($arComment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"], array("TAG" => "DOCUMENT ID")));
								}

								$arComment["TextFormated"] = $p->convert($arComment["POST_TEXT"], false, $arImages, $arAllow, $arConvertParams);

								if (!empty($urlPreviewText))
								{
									$arComment["TextFormated"] .= $urlPreviewText;
								}
								$arComment["showedImages"] = $p->showedImages;
								if (!empty($p->showedImages))
								{
									foreach ($p->showedImages as $val)
									{
										if (!empty($arResult["arImages"][$arComment["ID"]][$val]))
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
							if (!empty($arParams['DATE_TIME_FORMAT_S']) && ($arParams['DATE_TIME_FORMAT_S'] === 'j F Y G:i' || $arParams['DATE_TIME_FORMAT_S'] === 'j F Y g:i a'))
							{
								$arComment["DateFormated"] = ltrim($arComment["DateFormated"], '0');
								$arComment["DATE_CREATE_DATE"] = ltrim($arComment["DATE_CREATE_DATE"], '0');
								$curYear = date('Y');
								$arComment["DateFormated"] = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $arComment["DateFormated"]);
								$arComment["DATE_CREATE_DATE"] = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $arComment["DATE_CREATE_DATE"]);
							}
							if ($arParams["MOBILE"] === "Y")
							{
								$timestamp = MakeTimeStamp($arComment["DATE_CREATE"]);
								$arComment["DATE_CREATE_TIME"] = FormatDate(Loc::getMessage('SONET_SBPC_MOBILE_FORMAT_TIME'), $timestamp);
							}
							else
							{
								$arComment["DATE_CREATE_TIME"] = FormatDateFromDB(
									$arComment["DATE_CREATE"],
									(
									mb_strpos($arParams["DATE_TIME_FORMAT_S"], 'a') !== false
										|| (
											$arParams["DATE_TIME_FORMAT_S"] === 'FULL'
											&& IsAmPmMode()
										) !== false
											? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'G:MI TT': 'G:MI T')
											: 'GG:MI'
									)
								);
							}

							$arResult["CommentsResult"][] = $arComment;
							$arResult["IDS"][] = $arComment["ID"];

							$arFieldsHave = array();
							if ($arComment["HAS_PROPS"] == "")
							{
								$arFieldsHave["HAS_PROPS"] = ($bHasProps ? "Y" : "N");
							}

							if (!empty($arFieldsHave))
							{
								CBlogComment::Update($arComment["ID"], $arFieldsHave, false);
							}
							$i++;
						}
						while (
							$i < count($arCommentsAll)
							&& ($arComment = $arCommentsAll[$i])
						);
					}
					unset($arResult["MESSAGE"], $arResult["ERROR_MESSAGE"]);

					if ($arParams["CACHE_TIME"] > 0)
					{
						if (defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->EndTagCache();
						}
						$cache->EndDataCache(array(
							"arResult" => $arResult
						));
					}
				}
			}

			$arResult["MESSAGE"] = $tmp["MESSAGE"];
			$arResult["ERROR_MESSAGE"] = $tmp["ERROR_MESSAGE"];
		}

		$arResult["commentUrl"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));
		$arResult["commentUrl"] .= (mb_strpos($arResult["commentUrl"], "?") !== false ? "&" : "?").$arParams["COMMENT_ID_VAR"]."=#comment_id###comment_id#";

		if ($arResult["use_captcha"])
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$cpt = new CCaptcha();
			$captchaPass = Option::get('main', 'captcha_password');
			if ($captchaPass === '')
			{
				$captchaPass = \Bitrix\Main\Security\Random::getString(10);
				Option::set('main', 'captcha_password', $captchaPass);
			}
			$cpt->SetCodeCrypt($captchaPass);
			$arResult["CaptchaCode"] = htmlspecialcharsbx($cpt->GetCodeCrypt());
		}

		if (
			isset($arResult["CommentsResult"])
			&& is_array($arResult["CommentsResult"])
		)
		{
			$arResult["newCount"] = 0;
			$arResult["newCountWOMark"] = 0;

			$arConvertParserParams = Array(
				"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
				"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
				"pathToUser" => $arParams["PATH_TO_USER"],
			);

			$handlerManager = new CommentAux\HandlerManager();
			$newFound = false;

			$arPost['NUM_COMMENTS_ALL'] = $this->getAllCommentsCount([
				'permissions' => $arResult['Perm'],
				'currentUserId' => $currentUserId,
				'cacheTime' => $arParams['CACHE_TIME'],
			]);

			foreach ($arResult["CommentsResult"] as $k1 => $v1)
			{
				if (
					$commentUrlId > 0
					&& $commentUrlId === (int)$v1['ID']
					&& (int)$v1['AUTHOR_ID'] === $currentUserId
					&& $v1['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_READY
				)
				{
					$arResult["MESSAGE"] = Loc::getMessage('B_B_PC_HIDDEN_POSTED');
				}

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
							'mobile' => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] === "Y"),
							'bPublicPage' => (isset($arParams["bPublicPage"]) && $arParams["bPublicPage"]),
							'cache' => false
						));
						$arResult["CommentsResult"][$k1]["TextFormated"]  = $handler->getText();
					}
				}
				elseif (
					!empty($v1["SHARE_DEST"])
					&& !empty($arParams["POST_DATA"]["SPERM_HIDDEN"])
				) // check for old shares
				{
					$dest = explode(",", $v1["SHARE_DEST"]);
					if (!empty($dest))
					{
						$bDestCut = false;
						foreach ($dest as $destId)
						{
							if (in_array($destId, $arParams["POST_DATA"]["SPERM_HIDDEN"]))
							{
								$bDestCut = true;
								break;
							}
						}

						if (
							$bDestCut
							&& $handler = CommentAux\Base::init(CommentAux\Share::getType(), [
								'destinationList' => $dest,
								'hiddenDestinationList' => $arParams["POST_DATA"]["SPERM_HIDDEN"],
							],
							[
								'mobile' => (isset($arParams["MOBILE"]) && $arParams["MOBILE"] === "Y"),
								'cache' => false,
							])
						)
						{
							$arResult["CommentsResult"][$k1]["TextFormated"] = $handler->getText();
						}
					}
				}

				$bAuthor = (
					(int)$v1["AUTHOR_ID"] > 0
					&& (int)$v1["AUTHOR_ID"] === $currentUserId
				);

				if (
					($arResult["bIntranetInstalled"] && $bAuthor)
					|| ($arResult["Perm"] >= Permissions::FULL && !$arResult["bIntranetInstalled"])
					|| CSocNetUser::IsCurrentUserModuleAdmin()
					|| CMain::getGroupRight('blog') >= 'W'
				)
				{
					$arResult["CommentsResult"][$k1]["CAN_DELETE"] = "Y";
					$arResult["CommentsResult"][$k1]["CAN_EDIT"] = "Y";
				}

				if (
					$bAuthor
					&& $arPost["AUTHOR_ID"] != $v1["AUTHOR_ID"]
					&& $v1["SHARE_DEST"] <> ''
				) // user can't delete his own share from other author post
				{
					$arResult["CommentsResult"][$k1]["CAN_DELETE"] = "N";
					$arResult["CommentsResult"][$k1]["CAN_EDIT"] = "N";
				}

				if (!$arResult["bIntranetInstalled"] & $arResult["Perm"] < Permissions::FULL && !empty($arResult["CommentsResult"][$k1-1]))
				{
					$arResult["CommentsResult"][$k1-1]["CAN_EDIT"] = "N";
				}

				if ((int) ($arParams["CREATED_BY_ID"] ?? null) > 0)
				{
					if ($v1["AUTHOR_ID"] != $arParams["CREATED_BY_ID"])
					{
						unset($arResult["CommentsResult"][$k1], $arResult["IDS"][$k1]);
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

					if (
						(int)$arParams["LAST_LOG_TS"] > 0
						&& !empty($arResult["CommentsResult"][$k1])
						&& $arParams["LAST_LOG_TS"] < $comment_date_create_ts
					)
					{
						if ($arParams["MARK_NEW_COMMENTS"] === "Y")
						{
							$arResult["newCount"]++;
						}

						$new = ((int)$v1["AUTHOR_ID"] !== $currentUserId);

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
							$arResult["newCountWOMark"]++;
							if ($arParams["MARK_NEW_COMMENTS"] === "Y")
							{
								$arResult["CommentsResult"][$k1]["NEW"] = "Y";
							}
						}
					}
				}

				if (isset($arResult["CommentsResult"][$k1]))
				{
					if ($arResult["Perm"] >= Permissions::MODERATE)
					{
						if ($v1["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_PUBLISH)
						{
							$arResult["CommentsResult"][$k1]["CAN_HIDE"] = "Y";
						}
						else
						{
							$arResult["CommentsResult"][$k1]["CAN_SHOW"] = "Y";
						}
					}
					elseif (
						(
							$currentUserId <= 0 // anonymous
							|| (int)$v1["AUTHOR_ID"] !== $currentUserId
						)
						&& $v1["PUBLISH_STATUS"] !== BLOG_PUBLISH_STATUS_PUBLISH
					)
					{
						unset($arResult["CommentsResult"][$k1], $arResult["IDS"][$k1]);
					}
				}
			}

			if ($arResult["newCount"] < $arParams["PAGE_SIZE_MIN"]) // 3
			{
				$arResult["newCount"] = $arParams["PAGE_SIZE_MIN"];
			}
			$arResult["~newCount"] = $arResult["newCount"];
			if ($commentUrlId > 0)
			{
				$arResult["newCount"] = count($arResult["CommentsResult"]);
			}
			if ($arParams["SHOW_RATING"] === "Y" && !empty($arResult["IDS"]))
			{
				$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_COMMENT', $arResult["IDS"]);
			}
		}

		$arResult["urlToPost"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST_CURRENT"]), array("post_id" => "#source_post_id#", "user_id" => $arPost["AUTHOR_ID"]));
		$arResult["urlToPost"] .= (mb_strpos($arResult["urlToPost"], "?") === false ? "?" : "&");

		$arResult["urlToDelete"] = $arResult["urlToPost"]."delete_comment_id=#comment_id#&comment_post_id=#post_id#&".bitrix_sessid_get();
		$arResult["urlToHide"] = $arResult["urlToPost"]."hide_comment_id=#comment_id#&comment_post_id=#post_id#&".bitrix_sessid_get();
		$arResult["urlToShow"] = $arResult["urlToPost"]."somment_id=#comment_id#&comment_post_id=#post_id#&".bitrix_sessid_get();
		$arResult["urlToAnswer"] = $arResult["urlToPost"]."answer_user_id=#user_id#&answer_post_id=#post_id#&".bitrix_sessid_get();

		$arResult["urlToMore"] = $arResult["urlToPost"]."last_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y&LAST_LOG_TS=#LAST_LOG_TS#";
		$arResult["urlToNew"] = $arResult["urlToPost"]."new_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y&show_new_ans=Y";

		$this->prepareData($arResult);

		include_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/component_adit.php");
	}

	$arResult["Post"] = $arPost;

	if ($this->isAjaxRequest())
	{
		if (!empty($arResult['COMMENT_ERROR']))
		{
			throw new \Bitrix\Main\InvalidOperationException($arResult['COMMENT_ERROR']);
		}
		elseif (!empty($arResult['ERROR_MESSAGE']))
		{
			throw new \Bitrix\Main\InvalidOperationException($arResult['ERROR_MESSAGE']);
		}
	}

	$this->IncludeComponentTemplate();

	return [
		"CanUserComment" => $arResult["CanUserComment"],
		"newCountWOMark" => $arResult["newCountWOMark"] ?? 0
	];
}

if ($this->isAjaxRequest())
{
	throw new \Bitrix\Main\AccessDeniedException(Loc::getMessage('B_B_PC_NO_RIGHTS'));
}
