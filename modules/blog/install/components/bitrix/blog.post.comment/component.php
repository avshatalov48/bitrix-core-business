<?
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

if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
if (!array_key_exists("PATH_TO_MESSAGES_CHAT", $arParams))
	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
if (!array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";
$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

if($arParams["SIMPLE_COMMENT"] == "Y")
	$simpleComment = true;
else
	$simpleComment = false;

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
// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["EDITOR_RESIZABLE"] = $arParams["EDITOR_RESIZABLE"] !== "N";
$arParams["EDITOR_CODE_DEFAULT"] = $arParams["EDITOR_CODE_DEFAULT"] === "Y";
$arParams["EDITOR_DEFAULT_HEIGHT"] = intVal($arParams["EDITOR_DEFAULT_HEIGHT"]);
if(IntVal($arParams["EDITOR_DEFAULT_HEIGHT"]) <= 0)
	$arParams["EDITOR_DEFAULT_HEIGHT"] = 200;
$arParams["ALLOW_VIDEO"] = ($arParams["ALLOW_VIDEO"] == "Y" ? "Y" : "N");
if(COption::GetOptionString("blog","allow_video", "Y") == "Y" && $arParams["ALLOW_VIDEO"] == "Y")
	$arResult["allowVideo"] = true;

if($arParams["ALLOW_IMAGE_UPLOAD"] == "A" || ($arParams["ALLOW_IMAGE_UPLOAD"] == "R" && $USER->IsAuthorized()))
	$arResult["allowImageUpload"] = true;
$arResult["Images"] = Array();

if($arResult["allowImageUpload"])
{
	if(!is_array($arParams["COMMENT_PROPERTY"]))
		$arParams["COMMENT_PROPERTY"] = Array("UF_BLOG_COMMENT_DOC");
	else
		$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_DOC";
}

$arResult["userID"] = $user_id = $USER->GetID();
$arResult["canModerate"] = false;
$arParams["AJAX_POST"] = ($arParams["AJAX_POST"] == "Y" ? "Y" : "N");

$arResult["ajax_comment"] = 0;

$blogModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("blog");
$arParams["SHOW_SPAM"] = ($arParams["SHOW_SPAM"] == "Y" && $blogModulePermissions >= "W" ? "Y" : "N");

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

$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]);
$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);

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
if(IntVal($arParams["ID"])>0)
	$arResult["Perm"] = CBlogPost::GetBlogUserCommentPerms($arParams["ID"], $user_id);
else
	$arResult["Perm"] = CBlog::GetBlogUserCommentPerms($arBlog["ID"], $user_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
{
	CBlogImage::AddImageResizeHandler(array("width" => 400, "height" => 400));
}

if(((!empty($arPost) && $arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH && $arPost["ENABLE_COMMENTS"] == "Y") || $simpleComment) && (($arBlog["ACTIVE"] == "Y" && $arGroup["SITE_ID"] == SITE_ID) || $simpleComment) )
{
	$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
	$arResult["Post"] = $arPost;

	if($arPost["BLOG_ID"] == $arBlog["ID"] || $simpleComment)
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
				if($arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
				{
					if(check_bitrix_sessid())
					{
						if(CBlogComment::Delete(IntVal($_GET["delete_comment_id"])))
						{
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/".$arComment["POST_ID"]."/C/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
							BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");

							if($arParams["AJAX_POST"] != "Y")
								LocalRedirect($APPLICATION->GetCurPageParam("delete_comment_id=".IntVal($_GET["delete_comment_id"])."&success=Y", Array("delete_comment_id", "sessid", "success", "commentId", "hide_comment_id", "show_comment_id")))."#comments";
							else
							{
								$arResult["ajax_comment"] = IntVal($_GET["delete_comment_id"]);
								$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_DELED");
							}
						}
					}
					else
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
				if($arParams["AJAX_POST"]!= "Y" || ($arParams["AJAX_POST"] == "Y" && IntVal($arResult["ajax_comment"]) <= 0))
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
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/".$arComment["POST_ID"]."/C/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
							BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");

							if($arParams["AJAX_POST"] != "Y")
								LocalRedirect($APPLICATION->GetCurPageParam($arParams["COMMENT_ID_VAR"]."=".$arComment["ID"], Array("show_comment_id", "sessid", "success", $arParams["COMMENT_ID_VAR"], "hide_comment_id", "delete_comment_id")))."#".$arComment["ID"];
							else
								$arResult["ajax_comment"] = $arComment["ID"];
						}
					}
					else
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
			if($arParams["AJAX_POST"]!= "Y" || ($arParams["AJAX_POST"] == "Y" && IntVal($arResult["ajax_comment"]) <= 0))
				$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
		}
		elseif(IntVal($_GET["hide_comment_id"])>0)
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
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arComment["POST_ID"]."/");
							BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/".$arComment["POST_ID"]."/C/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
							BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");

							if($arParams["AJAX_POST"] != "Y")
								LocalRedirect($APPLICATION->GetCurPageParam($arParams["COMMENT_ID_VAR"]."=".$arComment["ID"], Array("hide_comment_id", "sessid", "success", $arParams["COMMENT_ID_VAR"], "delete_comment_id", "show_comment_id")))."#".$arComment["ID"];
							else
								$arResult["ajax_comment"] = $arComment["ID"];
						}
					}
					else
						$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
				}
			}
			if($arParams["AJAX_POST"]!= "Y" || ($arParams["AJAX_POST"] == "Y" && IntVal($arResult["ajax_comment"]) <= 0))
				$arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_HIDE");
		}
		elseif(IntVal($_GET["hidden_add_comment_id"])>0)
		{
			$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
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
				$arResult["User"]["NAME"] = CBlogUser::GetUserNameEx($arResult["arUser"],$arResult["BlogUser"], $arParams);
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

						if ($_POST["blog_upload_image"] == "Y")
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
										$arResult["ERROR_MESSAGE"] = $ex->GetString();
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
										"DATE_CREATE" => ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL"),
										"AUTHOR_IP" => $UserIP[0],
										"AUTHOR_IP1" => $UserIP[1],
										"URL" => $arBlog["URL"],
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
								$dbDuplComment = CBlogComment::GetList(array("ID" => "DESC"), array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $arPost["ID"]), false, array("nTopCount" => 1), array("ID", "POST_ID", "BLOG_ID", "AUTHOR_ID", "POST_TEXT"));
								if($arDuplComment = $dbDuplComment->Fetch())
								{
									if($arDuplComment["POST_ID"] == $arFields["POST_ID"] && $arDuplComment["BLOG_ID"] == $arFields["BLOG_ID"] && IntVal($arDuplComment["AUTHOR_ID"]) == IntVal($arFields["AUTHOR_ID"]) && md5($arDuplComment["POST_TEXT"]) == md5($arFields["POST_TEXT"]))
									{
										$strErrorMessage .= GetMessage("B_B_PC_DUPLICATE_COMMENT");
									}
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

										if ($fileID <= 0 || !is_array($_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]]) || !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]]))
											continue;

										$arFile = CFile::GetFileArray($fileID);
										if (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === null)
										{
											$arImgFields = array(
												"BLOG_ID"	=> $arBlog["ID"],
												"POST_ID"	=> $arPost["ID"],
												"USER_ID"	=> $arResult["UserID"],
												"COMMENT_ID" => 0,
												"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
												"TITLE"		=> $arFile["FILE_NAME"],
												"IMAGE_SIZE"	=> $arFile["FILE_SIZE"],
												"FILE_ID" => $fileID,
												"IS_COMMENT" => "Y",
												"URL" => $arBlog["URL"],
												"USER_ID" => IntVal($user_id),
												"IMAGE_SIZE_CHECK" => "N",
											);
											$imgID = CBlogImage::Add($arImgFields);
											if (intval($imgID) <= 0)
											{
												$GLOBALS["APPLICATION"]->ThrowException("Error Adding file by CBlogImage::Add");
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
									$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_COMMENT", $arFields);

								$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

								$arFields["PATH"] = $commentUrl;
								if(strpos($arFields["PATH"], "?") !== false)
									$arFields["PATH"] .= "&";
								else
									$arFields["PATH"] .= "?";
								$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=#comment_id###comment_id#";

								if($commmentId = CBlogComment::Add($arFields))
								{
									$DB->Query("UPDATE b_blog_image SET COMMENT_ID=".IntVal($commmentId)." WHERE BLOG_ID=".IntVal($arBlog["ID"])." AND POST_ID=".IntVal($arPost["ID"])." AND IS_COMMENT = 'Y' AND (COMMENT_ID = 0 OR COMMENT_ID is null)", true);

									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arPost["ID"]."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arPost["ID"]."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
									BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/".$arPost["POST_ID"]."/C/");
									BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
									BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
									BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");

									$images = Array();
									$res = CBlogImage::GetList(array("ID"=>"ASC"), array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => $commmentId));
									while($aImg = $res->Fetch())
										$images[$aImg["ID"]] = $aImg["FILE_ID"];

									$AuthorName = "";
									if(IntVal($user_id)>0)
										$AuthorName = CBlogUser::GetUserNameEx($arResult["arUser"],$arResult["BlogUser"], $arParams);

									$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
									$arParserParams = Array(
										"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
										"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
									);

									$text4mail = $parserBlog->convert4mail($_POST['comment'], $images);
									$serverName = ((defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
									if (strlen($serverName) <=0)
										$serverName = $_SERVER["SERVER_NAME"];

									if(strpos($commentUrl, "?") !== false)
										$commentUrl .= "&";
									else
										$commentUrl .= "?";
									if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
										$commentAddedUrl = $commentUrl.$arParams["COMMENT_ID_VAR"]."=".$commmentId."&hidden_add_comment_id=".$commmentId;
									$commentUrl .= $arParams["COMMENT_ID_VAR"]."=".$commmentId."#".$commmentId;

									if (!preg_match("/^[a-z]+:\\/\\//", $commentUrl))
										$commentUrl = ((CMain::IsHTTPS()) ? 'https://' : 'http://').$serverName.$commentUrl;

									if(strlen($AuthorName)<=0)
										$AuthorName = $arFields["AUTHOR_NAME"];

									$arMailFields = array(
											"BLOG_ID"	=> $arBlog['ID'],
											"BLOG_NAME"	=> $arBlog['~NAME'],
											"BLOG_URL"	=> $arBlog['~URL'],
											"MESSAGE_TITLE" => $arPost['~TITLE'],
											"COMMENT_TITLE" => $_POST['subject'],
											"COMMENT_TEXT" => $text4mail,
											"COMMENT_DATE" => ConvertTimeStamp(false, "FULL"),
											"COMMENT_PATH" => $commentUrl,
											"AUTHOR"	 => $AuthorName,
											"EMAIL_FROM"	 => COption::GetOptionString("main","email_from", "nobody@nobody.com"),
									);
									if(!$bUseTitle)
										unset($arMailFields["COMMENT_TITLE"]);

									if ($arBlog['EMAIL_NOTIFY']=='Y' && $user_id != $arPost['AUTHOR_ID']) // comment author is not original post author
									{
										$res = CUser::GetByID($arPost['AUTHOR_ID']);
										if($arOwner = $res->GetNext())
										{
											$arMailFields["EMAIL_TO"] = $arOwner['EMAIL'];

											CEvent::Send(
												($bUseTitle) ? "NEW_BLOG_COMMENT" : "NEW_BLOG_COMMENT_WITHOUT_TITLE",
												SITE_ID,
												$arMailFields
											);
										}

										if($arPost["AUTHOR_ID"] != $arBlog["OWNER_ID"] && IntVal($arBlog["OWNER_ID"]) > 0)
										{
											$res = CUser::GetByID($arBlog["OWNER_ID"]);
											if($arOwnerBlog = $res->GetNext())
											{
												$arMailFields["EMAIL_TO"] = $arOwnerBlog['EMAIL'];

												CEvent::Send(
													($bUseTitle) ? "NEW_BLOG_COMMENT" : "NEW_BLOG_COMMENT_WITHOUT_TITLE",
													SITE_ID,
													$arMailFields
												);
											}
										}
									}

									if($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || strlen($arFields["PUBLISH_STATUS"]) <= 0)
									{
										if($arFields["PARENT_ID"] > 0) // In case the is an comment before - we'll notice author
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
													$text4mail1 = $parserBlog->convert4mail($arPrev["~POST_TEXT"], $images);
													$arMailFields["PARENT_COMMENT_TEXT"] = $text4mail1;
													$arMailFields["PARENT_COMMENT_TITLE"] = $arPrev["~TITLE"];
													$arMailFields["PARENT_COMMENT_DATE"] = $arPrev["DATE_CREATE"];

													CEvent::Send(
														($bUseTitle) ? "NEW_BLOG_COMMENT2COMMENT" : "NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE",
														SITE_ID,
														$arMailFields
													);
												}
											}
										}
									}

									if($arParams["AJAX_POST"] != "Y")
									{
										if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
											LocalRedirect($commentAddedUrl);
										else
											LocalRedirect($commentUrl);
									}
									else
									{
										if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
											$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
										$arResult["ajax_comment"] = $commmentId;
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
							elseif($arOldComment["AUTHOR_ID"] == $user_id || $blogModulePermissions >= "W")
							{
								$arFields = Array(
										"TITLE" => $_POST["subject"],
										"POST_TEXT" => $_POST["comment"],
										"URL" => $arBlog["URL"],
									);
								if(!$bUseTitle)
									unset($arFields["TITLE"]);
								if($arResult["Perm"] == BLOG_PERMS_PREMODERATE)
									$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;

								$fieldName = 'UF_BLOG_COMMENT_DOC';
								if (isset($GLOBALS[$fieldName]) && is_array($GLOBALS[$fieldName]))
								{
									$arAttachedFiles = array();
									foreach($GLOBALS[$fieldName] as $fileID)
									{
										$fileID = intval($fileID);
										if ($fileID <= 0 || !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]]))
											continue;

										$arFile = CFile::GetFileArray($fileID);
										if (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === null)
										{
											$arImgFields = array(
												"BLOG_ID"	=> $arBlog["ID"],
												"POST_ID"	=> $arPost["ID"],
												"USER_ID"	=> $arResult["UserID"],
												"COMMENT_ID" => $commentID,
												"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
												"TITLE"		=> $arFile["FILE_NAME"],
												"IMAGE_SIZE"	=> $arFile["FILE_SIZE"],
												"FILE_ID" => $fileID,
												"IS_COMMENT" => "Y",
												"URL" => $arBlog["URL"],
												"USER_ID" => IntVal($user_id),
											);
											$imgID = CBlogImage::Add($arImgFields);
											if (intval($imgID) <= 0)
											{
												$GLOBALS["APPLICATION"]->ThrowException("Error Adding file by CBlogImage::Add");
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
									$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_COMMENT", $arFields);

								$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

								$arFields["PATH"] = $commentUrl;
								if(strpos($arFields["PATH"], "?") !== false)
									$arFields["PATH"] .= "&";
								else
									$arFields["PATH"] .= "?";
								$arFields["PATH"] .= $arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;

								$dbComment = CBlogComment::GetList(array(), Array("POST_ID" => $arPost["ID"], "BLOG_ID" => $arBlog["ID"], "PARENT_ID" => $commentID));
								if($dbComment->Fetch() && $blogModulePermissions < "W")
								{
									$arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_EDIT_ALREADY_COMMENTED");
								}
								else
								{
									if($commentID = CBlogComment::Update($commentID, $arFields))
									{
										BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arPost["ID"]."/");
										BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arPost["ID"]."/");
										BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
										BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
										BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/".$arPost["POST_ID"]."/C/");
										BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
										BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
										BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");

										$images = Array();
										
										$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "COMMENT_ID" => $commentID, "IS_COMMENT" => "Y"));
										while($aImg = $res->Fetch())
											$images[$aImg["ID"]] = $aImg["FILE_ID"];

										$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
										if(strpos($commentUrl, "?") !== false)
											$commentUrl .= "&";
										else
											$commentUrl .= "?";

										if($arParams["AJAX_POST"] != "Y")
										{
											if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
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
											if(strlen($arFields["PUBLISH_STATUS"]) > 0 && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
												$arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_EDITED");
											$arResult["ajax_comment"] = $commentID;
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
				if(check_bitrix_sessid())
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

					$images = Array();
					preg_match_all("/\[img([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/ies".BX_UTF_PCRE_MODIFIER, $_POST["comment"], $matches);
					$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "USER_ID" => IntVal($user_id), "IS_COMMENT" => "Y"));
					while($aImg = $res->Fetch())
					{
						if(in_array($aImg["ID"], $matches[2]))
						{
							$images[$aImg["ID"]] = $aImg["FILE_ID"];
						}
					}

					$_POST["commentFormated"] = $p->convert($_POST["comment"], false, $images, $arAllow, $arParserParams);
				}
				else
					$_POST["show_preview"] = "N";
			}

			/////////////////////////////////////////////////////////////////////////////////////
			if($USER->IsAdmin())
				$arResult["ShowIP"] = "Y";
			else
				$arResult["ShowIP"] = COption::GetOptionString("blog", "show_ip", "Y");

			$cache = new CPHPCache;
			$cache_id = "blog_comment_".serialize($arParams)."_".$arResult["Perm"]."_".$USER->IsAuthorized();
			if(($tzOffset = CTimeZone::GetOffset()) <> 0)
				$cache_id .= "_".$tzOffset;
			$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arParams["ID"]."/";

			$tmp = Array();
			$tmp["MESSAGE"] = $arResult["MESSAGE"];
			$tmp["ERROR_MESSAGE"] = $arResult["ERROR_MESSAGE"];
			if((strlen($arResult["COMMENT_ERROR"]) > 0 || strlen($arResult["ERROR_MESSAGE"]) > 0) && $arParams["AJAX_POST"] == "Y")
			{
				$arResult["is_ajax_post"] = "Y";
			}
			else
			{
				if($arParams["AJAX_POST"] == "Y" && IntVal($arResult["ajax_comment"]) > 0)
				{
					$arResult["is_ajax_post"] = "Y";
					$cache_id .= $arResult["ajax_comment"];
					$arParams["CACHE_TIME"] = 0;
				}

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

					$arResult["Comments"] = array();
					$arResult["CommentsResult"] = Array();
					$arResult["IDS"] = Array();
					$arSelectFields = array("ID", "SMILE_TYPE", "TYPING", "IMAGE", "DESCRIPTION", "CLICKABLE", "SORT", "IMAGE_WIDTH", "IMAGE_HEIGHT", "LANG_NAME");
					$arSmiles = array();
					$res = CBlogSmile::GetList(array("SORT"=>"ASC","ID"=>"DESC"), array("SMILE_TYPE"=>"S", "LANG_LID"=>LANGUAGE_ID), false, false, $arSelectFields);
					while ($arr = $res->GetNext())
					{
						list($type)=explode(" ",$arr["TYPING"]);
						$arr["TYPE"]=str_replace("'","\'",$type);
						$arr["TYPE"]=str_replace("\\","\\\\",$arr["TYPE"]);
						$arSmiles[] = $arr;
					}
					$arResult["Smiles"] = $arSmiles;
					if(IntVal($arParams["ID"]) > 0)
					{
						$arOrder = Array("DATE_CREATE" => "ASC", "ID" => "ASC");
						$arFilter = Array("POST_ID" => $arParams["ID"], "BLOG_ID" => $arBlog["ID"]);
						if($arResult["is_ajax_post"] == "Y" && IntVal($arResult["ajax_comment"]) > 0)
						{
							$arFilter["ID"] = $arResult["ajax_comment"];
						}

						$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arBlog['ID'], "IS_COMMENT" => "Y"), false, false, Array("ID", "FILE_ID", "POST_ID", "BLOG_ID", "USER_ID", "TITLE", "COMMENT_ID", "IS_COMMENT"));
						while ($arImage = $res->Fetch())
						{
							$arImages[$arImage['ID']] = $arImage['FILE_ID'];
							$arResult["arImages"][$arImage["COMMENT_ID"]][$arImage['ID']] = Array(
								"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=70&height=70&type=square",
								"full" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=1000&height=1000"
							);
						}

						$arSelectedFields = Array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS");
						$dbComment = CBlogComment::GetList($arOrder, $arFilter, false, false, $arSelectedFields);
						$resComments = Array();

						$arResult["firstLevel"] = "";
						
						$blogUser = new Bitrix\Blog\BlogUser($arParams["CACHE_TIME"]);
						$blogUser->setBlogId($arBlog["ID"]);
						$commentsUsers = $blogUser->getUsers(\Bitrix\Blog\BlogUser::getCommentAuthorsIdsByPostId($arPost['ID']));
						
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
										"PUBLISH_STATUS" => $arComment["PUBLISH_STATUS"],
									);
								$arComment["ShowIP"] = $arResult["ShowIP"];
								if(empty($resComments[IntVal($arComment["PARENT_ID"])]))
								{
									$resComments[IntVal($arComment["PARENT_ID"])] = Array();
									if(strlen($arResult["firstLevel"])<=0)
									{
										$arResult["firstLevel"] = IntVal($arComment["PARENT_ID"]);
									}
								}

								if(IntVal($arComment["AUTHOR_ID"])>0)
								{
//
									if(empty($arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]))
									{
										$arUsrTmp = array();
										$arUsrTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComment["AUTHOR_ID"]));
										$arUsrTmp["AuthorName"] = Bitrix\Blog\BlogUser::GetUserNameEx(
											$commentsUsers[$arComment["AUTHOR_ID"]]["arUser"],
											$commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"],
											$arParams
										);
										$arUsrTmp["Blog"] = CBlog::GetByOwnerID(IntVal($arComment["AUTHOR_ID"]), $arParams["GROUP_ID"]);
//										i think it was wrong O_o
//										if($arUsrTmp["AUTHOR_ID"] == $arUsrTmp["AUTHOR_ID"])
										if($arResult["userID"] == $arComment["AUTHOR_ID"])
											$arUsrTmp["AuthorIsPostAuthor"] = "Y";
//
										$arResult["USER_CACHE"][$arComment["AUTHOR_ID"]] = $arUsrTmp;
									}
									
									$arComment["BlogUser"] = $commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"];
									$arComment["arUser"] = $commentsUsers[$arComment["AUTHOR_ID"]]["arUser"];
//									$arComment["AuthorName"] = $commentsUsers[$arComment["AUTHOR_ID"]]["AUTHOR_NAME"];
									$arComment["AVATAR_file"] = $commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"]["AVATAR_file"];
									if ($arComment["AVATAR_file"] !== false)
										$arComment["AVATAR_img"] = $commentsUsers[$arComment["AUTHOR_ID"]]["BlogUser"]["AVATAR_img"]['30_30'];
//									from user cache
									$arComment["Blog"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["Blog"];
									$arComment["urlToAuthor"] = $arResult["USER_CACHE"][$arComment["AUTHOR_ID"]]["urlToAuthor"];
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
									if($arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
									{
										$arComment["urlToHide"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("hide_comment_id=".$arComment["ID"], Array("sessid", "delete_comment_id", "hide_comment_id", "success", "show_comment_id", "commentId")));
									}
									else
									{
										$arComment["urlToShow"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("show_comment_id=".$arComment["ID"], Array("sessid", "delete_comment_id", "show_comment_id", "success", "hide_comment_id", "commentId")));
									}
									if($arResult["Perm"]>=BLOG_PERMS_FULL)
									{
										$arComment["urlToDelete"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("delete_comment_id=".$arComment["ID"], Array("sessid", "delete_comment_id", "success", "hide_comment_id", "show_comment_id", "commentId")));
									}
									if($arParams["SHOW_SPAM"] == "Y")
									{
										if(IntVal($arComment["AUTHOR_ID"]) > 0)
											$arComment["urlToSpam"] = "/bitrix/admin/blog_comment.php?lang=ru&set_filter=Y&filter_author_id=".$arComment["AUTHOR_ID"];
										elseif(strlen($arComment["AUTHOR_IP"]) > 0)
											$arComment["urlToSpam"] = "/bitrix/admin/blog_comment.php?lang=ru&set_filter=Y&filter_author_anonym=Y&filter_author_ip=".$arComment["AUTHOR_IP"];
										else
											$arComment["urlToSpam"] = "/bitrix/admin/blog_comment.php?lang=ru&set_filter=Y&filter_author_anonym=Y&filter_author_email=".$arComment["AUTHOR_EMAIL"];
									}
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

								$arComment["TextFormated"] = $p->convert($arComment["~POST_TEXT"], false, $arImages, $arAllow, $arParserParams);

								$arComment["DateFormated"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arComment["DATE_CREATE"], CSite::GetDateFormat("FULL")));

								if(!empty($p->showedImages))
								{
									foreach($p->showedImages as $val)
									{
										if(!empty($arResult["arImages"][$arComment["ID"]][$val]))
											unset($arResult["arImages"][$arComment["ID"]][$val]);
									}
								}

								$arResult["COMMENT_PROPERTIES"] = array("SHOW" => "N");

								if (!empty($arParams["COMMENT_PROPERTY"]))
								{
									$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_COMMENT", $arComment["ID"], LANGUAGE_ID);

									if (count($arPostFields) > 0)
									{
										foreach ($arPostFields as $FIELD_NAME => $arPostField)
										{
											if (!in_array($FIELD_NAME, $arParams["COMMENT_PROPERTY"]))
												continue;
											$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
											$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
											$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
											$arComment["COMMENT_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
										}
									}
									if (!empty($arComment["COMMENT_PROPERTIES"]["DATA"]))
										$arComment["COMMENT_PROPERTIES"]["SHOW"] = "Y";
								}

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
								$arResult["IDS"][] = $arComment["ID"];
							}
							while($arComment = $dbComment->GetNext());
							$arResult["CommentsResult"] = $resComments;
						}
						if (!empty($arParams["COMMENT_PROPERTY"]))
						{
							$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_COMMENT", 0, LANGUAGE_ID);

							if (count($arParams["COMMENT_PROPERTY"]) > 0)
							{
								foreach ($arPostFields as $FIELD_NAME => $arPostField)
								{
									if (!in_array($FIELD_NAME, $arParams["COMMENT_PROPERTY"]))
										continue;
									$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
									$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
									$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
									$arResult["COMMENT_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
								}
							}
							if (!empty($arResult["COMMENT_PROPERTIES"]["DATA"]))
								$arResult["COMMENT_PROPERTIES"]["SHOW"] = "Y";
						}
					}
					unset($arResult["MESSAGE"]);
					unset($arResult["ERROR_MESSAGE"]);

					if($arResult["allowImageUpload"])
					{
						$arResult["Images"] = Array();
						$res = CBlogImage::GetList(array("ID"=>"ASC"), Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $arPost["ID"], "IS_COMMENT" => "Y"));
						while($aImg = $res->GetNext())
						{
							$aImg["SRC"] = CFile::GetPath($aImg["FILE_ID"]);
							$arResult["Images"][] = $aImg;
						}
					}

					if ($arParams["CACHE_TIME"] > 0)
						$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
				}
				$arResult["MESSAGE"] = $tmp["MESSAGE"];
				$arResult["ERROR_MESSAGE"] = $tmp["ERROR_MESSAGE"];
			}

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



		if(is_array($arResult["CommentsResult"]))
		{
			if($USER->IsAuthorized())
			{
				if(!empty($arPost))
				{
					global $stackCacheManager;
					$cache_id = "blog_comment_view_".$arResult["userID"];
					$stackCacheManager->SetLength($cache_id, 1000);
					$stackCacheManager->SetTTL($cache_id, 60*60*24*365);
					if ($stackCacheManager->Exist($cache_id, "c".$arPost["ID"]))
					{
						$arResult["lastPostView"] = $stackCacheManager->Get($cache_id, "c".$arPost["ID"]);
					}
					$stackCacheManager->Set($cache_id, "c".$arPost["ID"], time()+CTimeZone::GetOffset());
				}
			}

			$bNeedHide = false;
			foreach($arResult["CommentsResult"] as $k1 => $v1)
			{
				foreach($v1 as $k => $v)
				{
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
						if($v["AUTHOR_ID"] == $user_id || $blogModulePermissions >= "W")
							$arResult["Comments"][$v["ID"]]["CAN_EDIT"] = "Y";
					}
					else
					{
						if($blogModulePermissions >= "W")
							$arResult["Comments"][$v["ID"]]["CAN_EDIT"] = "Y";
					}

					if(strlen($arResult["lastPostView"]) > 0 && $arResult["lastPostView"] < MakeTimeStamp($v["DATE_CREATE"]))
						$arResult["Comments"][$v["ID"]]["NEW"] = "Y";
				}
			}
			if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
				$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_COMMENT', $arResult["IDS"]);

			foreach($arResult["Comments"] as $k => $v)
			{
				if($v["SHOW_AS_HIDDEN"] != "Y" && $v["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $v["SHOW_SCREENNED"] != "Y")
				{
					unset($arResult["Comments"][$k]);
					$bNeedHide = true;
				}
			}

			if($bNeedHide)
			{
				foreach($arResult["CommentsResult"][0] as $k => $v)
				{
					if(empty($arResult["Comments"][$v["ID"]]))
					{
						unset($arResult["CommentsResult"][0][$k]);
					}
				}

				$arResult["CommentsResult"][0] = array_values($arResult["CommentsResult"][0]);
			}
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


		$arResult["PAGE_COUNT"] = 0;
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
					function BXBlogSearchParentID($commentID, $arComments)
					{
						if(IntVal($arComments[$commentID]["PARENT_ID"]) > 0)
						{
							return BXBlogSearchParentID($arComments[$commentID]["PARENT_ID"], $arComments);
						}
						else
							return $commentID;
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

				$arResult["AllCommentsResult"] = $arResult["CommentsResult"][0];
				$arResult["PagesComment"] = Array();
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

				for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
				{
					foreach($arResult["AllCommentsResult"] as $k => $v)
					{

						if($i == 1)
						{
							if($k <= ($firstPageCount-1))
								$arResult["PagesComment"][$i][$k] = $v;
						}
						else
						{
							if($k < ($firstPageCount + ($i-1)*$arParams["COMMENTS_COUNT"]) && $k >= ($firstPageCount + ($i-2)*$arParams["COMMENTS_COUNT"]))
								$arResult["PagesComment"][$i][$k] = $v;
						}
					}
				}
				unset($arResult["AllCommentsResult"]);

				$arResult["NEED_NAV"] = "Y";
				$arResult["PAGES"] = Array();
				$arResult["NEW_PAGES"] = Array();

				for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
				{
					if($i == 1)
						$arResult["NEW_PAGES"][$i] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("", Array($arParams["NAV_PAGE_VAR"], "commentID"))."#comments");
					else
						$arResult["NEW_PAGES"][$i] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["NAV_PAGE_VAR"].'='.$i, array($arParams["NAV_PAGE_VAR"], "commentID"))."#comments");
					
					if($i != $arResult["PAGE"])
					{
						if($i == 1)
							$arResult["PAGES"][] = '<a href="'.$link.'">'.$i.'</a>&nbsp;&nbsp;';
						else
							$arResult["PAGES"][] = '<a href="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["NAV_PAGE_VAR"].'='.$i, array($arParams["NAV_PAGE_VAR"], "commentID"))).'#comments">'.$i.'</a>&nbsp;&nbsp;';
					}
					else
						$arResult["PAGES"][] = "<b>".$i."</b>&nbsp;&nbsp;";
				}


			}
		}

		$this->IncludeComponentTemplate();
	}
}
?>