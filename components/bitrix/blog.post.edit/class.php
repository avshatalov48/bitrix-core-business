<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CBlogPostEdit extends CBitrixComponent
{
	const FILE_CONTROL_ID_PREFIX = 'blogfiles';
	const POST_FORM_PREFIX = 'POST_BLOG_FORM';
	const POST_MESSAGE_PREFIX = "POST_MESSAGE";
	public $userId;
	
	public function onPrepareComponentParams($arParams)
	{
		if (!CModule::IncludeModule("blog"))
		{
			ShowError(Loc::GetMessage("BLOG_MODULE_NOT_INSTALL"));
			return;
		}
		
		global $APPLICATION, $DB;
		
		$arParams["ID"] = intval($arParams["ID"]);
		$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
		if (!is_array($arParams["GROUP_ID"]))
			$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
		foreach ($arParams["GROUP_ID"] as $k => $v)
			if (intval($v) <= 0)
				unset($arParams["GROUP_ID"][$k]);
		
		if ($arParams["BLOG_VAR"] == '')
			$arParams["BLOG_VAR"] = "blog";
		if ($arParams["PAGE_VAR"] == '')
			$arParams["PAGE_VAR"] = "page";
		if ($arParams["USER_VAR"] == '')
			$arParams["USER_VAR"] = "id";
		if ($arParams["POST_VAR"] == '')
			$arParams["POST_VAR"] = "id";
		
		$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
		if ($arParams["PATH_TO_BLOG"] == '')
			$arParams["PATH_TO_BLOG"] = $APPLICATION->GetCurPage() . "?" . htmlspecialcharsbx($arParams["PAGE_VAR"]) . "=blog&" . htmlspecialcharsbx($arParams["BLOG_VAR"]) . "=#blog#";
		
		$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
		if ($arParams["PATH_TO_POST"] == '')
			$arParams["PATH_TO_POST"] = $APPLICATION->GetCurPage() . "?" . htmlspecialcharsbx($arParams["PAGE_VAR"]) . "=post&" . htmlspecialcharsbx($arParams["BLOG_VAR"]) . "=#blog#&" . htmlspecialcharsbx($arParams["POST_VAR"]) . "=#post_id#";
		
		$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
		if ($arParams["PATH_TO_POST_EDIT"] == '')
			$arParams["PATH_TO_POST_EDIT"] = $APPLICATION->GetCurPage() . "?" . htmlspecialcharsbx($arParams["PAGE_VAR"]) . "=post_edit&" . htmlspecialcharsbx($arParams["BLOG_VAR"]) . "=#blog#&" . htmlspecialcharsbx($arParams["POST_VAR"]) . "=#post_id#";
		
		$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
		if ($arParams["PATH_TO_USER"] == '')
			$arParams["PATH_TO_USER"] = $APPLICATION->GetCurPage() . "?" . htmlspecialcharsbx($arParams["PAGE_VAR"]) . "=user&" . htmlspecialcharsbx($arParams["USER_VAR"]) . "=#user_id#";
		
		$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
		if ($arParams["PATH_TO_DRAFT"] == '')
			$arParams["PATH_TO_DRAFT"] = $APPLICATION->GetCurPage() . "?" . htmlspecialcharsbx($arParams["PAGE_VAR"]) . "=draft&" . htmlspecialcharsbx($arParams["BLOG_VAR"]) . "=#blog#";
		
		$arParams["PATH_TO_GROUP_BLOG"] = trim($arParams["PATH_TO_GROUP_BLOG"]);
		if ($arParams["PATH_TO_GROUP_BLOG"] == '')
		{
			$arParams["PATH_TO_GROUP_BLOG"] = "/workgroups/group/#group_id#/blog/";
			if ($arParams["MICROBLOG"])
				$arParams["PATH_TO_GROUP_BLOG"] = "/workgroups/group/#group_id#/microblog/";
		}
		if ($arParams["PATH_TO_GROUP_POST"] == '')
		{
			$arParams["PATH_TO_GROUP_POST"] = "/workgroups/group/#group_id#/blog/#post_id#/";
			if ($arParams["MICROBLOG"])
				$arParams["PATH_TO_GROUP_POST"] = "/workgroups/group/#group_id#/microblog/#post_id#/";
		}
		if ($arParams["PATH_TO_GROUP_POST_EDIT"] == '')
		{
			$arParams["PATH_TO_GROUP_POST_EDIT"] = "/workgroups/group/#group_id#/blog/edit/#post_id#/";
			if ($arParams["MICROBLOG"])
				$arParams["PATH_TO_GROUP_POST_EDIT"] = "/workgroups/group/#group_id#/microblog/edit/#post_id#/?microblog=Y";
		}
		if ($arParams["PATH_TO_GROUP_DRAFT"] == '')
			$arParams["PATH_TO_GROUP_DRAFT"] = "/workgroups/group/#group_id#/blog/draft/";

		if (!is_array($arParams["POST_PROPERTY"]))
			$arParams["POST_PROPERTY"] = Array(CBlogPost::UF_NAME);
		else
			$arParams["POST_PROPERTY"][] = CBlogPost::UF_NAME;
		
		$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);
		$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
		
		$arParams["SMILES_COUNT"] = intval($arParams["SMILES_COUNT"]);
		if (intval($arParams["SMILES_COUNT"]) <= 0)
			$arParams["SMILES_COUNT"] = 5;
		
		$arParams["ALLOW_POST_MOVE"] = ($arParams["ALLOW_POST_MOVE"] == "Y") ? "Y" : "N";
		
		$arParams["IMAGE_MAX_WIDTH"] = $arParams["IMAGE_MAX_WIDTH"] ?
			intval($arParams["IMAGE_MAX_WIDTH"]) :
			COption::GetOptionString("blog", "image_max_width", 500);
		$arParams["IMAGE_MAX_HEIGHT"] = $arParams["IMAGE_MAX_HEIGHT"] ?
			intval($arParams["IMAGE_MAX_HEIGHT"]) :
			COption::GetOptionString("blog", " image_max_height", 500);
//		$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
		
		$arParams["EDITOR_RESIZABLE"] = $arParams["EDITOR_RESIZABLE"] !== "N";
		$arParams["EDITOR_CODE_DEFAULT"] = $arParams["EDITOR_CODE_DEFAULT"] === "Y";
		$arParams["EDITOR_DEFAULT_HEIGHT"] = intval($arParams["EDITOR_DEFAULT_HEIGHT"]);
		if (intval($arParams["EDITOR_DEFAULT_HEIGHT"]) <= 0)
			$arParams["EDITOR_DEFAULT_HEIGHT"] = 300;
		
		$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
		$arParams["USE_GOOGLE_CODE"] = $arParams["USE_GOOGLE_CODE"] === "Y";
		$arParams["SEO_USE"] = ($arParams["SEO_USE"] == "Y") ? "Y" : "N";
		
		$arParams["USE_AUTOSAVE"] = COption::GetOptionString("blog", "use_autosave", "Y");
		
		return $arParams;
	}
	
	public function executeComponent()
	{
		global $USER, $APPLICATION, $DB;
		
		$user_id = $USER->GetID();
		$this->arResult["UserID"] = $user_id;
		$this->setUserId($user_id);
//		check is user consent was given ever
		if($user_id > 0)
			$this->isUserGivenConsent();
			
		$this->arResult["enable_trackback"] = COption::GetOptionString("blog", "enable_trackback", "Y");
		$this->arResult["allowVideo"] = COption::GetOptionString("blog", "allow_video", "Y");
		$blogModulePermissions = $APPLICATION->GetGroupRight("blog");
		
		$this->arResult["preview"] = ($_POST["preview"] <> '') ? "Y" : "N";
		
		$arBlog = CBlog::GetByUrl($this->arParams["BLOG_URL"], $this->arParams["GROUP_ID"]);
		if (intval($this->arParams["ID"]) > 0)
			$this->arResult["perms"] = CBlogPost::GetBlogUserPostPerms($this->arParams["ID"], $this->arResult["UserID"]);
		else
			$this->arResult["perms"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $this->arResult["UserID"]);
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['mfi_mode']) && ($_REQUEST['mfi_mode'] == "upload"))
		{
			CBlogImage::AddImageResizeHandler(array("width" => 400, "height" => 400));
			CBlogImage::AddImageCreateHandler(array('IS_COMMENT' => 'N', 'USER_ID' => $user_id));
		}
		
		if (!empty($arBlog) && $arBlog["ACTIVE"] == "Y")
		{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if ($arGroup["SITE_ID"] == SITE_ID)
			{
				$this->arResult["Blog"] = $arBlog;
				$this->arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));

				$this->arResult["USE_NEW_EDITOR"] = isset($_POST["USE_NEW_EDITOR"]) && $_POST["USE_NEW_EDITOR"] == "Y";

//				change title and ID by CREATE or EDIT post
				if (intval($this->arParams["ID"]) > 0 && $arPost = CBlogPost::GetByID($this->arParams["ID"]))
				{
					$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
					$this->arResult["Post"] = $arPost;
					if ($this->arParams["SET_TITLE"] == "Y" && !$this->arParams["MICROBLOG"])
						$APPLICATION->SetTitle(str_replace("#BLOG#", $arBlog["NAME"], "" . Loc::GetMessage("BLOG_POST_EDIT") . ""));
				}
				else
				{
					$this->arParams["ID"] = 0;
					if ($this->arParams["SET_TITLE"] == "Y" && !$this->arParams["MICROBLOG"])
						$APPLICATION->SetTitle(str_replace("#BLOG#", $arBlog["NAME"], "" . Loc::GetMessage("BLOG_NEW_MESSAGE") . ""));
				}
				
				if (($this->arResult["perms"] >= BLOG_PERMS_MODERATE || ($this->arResult["perms"] >= BLOG_PERMS_PREMODERATE && ($this->arParams["ID"] == 0 || $arPost["AUTHOR_ID"] == $this->arResult["UserID"]))) && (intval($this->arParams["ID"]) <= 0 || $arPost["BLOG_ID"] == $arBlog["ID"]))
				{
					if (intval($this->arParams["ID"]) > 0 && $arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY && $this->arResult["perms"] == BLOG_PERMS_PREMODERATE)
					{
						$this->arResult["MESSAGE"] = Loc::GetMessage("BPE_HIDDEN_POSTED");
					}
					
					if (($_POST["apply"] || $_POST["save"] || $_POST["do_upload"] || $_POST["draft"]) && $this->arResult["preview"] != "Y")
					{
						if (!check_bitrix_sessid())
							$this->arResult["ERROR_MESSAGE"] = Loc::GetMessage("BPE_SESS");
					}

//					todo: maybe can remove "image_upload_frame" part of IF
//					todo: but we NEED process "del_image_id"
					if ($_GET["image_upload_frame"] == "Y" || $_GET["image_upload"] || $_POST["do_upload"] || $_GET["del_image_id"])
					{
						$this->arResult["imageUploadFrame"] = "Y";
						$this->arResult["imageUpload"] = "Y";
						$APPLICATION->RestartBuffer();
						header("Pragma: no-cache");
						
						if (check_bitrix_sessid() || $_REQUEST["sessid"] == '')
						{
//					delete image and file by click X
							if (intval($_GET["del_image_id"]) > 0)
							{
								$del_image_id = intval($_GET["del_image_id"]);
								$aImg = CBlogImage::GetByID($del_image_id);
								if (
									$aImg["BLOG_ID"] == $arBlog["ID"]
									&& $aImg["POST_ID"] == intval($this->arParams["ID"])
								)
								{
									CBlogImage::Delete($del_image_id);
								}
								$APPLICATION->RestartBuffer();
								die();
							}
							
							$arFields = Array();
							if ($_FILES["BLOG_UPLOAD_FILE"]["size"] > 0)
							{
								$arFields = array(
									"BLOG_ID" => $arBlog["ID"],
									"POST_ID" => $this->arParams["ID"],
									"USER_ID" => $this->arResult["UserID"],
									"=TIMESTAMP_X" => $DB->GetNowFunction(),
									"TITLE" => $_POST["IMAGE_TITLE"],
									
									"IMAGE_SIZE" => $_FILES["BLOG_UPLOAD_FILE"]["size"],
								);
								$arImage = array_merge(
									$_FILES["BLOG_UPLOAD_FILE"],
									array(
										"MODULE_ID" => "blog",
										"del" => "Y",
									)
								);
								$arFields["FILE_ID"] = $arImage;
							}
							elseif ($_POST["do_upload"] && $_FILES["FILE_ID"]["size"] > 0)
							{
								$arFields = array(
									"BLOG_ID" => $arBlog["ID"],
									"POST_ID" => $this->arParams["ID"],
									"USER_ID" => $this->arResult["UserID"],
									"=TIMESTAMP_X" => $DB->GetNowFunction(),
									"TITLE" => $_POST["IMAGE_TITLE"],
									"IMAGE_SIZE" => $_FILES["FILE_ID"]["size"],
								);
								$arImage = array_merge(
									$_FILES["FILE_ID"],
									array(
										"MODULE_ID" => "blog",
										"del" => "Y",
									)
								);
								$arFields["FILE_ID"] = $arImage;
							}
							if (!empty($arFields))
							{
								if ($imgID = CBlogImage::Add($arFields))
								{
									$aImg = CBlogImage::GetByID($imgID);
									$aImg = CBlogTools::htmlspecialcharsExArray($aImg);
									
									$iMaxW = 100;
									$iMaxH = 100;
									$aImg["PARAMS"] = CFile::_GetImgParams($aImg["FILE_ID"]);
									$intWidth = $aImg["PARAMS"]['WIDTH'];
									$intHeight = $aImg["PARAMS"]['HEIGHT'];
									if (
										$iMaxW > 0 && $iMaxH > 0
										&& ($intWidth > $iMaxW || $intHeight > $iMaxH)
									)
									{
										$coeff = ($intWidth / $iMaxW > $intHeight / $iMaxH ? $intWidth / $iMaxW : $intHeight / $iMaxH);
										$iHeight = intval(roundEx($intHeight / $coeff));
										$iWidth = intval(roundEx($intWidth / $coeff));
									}
									else
									{
										$iHeight = $intHeight;
										$iWidth = $intWidth;
									}
									
									$file = "<img src=\"" . $aImg["PARAMS"]["SRC"] . "\" width=\"" . $iWidth . "\" height=\"" . $iHeight . "\" id=\"" . $aImg["ID"] . "\" border=\"0\" style=\"cursor:pointer\" onclick=\"InsertBlogImage('" . $aImg["ID"] . "', '" . $aImg["PARAMS"]['WIDTH'] . "');\" title=\"" . Loc::GetMessage("BLOG_P_INSERT") . "\">";
									
									$file = str_replace("'", "\'", $file);
									$file = str_replace("\r", " ", $file);
									$file = str_replace("\n", " ", $file);
									$this->arResult["ImageModified"] = $file;
									$this->arResult["Image"] = $aImg;
								}
								else
								{
									if ($ex = $APPLICATION->GetException())
										$this->arResult["ERROR_MESSAGE"] = $ex->GetString();
								}
							}
						}
					}
					else
					{
						if (($_POST["apply"] || $_POST["save"]) && $this->arResult["preview"] != "Y" && empty($_POST["reset"])) // Save on button click
						{
							if (check_bitrix_sessid())
							{
								if ($this->arResult["ERROR_MESSAGE"] == '')
								{
									$TRACKBACK = trim($_POST["TRACKBACK"]);
									InitBVar($_POST["ENABLE_TRACKBACK"]);
									
									$categoriesIds = $this->getCategoriesIds($arBlog);
									$datePublish = $this->getDatePublish();
									$publishStatus = $this->getPublishStatus($this->arResult["perms"]);

//									FIELDS for create or uprate blog post
									$arFields = array(
										"TITLE" => trim($_POST["POST_TITLE"]),
										"DETAIL_TEXT" => trim(($_POST["POST_MESSAGE_TYPE"] == "html") ? $_POST["POST_MESSAGE_HTML"] : $_POST["POST_MESSAGE"]),
										"DETAIL_TEXT_TYPE" => "text",
										"DATE_PUBLISH" => $datePublish,
										"PUBLISH_STATUS" => $publishStatus,
										"ENABLE_TRACKBACK" => $_POST["ENABLE_TRACKBACK"],
										"ENABLE_COMMENTS" => ($_POST["ENABLE_COMMENTS"] == "N") ? "N" : "Y",
										"CATEGORY_ID" => implode(",", $categoriesIds),
										"FAVORITE_SORT" => (intval($_POST["FAVORITE_SORT"]) > 0) ? intval($_POST["FAVORITE_SORT"]) : 0,
										"ATTACH_IMG" => "",
										"PATH" => CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($this->arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"])),
										"URL" => $arBlog["URL"],
									);
									
									$arFields = array_merge($arFields, $this->setSeoFields());
									$arFields = array_merge($arFields, $this->setCodeFields($arBlog));
									$arFields = array_merge($arFields, $this->setPermsFields());
									$arFields = array_merge($arFields, $this->setMicroblogFields($arFields["DETAIL_TEXT"], Loc::GetMessage("BLOG_EMPTY_TITLE_PLACEHOLDER")));
									
									if ($_POST["POST_MESSAGE_TYPE"] == "html" && $_POST["POST_MESSAGE_HTML"] == '')
										$arFields["DETAIL_TEXT"] = $_POST["POST_MESSAGE"];
									
//									update or delete images to OLD EDITOR (by POST fields)
									if(!$this->arResult["USE_NEW_EDITOR"] && is_array($_POST["IMAGE_ID_title"]))
									{
										$processImagesResult = $this->processImagesOldVersion($arBlog);
										foreach($processImagesResult["DELETED_IMAGES"] as $imgID)
											$arFields["DETAIL_TEXT"] = str_replace("[IMG ID=$imgID]","",$arFields["DETAIL_TEXT"]);
									}
									
//									PARSE FILES: compare uploaded files, existing files and session
//									find images then need to attach to post, find not-imaged files
									if (isset($GLOBALS[CBlogPost::UF_NAME]) && is_array($GLOBALS[CBlogPost::UF_NAME]))
									{
										$parseFilesResult = $this->parseFilesArray();
										
										$arAttachedFiles = $parseFilesResult['ATTACHED_FILES'];
										$imagesToAttach = $parseFilesResult['IMAGES_TO_ATTACH'];
										$toReplaceInText = $parseFilesResult['TO_REPLACE'];

//										update user fields by new files
										$GLOBALS[CBlogPost::UF_NAME] = $arAttachedFiles;
										
										if (!empty($toReplaceInText['SEARCH']) && !empty($toReplaceInText['REPLACE']))
											$arFields["DETAIL_TEXT"] = str_replace($toReplaceInText['SEARCH'], $toReplaceInText['REPLACE'], $arFields["DETAIL_TEXT"]);
									}

//									update USER FILEDS
									if (count($this->arParams["POST_PROPERTY"]) > 0)
										$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_POST", $arFields);

//									UPDATE exist or SAVE new post
									$saveOrUpdateResult = $this->saveOrUpdatePost($arFields, $arBlog);
									$newID = $saveOrUpdateResult['ID'];
									$arFields = $saveOrUpdateResult['FIELDS'];
									$bAdd = $saveOrUpdateResult['ADD_RESULT'];
									$arOldPost = $saveOrUpdateResult['OLD_POST'];
									$this->arResult["ERROR_MESSAGE"] = $saveOrUpdateResult['ERROR_MSG'] <> '' ?
										$saveOrUpdateResult['ERROR_MSG'] :
										$this->arResult["ERROR_MESSAGE"];
									
									if (intval($newID) > 0)
									{
										CBlogPostCategory::DeleteByPostID($newID);
										foreach ($categoriesIds as $v)
											CBlogPostCategory::Add(Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $newID, "CATEGORY_ID" => $v));
										
//										attach images with POST_ID = 0 to this post
										if (!empty($imagesToAttach))
										{
											foreach ($imagesToAttach as $imageId)
												CBlogImage::Update($imageId, array("POST_ID" => $newID, "BLOG_ID" => $arBlog["ID"]));
										}
										
										if ($TRACKBACK <> '')
										{
											$arPingUrls = explode("\n", $TRACKBACK);
											CBlogTrackback::SendPing($newID, $arPingUrls);
										}
									}
									
//									move/copy post to another blog
									if (intval($newID) > 0 && intval($_POST["move2blog"]) > 0 && $this->arParams["ALLOW_POST_MOVE"] == "Y")
									{
										$copyOrMoveResult = $this->copyOrMovePost($arBlog);
										$this->arResult["ERROR_MESSAGE"] = $copyOrMoveResult['ERROR_MSG'] <> '' ?
											$copyOrMoveResult['ERROR_MSG'] :
											$this->arResult["ERROR_MESSAGE"];
										
										$pathTemplate = $copyOrMoveResult['PATHES']["TEMPLATE"];
										$pathTemplateEdit = $copyOrMoveResult['PATHES']["TEMPLATE_EDIT"];
										$pathTemplateDraft = $copyOrMoveResult['PATHES']["TEMPLATE_DRAFT"];
										$pathTemplateBlog = $copyOrMoveResult['PATHES']["TEMPLATE_BLOG"];
										
										$arCopyPost = $copyOrMoveResult['COPY_POST'];
										$arCopyBlog = $copyOrMoveResult['COPY_BLOG'];
										$copyID = $copyOrMoveResult['COPY_ID'];
									}

//									NOTIFY for NEW post
									if (
										(
											($bAdd && $newID && $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
											|| ($arOldPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
										)
										&& (
											intval($copyID) <= 0
											|| (intval($copyID) > 0 && $_POST["move2blogcopy"] == "Y")
										)
									)
									{
										$arFields["ID"] = $newID;
										$arParamsNotify = Array(
											"bSoNet" => false,
											"UserID" => $this->arResult["UserID"],
											"allowVideo" => $this->arResult["allowVideo"],
											"bGroupMode" => false,
											"PATH_TO_SMILE" => $this->arParams["PATH_TO_SMILE"],
											"PATH_TO_POST" => $this->arParams["PATH_TO_POST"],
											"user_id" => $user_id,
											"NAME_TEMPLATE" => $this->arParams["NAME_TEMPLATE"],
											"SHOW_LOGIN" => $this->arParams["SHOW_LOGIN"],
										);
										
										CBlogPost::Notify($arFields, $arBlog, $arParamsNotify);
										
										if (COption::GetOptionString("blog", "send_blog_ping", "N") == "Y")
										{
											if (!isset($serverName) || $serverName == '')
											{
												$serverName = ((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
												if ($serverName == '')
													$serverName = $_SERVER["SERVER_NAME"];
											}
											
											$blogUrl = "http://" . $serverName . CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($this->arParams["PATH_TO_BLOG"]), array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
											CBlog::SendPing($arBlog["NAME"], $blogUrl);
										}
									}

//									NOTIFY for COPY post
									if (isset($copyID) && intval($copyID) > 0 && $arCopyPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
									{
										$arCopyPost["ID"] = $copyID;
										$arParamsNotify = Array(
											"bSoNet" => false,
											"UserID" => $this->arResult["UserID"],
											"allowVideo" => $this->arResult["allowVideo"],
											"bGroupMode" => false,
											"PATH_TO_SMILE" => $this->arParams["PATH_TO_SMILE"],
											"PATH_TO_POST" => $pathTemplate,
											"user_id" => $user_id,
											"MICROBLOG" => ($this->arParams["MICROBLOG"]) ? "Y" : "N",
										);
										
										CBlogPost::Notify($arCopyPost, $arCopyBlog, $arParamsNotify);
										
										if (COption::GetOptionString("blog", "send_blog_ping", "N") == "Y")
										{
											if ($serverName == '')
											{
												$serverName = ((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
												if ($serverName == '')
													$serverName = $_SERVER["SERVER_NAME"];
											}
											
											$blogUrl = "http://" . $serverName . CComponentEngine::MakePathFromTemplate($pathTemplateBlog, array("blog" => $arCopyBlog["URL"], "user_id" => $arCopyBlog["OWNER_ID"]));
											CBlog::SendPing($arCopyBlog["NAME"], $blogUrl);
										}
									}

//									SAVE success - redirect
									if ($newID > 0 && $this->arResult["ERROR_MESSAGE"] == '') // Record saved successfully
									{
										$this->arParams["ID"] = $newID;
										$this->clearBlogCache($arBlog);
										
										if (intval($copyID) > 0 && $_POST["move2blogcopy"] != "Y")
										{
											if ($_POST["apply"] == '')
											{
												if ($arCopyPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT || $_POST["draft"] <> '')
													$redirectUrl = CComponentEngine::MakePathFromTemplate($pathTemplateDraft, array("blog" => $arCopyBlog["URL"], "user_id" => $arCopyBlog["OWNER_ID"]));
												elseif ($arCopyPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
													$redirectUrl = CComponentEngine::MakePathFromTemplate($pathTemplateEdit, array("blog" => $arCopyBlog["URL"], "post_id" => $copyID, "user_id" => $arCopyBlog["OWNER_ID"]));
												else
													$redirectUrl = CComponentEngine::MakePathFromTemplate($pathTemplateBlog, array("blog" => $arCopyBlog["URL"], "user_id" => $arCopyBlog["OWNER_ID"]));
											}
											else
												$redirectUrl = CComponentEngine::MakePathFromTemplate($pathTemplateEdit, array("blog" => $arCopyBlog["URL"], "post_id" => $copyID, "user_id" => $arCopyBlog["OWNER_ID"]));
										}
										else
										{
											if ($_POST["apply"] == '')
											{
												if ($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT || $_POST["draft"] <> '')
													$redirectUrl = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_DRAFT"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
												elseif ($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY)
													$redirectUrl = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id" => $newID, "user_id" => $arBlog["OWNER_ID"]));
												else
												{
													$redirectUrl = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
												}
											}
											else
												$redirectUrl = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id" => $newID, "user_id" => $arBlog["OWNER_ID"]));
										}
										LocalRedirect($redirectUrl);
									}
									else
									{
										if ($this->arResult["ERROR_MESSAGE"] == '')
										{
											if ($ex = $APPLICATION->GetException())
												$this->arResult["ERROR_MESSAGE"] = $ex->GetString() . "<br />";
											else
												$this->arResult["ERROR_MESSAGE"] = "Error saving data to database.<br />";
										}
									}
								}
							}
							else
								$this->arResult["ERROR_MESSAGE"] = Loc::GetMessage("BPE_SESS");
						}
						elseif ($_POST["reset"])
						{
							if (isset($arFields) && $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT)
								LocalRedirect(CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_DRAFT"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"])));
							else
								LocalRedirect(CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"])));
						}

//						SHOW
						if ($this->arParams["ID"] > 0 && $this->arResult["ERROR_MESSAGE"] == '' && $this->arResult["preview"] != "Y") // Edit post
						{
							$postToShowParams = $this->initPostToShowExistPost($arPost, $arBlog);
							$this->arResult["PostToShow"] = is_array($this->arResult["PostToShow"]) ?
								array_merge($this->arResult["PostToShow"], $postToShowParams) :
								$postToShowParams;
							
							if ($this->arParams["ALLOW_POST_MOVE"] == "Y")
							{
								//copy or move post to another blog
//								FINT other blogs TO MOVE
								$this->arResult["avBlog"] = $this->getAvBlogs($arBlog, $blogModulePermissions);
								foreach ($this->arResult["avBlog"] as $id => $blog)
								{
									$this->arResult["avBlogCategory"]["users_" . $blog["GROUP_ID"]][$id] = $blog;
								}
							}
						}
						else
						{
							$postToShowParams = $this->initPostToShowPreview($arBlog);
							$this->arResult["PostToShow"] = is_array($this->arResult["PostToShow"]) ?
								array_merge($this->arResult["PostToShow"], $postToShowParams) :
								$postToShowParams;
						}
						$this->arResult["BLOG_POST_PERMS"] = $GLOBALS["AR_BLOG_POST_PERMS"];
						$this->arResult["BLOG_COMMENT_PERMS"] = $GLOBALS["AR_BLOG_COMMENT_PERMS"];

//						set RIGHTS
						if (!$USER->IsAdmin() && $blogModulePermissions < "W")
						{
							$this->arResult["post_everyone_max_rights"] = COption::GetOptionString("blog", "post_everyone_max_rights", "");
							$this->arResult["comment_everyone_max_rights"] = COption::GetOptionString("blog", "comment_everyone_max_rights", "");
							$this->arResult["post_auth_user_max_rights"] = COption::GetOptionString("blog", "post_auth_user_max_rights", "");
							$this->arResult["comment_auth_user_max_rights"] = COption::GetOptionString("blog", "comment_auth_user_max_rights", "");
							$this->arResult["post_group_user_max_rights"] = COption::GetOptionString("blog", "post_group_user_max_rights", "");
							$this->arResult["comment_group_user_max_rights"] = COption::GetOptionString("blog", "comment_group_user_max_rights", "");
							
							foreach ($this->arResult["BLOG_POST_PERMS"] as $v)
							{
								if ($this->arResult["post_everyone_max_rights"] <> '' && $v <= $this->arResult["post_everyone_max_rights"])
									$this->arResult["ar_post_everyone_rights"][] = $v;
								if ($this->arResult["post_auth_user_max_rights"] <> '' && $v <= $this->arResult["post_auth_user_max_rights"])
									$this->arResult["ar_post_auth_user_rights"][] = $v;
								if ($this->arResult["post_group_user_max_rights"] <> '' && $v <= $this->arResult["post_group_user_max_rights"])
									$this->arResult["ar_post_group_user_rights"][] = $v;
							}
							
							foreach ($this->arResult["BLOG_COMMENT_PERMS"] as $v)
							{
								if ($this->arResult["comment_everyone_max_rights"] <> '' && $v <= $this->arResult["comment_everyone_max_rights"])
									$this->arResult["ar_comment_everyone_rights"][] = $v;
								if ($this->arResult["comment_auth_user_max_rights"] <> '' && $v <= $this->arResult["comment_auth_user_max_rights"])
									$this->arResult["ar_comment_auth_user_rights"][] = $v;
								if ($this->arResult["comment_group_user_max_rights"] <> '' && $v <= $this->arResult["comment_group_user_max_rights"])
									$this->arResult["ar_comment_group_user_rights"][] = $v;
							}
						}
						
						$this->arResult["UserGroups"] = array();
						$res = CBlogUserGroup::GetList(array(), $arFilter = array("BLOG_ID" => $arBlog["ID"]));
						while ($aUGroup = $res->GetNext())
							$this->arResult["UserGroups"][] = $aUGroup;
						
						$this->arResult["Smiles"] = $this->getSmilesParams();
						$this->arResult["SmilesCount"] = count($this->arResult["Smiles"]);

//						get IMAGES and attached FILES
						$this->arResult["Images"] = Array();
						$this->arResult["Files"] = Array();
						if (!empty($arBlog))
						{
							$arFilter = array(
								"POST_ID" => $this->arParams["ID"],
								"BLOG_ID" => $arBlog["ID"],
								"IS_COMMENT" => "N",
							);
							if ($this->arParams["ID"] == 0)
								$arFilter["USER_ID"] = $this->arResult["UserID"];
							
							$iMaxW = 100;
							$iMaxH = 100;
							$res = CBlogImage::GetList(array("ID" => "ASC"), $arFilter);
							while ($aImg = $res->GetNext(true, false))
							{
								$aImg = CBlogTools::htmlspecialcharsExArray($aImg);
								$aImg = array_merge(CFile::GetfileArray($aImg["FILE_ID"]), $aImg);
								$aImg["PARAMS"] = CFile::_GetImgParams($aImg["FILE_ID"]);
								$intWidth = $aImg["PARAMS"]['WIDTH'];
								$intHeight = $aImg["PARAMS"]['HEIGHT'];
								if (
									$iMaxW > 0 && $iMaxH > 0
									&& ($intWidth > $iMaxW || $intHeight > $iMaxH)
								)
								{
									$coeff = ($intWidth / $iMaxW > $intHeight / $iMaxH ? $intWidth / $iMaxW : $intHeight / $iMaxH);
									$iHeight = intval(roundEx($intHeight / $coeff));
									$iWidth = intval(roundEx($intWidth / $coeff));
								}
								else
								{
									$iHeight = $intHeight;
									$iWidth = $intWidth;
								}
								
								$aImg["FileShow"] = "<img src=\"" . $aImg["PARAMS"]["SRC"] . "\" width=\"" . $iWidth . "\" height=\"" . $iHeight . "\" id=\"" . $aImg["ID"] . "\" border=\"0\" style=\"cursor:pointer\" onclick=\"InsertBlogImage('" . $aImg["ID"] . "', '" . $aImg["PARAMS"]['WIDTH'] . "');\" title=\"" . Loc::GetMessage("BLOG_P_INSERT") . "\">";
								$aImg["DEL_URL"] = $APPLICATION->GetCurPageParam(
									"del_image_id=" . $aImg["ID"] . "&" . bitrix_sessid_get(),
									Array("sessid", "image_upload_frame", "image_upload", "do_upload", "del_image_id"));

//								create THUMBNAIL
								$aImg["THUMBNAIL"] = CFile::ResizeImageGet(
									$aImg["FILE_ID"],
									array("width" => 90, "height" => 90),
									BX_RESIZE_IMAGE_EXACT,
									true
								);
								
								$this->arResult["Images"][] = $aImg;
//								$this->arResult["Files"][$aImg["FILE_ID"]] = $aImg["FILE_ID"];
							}
						}

//						prapare CATEGORIES TEXT
						if (mb_strpos($this->arResult["PostToShow"]["CATEGORY_ID"], ",") !== false)
							$this->arResult["PostToShow"]["CATEGORY_ID"] = explode(",", trim($this->arResult["PostToShow"]["CATEGORY_ID"]));
						$this->arResult["Category"] = Array();
						if ($this->arResult["PostToShow"]["CategoryText"] == '')
						{
							$res = CBlogCategory::GetList(array("NAME" => "ASC"), array("BLOG_ID" => $arBlog["ID"]));
							while ($arCategory = $res->GetNext())
							{
								if (is_array($this->arResult["PostToShow"]["CATEGORY_ID"]))
								{
									if (in_array($arCategory["ID"], $this->arResult["PostToShow"]["CATEGORY_ID"]))
										$arCategory["Selected"] = "Y";
								}
								else
								{
									if (intval($arCategory["ID"]) == intval($this->arResult["PostToShow"]["CATEGORY_ID"]))
										$arCategory["Selected"] = "Y";
								}
								if ($arCategory["Selected"] == "Y")
									$this->arResult["PostToShow"]["CategoryText"] .= $arCategory["~NAME"] . ", ";
								
								$this->arResult["Category"][$arCategory["ID"]] = $arCategory;
							}
							$this->arResult["PostToShow"]["CategoryText"] = mb_substr($this->arResult["PostToShow"]["CategoryText"], 0, mb_strlen($this->arResult["PostToShow"]["CategoryText"]) - 2);
						}
						
//						set PROPERTIES from user fields
						$this->arResult["POST_PROPERTIES"] = array("SHOW" => "N");
						if (!empty($this->arParams["POST_PROPERTY"]))
						{
							$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $this->arParams["ID"], LANGUAGE_ID);
							
							if (count($this->arParams["POST_PROPERTY"]) > 0)
							{
								foreach ($arPostFields as $FIELD_NAME => $arPostField)
								{
									if (!in_array($FIELD_NAME, $this->arParams["POST_PROPERTY"]))
										continue;
									$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
									$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
									$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
									if ($this->arResult["ERROR_MESSAGE"] <> '' && !empty($_POST[$FIELD_NAME]))
									{
										$arPostField["VALUE"] = $_POST[$FIELD_NAME];
									}
									if($imageMsxSize = intval(COption::GetOptionString('blog', 'image_max_size')))
									{
										if($imageMsxSize > 0)
											$arPostField["SETTINGS"]["MAX_ALLOWED_SIZE"] = $imageMsxSize;
									}
									$this->arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
								}
							}
							if (!empty($this->arResult["POST_PROPERTIES"]["DATA"]))
								$this->arResult["POST_PROPERTIES"]["SHOW"] = "Y";
						}
						
						$this->arResult["CUR_PAGE"] = htmlspecialcharsbx(urlencode($APPLICATION->GetCurPageParam()));
						
						$serverName = ((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
						if ($serverName == '')
							$serverName = $_SERVER["HTTP_HOST"];
						$serverName = "http://" . $serverName;
						
						$this->arResult["PATH_TO_POST"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($this->arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"]));
						$this->arResult["PATH_TO_POST1"] = $serverName.mb_substr($this->arResult["PATH_TO_POST"], 0, mb_strpos($this->arResult["PATH_TO_POST"], "#post_id#"));
						$this->arResult["PATH_TO_POST2"] = mb_substr($this->arResult["PATH_TO_POST"], mb_strpos($this->arResult["PATH_TO_POST"], "#post_id#") + mb_strlen("#post_id#"));
						
						if ($this->arResult["preview"] == "Y")
						{
							if (check_bitrix_sessid())
							{
								$this->arResult["postPreview"]["TITLE"] = $this->arResult["PostToShow"]["TITLE"];
								$this->arResult["postPreview"]["CATEGORY_ID"] = $this->arResult["PostToShow"]["CATEGORY_ID"];
								$this->arResult["postPreview"]["DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html") ? $_POST["POST_MESSAGE_HTML"] : ($_POST["POST_MESSAGE"]));
								$this->arResult["postPreview"]["POST_MESSAGE_TYPE"] = htmlspecialcharsEx($_POST["POST_MESSAGE_TYPE"]);
								$this->arResult["postPreview"]["DATE_PUBLISH"] = $this->arResult["PostToShow"]["DATE_PUBLISH"];
								$this->arResult["postPreview"]["DATE_PUBLISH_FORMATED"] = FormatDate($this->arParams["DATE_TIME_FORMAT"], MakeTimeStamp($this->arResult["postPreview"]["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
								$this->arResult["postPreview"]["DATE_PUBLISH_DATE"] = ConvertDateTime($this->arResult["postPreview"]["DATE_PUBLISH"], FORMAT_DATE);
								$this->arResult["postPreview"]["DATE_PUBLISH_TIME"] = ConvertDateTime($this->arResult["postPreview"]["DATE_PUBLISH"], "HH:MI");
								$this->arResult["postPreview"]["DATE_PUBLISH_D"] = ConvertDateTime($this->arResult["postPreview"]["DATE_PUBLISH"], "DD");
								$this->arResult["postPreview"]["DATE_PUBLISH_M"] = ConvertDateTime($this->arResult["postPreview"]["DATE_PUBLISH"], "MM");
								$this->arResult["postPreview"]["DATE_PUBLISH_Y"] = ConvertDateTime($this->arResult["postPreview"]["DATE_PUBLISH"], "YYYY");
								$this->arResult["postPreview"]["FAVORITE_SORT"] = htmlspecialcharsEx($this->arResult["FAVORITE_SORT"]);
								if ($_POST["POST_MESSAGE_TYPE"] == "html" && $_POST["POST_MESSAGE_HTML"] == '')
								{
									$this->arResult["postPreview"]["DETAIL_TEXT"] = htmlspecialcharsEx($_POST["POST_MESSAGE"]);
									$this->arResult["postPreview"]["~DETAIL_TEXT"] = $_POST["POST_MESSAGE"];
								}
								
								if (!empty($_POST["CATEGORY_ID"]))
								{
									foreach ($_POST["CATEGORY_ID"] as $v)
									{
										
										if (mb_substr($v, 0, 4) == "new_")
											$this->arResult["Category"][$v] = Array("ID" => $v, "NAME" => mb_substr($v, 4), "Selected" => "Y");
									}
								}
								
								$p = new blogTextParser(false, $this->arParams["PATH_TO_SMILE"]);
								$arParserParams = Array(
									"imageWidth" => $this->arParams["IMAGE_MAX_WIDTH"],
									"imageHeight" => $this->arParams["IMAGE_MAX_HEIGHT"],
								);
								
								$res = CBlogImage::GetList(array("ID" => "ASC"), array("POST_ID" => $arPost['ID'], "BLOG_ID" => $arBlog['ID'], "IS_COMMENT" => "N"));
								$arImages = array();
								while ($arImage = $res->Fetch())
									$arImages[$arImage['ID']] = $arImage['FILE_ID'];
								
								if ($this->arResult["postPreview"]["POST_MESSAGE_TYPE"] == "html" && $this->arResult["allowHTML"] == "Y")
								{
									$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
									if ($this->arResult["allowVideo"] != "Y")
										$arAllow["VIDEO"] = "N";
									
									$this->arResult["postPreview"]["textFormated"] = $p->convert($this->arResult["postPreview"]["~DETAIL_TEXT"], false, $arImages, $arAllow, $arParserParams);
								}
								else
								{
									$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
									if ($this->arResult["allowVideo"] != "Y")
										$arAllow["VIDEO"] = "N";
									$this->arResult["postPreview"]["textFormated"] = $p->convert($this->arResult["postPreview"]["DETAIL_TEXT"], false, $arImages, $arAllow, $arParserParams);
								}
								$this->arResult["postPreview"]["BlogUser"] = CBlogUser::GetByID($this->arResult["UserID"], BLOG_BY_USER_ID);
								$this->arResult["postPreview"]["BlogUser"] = CBlogTools::htmlspecialcharsExArray($this->arResult["postPreview"]["BlogUser"]);
								$dbUser = CUser::GetByID($this->arResult["UserID"]);
								$this->arResult["postPreview"]["arUser"] = $dbUser->GetNext();
								$this->arResult["postPreview"]["AuthorName"] = CBlogUser::GetUserName($this->arResult["postPreview"]["BlogUser"]["ALIAS"], $this->arResult["postPreview"]["arUser"]["NAME"], $this->arResult["postPreview"]["arUser"]["LAST_NAME"], $this->arResult["postPreview"]["arUser"]["LOGIN"]);
								
								$this->arResult["postPreview"]["BlogUser"]["AVATAR_file"] = CFile::GetFileArray($this->arResult["postPreview"]["BlogUser"]["AVATAR"]);
								if ($this->arResult["postPreview"]["BlogUser"]["AVATAR_file"] !== false)
								{
									$this->arResult["postPreview"]["BlogUser"]["Avatar_resized"] = CFile::ResizeImageGet(
										$this->arResult["postPreview"]["BlogUser"]["AVATAR_file"],
										array("width" => 100, "height" => 100),
										BX_RESIZE_IMAGE_EXACT,
										false
									);
									
									$this->arResult["postPreview"]["BlogUser"]["AVATAR_img"] = CFile::ShowImage($this->arResult["postPreview"]["BlogUser"]["Avatar_resized"]["src"], 100, 100, "border=0 align='right'");
								}
								
								if ($this->arResult["PostToShow"]["CategoryText"] <> '')
								{
									$arCatTmp = explode(",", $this->arResult["PostToShow"]["CategoryText"]);
									if (is_array($arCatTmp))
									{
										foreach ($arCatTmp as $v)
											$this->arResult["postPreview"]["Category"][] = Array("NAME" => htmlspecialcharsbx(trim($v)));
									}
								}
								elseif ($this->arResult["postPreview"]["CATEGORY_ID"] <> '')
								{
									foreach ($this->arResult["postPreview"]["CATEGORY_ID"] as $v)
									{
										if ($v <> '')
										{
											$this->arResult["postPreview"]["Category"][] = $this->arResult["Category"][$v];
										}
									}
								}
							}
							else
								$this->arResult["preview"] = "N";
						}
					}
				}
				else
					$this->arResult["FATAL_MESSAGE"] = Loc::GetMessage("BLOG_ERR_NO_RIGHTS");
			}
			else
			{
				$this->arResult["FATAL_MESSAGE"] = Loc::GetMessage("B_B_MES_NO_BLOG");
				CHTTP::SetStatus("404 Not Found");
			}
		}
		else
		{
			$this->arResult["FATAL_MESSAGE"] = Loc::GetMessage("B_B_MES_NO_BLOG");
			CHTTP::SetStatus("404 Not Found");
		}
		
		$this->IncludeComponentTemplate();
	}
	
	private function setUserId($userId)
	{
		$this->userId = $userId;
	}
	
	public function createPostFormId()
	{
		return self::POST_FORM_PREFIX;
	}
	
	public function createEditorId()
	{
		return self::POST_MESSAGE_PREFIX;
	}
	
	private function processImagesOldVersion($arBlog)
	{
		$deletedIds = array();
		
		foreach($_POST["IMAGE_ID_title"] as $imgID => $imgTitle)
		{
			$aImg = CBlogImage::GetByID($imgID);
			$aImg = CBlogTools::htmlspecialcharsExArray($aImg);
			if (($aImg["BLOG_ID"]==$arBlog["ID"]) && $aImg["POST_ID"]==$this->arParams["ID"])
			{
				if ($_POST["IMAGE_ID_del"][$imgID])
				{
					if(CBlogImage::Delete($imgID));
						$deletedIds[] = $imgID;
				}
				else
				{
					CBlogImage::Update($imgID, array("TITLE"=>$imgTitle));
				}
			}
		}
		
		return array('DELETED_IMAGES' => $deletedIds);
	}
	
	private function parseFilesArray()
	{
		$existingFiles = array();
		if ($this->arParams["ID"] > 0 && $_POST["blog_upload_cid"] == '')
		{
			$dbP = CBlogPost::GetList(array(), array("ID" => $this->arParams["ID"]), false, false, array("ID", "UF_BLOG_POST_DOC"));
			if ($arP = $dbP->Fetch())
			{
				$existingFiles = $arP["UF_BLOG_POST_DOC"];
			}
		}
		
		$imagesToAttach = array();    // images ids to attach them to blog post
		$arAttachedFiles = array();
		$toReplaseInText = array('SEARCH' => array(), 'REPLACE' => array());
		$notAttachedImages = $this->getNotAttachedFiles(true);
		foreach ($GLOBALS[CBlogPost::UF_NAME] as $fileID)
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
	
	private function getCategoryIdsByPostNames($blogParams)
	{
		$categoriesIds = array();
		$arCatBlog = array();
		
		$dbCategory = CBlogCategory::GetList(Array(), Array("BLOG_ID" => $blogParams["ID"]));
		while ($category = $dbCategory->Fetch())
		{
			$arCatBlog[mb_strtolower($category["NAME"])] = $category["ID"];
		}
		$tags = explode(",", $_POST["TAGS"]);
		foreach ($tags as $tg)
		{
			$tg = trim($tg);
			if (!in_array($arCatBlog[mb_strtolower($tg)], $categoriesIds))
			{
				if (intval($arCatBlog[mb_strtolower($tg)]) > 0)
				{
					$categoriesIds[] = $arCatBlog[mb_strtolower($tg)];
				}
				else
				{
					$categoriesIds[] = CBlogCategory::Add(array("BLOG_ID" => $blogParams["ID"], "NAME" => $tg));
					BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/category/");
				}
			}
		}
		
		return $categoriesIds;
	}
	
	private function getCategoryIdsByPostIds($blogParams)
	{
		$categoriesIds = array();
		foreach ($_POST["CATEGORY_ID"] as $v)
		{
			if (mb_substr($v, 0, 4) == "new_")
			{
				$categoriesIds[] = CBlogCategory::Add(array("BLOG_ID" => $blogParams["ID"], "NAME" => mb_substr($v, 4)));
				BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/category/");
			}
			else
				$categoriesIds[] = $v;
		}
		
		return $categoriesIds;
	}
	
	private function getCategoriesIds($blogParams)
	{
		$categoriesIds = Array();
		if (!empty($_POST["TAGS"]))
			$categoriesIds = array_merge($categoriesIds, $this->getCategoryIdsByPostNames($blogParams));
		elseif (!empty($_POST["CATEGORY_ID"]))
			$categoriesIds = array_merge($categoriesIds, $this->getCategoryIdsByPostIds($blogParams));
		
		return $categoriesIds;
	}
	
	private function getDatePublish()
	{
		if ($_POST["DATE_PUBLISH_DEF"] <> '')
			$DATE_PUBLISH = $_POST["DATE_PUBLISH_DEF"];
		elseif ($_POST["DATE_PUBLISH"] == '')
			$DATE_PUBLISH = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL");
		else
			$DATE_PUBLISH = $_POST["DATE_PUBLISH"];
		
		return $DATE_PUBLISH;
	}
	
	private function getPublishStatus($perms)
	{
//		default - draft
		$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_DRAFT;
		
		if ($_POST["draft"] <> '' || $_POST["PUBLISH_STATUS"] == "D")
			$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_DRAFT;
		elseif ($perms == BLOG_PERMS_PREMODERATE)
			$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_READY;
		elseif ($_POST["PUBLISH_STATUS"] == '' || $_POST["PUBLISH_STATUS"] == "P")
			$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_PUBLISH;
		
		return $PUBLISH_STATUS;
	}
	
	private function setSeoFields()
	{
		$arFields = array();
		if ($this->arParams["SEO_USE"] == "Y")
		{
			$arFields["SEO_TITLE"] = $_POST["SEO_TITLE"];
			$arFields["SEO_TAGS"] = $_POST["SEO_TAGS"];
			$arFields["SEO_DESCRIPTION"] = $_POST["SEO_DESCRIPTION"];
		}
		
		return $arFields;
	}
	
	private function setCodeFields($blogParams)
	{
		$arFields = array();
		if ($this->arParams["ALLOW_POST_CODE"] && trim($_POST["CODE"]) <> '')
		{
			$arFields["CODE"] = trim($_POST["CODE"]);
			$arPCFilter = array("BLOG_ID" => $blogParams["ID"], "CODE" => $arFields["CODE"]);
			if (intval($this->arParams["ID"]) > 0)
				$arPCFilter["!ID"] = $this->arParams["ID"];
			$db = CBlogPost::GetList(Array(), $arPCFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
			if ($db->Fetch())
			{
				$uind = 0;
				do
				{
					$uind++;
					$arFields["CODE"] = $arFields["CODE"] . $uind;
					$arPCFilter["CODE"] = $arFields["CODE"];
					$db = CBlogPost::GetList(Array(), $arPCFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
				} while ($db->Fetch());
			}
		}
		
		return $arFields;
	}
	
	private function setPermsFields()
	{
		$arFields = array();
		if ($_POST["blog_perms"] == 1)
		{
			if ($_POST["perms_p"][1] > BLOG_PERMS_READ)
				$_POST["perms_p"][1] = BLOG_PERMS_READ;
			$arFields["PERMS_POST"] = $_POST["perms_p"];
			$arFields["PERMS_COMMENT"] = $_POST["perms_c"];
		}
		else
		{
			$arFields["PERMS_POST"] = array();
			$arFields["PERMS_COMMENT"] = array();
		}
		
		return $arFields;
	}
	
	private function setMicroblogFields($title1, $title2)
	{
		$arFields = array();
		if ($this->arParams["MICROBLOG"])
		{
			$arFields["MICRO"] = "Y";
			$arFields["TITLE"] = trim(blogTextParser::killAllTags($title1));
			if ($arFields["TITLE"] == '')
				$arFields["TITLE"] = $title2;
		}
		
		return $arFields;
	}
	
	private function saveOrUpdatePost($fields, $blogParams)
	{
		global $DB;
		$bAdd = false;
		$arOldPost = array();
		$errorMsg = '';
		$newID = false;
		
		if ($this->arParams["ID"] > 0)
		{
			$arOldPost = CBlogPost::GetByID($this->arParams["ID"]);
			if ($_POST["apply"] && $_POST["PUBLISH_STATUS"] == '')
				$fields["PUBLISH_STATUS"] = $arOldPost["PUBLISH_STATUS"];
			if ($_POST["DATE_PUBLISH"] == '')
				unset($fields["DATE_PUBLISH"]);
			$newID = CBlogPost::Update($this->arParams["ID"], $fields);
		}
		else
		{
			$fields["=DATE_CREATE"] = $DB->GetNowFunction();
			$fields["AUTHOR_ID"] = $this->userId;
			$fields["BLOG_ID"] = $blogParams["ID"];
			
			if ($_POST["apply"] && $_POST["PUBLISH_STATUS"] == '')
				$fields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_DRAFT;
			
			$dbDuplPost = CBlogPost::GetList(array("ID" => "DESC"), array("BLOG_ID" => $blogParams["ID"]), false, array("nTopCount" => 1), array("ID", "BLOG_ID", "AUTHOR_ID", "DETAIL_TEXT", "TITLE"));
			if ($arDuplPost = $dbDuplPost->Fetch())
			{
				if ($arDuplPost["BLOG_ID"] == $fields["BLOG_ID"] && intval($arDuplPost["AUTHOR_ID"]) == intval($fields["AUTHOR_ID"]) && md5($arDuplPost["DETAIL_TEXT"]) == md5($fields["DETAIL_TEXT"]) && md5($arDuplPost["TITLE"]) == md5($fields["TITLE"]))
				{
					$errorMsg = Loc::GetMessage("B_B_PC_DUPLICATE_POST");
				}
			}
			
			if ($errorMsg == '')
			{
				$newID = CBlogPost::Add($fields);
				$bAdd = true;
			}
		}
		
		return array(
			'ID' => $newID,
			'FIELDS' => $fields,
			'ADD_RESULT' => $bAdd,
			'ERROR_MSG' => $errorMsg,
			'OLD_POST' => $arOldPost,
		);
	}
	
	private function copyOrMovePost($blogParams)
	{
		global $DB, $APPLICATION;
		$arCopyPost = array();
		$copyID = false;
		$errorMsg = '';
		if ($arCopyBlog = CBlog::GetByID($_POST["move2blog"]))
		{
			$copyPerms = CBlog::GetBlogUserPostPerms($arCopyBlog["ID"], $this->userId);
			if ($copyPerms >= BLOG_PERMS_PREMODERATE)
			{
				$arCopyPost = CBlogPost::GetByID($this->arParams["ID"]);
				$arCopyPost["BLOG_ID"] = $arCopyBlog["ID"];
				unset($arCopyPost["ID"]);
				unset($arCopyPost["ATTACH_IMG"]);
				unset($arCopyPost["VIEWS"]);

				$pathes = array(
					"TEMPLATE" => htmlspecialcharsBack($this->arParams["PATH_TO_BLOG_POST"]),
					"TEMPLATE_EDIT" => htmlspecialcharsBack($this->arParams["PATH_TO_BLOG_POST_EDIT"]),
					"TEMPLATE_DRAFT" => htmlspecialcharsBack($this->arParams["PATH_TO_BLOG_DRAFT"]),
					"TEMPLATE_BLOG" => htmlspecialcharsBack($this->arParams["PATH_TO_BLOG_BLOG"]),
				);
				
				$arCopyPost["PATH"] = CComponentEngine::MakePathFromTemplate($pathes["TEMPLATE"], array("blog" => $arCopyBlog["URL"], "post_id" => "#post_id#", "user_id" => $arCopyBlog["OWNER_ID"]));
				
				$arCopyPost["PERMS_POST"] = Array();
				$arCopyPost["PERMS_COMMENT"] = Array();
				if ($copyPerms == BLOG_PERMS_PREMODERATE)
					$arCopyPost["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
				
//				update USER FILEDS
				if (count($this->arParams["POST_PROPERTY"]) > 0)
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_POST", $arCopyPost);
				
				if ($copyID = CBlogPost::Add($arCopyPost))
				{
					$arCopyPostUpdate = Array();
					//images
					$arPat = Array();
					$arRep = Array();
					
					$arFilter = array(
						"POST_ID" => $this->arParams["ID"],
						"BLOG_ID" => $blogParams["ID"],
						"IS_COMMENT" => "N",
					);
					$res = CBlogImage::GetList(array("ID" => "ASC"), $arFilter);
					while ($arImg = $res->GetNext())
					{
						$arNewImg = Array("FILE_ID" => CFile::MakeFileArray($arImg["FILE_ID"]));
						$arNewImg["BLOG_ID"] = $arCopyBlog["ID"];
						$arNewImg["POST_ID"] = $copyID;
						$arNewImg["USER_ID"] = $arImg["USER_ID"];
						$arNewImg["=TIMESTAMP_X"] = $DB->GetNowFunction();
						$arNewImg["TITLE"] = $arImg["TITLE"];
						$arNewImg["MODULE_ID"] = "blog";

						if ($imgID = CBlogImage::Add($arNewImg))
						{
							$arPat[] = "[IMG ID=" . $arImg["ID"] . "]";
							$arRep[] = "[IMG ID=" . $imgID . "]";
						}
					}
					if (!empty($arRep))
					{
						$arCopyPostUpdate["DETAIL_TEXT"] = str_replace($arPat, $arRep, $arCopyPost["DETAIL_TEXT"]);
					}
					
					//tags
					$arCopyCat = Array();
					$dbCategory = CBlogCategory::GetList(Array(), Array("BLOG_ID" => $arCopyBlog["ID"]));
					while ($arCategory = $dbCategory->Fetch())
					{
						$arCatBlogCopy[mb_strtolower($arCategory["NAME"])] = $arCategory["ID"];
					}
					
					$dbCat = CBlogPostCategory::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $blogParams["ID"], "POST_ID" => $this->arParams["ID"]));
					while ($arCat = $dbCat->Fetch())
					{
						if (empty($arCatBlogCopy[mb_strtolower($arCat["NAME"])]))
							$v = CBlogCategory::Add(array("BLOG_ID" => $arCopyBlog["ID"], "NAME" => $arCat["NAME"]));
						else
							$v = $arCatBlogCopy[mb_strtolower($arCat["NAME"])];
						CBlogPostCategory::Add(Array("BLOG_ID" => $arCopyBlog["ID"], "POST_ID" => $copyID, "CATEGORY_ID" => $v));
						$arCopyCat[] = $v;
					}
					if (!empty($arCopyCat))
						$arCopyPostUpdate["CATEGORY_ID"] = implode(",", $arCopyCat);
					
					if ($_POST["move2blogcopy"] == "Y")
					{
						$arCopyPostUpdate["NUM_COMMENTS"] = 0;
						$arCopyPostUpdate["NUM_COMMENTS_ALL"] = 0;
					}
					
					if (!empty($arCopyPostUpdate))
					{
						$copyID = CBlogPost::Update($copyID, $arCopyPostUpdate);
						$arCopyPost = CBlogPost::GetByID($copyID);
					}
					
					if ($_POST["move2blogcopy"] != "Y")
					{
						if (CBlogPost::CanUserDeletePost($this->arParams["ID"], $this->userId))
						{
							$dbC = CBlogComment::GetList(Array("ID" => "ASC"), Array("BLOG_ID" => $blogParams["ID"], "POST_ID" => $this->arParams["ID"]), false, false, Array("PATH", "PUBLISH_STATUS", "POST_TEXT", "TITLE", "DATE_CREATE", "AUTHOR_IP1", "AUTHOR_IP", "AUTHOR_EMAIL", "AUTHOR_NAME", "AUTHOR_ID", "PARENT_ID", "POST_ID", "BLOG_ID", "ID"));
							while ($arC = $dbC->Fetch())
							{
								$arCTmp = Array(
									"BLOG_ID" => $arCopyBlog["ID"],
									"POST_ID" => $copyID,
								);
								CBlogComment::Update($arC["ID"], $arCTmp);
							}
							$arFilter = array(
								"POST_ID" => $this->arParams["ID"],
								"BLOG_ID" => $blogParams["ID"],
								"IS_COMMENT" => "Y",
							);
							$res = CBlogImage::GetList(array("ID" => "ASC"), $arFilter);
							while ($arImg = $res->GetNext())
							{
								$arNewImg = Array(
									"BLOG_ID" => $arCopyBlog["ID"],
									"POST_ID" => $copyID,
								);
								
								CBlogImage::Update($arImg["ID"], $arNewImg);
							}
							
							if (!CBlogPost::Delete($this->arParams["ID"]))
								$errorMsg = Loc::GetMessage("BPE_COPY_DELETE_ERROR");
							else
								CBlogPost::DeleteLog($this->arParams["ID"], $this->arParams["MICROBLOG"]);
						}
					}
					
					$this->clearBlogCache($arCopyBlog);
				}
				else
				{
					$errorMsg = Loc::GetMessage("BPE_COPY_ERROR");
					if ($ex = $APPLICATION->GetException())
						$errorMsg .= $ex->GetString();
				}
			}
			else
				$errorMsg = Loc::GetMessage("BPE_COPY_NO_PERM");
		}
		else
		{
			$errorMsg = Loc::GetMessage("BPE_COPY_NO_BLOG");
		}
		
		return array(
			'COPY_ID' => $copyID,
			'PATHES' => $pathes,
			'ERROR_MSG' => $errorMsg,
			'COPY_POST' => $arCopyPost,
			'COPY_BLOG' => !empty($arCopyBlog) ? $arCopyBlog : array(),
		);
	}
	
	private function initPostToShowExistPost($postParams, $blogParams)
	{
		$postToShowParams = array();
		$postToShowParams["TITLE"] = $postParams["TITLE"];
		$postToShowParams["DETAIL_TEXT"] = $postParams["DETAIL_TEXT"];
		$postToShowParams["~DETAIL_TEXT"] = $postParams["~DETAIL_TEXT"];
		$postToShowParams["DETAIL_TEXT_TYPE"] = $postParams["DETAIL_TEXT_TYPE"];
		$postToShowParams["PUBLISH_STATUS"] = $postParams["PUBLISH_STATUS"];
		$postToShowParams["ENABLE_TRACKBACK"] = $postParams["ENABLE_TRACKBACK"] == "Y";
		$postToShowParams["ENABLE_COMMENTS"] = $postParams["ENABLE_COMMENTS"];
		$postToShowParams["ATTACH_IMG"] = $postParams["ATTACH_IMG"];
		$postToShowParams["DATE_PUBLISH"] = $postParams["DATE_PUBLISH"];
		$postToShowParams["CATEGORY_ID"] = $postParams["CATEGORY_ID"];
		$postToShowParams["FAVORITE_SORT"] = $postParams["FAVORITE_SORT"];
		if ($this->arParams["ALLOW_POST_CODE"])
		{
			$postToShowParams["CODE"] = $postParams["CODE"];
		}
		if ($this->arParams["SEO_USE"] == "Y")
		{
			$postToShowParams["SEO_TITLE"] = $postParams["SEO_TITLE"];
			$postToShowParams["SEO_TAGS"] = $postParams["SEO_TAGS"];
			$postToShowParams["SEO_DESCRIPTION"] = $postParams["SEO_DESCRIPTION"];
		}
		
		$res = CBlogUserGroupPerms::GetList(array("ID" => "DESC"), array("BLOG_ID" => $blogParams["ID"], "POST_ID" => $this->arParams["ID"]));
		while ($arPerms = $res->Fetch())
		{
			if ($arPerms["AUTOSET"] == "N")
				$postToShowParams["ExtendedPerms"] = "Y";
			if ($arPerms["PERMS_TYPE"] == "P")
				$postToShowParams["arUGperms_p"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
			elseif ($arPerms["PERMS_TYPE"] == "C")
				$postToShowParams["arUGperms_c"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
		}
		
		return $postToShowParams;
	}
	
	
	private function initPostToShowPreview($blogParams)
	{
		$postToShowParams = array();
		$postToShowParams["TITLE"] = htmlspecialcharsEx($_POST["POST_TITLE"]);
		$postToShowParams["CATEGORY_ID"] = $_POST["CATEGORY_ID"];
		$postToShowParams["CategoryText"] = htmlspecialcharsEx($_POST["TAGS"]);
		$postToShowParams["DETAIL_TEXT_TYPE"] = htmlspecialcharsEx($_POST["POST_MESSAGE_TYPE"]);
		$postToShowParams["DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html") ? $_POST["POST_MESSAGE_HTML"] : htmlspecialcharsEx($_POST["POST_MESSAGE"]));
		$postToShowParams["~DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html") ? $_POST["POST_MESSAGE_HTML"] : $_POST["POST_MESSAGE"]);
		$postToShowParams["PUBLISH_STATUS"] = htmlspecialcharsEx($_POST["PUBLISH_STATUS"]);
		$postToShowParams["ENABLE_TRACKBACK"] = htmlspecialcharsEx($_POST["ENABLE_TRACKBACK"]);
		$postToShowParams["ENABLE_COMMENTS"] = htmlspecialcharsEx($_POST["ENABLE_COMMENTS"]);
		$postToShowParams["TRACKBACK"] = htmlspecialcharsEx($_POST["TRACKBACK"]);
		$postToShowParams["DATE_PUBLISH"] = $_POST["DATE_PUBLISH"] ? htmlspecialcharsEx($_POST["DATE_PUBLISH"]) : ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL");
		$postToShowParams["FAVORITE_SORT"] = htmlspecialcharsEx($_POST["FAVORITE_SORT"]);
		if ($_POST["POST_MESSAGE_TYPE"] == "html" && $_POST["POST_MESSAGE_HTML"] == '')
		{
			$postToShowParams["DETAIL_TEXT"] = htmlspecialcharsEx($_POST["POST_MESSAGE"]);
			$postToShowParams["~DETAIL_TEXT"] = $_POST["POST_MESSAGE"];
		}
		
		if ($this->arParams["ALLOW_POST_CODE"])
		{
			$postToShowParams["CODE"] = htmlspecialcharsEx($_POST["CODE"]);
		}
		if ($this->arParams["SEO_USE"] == "Y")
		{
			$postToShowParams["SEO_TITLE"] = htmlspecialcharsEx($_POST["SEO_TITLE"]);
			$postToShowParams["SEO_TAGS"] = htmlspecialcharsEx($_POST["SEO_TAGS"]);
			$postToShowParams["SEO_DESCRIPTION"] = htmlspecialcharsEx($_POST["SEO_DESCRIPTION"]);
		}
		
		if ($_POST["apply"] || $_POST["save"] || $this->arResult["preview"] == "Y")
		{
			$postToShowParams["arUGperms_p"] = $_POST["perms_p"];
			$postToShowParams["arUGperms_c"] = $_POST["perms_c"];
			$postToShowParams["ExtendedPerms"] = (intval($_POST["blog_perms"]) == 1 ? "Y" : "N");
		}
		else
		{
			$res = CBlogUserGroupPerms::GetList(array("ID" => "DESC"), array("BLOG_ID" => $blogParams["ID"], "POST_ID" => 0));
			while ($arPerms = $res->Fetch())
			{
				if ($arPerms["PERMS_TYPE"] == "P")
					$postToShowParams["arUGperms_p"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
				elseif ($arPerms["PERMS_TYPE"] == "C")
					$postToShowParams["arUGperms_c"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
			}
		}
		
		return $postToShowParams;
	}
	
	
	private function getAvBlogs($blogParams, $blogModulePermissions)
	{
		global $USER;
		$avBlogs = array();
		
		if ($USER->IsAdmin() || $blogModulePermissions >= "W")
		{
			$arFilter = [
				"=ACTIVE" => "Y",
				"GROUP_SITE_ID" => SITE_ID,
				"!ID" => $blogParams["ID"],
			];

			$dbBlog = CBlog::GetList(
				["NAME" => "ASC"],
				$arFilter,
				false,
				false,
				["ID", "NAME", "OWNER_ID", "URL", "GROUP_ID", "GROUP_NAME"]
			);
			while ($arBlogS = $dbBlog->GetNext())
			{
				$arBlogS["PERMS"] = BLOG_PERMS_FULL;
				$avBlogs[$arBlogS["ID"]] = $arBlogS;
			}
		}
		else
		{
			$arFilter = Array(
				"USE_SOCNET" => "N",
				">=PERMS" => BLOG_PERMS_PREMODERATE,
				"PERMS_TYPE" => BLOG_PERMS_POST,
				"PERMS_USER_ID" => $this->userId,
				"PERMS_POST_ID" => false,
				"=ACTIVE" => "Y",
				"GROUP_SITE_ID" => SITE_ID,
				"!ID" => $blogParams["ID"],
			);

			$dbBlog = CBlog::GetList(
				["NAME" => "ASC"],
				$arFilter,
				false,
				false,
				["ID", "NAME", "OWNER_ID", "URL", "PERMS", "GROUP_ID", "GROUP_NAME"]
			);
			while ($arBlogS = $dbBlog->GetNext())
			{
				$arBlogS["USE_SOCNET"] = "N";
				$avBlogs[$arBlogS["ID"]] = $arBlogS;
			}
			$arFilter = [
				"OWNER_ID" => $this->userId,
				"=ACTIVE" => "Y",
				"GROUP_SITE_ID" => SITE_ID,
				"!ID" => $blogParams["ID"],
			];

			$dbBlog = CBlog::GetList(
				["NAME" => "ASC"],
				$arFilter,
				false,
				false,
				["ID", "NAME", "OWNER_ID", "URL", "GROUP_ID", "GROUP_NAME"]
			);
			while ($arBlogS = $dbBlog->GetNext())
			{
				$arBlogS["PERMS"] = BLOG_PERMS_FULL;
				$avBlogs[$arBlogS["ID"]] = $arBlogS;
			}
		}
		
		return $avBlogs;
	}
	
	
	private function getSmilesParams()
	{
		$smilesParams = CBlogSmile::getSmiles(CSmile::TYPE_SMILE, LANGUAGE_ID);
		foreach($smilesParams as $key => $value)
		{
			$smilesParams[$key]["LANG_NAME"] = $value["NAME"];
			$smilesParams[$key]["~LANG_NAME"] = htmlspecialcharsback($value["NAME"]);
			list($type) = explode(" ", $value["TYPING"]);
			$smilesParams[$key]["TYPE"] = str_replace("'", "\'", $type);
			$smilesParams[$key]["TYPE"] = str_replace("\\", "\\\\", $smilesParams[$key]["TYPE"]);
		}

		return $smilesParams;
	}
	
	private function clearBlogCache($blogParams)
	{
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/first_page/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/calendar/");
		BXClearCache(true, "/" . SITE_ID . "/blog/last_messages/");
		BXClearCache(true, "/" . SITE_ID . "/blog/commented_posts/");
		BXClearCache(true, "/" . SITE_ID . "/blog/popular_posts/");
		BXClearCache(true, "/" . SITE_ID . "/blog/last_comments/");
		BXClearCache(true, "/" . SITE_ID . "/blog/groups/" . $blogParams["GROUP_ID"] . "/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/rss_out/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/rss_all/");
		BXClearCache(true, "/" . SITE_ID . "/blog/rss_sonet/");
		BXClearCache(true, "/" . SITE_ID . "/blog/rss_all/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/favorite/");
		BXClearCache(true, "/" . SITE_ID . "/blog/last_messages_list_extranet/");
		BXClearCache(true, "/" . SITE_ID . "/blog/last_messages_list/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/comment/" . $this->arParams["ID"] . "/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/trackback/" . $this->arParams["ID"] . "/");
		BXClearCache(true, "/" . SITE_ID . "/blog/" . $blogParams["URL"] . "/post/" . $this->arParams["ID"] . "/");
	}
	
	private function isUserGivenConsent()
	{
		if(isset($this->arParams["USER_CONSENT"]) && $this->arParams["USER_CONSENT"] == "Y"
			&& isset($this->arParams["USER_CONSENT_ID"]) && $this->arParams["USER_CONSENT_ID"])
		{
			$this->arParams["USER_CONSENT_WAS_GIVEN"] = \Bitrix\Blog\BlogUser::isUserGivenConsent(
				$this->arResult['UserID'],
				$this->arParams["USER_CONSENT_ID"]
			);
		}
	}
}

?>