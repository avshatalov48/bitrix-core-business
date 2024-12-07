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
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @param string $componentName
 * @param CBitrixComponent $this
 */

if(!is_array($arParams["POST_BIND_USER"]))
	$arParams["POST_BIND_USER"] = array();

$arResult["IDEA_MODERATOR"] = false;
if((!empty($arParams["POST_BIND_USER"]) && array_intersect($USER->GetUserGroupArray(), $arParams["POST_BIND_USER"]))
	||$USER->IsAdmin()
)
	$arResult["IDEA_MODERATOR"] = true;

$arParams["POST_BIND_STATUS_DEFAULT"] = intval($arParams["POST_BIND_STATUS_DEFAULT"]);
$arParams["ID"] = intval($arParams["ID"]);
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

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if($arParams["PATH_TO_POST_EDIT"] == '')
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
if($arParams["PATH_TO_DRAFT"] == '')
	$arParams["PATH_TO_DRAFT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=draft&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);
//$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams["SMILES_COUNT"] = intval($arParams["SMILES_COUNT"]);
if(intval($arParams["SMILES_COUNT"]) <= 0)
	$arParams["SMILES_COUNT"] = 5;

$arParams["IMAGE_MAX_WIDTH"] = intval($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = intval($arParams["IMAGE_MAX_HEIGHT"]);

$arParams["EDITOR_RESIZABLE"] = $arParams["EDITOR_RESIZABLE"] !== "N";
$arParams["EDITOR_CODE_DEFAULT"] = $arParams["EDITOR_CODE_DEFAULT"] === "Y";
$arParams["EDITOR_DEFAULT_HEIGHT"] = intval($arParams["EDITOR_DEFAULT_HEIGHT"]);
if(intval($arParams["EDITOR_DEFAULT_HEIGHT"]) <= 0)
	$arParams["EDITOR_DEFAULT_HEIGHT"] = 300;

$arResult["UserID"] = $USER->GetID();
$arResult["allowHTML"] = COption::GetOptionString("blog","allow_html", "N");
$arResult["allowVideo"] = COption::GetOptionString("blog","allow_video", "Y");
$blogModulePermissions = $APPLICATION->GetGroupRight("blog");

$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
$arParams["USE_GOOGLE_CODE"] = $arParams["USE_GOOGLE_CODE"] === "Y";

if($arParams["DISABLE_SONET_LOG"] != "Y")
	$arParams["DISABLE_SONET_LOG"] = "N";

$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
if(intval($arParams["ID"]) > 0)
	$arResult["perms"] = CBlogPost::GetBlogUserPostPerms($arParams["ID"], $arResult["UserID"]);
else
	$arResult["perms"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $arResult["UserID"]);


if ((!empty($arBlog) && $arBlog["ACTIVE"] == "Y") ||
	(empty($arBlog) && $arResult["bSoNet"]))
{
	$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
	if ($arGroup["SITE_ID"] == SITE_ID || empty($arBlog))
	{
		if($arResult["allowHTML"] == "Y" && $arBlog["ALLOW_HTML"] == "Y")
			$arResult["allow_html"] = "Y";
		$arResult["Blog"] = $arBlog;
				//NavChain, Title & Prepare data
		if($arParams["ID"] > 0 && ($arPost = CBlogPost::GetByID($arParams["ID"])) && $arPost)
		{
			$arResult["Post"] = $arPost = CBlogTools::htmlspecialcharsExArray($arPost);
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(GetMessage("BLOG_POST_EDIT", array("#IDEA_TITLE#"=>$arResult["Post"]["TITLE"])));
			if($arParams["SET_NAV_CHAIN"] == "Y")
				$APPLICATION->AddChainItem(GetMessage("BLOG_POST_EDIT", array("#IDEA_TITLE#"=>$arResult["Post"]["TITLE"])), CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("post_id"=>$arParams["ID"])));
		}
		else
		{
			$arPost = array();
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(GetMessage("IDEA_NEW_MESSAGE"));
			if($arParams["SET_NAV_CHAIN"] == "Y")
				$APPLICATION->AddChainItem(GetMessage("IDEA_NEW_MESSAGE"), CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("post_id"=>"new")));
		}
		if (($arParams["ID"] <= 0 || $arPost["BLOG_ID"]==$arBlog["ID"]) &&
			(
				($arResult["IDEA_MODERATOR"] && $arResult["perms"] >= BLOG_PERMS_MODERATE) ||
				($arResult["perms"] >= BLOG_PERMS_PREMODERATE && ($arParams["ID"]==0 || $arPost["AUTHOR_ID"] == $arResult["UserID"]))
			) &&
			$USER->IsAuthorized()
		)
		{
			if($arParams["ID"] > 0 && $arPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY && $arResult["perms"] == BLOG_PERMS_PREMODERATE)
				$arResult["UTIL_MESSAGE"] = GetMessage("BPE_HIDDEN_POSTED");
				//Upload & Delete Images
			else if ($_GET["image_upload_frame"] == "Y" || $_GET["image_upload"] || $_POST["do_upload"])
			{
				$arResult["imageUploadFrame"] = "Y";
				$arResult["imageUpload"] = "Y";
				$APPLICATION->RestartBuffer();
				header("Pragma: no-cache");

				if(check_bitrix_sessid() || $_REQUEST["sessid"] == '')
				{
					$arFields = Array();
					if ($_FILES["BLOG_UPLOAD_FILE"]["size"] > 0)
					{
						$arFields = array(
							"BLOG_ID"	=> $arBlog["ID"],
							"POST_ID"	=> $arParams["ID"],
							"USER_ID"	=> $arResult["UserID"],
							"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
							"TITLE"		=> $_POST["IMAGE_TITLE"],
							"IMAGE_SIZE"	=> $_FILES["BLOG_UPLOAD_FILE"]["size"]
						);
						$arImage = array_merge(
							$_FILES["BLOG_UPLOAD_FILE"],
							array(
								"MODULE_ID" => "blog",
								"del" => "Y"
							)
						);
						$arFields["FILE_ID"] = $arImage;
					}
					elseif ($_POST["do_upload"] && $_FILES["FILE_ID"]["size"] > 0)
					{
						$arFields = array(
							"BLOG_ID"	=> $arBlog["ID"],
							"POST_ID"	=> $arParams["ID"],
							"USER_ID"	=> $arResult["UserID"],
							"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
							"TITLE"		=> $_POST["IMAGE_TITLE"],
							"IMAGE_SIZE"	=> $_FILES["FILE_ID"]["size"]
						);
						$arImage = array_merge(
							$_FILES["FILE_ID"],
							array(
								"MODULE_ID" => "blog",
								"del" => "Y"
							)
						);
						$arFields["FILE_ID"] = $arImage;
					}
					if(!empty($arFields))
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
							if(
								$iMaxW > 0 && $iMaxH > 0
								&& ($intWidth > $iMaxW || $intHeight > $iMaxH)
							)
							{
								$coeff = ($intWidth/$iMaxW > $intHeight/$iMaxH? $intWidth/$iMaxW : $intHeight/$iMaxH);
								$iHeight = intval(roundEx($intHeight/$coeff));
								$iWidth = intval(roundEx($intWidth/$coeff));
							}
							else
							{
								$iHeight = $intHeight;
								$iWidth = $intWidth;
							}

							$file = "<img src=\"".$aImg["PARAMS"]["SRC"]."\" width=\"".$iWidth."\" height=\"".$iHeight."\" id=\"".$aImg["ID"]."\" border=\"0\" style=\"cursor:pointer\" onclick=\"InsertBlogImage('".$aImg["ID"]."', '".$aImg["PARAMS"]['WIDTH']."');\" title=\"".GetMessage("BLOG_P_INSERT")."\">";

							$file = str_replace("'","\'",$file);
							$file = str_replace("\r"," ",$file);
							$file = str_replace("\n"," ",$file);
							$arResult["ImageModified"] = $file;
							$arResult["Image"] = $aImg;
						}
						else
						{
							if ($ex = $APPLICATION->GetException())
								$arResult["ERROR_MESSAGE"] = $ex->GetString();
						}
					}
				}
			}
			else
			{
				$arSite = CSite::GetByID(SITE_ID)->Fetch();
				$serverName = ( $arSite["SERVER_NAME"] == '' ?
						(defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '' ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", "www.bitrixsoft.com")) :
						$_SERVER["HTTP_HOST"]
				);
				if (!$_POST["apply"] && !$_POST["save"]) // Save on button click
				{

				}
				else if (!check_bitrix_sessid())
				{
					$arResult["ERROR_MESSAGE"] = GetMessage("BPE_SESS");
				}
				else
				{
					$CATEGORYtmp = Array();
					if(!empty($_POST["TAGS"]))
					{
						$arCatBlog = array();
						$dbCategory = CBlogCategory::GetList(Array(), Array("BLOG_ID" => $arBlog["ID"]));
						while($arCategory = $dbCategory->Fetch())
						{
							$arCatBlog[mb_strtolower($arCategory["NAME"])] = $arCategory["ID"];
						}
						$tags = explode (",", $_POST["TAGS"]);
						foreach($tags as $tg)
						{
							$tg = trim($tg);
							if(!in_array($arCatBlog[mb_strtolower($tg)], $CATEGORYtmp))
							{
								if(intval($arCatBlog[mb_strtolower($tg)]) > 0)
									$CATEGORYtmp[] = $arCatBlog[mb_strtolower($tg)];
								else
									$CATEGORYtmp[] = CBlogCategory::Add(array("BLOG_ID" => $arBlog["ID"], "NAME" => $tg));
							}
						}
					}
					elseif (!empty($_POST["CATEGORY_ID"]))
					{
						foreach($_POST["CATEGORY_ID"] as $v)
						{
							if(mb_substr($v, 0, 4) == "new_")
								$CATEGORYtmp[] = CBlogCategory::Add(array("BLOG_ID"=>$arBlog["ID"],"NAME"=> mb_substr($v, 4)));
							else
								$CATEGORYtmp[] = $v;
						}
					}
					$CATEGORY_ID = implode(",", $CATEGORYtmp);

					$DATE_PUBLISH = $_POST["DATE_PUBLISH"];
					if($_POST["DATE_PUBLISH_DEF"] <> '')
						$DATE_PUBLISH = $_POST["DATE_PUBLISH_DEF"];
					elseif ($_POST["DATE_PUBLISH"] == '')
						$DATE_PUBLISH = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");

					$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_PUBLISH;

					$arFields = array(
						"TITLE"			=> trim($_POST["POST_TITLE"]),
						"DETAIL_TEXT"		=> trim(($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : $_POST["POST_MESSAGE"]),
						"DETAIL_TEXT_TYPE"	=> ($arResult["allowHTML"] == "Y" && $arBlog["ALLOW_HTML"] == "Y") ? $_POST["POST_MESSAGE_TYPE"] : "text",
						"DATE_PUBLISH"		=> $DATE_PUBLISH,
						"PUBLISH_STATUS"	=> $PUBLISH_STATUS,
						"ENABLE_TRACKBACK"	=> "N",
						"ENABLE_COMMENTS"	=> ($_POST["ENABLE_COMMENTS"] == "N") ? "N" : "Y",
						"CATEGORY_ID"		=> $CATEGORY_ID,
						"FAVORITE_SORT" => (intval($_POST["FAVORITE_SORT"]) > 0) ? intval($_POST["FAVORITE_SORT"]) : 0,
						"ATTACH_IMG" => "",
						"PATH" => CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]))
					);
					$_POST["CODE"] = trim($_POST["CODE"]);
					if($arParams["ALLOW_POST_CODE"] && $_POST["CODE"] <> '')
					{
						$arFields["CODE"] = $_POST["CODE"];
						$arPCFilter = array("BLOG_ID" => $arBlog["ID"], "CODE" => $arFields["CODE"]);
						if($arParams["ID"] > 0)
							$arPCFilter["!ID"] = $arParams["ID"];
						$db = CBlogPost::GetList(Array(), $arPCFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
						if($db->Fetch())
						{
							$uind = 0;
							do
							{
								$uind++;
								$arFields["CODE"] = $arFields["CODE"].$uind;
								$arPCFilter["CODE"]  = $arFields["CODE"];
								$db = CBlogPost::GetList(Array(), $arPCFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
							}
							while ($db->Fetch());
						}
					}
					if($_POST["POST_MESSAGE_TYPE"] == "html" && $_POST["POST_MESSAGE_HTML"] == '')
						$arFields["DETAIL_TEXT"] = $_POST["POST_MESSAGE"];

					$arFields["PERMS_POST"] = array();
					$arFields["PERMS_COMMENT"] = array();

					if(is_array($_POST["IMAGE_ID_title"]))
					{
						foreach($_POST["IMAGE_ID_title"] as $imgID => $imgTitle)
						{
							$aImg = CBlogImage::GetByID($imgID);
							$aImg = CBlogTools::htmlspecialcharsExArray($aImg);
							if ($aImg["BLOG_ID"]==$arBlog["ID"] && $aImg["POST_ID"]==$arParams["ID"])
							{
								if ($_POST["IMAGE_ID_del"][$imgID])
								{
									CBlogImage::Delete($imgID);
									$arFields["DETAIL_TEXT"] = str_replace("[IMG ID=$imgID]","",$arFields["DETAIL_TEXT"]);
								}
								else
									CBlogImage::Update($imgID, array("TITLE"=>$imgTitle));
							}
						}
					}

					if (count($arParams["POST_PROPERTY"]) > 0)
						$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("BLOG_POST", $arFields);

					$arDefaultStatus = CIdeaManagment::getInstance()->Idea()->GetDefaultStatus(array($arFields[CIdeaManagment::UFStatusField], $arParams["POST_BIND_STATUS_DEFAULT"]));

					//unset Status field to make Handle status update
					unset($arFields[CIdeaManagment::UFStatusField]);

					if ($arParams["ID"] > 0) //Update
					{
						$arFields["AUTHOR_ID"] = $arPost["AUTHOR_ID"];
						$newID = CBlogPost::Update($arParams["ID"], $arFields);
						$arFields["ACTION"] = "UPDATE";
					}
					else //Add
					{
						$arFields["AUTHOR_ID"] = $arResult["UserID"];
						$arFields["=DATE_CREATE"] = $DB->GetNowFunction();
						$arFields["BLOG_ID"] = $arBlog["ID"];

						$newID = CBlogPost::Add($arFields);
						$arFields["ACTION"] = "ADD";
					}

					//Update idea status
					if($newID>0 && $arDefaultStatus["ID"]>0)
					{
						$bStatus = CIdeaManagment::getInstance()->Idea($newID)->SetStatus($arDefaultStatus["ID"]);
						if($bStatus)
						{/*(TODO: socnet ribbon, notification*/}
					}

					//Update categories & images
					if($newID > 0)
					{
						CBlogPostCategory::DeleteByPostID($newID);
						foreach($CATEGORYtmp as $v)
							CBlogPostCategory::Add(Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $newID, "CATEGORY_ID"=>$v));
						$DB->Query("UPDATE b_blog_image SET POST_ID=".$newID." WHERE BLOG_ID=".$arBlog["ID"]." AND POST_ID=0", true);
					}

					if($newID > 0)
					{
						if($arFields["ACTION"] == "ADD")
							$arResult["SUCCESS_MESSAGE"] = GetMessage("IDEA_NEW_MESSAGE_SUCCESS");

						//Get Author Name
						$arResult["BlogUser"] = CBlogUser::GetByID($arFields["AUTHOR_ID"], BLOG_BY_USER_ID);
						$arResult["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["BlogUser"]);
						$arResult["arUser"] = CUser::GetByID($arFields["AUTHOR_ID"])->GetNext();
						$AuthorName = CBlogUser::GetUserName($arResult["BlogUser"]["~ALIAS"], $arResult["arUser"]["~NAME"], $arResult["arUser"]["~LAST_NAME"], $arResult["arUser"]["~LOGIN"], $arResult["arUser"]["~SECOND_NAME"]);

						$IdeaParser = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);

						$arCategoryList = CIdeaManagment::getInstance()->Idea()->GetCategoryList();
						$arNotifyFields = array_merge(
							$arFields,
							array(
								"AUTHOR" => $AuthorName,
								"IDEA_TEXT" => $IdeaParser->convert4mail($arFields["DETAIL_TEXT"]),
								"SHOW_RATING" => $arParams["SHOW_RATING"],
								"RATING_TYPE_ID" => 'BLOG_POST',
								"RATING_ENTITY_ID" => $newID,
								"ID" => $newID,
								"TYPE" => "IDEA",
								"CATEGORY" => $arFields[CIdeaManagment::UFCategroryCodeField],
								"FULL_PATH" => "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arFields["PATH"]), array("post_id" => $newID)),
								"PATH" => CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arFields["PATH"]), array("post_id" => $newID)),
								"CATEGORIES" => $arCategoryList
							)
						);

						//Notifications
						$Notify = CIdeaManagment::getInstance()->Notification($arNotifyFields);
						//Socialnetwork notification
						$Notify->getSonetNotify()->Send();
						//Email subscribe
						$Notify->getEmailNotify()->Add($newID);
						//Email notification
						$Notify->getEmailNotify()->Send();

						//Clear Caching and redirect
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/pages/");
						BXClearCache(True, "/".SITE_ID."/idea/".$arBlog["ID"]."/post/".$newID."/");
						BXClearCache(True, '/'.SITE_ID.'/idea/statistic_list/');
						BXClearCache(True, '/'.SITE_ID.'/idea/tags/');

						//Redirect if not AJAX
						if($_REQUEST["AJAX"] != "Y")
							LocalRedirect($arNotifyFields["PATH"]);
						$arResult["ID"] = $newID;
					}
					else
					{
						if ($ex = $APPLICATION->GetException())
							$arResult["ERROR_MESSAGE"] = $ex->GetString()."<br />";
						else
							$arResult["ERROR_MESSAGE"] = "Error saving data to database.<br />";
					}
				}

				if ($arParams["ID"] > 0 && $arResult["ERROR_MESSAGE"] == '' && $arResult["preview"] != "Y") // Edit post
				{
					$arResult["PostToShow"]["TITLE"] = $arPost["TITLE"];
					$arResult["PostToShow"]["DETAIL_TEXT"] = $arPost["DETAIL_TEXT"];
					$arResult["PostToShow"]["~DETAIL_TEXT"] = $arPost["~DETAIL_TEXT"];
					$arResult["PostToShow"]["DETAIL_TEXT_TYPE"] = $arPost["DETAIL_TEXT_TYPE"];
					$arResult["PostToShow"]["PUBLISH_STATUS"] = $arPost["PUBLISH_STATUS"];
					$arResult["PostToShow"]["ENABLE_TRACKBACK"] = false;
					$arResult["PostToShow"]["ENABLE_COMMENTS"] = $arPost["ENABLE_COMMENTS"];
					$arResult["PostToShow"]["ATTACH_IMG"] = $arPost["ATTACH_IMG"];
					$arResult["PostToShow"]["DATE_PUBLISH"] = $arPost["DATE_PUBLISH"];
					$arResult["PostToShow"]["CATEGORY_ID"] = $arPost["CATEGORY_ID"];
					$arResult["PostToShow"]["FAVORITE_SORT"] = $arPost["FAVORITE_SORT"];
					if($arParams["ALLOW_POST_CODE"])
						$arResult["PostToShow"]["CODE"] = $arPost["CODE"];

					$res = CBlogUserGroupPerms::GetList(array("ID"=>"DESC"),array("BLOG_ID"=>$arBlog["ID"],"POST_ID"=>$arParams["ID"]));
					while($arPerms = $res->Fetch())
					{
						if ($arPerms["AUTOSET"]=="N")
							$arResult["PostToShow"]["ExtendedPerms"] = "Y";
						if ($arPerms["PERMS_TYPE"]=="P")
							$arResult["PostToShow"]["arUGperms_p"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
						elseif ($arPerms["PERMS_TYPE"]=="C")
							$arResult["PostToShow"]["arUGperms_c"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
					}
				}
				else
				{
					$arResult["PostToShow"]["TITLE"] = htmlspecialcharsEx($_POST["POST_TITLE"]);
					$arResult["PostToShow"]["CATEGORY_ID"] = $_POST["CATEGORY_ID"];
					$arResult["PostToShow"]["CategoryText"] = htmlspecialcharsEx($_POST["TAGS"]);
					$arResult["PostToShow"]["DETAIL_TEXT_TYPE"] = htmlspecialcharsEx($_POST["POST_MESSAGE_TYPE"]);
					$arResult["PostToShow"]["DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : htmlspecialcharsEx($_POST["POST_MESSAGE"]));
					$arResult["PostToShow"]["~DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : $_POST["POST_MESSAGE"]);
					$arResult["PostToShow"]["PUBLISH_STATUS"] = htmlspecialcharsEx($_POST["PUBLISH_STATUS"]);
					$arResult["PostToShow"]["ENABLE_TRACKBACK"] = "N";
					$arResult["PostToShow"]["ENABLE_COMMENTS"] = htmlspecialcharsEx($_POST["ENABLE_COMMENTS"]);
					$arResult["PostToShow"]["TRACKBACK"] = htmlspecialcharsEx($_POST["TRACKBACK"]);
					$arResult["PostToShow"]["DATE_PUBLISH"] = $_POST["DATE_PUBLISH"] ? htmlspecialcharsEx($_POST["DATE_PUBLISH"]) : ConvertTimeStamp(time()+CTimeZone::GetOffset(),"FULL");

					$arResult["PostToShow"]["FAVORITE_SORT"] = htmlspecialcharsEx($_POST["FAVORITE_SORT"]);
					if($_POST["POST_MESSAGE_TYPE"] == "html" && $_POST["POST_MESSAGE_HTML"] == '')
					{
						$arResult["PostToShow"]["DETAIL_TEXT"] = htmlspecialcharsEx($_POST["POST_MESSAGE"]);
						$arResult["PostToShow"]["~DETAIL_TEXT"] = $_POST["POST_MESSAGE"];
					}

					if($arParams["ALLOW_POST_CODE"])
						$arResult["PostToShow"]["CODE"] = htmlspecialcharsEx($_POST["CODE"]);

					if ($_POST["apply"] || $_POST["save"])
					{
						$arResult["PostToShow"]["arUGperms_p"] = $_POST["perms_p"];
						$arResult["PostToShow"]["arUGperms_c"] = $_POST["perms_c"];
						$arResult["PostToShow"]["ExtendedPerms"] = (intval($_POST["blog_perms"])==1 ? "Y" : "N");
					}
					else
					{
						$res = CBlogUserGroupPerms::GetList(array("ID"=>"DESC"),array("BLOG_ID"=>$arBlog["ID"],"POST_ID"=>0));
						while($arPerms = $res->Fetch())
						{
							if ($arPerms["PERMS_TYPE"]=="P")
								$arResult["PostToShow"]["arUGperms_p"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
							elseif ($arPerms["PERMS_TYPE"]=="C")
								$arResult["PostToShow"]["arUGperms_c"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
						}
					}
				}
				$arResult["BLOG_POST_PERMS"] = (is_array($GLOBALS["AR_BLOG_POST_PERMS"]) ? $GLOBALS["AR_BLOG_POST_PERMS"] : array());
				$arResult["BLOG_COMMENT_PERMS"] = (is_array($GLOBALS["AR_BLOG_COMMENT_PERMS"]) ? $GLOBALS["AR_BLOG_COMMENT_PERMS"] : array());

				if(!$USER->IsAdmin() && $blogModulePermissions < "W")
				{
					$arResult["post_everyone_max_rights"] = COption::GetOptionString("blog", "post_everyone_max_rights", "");
					$arResult["comment_everyone_max_rights"] = COption::GetOptionString("blog", "comment_everyone_max_rights", "");
					$arResult["post_auth_user_max_rights"] = COption::GetOptionString("blog", "post_auth_user_max_rights", "");
					$arResult["comment_auth_user_max_rights"] = COption::GetOptionString("blog", "comment_auth_user_max_rights", "");
					$arResult["post_group_user_max_rights"] = COption::GetOptionString("blog", "post_group_user_max_rights", "");
					$arResult["comment_group_user_max_rights"] = COption::GetOptionString("blog", "comment_group_user_max_rights", "");

					foreach($arResult["BLOG_POST_PERMS"] as  $v)
					{
						if($arResult["post_everyone_max_rights"] <> '' && $v <= $arResult["post_everyone_max_rights"])
							$arResult["ar_post_everyone_rights"][] = $v;
						if($arResult["post_auth_user_max_rights"] <> '' && $v <= $arResult["post_auth_user_max_rights"])
							$arResult["ar_post_auth_user_rights"][] = $v;
						if($arResult["post_group_user_max_rights"] <> '' && $v <= $arResult["post_group_user_max_rights"])
							$arResult["ar_post_group_user_rights"][] = $v;

					}

					foreach($arResult["BLOG_COMMENT_PERMS"] as  $v)
					{
						if($arResult["comment_everyone_max_rights"] <> '' && $v <= $arResult["comment_everyone_max_rights"])
							$arResult["ar_comment_everyone_rights"][] = $v;
						if($arResult["comment_auth_user_max_rights"] <> '' && $v <= $arResult["comment_auth_user_max_rights"])
							$arResult["ar_comment_auth_user_rights"][] = $v;
						if($arResult["comment_group_user_max_rights"] <> '' && $v <= $arResult["comment_group_user_max_rights"])
							$arResult["ar_comment_group_user_rights"][] = $v;
					}
				}

				$arResult["Smiles"] = CBlogSmile::getSmiles(CSmile::TYPE_SMILE, LANGUAGE_ID);
				foreach($arResult["Smiles"] as $key => $value)
				{
					$arResult["Smiles"][$key]["LANG_NAME"] = $value["NAME"];
					$arResult["Smiles"][$key]["~LANG_NAME"] = htmlspecialcharsback($value["NAME"]);
					list($type) = explode(" ", $value["TYPING"]);
					$arResult["Smiles"][$key]["TYPE"] = str_replace("'", "\'", $type);
					$arResult["Smiles"][$key]["TYPE"] = str_replace("\\", "\\\\", $arResult["Smiles"][$key]["TYPE"]);
				}
				$arResult["SmilesCount"] = count($arResult["Smiles"]);

				$arResult["Images"] = Array();
				if(!empty($arBlog))
				{
					$arFilter = array(
						"POST_ID" => $arParams["ID"],
						"BLOG_ID" => $arBlog["ID"],
						"IS_COMMENT" => "N",
					);
					if ($arParams["ID"]==0)
						$arFilter["USER_ID"] = $arResult["UserID"];

					$iMaxW = 100; $iMaxH = 100;
					$res = CBlogImage::GetList(array("ID"=>"ASC"), $arFilter);
					while($aImg = $res->GetNext())
					{
						$aImg["PARAMS"] = CFile::_GetImgParams($aImg["FILE_ID"]);
						$intWidth = $aImg["PARAMS"]['WIDTH'];
						$intHeight = $aImg["PARAMS"]['HEIGHT'];
						if(
							$iMaxW > 0 && $iMaxH > 0
							&& ($intWidth > $iMaxW || $intHeight > $iMaxH)
						)
						{
							$coeff = ($intWidth/$iMaxW > $intHeight/$iMaxH? $intWidth/$iMaxW : $intHeight/$iMaxH);
							$iHeight = intval(roundEx($intHeight/$coeff));
							$iWidth = intval(roundEx($intWidth/$coeff));
						}
						else
						{
							$iHeight = $intHeight;
							$iWidth = $intWidth;
						}

						$aImg["FileShow"] = "<img src=\"".$aImg["PARAMS"]["SRC"]."\" width=\"".$iWidth."\" height=\"".$iHeight."\" id=\"".$aImg["ID"]."\" border=\"0\" style=\"cursor:pointer\" onclick=\"InsertBlogImage('".$aImg["ID"]."', '".$aImg["PARAMS"]['WIDTH']."');\" title=\"".GetMessage("BLOG_P_INSERT")."\">";
						$arResult["Images"][] = $aImg;
					}
				}

				if(mb_strpos($arResult["PostToShow"]["CATEGORY_ID"], ",") !== false)
					$arResult["PostToShow"]["CATEGORY_ID"] = explode(",", trim($arResult["PostToShow"]["CATEGORY_ID"]));

				$arResult["Category"] = Array();

				if($arResult["PostToShow"]["CategoryText"] == '')
				{
					$res = CBlogCategory::GetList(array("NAME"=>"ASC"),array("BLOG_ID"=>$arBlog["ID"]));
					while ($arCategory=$res->GetNext())
					{
						if(is_array($arResult["PostToShow"]["CATEGORY_ID"]))
						{
							if(in_array($arCategory["ID"], $arResult["PostToShow"]["CATEGORY_ID"]))
								$arCategory["Selected"] = "Y";
						}
						else
						{
							if(intval($arCategory["ID"])==intval($arResult["PostToShow"]["CATEGORY_ID"]))
								$arCategory["Selected"] = "Y";
						}
						if($arCategory["Selected"] == "Y")
							$arResult["PostToShow"]["CategoryText"] .= $arCategory["~NAME"].", ";

						$arResult["Category"][$arCategory["ID"]] = $arCategory;
					}
					$arResult["PostToShow"]["CategoryText"] = mb_substr($arResult["PostToShow"]["CategoryText"], 0, mb_strlen($arResult["PostToShow"]["CategoryText"]) - 2);
				}

				$arResult["POST_PROPERTIES"] = array("SHOW" => "N");

				if (!empty($arParams["POST_PROPERTY"]))
				{
					$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arParams["ID"], LANGUAGE_ID);

					if (count($arParams["POST_PROPERTY"]) > 0)
					{
						foreach ($arPostFields as $FIELD_NAME => $arPostField)
						{
							if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
								continue;
							$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
							$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
							$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
							if ($FIELD_NAME == "UF_STATUS" && !empty($arParams["POST_BIND_STATUS_DEFAULT"]))
								$arPostField["SETTINGS"]["DEFAULT_VALUE"] = $arParams["POST_BIND_STATUS_DEFAULT"];
							$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
						}
					}
					if (!empty($arResult["POST_PROPERTIES"]["DATA"]))
						$arResult["POST_PROPERTIES"]["SHOW"] = "Y";
				}

				$serverName = "http://".$serverName;

				$arResult["PATH_TO_POST"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
				$arResult["PATH_TO_POST1"] = $serverName.mb_substr($arResult["PATH_TO_POST"], 0, mb_strpos($arResult["PATH_TO_POST"], "#post_id#"));
				$arResult["PATH_TO_POST2"] = mb_substr($arResult["PATH_TO_POST"], mb_strpos($arResult["PATH_TO_POST"], "#post_id#") + mb_strlen("#post_id#"));
			}
		}
		else
		{
			$arResult["FATAL_MESSAGE"] = GetMessage("BLOG_ERR_NO_RIGHTS");
			$arResult["FATAL_MESSAGE_CODE"] = "NO_RIGHTS";
		}
	}
	else
	{
		$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_BLOG");
		$arResult["FATAL_MESSAGE_CODE"] = "NO_BLOG";
		CHTTP::SetStatus("404 Not Found");
	}
}
else
{
	$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_BLOG");
	CHTTP::SetStatus("404 Not Found");
}
$arResult['ID'] = $this->randString(6);
$this->IncludeComponentTemplate();
?>