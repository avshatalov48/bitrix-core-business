<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Blog\BlogUser;
Loc::loadMessages(__FILE__);

class CBlogPostCommentEdit extends CBitrixComponent
{
	const FILE_CONTROL_ID_PREFIX = 'blogcommentfiles';
	const POST_COMMENT_FORM_PREFIX = 'POST_BLOG_COMMENT_FORM';
	const POST_COMMENT_MESSAGE = "POST_COMMENT_MESSAGE";
	const AVATAR_SIZE_COMMENT = 100;
	protected $commentUrlID;

	public function onPrepareComponentParams($arParams)
	{
		if (!CModule::IncludeModule("blog"))
		{
			ShowError(Loc::GetMessage("BLOG_MODULE_NOT_INSTALL"));
			return;
		}

		global $APPLICATION, $DB, $USER;

		$arParams["ID"] = trim($arParams["ID"]);
		$arParams["ID_BY_CODE"] = false;
		if(!is_numeric($arParams["ID"]) || mb_strlen(intval($arParams["ID"])) != mb_strlen($arParams["ID"]))
		{
			$arParams["ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["ID"]));
			$arParams["ID_BY_CODE"] = true;
		}
		else
			$arParams["ID"] = intval($arParams["ID"]);

		$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

		if (!isset($arParams["GROUP_ID"]))
		{
			$arParams["GROUP_ID"] = [];
		}
		elseif (!is_array($arParams["GROUP_ID"]))
		{
			$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
		}

		foreach($arParams["GROUP_ID"] as $k=>$v)
			if(intval($v) <= 0)
				unset($arParams["GROUP_ID"][$k]);

		if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
			$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
		else
			$arParams["CACHE_TIME"] = 0;

		if(empty($arParams["BLOG_VAR"]))
			$arParams["BLOG_VAR"] = "blog";
		if(empty($arParams["PAGE_VAR"]))
			$arParams["PAGE_VAR"] = "page";
		if(empty($arParams["USER_VAR"]))
			$arParams["USER_VAR"] = "id";
		if(empty($arParams["POST_VAR"]))
			$arParams["POST_VAR"] = "id";
		if(empty($arParams["NAV_PAGE_VAR"]))
			$arParams["NAV_PAGE_VAR"] = "pagen";
		if(empty($arParams["COMMENT_ID_VAR"]))
			$arParams["COMMENT_ID_VAR"] = "commentId";
//		pagination for old-style (tree) comments
		if(isset($_GET[$arParams["NAV_PAGE_VAR"]]) && intval($_GET[$arParams["NAV_PAGE_VAR"]])>0)
			$arParams["PAGEN"] = intval($_REQUEST[$arParams["NAV_PAGE_VAR"]]);
		else
			$arParams["PAGEN"] = 1;

		if(intval($arParams["COMMENTS_COUNT"])<=0)
			$arParams["COMMENTS_COUNT"] = 25;

		$arParams["PAGE_SIZE"] = $arParams["COMMENTS_COUNT"];
		$arParams["PAGE_SIZE_MIN"] = 3;

		if(!isset($arParams["USE_ASC_PAGING"]) || $arParams["USE_ASC_PAGING"] != "Y")
			$arParams["USE_DESC_PAGING"] = "Y";

		$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"] ?? '');
		if($arParams["PATH_TO_BLOG"] == '')
			$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

		$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"] ?? '');
		if($arParams["PATH_TO_USER"] == '')
			$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

		$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
		if($arParams["PATH_TO_POST"] == '')
			$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#"."&".$arParams["POST_VAR"]."=#post_id#");

		$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);

		if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
			$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
		if (!array_key_exists("PATH_TO_MESSAGES_CHAT", $arParams))
			$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
		if (!array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
			$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

		if (trim($arParams["NAME_TEMPLATE"] ?? '') == '')
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		$arParams['SHOW_LOGIN'] = !isset($arParams['SHOW_LOGIN']) || $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";
		$arParams["IMAGE_MAX_WIDTH"] = intval($arParams["IMAGE_MAX_WIDTH"] ?? 0);
		$arParams["IMAGE_MAX_HEIGHT"] = intval($arParams["IMAGE_MAX_HEIGHT"] ?? 0);
		$arParams["ALLOW_POST_CODE"] = !isset($arParams["ALLOW_POST_CODE"]) || $arParams["ALLOW_POST_CODE"] !== "N";

		$arParams["SMILES_COUNT"] = intval($arParams["SMILES_COUNT"] ?? 0);
		if($arParams["SMILES_COUNT"] <= 0)
			$arParams["SMILES_COUNT"] = 4;

		$arParams["SMILES_COLS"] = intval($arParams["SMILES_COLS"] ?? 0);
		if($arParams["SMILES_COLS"] < 0)
			$arParams["SMILES_COLS"] = 0;

		$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

		$arParams["EDITOR_RESIZABLE"] = !isset($arParams["EDITOR_RESIZABLE"]) || $arParams["EDITOR_RESIZABLE"] !== "N";
		$arParams["EDITOR_CODE_DEFAULT"] = isset($arParams["EDITOR_CODE_DEFAULT"]) && $arParams["EDITOR_CODE_DEFAULT"] === "Y";
		$arParams["EDITOR_DEFAULT_HEIGHT"] = intval($arParams["EDITOR_DEFAULT_HEIGHT"] ?? 0);
		if($arParams["EDITOR_DEFAULT_HEIGHT"] <= 0)
			$arParams["EDITOR_DEFAULT_HEIGHT"] = 200;
		$arParams["ALLOW_VIDEO"] = (isset($arParams["ALLOW_VIDEO"]) && $arParams["ALLOW_VIDEO"] == "Y" ? "Y" : "N");

		if (!isset($arParams["COMMENT_PROPERTY"]) || !is_array($arParams["COMMENT_PROPERTY"]))
		{
			$arParams["COMMENT_PROPERTY"] = [];
		}
		if (isset($arParams["ALLOW_IMAGE_UPLOAD"]))
		{
			if($arParams["ALLOW_IMAGE_UPLOAD"] == "A" || ($arParams["ALLOW_IMAGE_UPLOAD"] == "R" && $USER->IsAuthorized()))
			{
				if(!is_array($arParams["COMMENT_PROPERTY"]))
					$arParams["COMMENT_PROPERTY"] = Array(CBlogComment::UF_NAME);
				else
					$arParams["COMMENT_PROPERTY"][] = CBlogComment::UF_NAME;
			}
		}

//		get consent for registered users or not. Default = N
		$arParams["USER_CONSENT_FOR_REGISTERED"] =
			isset($arParams["USER_CONSENT_FOR_REGISTERED"]) ? $arParams["USER_CONSENT_FOR_REGISTERED"] : "N";

//		now we always use only AJAX comments, old redirect-style it is boring and not cool. Hardcode.
		$arParams["AJAX_POST"] = "Y";

//		to use cool ajax pagintaion in old component without crash of arResult
		$arParams["AJAX_PAGINATION"] = (isset($arParams["AJAX_PAGINATION"]) && $arParams["AJAX_PAGINATION"] == "Y");

		$arParams["BLOG_MODULE_PERMS"] = $GLOBALS["APPLICATION"]->GetGroupRight("blog");
		$arParams["SHOW_SPAM"] = ($arParams["SHOW_SPAM"] == "Y" && $arParams["BLOG_MODULE_PERMS"] >= "W" ? "Y" : "N");

		$arParams["AVATAR_SIZE_COMMENT"] = self::AVATAR_SIZE_COMMENT;

		return $arParams;
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION, $DB;

		$simpleComment = $this->arParams["SIMPLE_COMMENT"] == "Y";

		$this->arResult["USE_COMMENT_TITLE"] = true;
		$this->arParams["NOT_USE_COMMENT_TITLE"] = ($this->arParams["NOT_USE_COMMENT_TITLE"] != "Y") ? "N" : "Y";
		if($this->arParams["NOT_USE_COMMENT_TITLE"] == "Y")
			$this->arResult["USE_COMMENT_TITLE"] = false;

		$this->commentUrlID = intval($_REQUEST[$this->arParams["COMMENT_ID_VAR"]] ?? 0);

// 		activation rating
		CRatingsComponentsMain::GetShowRating($this->arParams);

		if(COption::GetOptionString("blog","allow_video", "Y") == "Y" && $this->arParams["ALLOW_VIDEO"] == "Y")
			$this->arResult["allowVideo"] = true;

		$this->arResult["allowImageUpload"] = false;
		if (isset($this->arParams["ALLOW_IMAGE_UPLOAD"]))
		{
			if($this->arParams["ALLOW_IMAGE_UPLOAD"] == "A" || ($this->arParams["ALLOW_IMAGE_UPLOAD"] == "R" && $USER->IsAuthorized()))
			{
				$this->arResult["allowImageUpload"] = true;
			}
		}

		$this->arResult["userID"] = $user_id = $USER->GetID();
		$this->arResult["canModerate"] = false;

		$this->arResult["ajax_comment"] = 0;

		if($this->arParams["NO_URL_IN_COMMENTS"] == "L")
		{
			$this->arResult["NoCommentUrl"] = true;
			$this->arResult["NoCommentReason"] = GetMessage("B_B_PC_MES_NOCOMMENTREASON_L");
		}
		if(!$USER->IsAuthorized() && $this->arParams["NO_URL_IN_COMMENTS"] == "A")
		{
			$this->arResult["NoCommentUrl"] = true;
			$this->arResult["NoCommentReason"] = GetMessage("B_B_PC_MES_NOCOMMENTREASON_A");
		}

		if(isset($this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"]) && is_numeric($this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"]))
		{
			$this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"] = floatVal($this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"]);
			$this->arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] = "Y";
			if($USER->IsAuthorized())
			{
				$authorityRatingId = CRatings::GetAuthorityRating();
				$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $user_id);
				if($arRatingResult["CURRENT_VALUE"] < $this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
				{
					$this->arResult["NoCommentUrl"] = true;
					$this->arResult["NoCommentReason"] = GetMessage("B_B_PC_MES_NOCOMMENTREASON_R");
				}
			}
		}

		$arBlog = CBlog::GetByUrl($this->arParams["BLOG_URL"], $this->arParams["GROUP_ID"]);
		$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);

		$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
		$this->arResult["Blog"] = $arBlog;

		if($this->arParams["ID_BY_CODE"])
			$this->arParams["ID"] = CBlogPost::GetID($this->arParams["ID"], $arBlog["ID"]);

		$this->arParams["ENTITY_XML_ID"] = self::createXmlId($this->arParams["ID"]);

		$arPost = CBlogPost::GetByID($this->arParams["ID"]);
		if(empty($arPost) && !$this->arParams["ID_BY_CODE"])
		{
			$this->arParams["ID"] = CBlogPost::GetID($this->arParams["ID"], $arBlog["ID"]);
			$arPost = CBlogPost::GetByID($this->arParams["ID"]);
		}
		if(intval($this->arParams["ID"])>0)
			$this->arResult["Perm"] = CBlogPost::GetBlogUserCommentPerms($this->arParams["ID"], $user_id);
		else
			$this->arResult["Perm"] = CBlog::GetBlogUserCommentPerms($arBlog["ID"], $user_id);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
		{
			CBlogImage::AddImageResizeHandler(array("width" => 400, "height" => 400));
			CBlogImage::AddImageCreateHandler(array('IS_COMMENT' => 'Y', 'USER_ID' => $user_id));
		}

		$this->arResult["Comments"] = [];
		$this->arResult["CommentsResult"] = [];
		$this->arResult["IDS"] = [];

		if(((!empty($arPost) && $arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH && $arPost["ENABLE_COMMENTS"] == "Y") || $simpleComment) && (($arBlog["ACTIVE"] == "Y" && $arGroup["SITE_ID"] == SITE_ID) || $simpleComment) )
		{
			$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
			$this->arResult["Post"] = $arPost;

			if($arPost["BLOG_ID"] == $arBlog["ID"] || $simpleComment)
			{
				//Comment delete
				if(isset($_GET["delete_comment_id"]) && intval($_GET["delete_comment_id"])>0)
				{
					if($_GET["success"] == "Y")
					{
						$this->arResult["MESSAGE"] = GetMessage("B_B_PC_MES_DELED");
					}
					else
					{
						$arComment = CBlogComment::GetByID(intval($_GET["delete_comment_id"]));
						if($this->arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
						{
							if(check_bitrix_sessid())
							{
								if(CBlogComment::Delete(intval($_GET["delete_comment_id"])))
								{
									self::clearBlogCaches($this->arParams["BLOG_URL"], $arComment["POST_ID"]);

									$this->arResult["ajax_comment"] = intval($_GET["delete_comment_id"]);
									$this->arResult["MESSAGE"] = GetMessage("B_B_PC_MES_DELED");
								}
							}
							else
								$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
						}
						if(intval($this->arResult["ajax_comment"]) <= 0)
							$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_DELETE");
					}
				}
				elseif(isset($_GET["show_comment_id"]) && intval($_GET["show_comment_id"])>0)
				{
					$arComment = CBlogComment::GetByID(intval($_GET["show_comment_id"]));
					if($this->arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
					{
						if($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_READY)
						{
							$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
						}
						else
						{
							if(check_bitrix_sessid())
							{
								if($commentID = CBlogComment::Update($arComment["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH)))
								{
									self::clearBlogCaches($this->arParams["BLOG_URL"], $arComment["POST_ID"]);

									$this->arResult["ajax_comment"] = $arComment["ID"];
								}
							}
							else
								$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
						}
					}
					if(intval($this->arResult["ajax_comment"]) <= 0)
						$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
				}
				elseif(isset($_GET["hide_comment_id"]) && intval($_GET["hide_comment_id"])>0)
				{
					$arComment = CBlogComment::GetByID(intval($_GET["hide_comment_id"]));
					if($this->arResult["Perm"]>=BLOG_PERMS_MODERATE && !empty($arComment))
					{
						if($arComment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
						{
							$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SHOW");
						}
						else
						{
							if(check_bitrix_sessid())
							{
								if($commentID = CBlogComment::Update($arComment["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
								{
									self::clearBlogCaches($this->arParams["BLOG_URL"], $arComment["POST_ID"]);

									$this->arResult["ajax_comment"] = $arComment["ID"];
								}
							}
							else
								$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
						}
					}
					if(intval($this->arResult["ajax_comment"]) <= 0)
						$this->arResult["ERROR_MESSAGE"] = GetMessage("B_B_PC_MES_ERROR_HIDE");
				}
				elseif(isset($_GET["hidden_add_comment_id"]) && intval($_GET["hidden_add_comment_id"])>0)
				{
					$this->arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
				}

				//Comments output
				if($this->arResult["Perm"]>=BLOG_PERMS_READ)
				{
					$this->arResult["CanUserComment"] = false;
					$this->arResult["canModerate"] = false;
					if($this->arResult["Perm"] >= BLOG_PERMS_PREMODERATE)
						$this->arResult["CanUserComment"] = true;
					if($this->arResult["Perm"] >= BLOG_PERMS_MODERATE)
						$this->arResult["canModerate"] = true;

					if(intval($user_id)>0)
						$this->setParamsForRegisteredUsers($user_id);

					if(!$USER->IsAuthorized())
					{
						$useCaptcha = COption::GetOptionString("blog", "captcha_choice", "U");
						if(empty($arBlog))
						{
							$arBlog = CBlog::GetByUrl($this->arParams["BLOG_URL"], $this->arParams["GROUP_ID"]);
							$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
							$this->arResult["Blog"] = $arBlog;
						}
						if($useCaptcha == "U")
							$this->arResult["use_captcha"] = ($arBlog["ENABLE_IMG_VERIF"]=="Y")? true : false;
						elseif($useCaptcha == "A")
							$this->arResult["use_captcha"] = true;
						else
							$this->arResult["use_captcha"] = false;
					}
					else
					{
						$this->arResult["use_captcha"] = false;
					}

					/////////////////////////////////////////////////////////////////////////////////////

					if(!empty($arPost["ID"]) && $_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["post"]) && empty($_POST["preview"]))
					{
//						convert charset
						if ($_POST["decode"] == "Y")
						{
							CUtil::JSPostUnescape();
						}

						if($this->arResult["Perm"] >= BLOG_PERMS_PREMODERATE)
						{
							if(check_bitrix_sessid())
							{
								$strErrorMessage = '';
								if(empty($this->arResult["Blog"]))
								{
									$arBlog = CBlog::GetByUrl($this->arParams["BLOG_URL"], $this->arParams["GROUP_ID"]);
									$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
									$this->arResult["Blog"] = $arBlog;
								}

								if ($_POST["blog_upload_image"] == "Y")
								{
									if ($_FILES["BLOG_UPLOAD_FILE"]["size"] > 0)
									{
										$this->arResult["imageUploadFrame"] = "Y";
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
											"USER_ID" => intval($user_id),
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
											$this->arResult["Image"] = Array("ID" => $aImg["ID"], "SRC" => $aImg["PARAMS"]["SRC"], "WIDTH" => $aImg["PARAMS"]["WIDTH"], "HEIGHT" => $aImg["PARAMS"]["HEIGHT"]);
										}
										else
										{
											if ($ex = $APPLICATION->GetException())
												$this->arResult["ERROR_MESSAGE"] = $ex->GetString();
										}
										$this->IncludeComponentTemplate();
										return;
									}
								}

								if($_POST["act"] != "edit")
								{
									if ($this->arResult["use_captcha"])
									{
										include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
										$captcha_code = $_POST["captcha_code"];
										$captcha_word = $_POST["captcha_word"];
										$cpt = new CCaptcha();
										$captchaPass = COption::GetOptionString("main", "captcha_password", "");
										if ($captcha_code <> '')
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
									if($this->arResult["Perm"] == BLOG_PERMS_PREMODERATE)
										$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;

									if(!$this->arResult["USE_COMMENT_TITLE"])
										unset($arFields["TITLE"]);

									if(intval($user_id)>0)
										$arFields["AUTHOR_ID"] = $user_id;
									else
									{
										$arFields["AUTHOR_NAME"] = trim($_POST["user_name"]);
										if(trim($_POST["user_email"]) <> '')
											$arFields["AUTHOR_EMAIL"] = trim($_POST["user_email"]);
										if($arFields["AUTHOR_NAME"] == '')
											$strErrorMessage .= GetMessage("B_B_PC_NO_ANAME")."<br />";
										$_SESSION["blog_user_name"] = $_POST["user_name"];
										$_SESSION["blog_user_email"] = $_POST["user_email"];
									}

									if(intval($_POST["parentId"])>0)
										$arFields["PARENT_ID"] = intval($_POST["parentId"]);
									else
										$arFields["PARENT_ID"] = false;
									if($_POST["comment"] == '')
										$strErrorMessage .= GetMessage("B_B_PC_NO_COMMENT")."<br />";

									if($strErrorMessage == '')
									{
										$dbDuplComment = CBlogComment::GetList(array("ID" => "DESC"), array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $arPost["ID"]), false, array("nTopCount" => 1), array("ID", "POST_ID", "BLOG_ID", "AUTHOR_ID", "POST_TEXT"));
										if($arDuplComment = $dbDuplComment->Fetch())
										{
											if($arDuplComment["POST_ID"] == $arFields["POST_ID"] && $arDuplComment["BLOG_ID"] == $arFields["BLOG_ID"] && intval($arDuplComment["AUTHOR_ID"]) == intval($arFields["AUTHOR_ID"]) && md5($arDuplComment["POST_TEXT"]) == md5($arFields["POST_TEXT"]))
											{
												$strErrorMessage .= GetMessage("B_B_PC_DUPLICATE_COMMENT");
											}
										}
									}

									if($strErrorMessage == '')
									{
										$fieldName = CBlogComment::UF_NAME;
										if (isset($GLOBALS[$fieldName]) && is_array($GLOBALS[$fieldName]))
										{
											$parseFilesResult = $this->parseFilesArray();
											$arAttachedFiles = $parseFilesResult['ATTACHED_FILES'];
											$imagesToAttach = $parseFilesResult['IMAGES_TO_ATTACH'];
											$toReplaceInText = $parseFilesResult['TO_REPLACE'];

//											update user fields by new files
											$GLOBALS[CBlogComment::UF_NAME] = $arAttachedFiles;

											if (!empty($toReplaceInText['SEARCH']) && !empty($toReplaceInText['REPLACE']))
												$arFields["POST_TEXT"] = str_replace($toReplaceInText['SEARCH'], $toReplaceInText['REPLACE'], $arFields["POST_TEXT"]);
										}

										if (!empty($this->arParams["COMMENT_PROPERTY"]))
											$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_COMMENT", $arFields);

										$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($this->arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $this->arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $this->arParams["SOCNET_GROUP_ID"]));

										$arFields["PATH"] = $commentUrl;
										if(mb_strpos($arFields["PATH"], "?") !== false)
											$arFields["PATH"] .= "&";
										else
											$arFields["PATH"] .= "?";
										$arFields["PATH"] .= $this->arParams["COMMENT_ID_VAR"]."=#comment_id###comment_id#";

										if($commentID = CBlogComment::Add($arFields))
										{
//											attach images with COMMENT_ID = 0 to this post
											if (!empty($imagesToAttach))
											{
												foreach ($imagesToAttach as $imageId)
													CBlogImage::Update($imageId, array(
														"POST_ID" => $arPost["ID"],
														"BLOG_ID" => $arBlog["ID"],
														"COMMENT_ID" => intval($commentID)
													));
											}

											self::clearBlogCaches($this->arParams["BLOG_URL"], $arPost["ID"]);

											$images = Array();
											$res = CBlogImage::GetList(array("ID"=>"ASC"), array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"], "IS_COMMENT" => "Y", "COMMENT_ID" => $commentID));
											while($aImg = $res->Fetch())
												$images[$aImg["ID"]] = $aImg["FILE_ID"];

											$AuthorName = "";
											if(intval($user_id)>0)
												$AuthorName = CBlogUser::GetUserNameEx($this->arResult["arUser"],$this->arResult["BlogUser"], $this->arParams);

											$parserBlog = new blogTextParser(false, $this->arParams["PATH_TO_SMILE"]);
											$arParserParams = Array(
												"imageWidth" => $this->arParams["IMAGE_MAX_WIDTH"],
												"imageHeight" => $this->arParams["IMAGE_MAX_HEIGHT"],
											);

											$text4mail = $parserBlog->convert4mail($_POST['comment'], $images);
											$serverName = ((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
											if ($serverName == '')
												$serverName = $_SERVER["SERVER_NAME"];

											if(mb_strpos($commentUrl, "?") !== false)
												$commentUrl .= "&";
											else
												$commentUrl .= "?";
											if($arFields["PUBLISH_STATUS"] <> '' && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
												$commentAddedUrl = $commentUrl.$this->arParams["COMMENT_ID_VAR"]."=".$commentID."&hidden_add_comment_id=".$commentID;
											$commentUrl .= $this->arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;

											if (!preg_match("/^[a-z]+:\\/\\//", $commentUrl))
												$commentUrl = ((CMain::IsHTTPS()) ? 'https://' : 'http://').$serverName.$commentUrl;

											if($AuthorName == '')
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
											if(!$this->arResult["USE_COMMENT_TITLE"])
												unset($arMailFields["COMMENT_TITLE"]);

											if ($arBlog['EMAIL_NOTIFY']=='Y' && $user_id != $arPost['AUTHOR_ID']) // comment author is not original post author
											{
												$res = CUser::GetByID($arPost['AUTHOR_ID']);
												if($arOwner = $res->GetNext())
												{
													$arMailFields["EMAIL_TO"] = $arOwner['EMAIL'];

													CEvent::Send(
														($this->arResult["USE_COMMENT_TITLE"]) ? "NEW_BLOG_COMMENT" : "NEW_BLOG_COMMENT_WITHOUT_TITLE",
														SITE_ID,
														$arMailFields
													);
												}

												if($arPost["AUTHOR_ID"] != $arBlog["OWNER_ID"] && intval($arBlog["OWNER_ID"]) > 0)
												{
													$res = CUser::GetByID($arBlog["OWNER_ID"]);
													if($arOwnerBlog = $res->GetNext())
													{
														$arMailFields["EMAIL_TO"] = $arOwnerBlog['EMAIL'];

														CEvent::Send(
															($this->arResult["USE_COMMENT_TITLE"]) ? "NEW_BLOG_COMMENT" : "NEW_BLOG_COMMENT_WITHOUT_TITLE",
															SITE_ID,
															$arMailFields
														);
													}
												}
											}

											if($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $arFields["PUBLISH_STATUS"] == '')
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
															$arPrevBlog = CBlog::GetByOwnerID($arPrev['AUTHOR_ID'], $this->arParams["GROUP_ID"]);
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
																($this->arResult["USE_COMMENT_TITLE"]) ? "NEW_BLOG_COMMENT2COMMENT" : "NEW_BLOG_COMMENT2COMMENT_WITHOUT_TITLE",
																SITE_ID,
																$arMailFields
															);
														}
													}
												}
											}

											if($arFields["PUBLISH_STATUS"] <> '' && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
												$this->arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_ADDED");
											$this->arResult["ajax_comment"] = $commentID;
										}
										else
										{
											if ($e = $APPLICATION->GetException())
												$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$e->GetString();
										}
									}
									else
									{
										if ($e = $APPLICATION->GetException())
											$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$e->GetString();
										if($strErrorMessage <> '')
											$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR")."</b><br />".$strErrorMessage;
									}
								}
								else //update comment
								{
									$commentID = intval($_POST["edit_id"]);
									$arOldComment = CBlogComment::GetByID($commentID);
									if($commentID <= 0 || empty($arOldComment))
										$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_COM_ERROR_LOST");
									elseif($arOldComment["AUTHOR_ID"] == $user_id || $this->arParams["BLOG_MODULE_PERMS"] >= "W")
									{
										$arFields = Array(
											"TITLE" => $_POST["subject"],
											"POST_TEXT" => $_POST["comment"],
											"URL" => $arBlog["URL"],
										);
										if(!$this->arResult["USE_COMMENT_TITLE"])
											unset($arFields["TITLE"]);
										if($this->arResult["Perm"] == BLOG_PERMS_PREMODERATE)
											$arFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;

//										PARSE FILES: compare uploaded files, existing files and session
//										find images then need to attach to post, find not-imaged files
										$fieldName = CBlogComment::UF_NAME;
										if (isset($GLOBALS[$fieldName]) && is_array($GLOBALS[$fieldName]))
										{
											$parseFilesResult = $this->parseFilesArray();
											$arAttachedFiles = $parseFilesResult['ATTACHED_FILES'];
											$imagesToAttach = $parseFilesResult['IMAGES_TO_ATTACH'];
											$toReplaceInText = $parseFilesResult['TO_REPLACE'];

//											update user fields by new files
											$GLOBALS[CBlogComment::UF_NAME] = $arAttachedFiles;

											if (!empty($toReplaceInText['SEARCH']) && !empty($toReplaceInText['REPLACE']))
												$arFields["POST_TEXT"] = str_replace($toReplaceInText['SEARCH'], $toReplaceInText['REPLACE'], $arFields["POST_TEXT"]);

										}
										if (!empty($this->arParams["COMMENT_PROPERTY"]))
											$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_COMMENT", $arFields);

										$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($this->arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $this->arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $this->arParams["SOCNET_GROUP_ID"]));

										$arFields["PATH"] = $commentUrl;
										if(mb_strpos($arFields["PATH"], "?") !== false)
											$arFields["PATH"] .= "&";
										else
											$arFields["PATH"] .= "?";
										$arFields["PATH"] .= $this->arParams["COMMENT_ID_VAR"]."=".$commentID."#".$commentID;

										$dbComment = CBlogComment::GetList(array(), Array("POST_ID" => $arPost["ID"], "BLOG_ID" => $arBlog["ID"], "PARENT_ID" => $commentID));
										if($dbComment->Fetch() && $this->arParams["BLOG_MODULE_PERMS"] < "W")
										{
											$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_EDIT_ALREADY_COMMENTED");
										}
										else
										{
											if($commentID = CBlogComment::Update($commentID, $arFields))
											{
//												attach images with COMMENT_ID = 0 to this post
												if (!empty($imagesToAttach))
												{
													foreach ($imagesToAttach as $imageId)
														CBlogImage::Update($imageId, array(
															"POST_ID" => $arPost["ID"],
															"BLOG_ID" => $arBlog["ID"],
															"COMMENT_ID" => intval($commentID)
														));
												}

												self::clearBlogCaches($this->arParams["BLOG_URL"], $arPost["ID"]);
//
												$images = Array();

												$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "COMMENT_ID" => $commentID, "IS_COMMENT" => "Y"));
												while($aImg = $res->Fetch())
													$images[$aImg["ID"]] = $aImg["FILE_ID"];

												$commentUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($this->arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $this->arParams["ALLOW_POST_CODE"]), "user_id" => $arBlog["OWNER_ID"], "group_id" => $this->arParams["SOCNET_GROUP_ID"]));
												if(mb_strpos($commentUrl, "?") !== false)
													$commentUrl .= "&";
												else
													$commentUrl .= "?";

												if($arFields["PUBLISH_STATUS"] <> '' && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
													$this->arResult["MESSAGE"] = GetMessage("B_B_PC_MES_HIDDEN_EDITED");
												$this->arResult["ajax_comment"] = $commentID;

											}
											else
											{
												if ($e = $APPLICATION->GetException())
													$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".$e->GetString();
											}
										}
									}
									else
									{
										$this->arResult["COMMENT_ERROR"] = "<b>".GetMessage("B_B_PC_COM_ERROR_EDIT")."</b><br />".GetMessage("B_B_PC_NO_RIGHTS_EDIT");
									}
								}
							}
							else
								$this->arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_MES_ERROR_SESSION");
						}
						else
							$this->arResult["COMMENT_ERROR"] = GetMessage("B_B_PC_NO_RIGHTS");
					}

//					PREVIEW
					elseif($_POST["preview"] <> '')
					{
						if(check_bitrix_sessid())
						{
							$p = new blogTextParser(false, $this->arParams["PATH_TO_SMILE"]);
							$arParserParams = Array(
								"imageWidth" => $this->arParams["IMAGE_MAX_WIDTH"],
								"imageHeight" => $this->arParams["IMAGE_MAX_HEIGHT"],
							);
							$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "USER_LINK" => "N");
							if(COption::GetOptionString("blog","allow_video", "Y") != "Y" || $this->arParams["ALLOW_VIDEO"] != "Y")
								$arAllow["VIDEO"] = "N";

							if($this->arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && !$this->arResult["NoCommentUrl"] && $USER->IsAuthorized())
							{
								$authorityRatingId = CRatings::GetAuthorityRating();
								$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $user_id);
								if($arRatingResult["CURRENT_VALUE"] < $this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
									$this->arResult["NoCommentUrl"] = true;
							}

							if($this->arResult["NoCommentUrl"])
								$arAllow["CUT_ANCHOR"] = "Y";

							$images = Array();
							preg_match_all("/\[img([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/ies".BX_UTF_PCRE_MODIFIER, $_POST["comment"], $matches);
							$res = CBlogImage::GetList(array(), array("POST_ID"=>$arPost["ID"], "BLOG_ID" => $arBlog["ID"], "USER_ID" => intval($user_id), "IS_COMMENT" => "Y"));
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
						$this->arResult["ShowIP"] = "Y";
					else
						$this->arResult["ShowIP"] = COption::GetOptionString("blog", "show_ip", "Y");

					$tmp = [];
					$tmp["MESSAGE"] = $this->arResult["MESSAGE"] ?? '';
					$tmp["ERROR_MESSAGE"] = $this->arResult["ERROR_MESSAGE"] ?? '';

					if(!empty($this->arResult["COMMENT_ERROR"]) || !empty($this->arResult["ERROR_MESSAGE"]))
					{
						$this->arResult["is_ajax_post"] = "Y";
					}
					else
					{
						if(intval($this->arResult["ajax_comment"]) > 0)
						{
							$this->arResult["is_ajax_post"] = "Y";
							$this->arParams["CACHE_TIME"] = 0;
						}

						if(intval($this->arParams["ID"]) > 0)
						{
							$this->createSmilesParams();
							$this->createImagesParams();

//							get ALL USERS, which wrote comments for current post
							$blogUser = new BlogUser($this->arParams["CACHE_TIME"]);
							$blogUser->setBlogId($arBlog["ID"]);
							$this->arResult["COMMENTS_USERS"] = $blogUser->getUsers(BlogUser::getCommentAuthorsIdsByPostId($arPost['ID']));

//							create list of all comments with base params.
							$this->createCommentsList();
						}
						unset($this->arResult["MESSAGE"]);
						unset($this->arResult["ERROR_MESSAGE"]);

						$this->arResult["MESSAGE"] = $tmp["MESSAGE"];
						$this->arResult["ERROR_MESSAGE"] = $tmp["ERROR_MESSAGE"];
					}

//					add captcha, if set param
					$this->addCaptcha();
				}

				$this->createAdditionalCommentsParams();

//				to mark NEW comments later
				if($USER->IsAuthorized())
					$this->markNewComments();
//					$this->saveLastPostView();

//				message if use premoderate
				if($USER->IsAuthorized())
				{
					if(intval($this->commentUrlID) > 0 && empty($this->arResult["Comments"][$this->commentUrlID]))
					{
						$arComment = CBlogComment::GetByID($this->commentUrlID);
						if($arComment["AUTHOR_ID"] == $this->arResult["userID"] && $arComment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
							$this->arResult["MESSAGE"] = GetMessage("B_B_PC_HIDDEN_POSTED");
					}
				}

//				for only visible comments (only current page) use conversion and geneerate additional params
				$this->IncludeComponentTemplate();
			}
		}
	}



	/**
	 * Create list of ALL comments for this post, but with just base parameters.
	 * Need to small cache of comments list, to convert them in tree or flat list.
	 * And next we can add additional params only for visible elements.
	 */
	protected function createCommentsList()
	{
		$cache = new CPHPCache;
		$cacheId = $this->createCacheId("comments_all");
		$cachePath = $this->createCachePath();
		if ($this->arParams["CACHE_TIME"] > 0 && $cache->InitCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath))
		{
			$vars = $cache->GetVars();
			$this->arResult = array_merge($this->arResult, $vars["arResult"]);

			$template = new CBitrixComponentTemplate();
			$template->ApplyCachedData($vars["templateCachedData"]);

			$cache->Output();
		}
		else
		{
			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath);


//			PROCESS
			$arOrder = Array("DATE_CREATE" => "ASC", "ID" => "ASC");
			$arFilter = Array("POST_ID" => $this->arParams["ID"], "BLOG_ID" => $this->arResult["Blog"]["ID"]);
			if(isset($this->arResult["is_ajax_post"]) && $this->arResult["is_ajax_post"] == "Y" && intval($this->arResult["ajax_comment"]) > 0)
				$arFilter["ID"] = $this->arResult["ajax_comment"];
			$arSelectedFields = Array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL",
				"AUTHOR_IP", "AUTHOR_IP1", "TITLE", "POST_TEXT", "DATE_CREATE", "PUBLISH_STATUS");
			$dbComment = CBlogComment::GetList($arOrder, $arFilter, false, false, $arSelectedFields);

//			create params for every COMMENT
			$this->arResult["firstLevel"] = "";
			$resComments = [];
			while($comment = $dbComment->GetNext())
			{
//				clear useless tilda
				foreach($comment as $key => $value)
				{
					if(!in_array($key, array("POST_TEXT", "TITLE")))
						unset($comment["~".$key]);
				}
//				create TREE for old-style comments. For new LIST view - we create list after, in result modifer
				if (empty($resComments[intval($comment["PARENT_ID"])]))
				{
					$resComments[intval($comment["PARENT_ID"])] = Array();
					if ($this->arResult["firstLevel"] === '')
						$this->arResult["firstLevel"] = intval($comment["PARENT_ID"]);
				}
				$resComments[(int)$comment["PARENT_ID"]][] = $comment;

//				save IDs in another array
				$this->arResult["IDS"][] = $comment["ID"];

//				save unsorted comments in another array
				$this->arResult["Comments"][$comment["ID"]] = Array(
					"ID" => $comment["ID"],
					"PARENT_ID" => $comment["PARENT_ID"],
					"PUBLISH_STATUS" => $comment["PUBLISH_STATUS"],
				);
			}
			$this->arResult["CommentsResult"] = $resComments;

			if($this->arParams["SHOW_RATING"] == "Y" && !empty($this->arResult["IDS"]))
				$this->arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_COMMENT', $this->arResult["IDS"]);

//			set params for view all comments properties
			$this->createCommentsProperties();
//			end PROCESS


			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $this->arResult));
		}
	}

	public function createPostFormId()
	{
		return self::POST_COMMENT_FORM_PREFIX;
	}

	public function createEditorId()
	{
		return self::POST_COMMENT_MESSAGE;
	}

	protected function createCacheId($uniqueString = "")
	{
		global $USER;
		$cache_id = serialize($this->arParams)."_".$this->arResult["Perm"]."_".$USER->IsAuthorized();

		if(($tzOffset = CTimeZone::GetOffset()) <> 0)
			$cache_id .= "_".$tzOffset;

		if(isset($this->arResult["is_ajax_post"]) && $this->arResult["is_ajax_post"] == "Y")
			$cache_id .= 'ajax_comment'.$this->arResult["ajax_comment"];

//		add unique key
		if($uniqueString <> '')
			$cache_id .= '_'.$uniqueString;

		return "blog_comment_".md5($cache_id);
	}

	protected function createCachePath()
	{
		return "/".SITE_ID."/blog/".$this->arParams["BLOG_URL"]."/comment/".$this->arParams["ID"]."/";
	}

	private function createXmlId($entityId)
	{
		return "BLOG_" . $entityId;
	}

	private function parseFilesArray()
	{
		$existingFiles = array();
		if ($this->arParams["ID"] > 0 && $_POST["blog_upload_cid"] == '')
		{
			$dbP = CBlogComment::GetList(array(), array("ID" => $this->arParams["ID"]), false, false, array("ID", "UF_BLOG_COMMENT_DOC"));
			if ($arP = $dbP->Fetch())
			{
				$existingFiles = $arP["UF_BLOG_COMMENT_DOC"];
			}
		}

		$imagesToAttach = array();    // images ids to attach them to blog post
		$arAttachedFiles = array();
		$toReplaseInText = array('SEARCH' => array(), 'REPLACE' => array());
		$notAttachedImages = $this->getNotAttachedFiles(true);
		foreach ($GLOBALS[CBlogComment::UF_NAME] as $fileID)
		{
			$fileID = intval($fileID);
			if ($fileID <= 0)
			{
				continue;
			}
			elseif (!is_array($_SESSION["MFI_UPLOADED_FILES_" . $_POST["blog_upload_cid"]]) || !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_" . $_POST["blog_upload_cid"]]))
			{
				if (empty($existingFiles) || !in_array($fileID, $existingFiles))
					continue;
			}

//			$arFile = CFile::GetFileArray($fileID);
			if (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === NULL)
			{
				if (isset($notAttachedImages[$fileID]) && $notAttachedImages[$fileID])
				{
					$imagesToAttach[] = $notAttachedImages[$fileID];
//					collect strings to replace in DETAIL TEXT
					$toReplaseInText['SEARCH'][] = "[IMG ID=" . $fileID . "file";
					$toReplaseInText['REPLACE'][] = "[IMG ID=" . $notAttachedImages[$fileID] . "";
				}
			}
			else
			{
				$arAttachedFiles[] = $fileID;
			}
		}

//								save in userfields only NOT_IMAGE files


		return array(
			'ATTACHED_FILES' => $arAttachedFiles,
			'IMAGES_TO_ATTACH' => $imagesToAttach,
			'TO_REPLACE' => $toReplaseInText,
		);
	}

	private function getNotAttachedFiles($removeOldFiles = false)
	{
		$notAttachedImages = array();
		$resNotAttachedImages = CBlogImage::GetList(
			array(),
			array(
				"POST_ID" => 0,
				"BLOG_ID" => 0,
				"IS_COMMENT" => 'Y',
				"COMMENT_ID" => 0
			),
			false, false,
			array("ID", "FILE_ID", "TIMESTAMP_X")
		);
		while ($image = $resNotAttachedImages->Fetch())
		{
//			if file upload in current session
			if (
				is_array($_SESSION["MFI_UPLOADED_FILES_" . $_POST["blog_upload_cid"]]) &&
				in_array($image["FILE_ID"], $_SESSION["MFI_UPLOADED_FILES_" . $_POST["blog_upload_cid"]])
			)
				$notAttachedImages[$image["FILE_ID"]] = $image["ID"];

			if ($removeOldFiles)
			{
//				remove too old files
//				default lifetime - 1 day
				$imageCreateDate = new DateTime($image["TIMESTAMP_X"]);
				$nowDate = new DateTime();
				if ($nowDate > $imageCreateDate->add(new DateInterval('PT' . CBlogImage::NOT_ATTACHED_IMAGES_LIFETIME . 'S')))
				{
					CBlogImage::Delete($image["ID"]);
					unset($notAttachedImages[$image["FILE_ID"]]);
				}
			}
		}

		return $notAttachedImages;
	}

	public function bindPostToEditorForm($xmlId, $formIdGet = NULL, $arParams)
	{
		static $formId = NULL;
		if ($formIdGet !== NULL)
		{
			$formId = $formIdGet;

			return '';
		}

		$scriptStr = "
			<script type=\"text/javascript\">
				BX.ready(function(){
					__blogLinkEntity({" .
			CUtil::JSEscape($xmlId) . " : ['BG', " . $arParams["ID"] . ", '" . $arParams["LOG_ID"] . "']},";

		if ($formId == NULL)
			$scriptStr .= "\"" . $this->createPostFormId() . "\"";
		else
			$scriptStr .= "\"" . $formId . "\"";

		$scriptStr .= ");});</script>";

		return $scriptStr;
	}

	/**
	 * If not set "consent for registered users" option - always set flag to true;
	 * Else - match flag by checking consents for this component URL
	 */
	private function isUserGivenConsent()
	{
		if(isset($this->arParams["USER_CONSENT_FOR_REGISTERED"]) && $this->arParams["USER_CONSENT_FOR_REGISTERED"] != "Y")
		{
			$this->arParams["USER_CONSENT_WAS_GIVEN"] = true;
		}
		elseif(isset($this->arParams["USER_CONSENT"]) && $this->arParams["USER_CONSENT"] == "Y"
			&& isset($this->arParams["USER_CONSENT_ID"]) && $this->arParams["USER_CONSENT_ID"])
		{
			$this->arParams["USER_CONSENT_WAS_GIVEN"] = BlogUser::isUserGivenConsent(
				$this->arResult['arUser']['ID'],
				$this->arParams["USER_CONSENT_ID"]
			);
		}
	}

	private function setParamsForRegisteredUsers($user_id)
	{
		$this->arResult["BlogUser"] = CBlogUser::GetByID($user_id, BLOG_BY_USER_ID);
		$this->arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($this->arResult["BlogUser"]);
		$dbUser = CUser::GetByID($user_id);
		$this->arResult["arUser"] = $dbUser->GetNext();
		$this->arResult["User"]["NAME"] = CBlogUser::GetUserNameEx($this->arResult["arUser"],$this->arResult["BlogUser"], $this->arParams);
		$this->arResult["User"]["ID"] = $user_id;

//		check is user consent was given ever
		$this->isUserGivenConsent();
	}

	private function createSmilesParams()
	{
		$cache = new CPHPCache;
		$cacheId = $this->createCacheId("smiles");
		$cachePath = $this->createCachePath();
		if ($this->arParams["CACHE_TIME"] > 0 && $cache->InitCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath))
		{
			$vars = $cache->GetVars();
			$this->arResult = array_merge($this->arResult, $vars["arResult"]);

			$template = new CBitrixComponentTemplate();
			$template->ApplyCachedData($vars["templateCachedData"]);

			$cache->Output();
		}
		else
		{
			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath);

//			PROCESS
			$this->arResult["Smiles"] = CBlogSmile::getSmiles(CSmile::TYPE_SMILE, LANGUAGE_ID);
			foreach($this->arResult["Smiles"] as $key => $value)
			{
				$this->arResult["Smiles"][$key]["LANG_NAME"] = $value["NAME"];
				$this->arResult["Smiles"][$key]["~LANG_NAME"] = htmlspecialcharsback($value["NAME"]);
				list($type) = explode(" ", $value["TYPING"]);
				$this->arResult["Smiles"][$key]["TYPE"] = str_replace("'", "\'", $type);
				$this->arResult["Smiles"][$key]["TYPE"] = str_replace("\\", "\\\\", $this->arResult["Smiles"][$key]["TYPE"]);
			}
			$this->arResult["SmilesCount"] = count($this->arResult["Smiles"]);
//			end PROCESS

			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $this->arResult));
		}
	}


	protected function createImagesParams()
	{
		$cache = new CPHPCache;
		$cacheId = $this->createCacheId("images");
		$cachePath = $this->createCachePath();
		if ($this->arParams["CACHE_TIME"] > 0 && $cache->InitCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath))
		{
			$vars = $cache->GetVars();
			$this->arResult = array_merge($this->arResult, $vars["arResult"]);

			$template = new CBitrixComponentTemplate();
			$template->ApplyCachedData($vars["templateCachedData"]);

			$cache->Output();
		}
		else
		{
//			PROCESS
			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath);

			$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$this->arParams['ID'], "BLOG_ID"=>$this->arResult["Blog"]['ID'], "IS_COMMENT" => "Y"), false, false, Array("ID", "FILE_ID", "POST_ID", "BLOG_ID", "USER_ID", "TITLE", "COMMENT_ID", "IS_COMMENT"));
			$this->arResult["arImages"] = Array();
			$this->arResult["Images"] = Array();
			while ($arImage = $res->Fetch())
			{
				$this->arResult["arImagesFiles"][$arImage['ID']] = $arImage['FILE_ID'];
				$currImage = array(
					"small" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=70&height=70&type=square",
					"full" => "/bitrix/components/bitrix/blog/show_file.php?fid=".$arImage['ID']."&width=1000&height=1000"
				);
				$currImage = array_merge(CFile::GetfileArray($arImage['FILE_ID']), $currImage);
				$this->arResult["arImages"][$arImage["COMMENT_ID"]][$arImage['ID']] = $currImage;

				if ($this->arResult["allowImageUpload"])
				{
					$arImage["SRC"] = CFile::GetPath($arImage["FILE_ID"]);
					$this->arResult["Images"][] = $arImage;
				}
			}
//			end PROCESS

			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $this->arResult));
		}
	}

	/**
	 * Formatting author name, set url and blog params and save this in user cache
	 * @param $userId
	 */
	protected function setCommentAuthorCache($userId)
	{
		$arUsrTmp = array();
		$arUsrTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_USER"], array("user_id" => $userId));
		$arUsrTmp["AuthorName"] = BlogUser::GetUserNameEx(
			$this->arResult["COMMENTS_USERS"][$userId]["arUser"],
			$this->arResult["COMMENTS_USERS"][$userId]["BlogUser"],
			$this->arParams
		);
		$arUsrTmp["Blog"] = CBlog::GetByOwnerID(intval($userId), $this->arParams["GROUP_ID"]);
		if($this->arResult["userID"] == $userId)
			$arUsrTmp["AuthorIsPostAuthor"] = "Y";

		$this->arResult["USER_CACHE"][$userId] = $arUsrTmp;
	}

	protected function createCommentsProperties()
	{
		$this->arResult["COMMENT_PROPERTIES"] = array("SHOW" => "N");	//by default
		if (!empty($this->arParams["COMMENT_PROPERTY"]))
		{
			$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_COMMENT", 0, LANGUAGE_ID);
			if (count($this->arParams["COMMENT_PROPERTY"]) > 0)
			{
				foreach ($arPostFields as $FIELD_NAME => $arPostField)
				{
					if (!in_array($FIELD_NAME, $this->arParams["COMMENT_PROPERTY"]))
						continue;
					$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
					$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
					$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
					$this->arResult["COMMENT_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
				}
			}
			if (!empty($this->arResult["COMMENT_PROPERTIES"]["DATA"]))
				$this->arResult["COMMENT_PROPERTIES"]["SHOW"] = "Y";
		}
	}

	/**
	 * Get all all comments from cache, or process them in cycle, create params, page etc
	 */
	protected function createAdditionalCommentsParams()
	{
		$cache = new CPHPCache;
		$cacheId = $this->createCacheId(implode(",",$this->arResult["IDS"]));
		$cachePath = $this->createCachePath();
		if ($this->arParams["CACHE_TIME"] > 0 && $cache->InitCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath))
		{
			$Vars = $cache->GetVars();
			$this->arResult = array_merge($this->arResult, $Vars["arResult"]);

			$template = new CBitrixComponentTemplate();
			$template->ApplyCachedData($Vars["templateCachedData"]);

			$cache->Output();
		}

		else
		{
			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath);

//			ajax-style - processing only comments for current page
			if($this->arParams["AJAX_PAGINATION"])
				$this->createCommentsPages();

			if(is_array($this->arResult["CommentsResult"]))
			{
				$textParser = new blogTextParser(false, $this->arParams["PATH_TO_SMILE"]);
				foreach ($this->arResult["CommentsResult"] as $level => $comments)
				{
					foreach ($comments as $key => $comment)
					{
						$this->arResult["CommentsResult"][$level][$key] = array_merge($comment, $this->createAdditionalCommentParams($comment, $textParser));
					}
				}
			}

//			split comments to pages - old style, put all comments at one hit
			if(!$this->arParams["AJAX_PAGINATION"])
				$this->createCommentsPages();
//			add converted fields to pages if AJAX-paging
			else
				$this->arResult["PagesComment"][$this->arResult["PAGE"]] = $this->arResult["CommentsResult"][0];

			if ($this->arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $this->arResult));
		}
	}

	/**
	 * Create base params for one comment, author params, formatting title and text etc
	 *
	 * @param $comment
	 * @param blogTextParser $textParser
	 * @return mixed
	 */
	protected function createAdditionalCommentParams($comment, blogTextParser $textParser)
	{
		global $APPLICATION;

		if (intval($comment["AUTHOR_ID"]) > 0)
		{
//			formatting AUTHOR name, set url and blog params and save this in user cache
			if (empty($this->arResult["USER_CACHE"][$comment["AUTHOR_ID"]]))
				$this->setCommentAuthorCache($comment["AUTHOR_ID"]);

//			set AUTHOR PARAMS
			$comment["BlogUser"] = $this->arResult["COMMENTS_USERS"][$comment["AUTHOR_ID"]]["BlogUser"];
			$comment["arUser"] = $this->arResult["COMMENTS_USERS"][$comment["AUTHOR_ID"]]["arUser"];
			$comment["AuthorName"] = HtmlFilter::encode($this->arResult["COMMENTS_USERS"][$comment["AUTHOR_ID"]]["AUTHOR_NAME"]);
			$comment["AVATAR_file"] = $this->arResult["COMMENTS_USERS"][$comment["AUTHOR_ID"]]["BlogUser"]["AVATAR_file"];
			if ($comment["AVATAR_file"] !== false)
				$comment["AVATAR_img"] = $this->arResult["COMMENTS_USERS"][$comment["AUTHOR_ID"]]["BlogUser"]["AVATAR_img"]['30_30'];
//			from user cache
			$comment["Blog"] = $this->arResult["USER_CACHE"][$comment["AUTHOR_ID"]]["Blog"];
			$comment["urlToAuthor"] = $this->arResult["USER_CACHE"][$comment["AUTHOR_ID"]]["urlToAuthor"];
			$comment["AuthorIsPostAuthor"] = $this->arResult["USER_CACHE"][$comment["AUTHOR_ID"]]["AuthorIsPostAuthor"];

			if (!empty($comment["Blog"]))
				$comment["urlToBlog"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_BLOG"], array("blog" => $comment["Blog"]["URL"], "user_id" => $comment["Blog"]["OWNER_ID"], "group_id" => $this->arParams["SOCNET_GROUP_ID"]));
		}
		else
		{
			$comment["AuthorName"] = $comment["AUTHOR_NAME"];
			$comment["AuthorEmail"] = $comment["AUTHOR_EMAIL"];
		}

//		create URLs
		if ($this->arResult["canModerate"])
		{
			if ($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
			{
				$comment["urlToHide"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("hide_comment_id=" . $comment["ID"], Array("sessid", "delete_comment_id", "hide_comment_id", "success", "show_comment_id", "commentId")));
			}
			else
			{
				$comment["urlToShow"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("show_comment_id=" . $comment["ID"], Array("sessid", "delete_comment_id", "show_comment_id", "success", "hide_comment_id", "commentId")));
			}
			if ($this->arResult["Perm"] >= BLOG_PERMS_FULL)
			{
				$comment["urlToDelete"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("delete_comment_id=" . $comment["ID"], Array("sessid", "delete_comment_id", "success", "hide_comment_id", "show_comment_id", "commentId")));
			}
			if ($this->arParams["SHOW_SPAM"] == "Y")
			{
				if (intval($comment["AUTHOR_ID"]) > 0)
					$comment["urlToSpam"] = "/bitrix/admin/blog_comment.php?lang=ru&set_filter=Y&filter_author_id=" . $comment["AUTHOR_ID"];
				elseif ($comment["AUTHOR_IP"] <> '')
					$comment["urlToSpam"] = "/bitrix/admin/blog_comment.php?lang=ru&set_filter=Y&filter_author_anonym=Y&filter_author_ip=" . $comment["AUTHOR_IP"];
				else
					$comment["urlToSpam"] = "/bitrix/admin/blog_comment.php?lang=ru&set_filter=Y&filter_author_anonym=Y&filter_author_email=" . $comment["AUTHOR_EMAIL"];
			}
		}

//		OTHER
		$comment["ShowIP"] = $this->arResult["ShowIP"];
		$arAllow = $this->createCommentAllows($comment);

//		TITLE and TEXT
		if ($this->arResult["USE_COMMENT_TITLE"])
			$comment = array_merge($comment, $this->createCommentTitle($comment, $textParser));

		$arParserParams = Array(
			"imageWidth" => $this->arParams["IMAGE_MAX_WIDTH"],
			"imageHeight" => $this->arParams["IMAGE_MAX_HEIGHT"],
		);
		$comment["TextFormated"] = $textParser->convert($comment["~POST_TEXT"], false, $this->arResult["arImagesFiles"], $arAllow, $arParserParams);
		$comment["DateFormated"] = FormatDate($this->arParams["DATE_TIME_FORMAT"], MakeTimeStamp($comment["DATE_CREATE"], CSite::GetDateFormat("FULL")));

//		not show images, than put in comment text
		if(!empty($textParser->showedImages))
		{
			foreach($textParser->showedImages as $val)
			{
				if(!empty($this->arResult["arImages"][$comment["ID"]][$val]))
					unset($this->arResult["arImages"][$comment["ID"]][$val]);
			}
		}

		if($this->arResult["lastPostView"] <> '' && $this->arResult["lastPostView"] < MakeTimeStamp($comment["DATE_CREATE"]))
			$comment["NEW"] = "Y";

//		PROPERTIES
		if (!empty($this->arParams["COMMENT_PROPERTY"]))
		{
			$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_COMMENT", $comment["ID"], LANGUAGE_ID);
			if (count($arPostFields) > 0)
			{
				foreach ($arPostFields as $FIELD_NAME => $arPostField)
				{
					if (!in_array($FIELD_NAME, $this->arParams["COMMENT_PROPERTY"]))
						continue;
					$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
					$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
					$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
					$comment["COMMENT_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
				}
			}
			if (!empty($comment["COMMENT_PROPERTIES"]["DATA"]))
				$comment["COMMENT_PROPERTIES"]["SHOW"] = "Y";
		}

//		add converted fields to comments
		return $comment;
	}


	protected function createCommentTitle($arComment, blogTextParser $textParser)
	{
		if($arComment["TITLE"] <> '')
			$arComment["TitleFormated"] = $textParser->convert($arComment["TITLE"], false);
		if(mb_strpos($arComment["TITLE"], "RE") === false)
			$subj = "RE: ".$arComment["TITLE"];
		else
		{
			if(mb_strpos($arComment["TITLE"], "RE") == 0)
			{
				if(mb_strpos($arComment["TITLE"], "RE:") !== false)
				{
					$count = substr_count($arComment["TITLE"], "RE:");
					$subj = mb_substr($arComment["TITLE"], (mb_strrpos($arComment["TITLE"], "RE:") + 4));
				}
				else
				{
					if(mb_strpos($arComment["TITLE"], "[") == 2)
					{
						$count = mb_substr($arComment["TITLE"], 3, (mb_strpos($arComment["TITLE"], "]: ") - 3));
						$subj = mb_substr($arComment["TITLE"], (mb_strrpos($arComment["TITLE"], "]: ") + 3));
					}
				}
				$subj = "RE[".($count+1)."]: ".$subj;
			}
			else
				$subj = "RE: ".$arComment["TITLE"];
		}
		$arComment["CommentTitle"] = str_replace(array("\\", "\"", "'"), array("\\\\", "\\"."\"", "\\'"), $subj);

		return $arComment;
	}

	protected function createCommentAllows($arComment)
	{
		$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "USER_LINK" => "N");
		if(COption::GetOptionString("blog","allow_video", "Y") != "Y" || $this->arParams["ALLOW_VIDEO"] != "Y")
			$arAllow["VIDEO"] = "N";

		if($this->arParams["NO_URL_IN_COMMENTS"] == "L" || (intval($arComment["AUTHOR_ID"]) <= 0  && $this->arParams["NO_URL_IN_COMMENTS"] == "A"))
			$arAllow["CUT_ANCHOR"] = "Y";

		if($this->arParams["NO_URL_IN_COMMENTS_AUTHORITY_CHECK"] == "Y" && $arAllow["CUT_ANCHOR"] != "Y" && intval($arComment["AUTHOR_ID"]) > 0)
		{
			$authorityRatingId = CRatings::GetAuthorityRating();
			$arRatingResult = CRatings::GetRatingResult($authorityRatingId, $arComment["AUTHOR_ID"]);
			if($arRatingResult["CURRENT_VALUE"] < $this->arParams["NO_URL_IN_COMMENTS_AUTHORITY"])
				$arAllow["CUT_ANCHOR"] = "Y";
		}

		return $arAllow;
	}

	/**
	 * Loop all comments and mark HIDDEN and SKRINNED to hide them later
	 */
	private function createHiddenCommentsParams()
	{
		foreach ($this->arResult["CommentsResult"] as $level => $comments)
		{
			foreach ($comments as $comment)
			{
				$this->createHiddenCommentParams($comment);
			}
		}
	}

	/**
	 * Match and mark one comment HIDDEN and SKRINNED to hide him later
	 * @param $comment
	 */
	private function createHiddenCommentParams($comment)
	{
		if($this->arResult["Perm"] >= BLOG_PERMS_MODERATE || $this->arParams["BLOG_MODULE_PERMS"] >= "W")
			$this->arResult["Comments"][$comment["ID"]]["SHOW_SCREENNED"] = "Y";

		if(intval($comment["PARENT_ID"]) > 0 && $this->arParams["BLOG_MODULE_PERMS"] < "W")
		{
			$this->arResult["Comments"][$comment["PARENT_ID"]]["CAN_EDIT"] = "N";
			if($this->arResult["Perm"] < BLOG_PERMS_MODERATE)
			{
				if($this->arResult["Comments"][$comment["PARENT_ID"]]["SHOW_AS_HIDDEN"] != "Y" && $comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
					$this->arResult["Comments"][$comment["PARENT_ID"]]["SHOW_AS_HIDDEN"] = "Y";
				else
					$this->arResult["Comments"][$comment["PARENT_ID"]]["SHOW_AS_HIDDEN"] = "N";
			}
		}

		if(intval($comment["AUTHOR_ID"])>0)
		{
			if($comment["AUTHOR_ID"] == $this->arResult["userID"] || $this->arParams["BLOG_MODULE_PERMS"] >= "W")
				$this->arResult["Comments"][$comment["ID"]]["CAN_EDIT"] = "Y";
		}
		else
		{
			if($this->arParams["BLOG_MODULE_PERMS"] >= "W")
				$this->arResult["Comments"][$comment["ID"]]["CAN_EDIT"] = "Y";
		}
	}

	/**
	 * Find hidden comments, and if not need how them - remove from result
	 */
	private function hideHiddenComments()
	{
//		search HIDE COMMENTS
		$bNeedHide = false;
		foreach($this->arResult["Comments"] as $k => $v)
		{
			if($v["SHOW_AS_HIDDEN"] != "Y" && $v["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $v["SHOW_SCREENNED"] != "Y")
			{
				unset($this->arResult["Comments"][$k]);
				$bNeedHide = true;
			}
		}

//		remove HIDE COMMENTS from output
		if($bNeedHide && !empty($this->arResult["CommentsResult"][0]))
		{
			foreach($this->arResult["CommentsResult"][0] as $k => $v)
			{
				if(empty($this->arResult["Comments"][$v["ID"]]))
					unset($this->arResult["CommentsResult"][0][$k]);
			}

			$this->arResult["CommentsResult"][0] = array_values($this->arResult["CommentsResult"][0]);
		}
	}

	private function createCommentsPages()
	{
		global $APPLICATION;

//		match HIDDEN and SCRINNED markers and unset this comments
		$this->createHiddenCommentsParams();
		$this->hideHiddenComments();

		$this->arResult["PAGE_COUNT"] = 0;
		if (
			!empty($this->arResult["CommentsResult"])
			&& is_array($this->arResult["CommentsResult"])
			&& isset($this->arResult["CommentsResult"][0])
			&& count($this->arResult["CommentsResult"][0]) > $this->arParams["COMMENTS_COUNT"])
		{
			$this->arResult["PAGE"] = $this->arParams["PAGEN"];
			if($this->arParams["USE_DESC_PAGING"] == "Y")
			{
				$v1 = floor(count($this->arResult["CommentsResult"][0]) / $this->arParams["COMMENTS_COUNT"]);
				$firstPageCount = count($this->arResult["CommentsResult"][0]) - ($v1 - 1) * $this->arParams["COMMENTS_COUNT"];
			}
			else
			{
				$v1 = ceil(count($this->arResult["CommentsResult"][0]) / $this->arParams["COMMENTS_COUNT"]);
				$firstPageCount = $this->arParams["COMMENTS_COUNT"];
			}

			$this->arResult["PAGE_COUNT"] = $v1;
			if($this->arResult["PAGE"] > $this->arResult["PAGE_COUNT"])
				$this->arResult["PAGE"] = $this->arResult["PAGE_COUNT"];
			if($this->arResult["PAGE_COUNT"] > 1)
			{
				if(intval($this->commentUrlID) > 0)
				{
					function BXBlogSearchParentID($commentID, $arComments)
					{
						if(intval($arComments[$commentID]["PARENT_ID"]) > 0)
						{
							return BXBlogSearchParentID($arComments[$commentID]["PARENT_ID"], $arComments);
						}
						else
							return $commentID;
					}
					$parentCommentId = BXBlogSearchParentID($this->commentUrlID, $this->arResult["Comments"]);

					if(intval($parentCommentId) > 0)
					{
						foreach($this->arResult["CommentsResult"][0] as $k => $v)
						{
							if($v["ID"] == $parentCommentId)
							{
								if($k < $firstPageCount)
									$this->arResult["PAGE"] = 1;
								else
									$this->arResult["PAGE"] = ceil(($k + 1 - $firstPageCount) / $this->arParams["COMMENTS_COUNT"]) + 1;
								break;
							}
						}
					}
				}

				$this->arResult["AllCommentsResult"] = $this->arResult["CommentsResult"][0];
				$this->arResult["PagesComment"] = Array();
//				unset comments not from current page
				$childIdsToDelete = array();	// to collect child not from current page
				foreach($this->arResult["CommentsResult"][0] as $k => $v)
				{
					if($this->arResult["PAGE"] == 1)
					{
						if ($k > ($firstPageCount - 1))
						{
							$childIdsToDelete[] = $this->arResult["CommentsResult"][0][$k]["ID"];
							unset($this->arResult["CommentsResult"][0][$k]);
						}
					}
					else
					{
						if($k >= ($firstPageCount + ($this->arResult["PAGE"]-1)*$this->arParams["COMMENTS_COUNT"]) ||
							$k < ($firstPageCount + ($this->arResult["PAGE"]-2)*$this->arParams["COMMENTS_COUNT"]))
						{
							$childIdsToDelete[] = $this->arResult["CommentsResult"][0][$k]["ID"];
							unset($this->arResult["CommentsResult"][0][$k]);
						}
					}
				}
//				collect subchilds comments not from current page - only if AJAX mode and we need only current page
				if($this->arParams["AJAX_PAGINATION"])
				{
					$childIdsToDelete = $this->searchSubchildComments($childIdsToDelete);
					foreach ($childIdsToDelete as $id)
						unset($this->arResult["CommentsResult"][$id]);
				}

//				sort comments by pages
				for($i = 1; $i <= $this->arResult["PAGE_COUNT"]; $i++)
				{
					foreach($this->arResult["AllCommentsResult"] as $k => $v)
					{
						if($i == 1)
						{
							if($k <= ($firstPageCount-1))
								$this->arResult["PagesComment"][$i][$k] = $v;
						}
						else
						{
							if($k < ($firstPageCount + ($i-1)*$this->arParams["COMMENTS_COUNT"]) && $k >= ($firstPageCount + ($i-2)*$this->arParams["COMMENTS_COUNT"]))
								$this->arResult["PagesComment"][$i][$k] = $v;
						}
					}
				}
				unset($this->arResult["AllCommentsResult"]);

				$this->arResult["NEED_NAV"] = "Y";
				$this->arResult["PAGES"] = Array();
				$this->arResult["NEW_PAGES"] = Array();

				for($i = 1; $i <= $this->arResult["PAGE_COUNT"]; $i++)
				{
					if($i == 1)
						$this->arResult["NEW_PAGES"][$i] = htmlspecialcharsbx($APPLICATION->GetCurPageParam("", Array($this->arParams["NAV_PAGE_VAR"], "commentID"))."#comments");
					else
						$this->arResult["NEW_PAGES"][$i] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($this->arParams["NAV_PAGE_VAR"].'='.$i, array($this->arParams["NAV_PAGE_VAR"], "commentID"))."#comments");

					if($i != $this->arResult["PAGE"])
					{
						if($i == 1)
							$this->arResult["PAGES"][] = '<a href="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam("", Array($this->arParams["NAV_PAGE_VAR"], "commentID"))."#comments").'">'.$i.'</a>&nbsp;&nbsp;';
						else
							$this->arResult["PAGES"][] = '<a href="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam($this->arParams["NAV_PAGE_VAR"].'='.$i, array($this->arParams["NAV_PAGE_VAR"], "commentID"))).'#comments">'.$i.'</a>&nbsp;&nbsp;';
					}
					else
						$this->arResult["PAGES"][] = "<b>".$i."</b>&nbsp;&nbsp;";
				}
			}
		}
	}

	/**
	 * collect subchilds comments not from current page
	 * @param $ids
	 * @return array
	 */
	private function searchSubchildComments($ids)
	{
		if(empty($ids))
			return $ids;

		$subchildIds = array();
		foreach($ids as $id)
		{
			if (array_key_exists($id, $this->arResult["CommentsResult"]))
				foreach($this->arResult["CommentsResult"][$id] as $subchild)
				{
					$subchildIds[] = $subchild["ID"];
				}
		}

		return array_merge($ids, $this->searchSubchildComments($subchildIds));
	}

	protected function markNewComments()
	{
		$this->saveLastPostView();
		foreach ($this->arResult["CommentsResult"] as $comments)
		{
			foreach ($comments as $key => $comment)
			{
				if($this->arResult["lastPostView"] <> '' && $this->arResult["lastPostView"] < MakeTimeStamp($comment["DATE_CREATE"]))
					$this->arResult["Comments"][$comment["ID"]]["NEW"] = "Y";
			}
		}
	}

	protected function saveLastPostView()
	{
		global $stackCacheManager;
		$cacheId = "blog_comment_view_".$this->arResult["userID"];
		$stackCacheManager->SetLength($cacheId, 1000);
		$stackCacheManager->SetTTL($cacheId, 60*60*24*365);
		if ($stackCacheManager->Exist($cacheId, "c".$this->arParams["ID"]))
		{
			$this->arResult["lastPostView"] = $stackCacheManager->Get($cacheId, "c".$this->arParams["ID"]);
		}
		$currTime = time()+CTimeZone::GetOffset();
//		use time from cache or current time if cache is empty
		if(!isset($this->arResult["lastPostView"]))
			$this->arResult["lastPostView"] = $currTime;
//		always save new time in cache
		$stackCacheManager->Set($cacheId, "c".$this->arParams["ID"], $currTime);
	}

	private static function clearBlogCaches($blogUrl, $postId)
	{
		BXClearCache(True, "/".SITE_ID."/blog/".$blogUrl."/first_page/");
		BXClearCache(True, "/".SITE_ID."/blog/".$blogUrl."/pages/");
		BXClearCache(True, "/".SITE_ID."/blog/".$blogUrl."/comment/".$postId."/");
		BXClearCache(True, "/".SITE_ID."/blog/".$blogUrl."/post/".$postId."/");
		BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
		BXClearCache(True, "/".SITE_ID."/blog/".$blogUrl."/rss_out/".$postId."/C/");
		BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
		BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
		BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
	}

	protected function addCaptcha()
	{
		if($this->arResult["use_captcha"])
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$cpt = new CCaptcha();
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if ($captchaPass == '')
			{
				$captchaPass = randString(10);
				COption::SetOptionString("main", "captcha_password", $captchaPass);
			}
			$cpt->SetCodeCrypt($captchaPass);
			$this->arResult["CaptchaCode"] = htmlspecialcharsbx($cpt->GetCodeCrypt());
		}
	}

	public function printPaging($top = true, $useLink = true)
	{
		$paging = "";
		$paging .= '<div class="blog-comment-nav" id="blog-comment-nav-'. ($top ? 't' : 'b') .'">';
		$paging .= GetMessage("BPC_PAGE").' ';
		$id = "blog-comment-nav-";
		$id.= $top ? "t" : "b";
		$navFunc = $this->arParams["AJAX_PAGINATION"] ? 'bcNavAjax' : "bcNav";
		for($i = 1; $i <= $this->arResult["PAGE_COUNT"]; $i++)
		{
			$style = "blog-comment-nav-item";
			if($i == $this->arResult["PAGE"])
				$style .= " blog-comment-nav-item-sel";
			$paging .= '<a class="'.$style.'"';
			$paging .= $useLink ? ' href="'.$this->arResult["NEW_PAGES"][$i].'"' : ' href=""';
			$paging .= ' data-bx-href="'.$this->arResult["NEW_PAGES"][$i].'"';
			$paging .= ' onclick="return '.$navFunc.'(\''.$i.'\', this)" ';
			$paging .= ' id="'.$id.$i.'">'.$i.'</a>&nbsp;&nbsp;';;
		}
		$paging .= "</div>";

		echo $paging;
	}

	public function printCommentPages()
	{
//		only one page for ajax
		if($this->arParams["AJAX_PAGINATION"])
		{
//			strange dirty hack from old template ((
			$this->arParams["arImages"] = $this->arResult["arImages"];
			ob_start();
			?>
			<div id="blog-comment-page">
				<?RecursiveComments($this->arResult["CommentsResult"], $this->arResult["firstLevel"], 0, true, $this->arResult["canModerate"],
					$this->arResult["User"], $this->arResult["use_captcha"], $this->arResult["CanUserComment"],
					$this->arResult["COMMENT_ERROR"], $this->arResult["Comments"], $this->arParams);?>
			</div>
			<?
			echo ob_get_clean();
		}

//		all pages for old-style
		else
		{
			ob_start();
			for($i = 1; $i <= $this->arResult["PAGE_COUNT"]; $i++)
			{
				$tmp = $this->arResult["CommentsResult"];
				$tmp[0] = $this->arResult["PagesComment"][$i];
				?>
				<div id="blog-comment-page-<?=$i?>"<?if($this->arResult["PAGE"] != $i) echo "style=\"display:none;\""?>>
					<?RecursiveComments($tmp, $this->arResult["firstLevel"], 0, true, $this->arResult["canModerate"],
						$this->arResult["User"], $this->arResult["use_captcha"], $this->arResult["CanUserComment"],
						$this->arResult["COMMENT_ERROR"], $this->arResult["Comments"], $this->arParams);?>
				</div>
				<?
			}
			echo ob_get_clean();
		}
	}
}

?>