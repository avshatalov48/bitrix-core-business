<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
elseif (!CModule::IncludeModule("blog"))
	return;

$SocNetGroupID = false; 
$db_blog_group = CBlogGroup::GetList(array("ID" => "ASC"), Array("SITE_ID" => WIZARD_SITE_ID, "NAME" => GetMessage("BLOG_DEMO_GROUP")." (".WIZARD_SITE_ID.")"));
if ($res_blog_group = $db_blog_group->Fetch())
{
	 if (WIZARD_INSTALL_DEMO_DATA)
	 {
		$db_blog = CBlog::GetList(array(), array("GROUP_ID" => $res_blog_group["ID"]), false, false, array("ID"));
		if ($res_blog = $db_blog->Fetch())
		{
			do 
			{
				CBlog::Delete($res_blog["ID"]); 
			} while ($res_blog = $db_blog->Fetch()); 
		}
		$SocNetGroupID = $res_blog_group["ID"];
	 }
	 else
	 {
		if($arBlog = CBlog::GetByUrl("admin-blog-".WIZARD_SITE_ID))
		{
				CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID."/footer.php", array("BLOG_URL" => $arBlog["URL"]));
			CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("BLOG_URL" => $arBlog["URL"], "SEF_FOLDER" => WIZARD_SITE_DIR));
		}
		
		if($arUtilBlog = CBlog::GetByUrl("util-photo-blog-".WIZARD_SITE_ID))
			CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/photo.php", array("PHOTO_BLOG_URL" => $arUtilBlog["URL"]));

	 	return;
	 }
}

$utilGroupID = false; 
$db_blog_group = CBlogGroup::GetList(array("ID" => "ASC"), Array("SITE_ID" => WIZARD_SITE_ID, "NAME" => GetMessage("BLOG_DEMO_GROUP_UTIL")." (".WIZARD_SITE_ID.")"));
if ($res_blog_group = $db_blog_group->Fetch())
{
	 if (WIZARD_INSTALL_DEMO_DATA)
	 {
		$db_blog = CBlog::GetList(array(), array("GROUP_ID" => $res_blog_group["ID"]), false, false, array("ID"));
		if ($res_blog = $db_blog->Fetch())
		{
			do 
			{
				CBlog::Delete($res_blog["ID"]); 
			} while ($res_blog = $db_blog->Fetch()); 
		}
		$utilGroupID = $res_blog_group["ID"];
	 }
	 else
	 {
	 	return;
	 }
}

COption::SetOptionString('blog','avatar_max_size','30000');
COption::SetOptionString('blog','avatar_max_width','100');
COption::SetOptionString('blog','avatar_max_height','100');
COption::SetOptionString('blog','image_max_width','600');
COption::SetOptionString('blog','image_max_height','600');
COption::SetOptionString('blog','allow_alias','Y');
COption::SetOptionString('blog','block_url_change','Y');
COption::SetOptionString('blog','GROUP_DEFAULT_RIGHT','D');
COption::SetOptionString('blog','show_ip','N');
COption::SetOptionString('blog','enable_trackback','N');
COption::SetOptionString('blog','allow_html','Y');

$APPLICATION->SetGroupRight("blog", 1, "W");
COption::SetOptionString("blog", "GROUP_DEFAULT_RIGHT", "D");
$db_res = CBlogSitePath::GetList(array(), array("SITE_ID" => WIZARD_SITE_ID));
if ($db_res && $res = $db_res->Fetch())
{
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		$res_tmp = array(
			"B" => WIZARD_SITE_DIR,
			"P" => WIZARD_SITE_DIR."#post_id#/", 
			);
		do 
		{
			if (array_key_exists($res["TYPE"], $res_tmp) && $res["PATH"] != $res_tmp[$res["TYPE"]])
			{
				CBlogSitePath::Update($res["ID"], array("PATH" => $res_tmp[$res["TYPE"]])); 
			}
		} while ($db_res && $res = $db_res->Fetch()); 
	}
}
else
{
	CBlogSitePath::Add(array("SITE_ID" => WIZARD_SITE_ID, "PATH" => WIZARD_SITE_DIR, "TYPE" => "B"));
	CBlogSitePath::Add(array("SITE_ID" => WIZARD_SITE_ID, "PATH" => WIZARD_SITE_DIR."#post_id#/", "TYPE" => "P")); 

}

if ($SocNetGroupID == false)
	$SocNetGroupID = CBlogGroup::Add(array("SITE_ID" => WIZARD_SITE_ID, "NAME" => GetMessage("BLOG_DEMO_GROUP")." (".WIZARD_SITE_ID.")"));
if ($utilGroupID == false)
	$utilGroupID = CBlogGroup::Add(array("SITE_ID" => WIZARD_SITE_ID, "NAME" => GetMessage("BLOG_DEMO_GROUP_UTIL")." (".WIZARD_SITE_ID.")"));
	
/********************************************************************
				Creating Posts array and arranging for Users 
********************************************************************/
$dir = WIZARD_SERVICE_ABSOLUTE_PATH."/images/"; 
$arImages = array(); 
if (is_dir($dir) && $dh = opendir($dir)) 
{
	while (($file = readdir($dh)) !== false) 
	{
		if ($file == "." || $file == "..")
			continue; 
		$arImages[$file] = array (
				"name" => $file, 
				"type" => "image/jpeg", 
				"tmp_name" => $dir.$file, 
				"error" => 0, 
				"size" => filesize($dir.$file)); 
	}
	closedir($dh);
}
$arPosts = array(
	array(
		"TITLE" => GetMessage("BLOG_MESSAGE1_TITLE"),
		"DETAIL_TEXT" => GetMessage("BLOG_MESSAGE1_BODY"),
		"DETAIL_TEXT_TYPE" => "text",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_PUBLISH" => $DB->GetNowFunction(),
		"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
		"ENABLE_TRACKBACK" => 'N',
		"ENABLE_COMMENTS" => 'Y',
		"VIEWS" => 10,
		"CATEGORY_ID" => GetMessage("BLOG_MESSAGE1_TAGS"),
		"PERMS_POST" => array(1 => BLOG_PERMS_READ, 2 => BLOG_PERMS_READ),
		"PERMS_COMMENT" => array(1 => BLOG_PERMS_WRITE, 2 => BLOG_PERMS_WRITE), 
		"COMMENTS" => array(
			array(
				"AUTHOR" => GetMessage("BLOG_MESSAGE1_COMMENTS1_AUTHOR"), 
				"TEXT" => GetMessage("BLOG_MESSAGE1_COMMENTS1_TEXT")), 
			array(
				"AUTHOR" => GetMessage("BLOG_MESSAGE1_COMMENTS2_AUTHOR"), 
				"TEXT" => GetMessage("BLOG_MESSAGE1_COMMENTS2_TEXT"), 
				"COMMENTS" => array(
					array(
						"AUTHOR" => false, 
						"TEXT" => GetMessage("BLOG_MESSAGE1_COMMENTS3_TEXT")
					)
				)
			)
		)
	), 
	array(
		"TITLE" => GetMessage("BLOG_MESSAGE2_TITLE"),
		"DETAIL_TEXT" => GetMessage("BLOG_MESSAGE2_BODY"),
		"DETAIL_TEXT_TYPE" => "text",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_PUBLISH" => $DB->GetNowFunction(),
		"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
		"ENABLE_TRACKBACK" => 'N',
		"ENABLE_COMMENTS" => 'Y',
		"VIEWS" => 12,
		"CATEGORY_ID" => GetMessage("BLOG_MESSAGE2_TAGS"), 
		"PERMS_POST" => array(1 => BLOG_PERMS_READ, 2 => BLOG_PERMS_READ),
		"PERMS_COMMENT" => array(1 => BLOG_PERMS_WRITE, 2 => BLOG_PERMS_WRITE), 
		"COMMENTS" => array(
			array(
				"AUTHOR" => GetMessage("BLOG_MESSAGE2_COMMENTS1_AUTHOR"), 
				"TEXT" => GetMessage("BLOG_MESSAGE2_COMMENTS1_TEXT")), 
			array(
				"AUTHOR" => GetMessage("BLOG_MESSAGE2_COMMENTS2_AUTHOR"), 
				"TEXT" => GetMessage("BLOG_MESSAGE2_COMMENTS2_TEXT"), 
				"COMMENTS" => array(
					array(
						"AUTHOR" => false, 
						"TEXT" => GetMessage("BLOG_MESSAGE2_COMMENTS3_TEXT")
					)
				)
			)
		)
	), 
	array(
		"TITLE" => GetMessage("BLOG_MESSAGE3_TITLE"),
		"DETAIL_TEXT" => GetMessage("BLOG_MESSAGE3_BODY"),
		"DETAIL_TEXT_TYPE" => "text",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_PUBLISH" => $DB->GetNowFunction(),
		"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
		"ENABLE_TRACKBACK" => 'N',
		"ENABLE_COMMENTS" => 'Y',
		"VIEWS" => 8,
		"CATEGORY_ID" => GetMessage("BLOG_MESSAGE3_TAGS"), 
		"PERMS_POST" => array(1 => BLOG_PERMS_READ, 2 => BLOG_PERMS_READ),
		"PERMS_COMMENT" => array(1 => BLOG_PERMS_WRITE, 2 => BLOG_PERMS_WRITE), 
		"COMMENTS" => array(
			array(
				"AUTHOR" => GetMessage("BLOG_MESSAGE3_COMMENTS1_AUTHOR"), 
				"TEXT" => GetMessage("BLOG_MESSAGE3_COMMENTS1_TEXT"), 
				"COMMENTS" => array(
					array(
					"AUTHOR" => false, 
					"TEXT" => GetMessage("BLOG_MESSAGE3_COMMENTS2_TEXT")
				)
				)
			), 
			array(
				"AUTHOR" => GetMessage("BLOG_MESSAGE3_COMMENTS3_AUTHOR"), 
				"TEXT" => GetMessage("BLOG_MESSAGE3_COMMENTS3_TEXT"), 
				"COMMENTS" => array(
					array(
						"AUTHOR" => false, 
						"TEXT" => GetMessage("BLOG_MESSAGE3_COMMENTS4_TEXT")
					)
				)
			)
		)
	), 
	array(
		"TITLE" => GetMessage("BLOG_MESSAGE4_TITLE"),
		"DETAIL_TEXT" => GetMessage("BLOG_MESSAGE4_BODY"),
		"DETAIL_TEXT_TYPE" => "text",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_PUBLISH" => $DB->GetNowFunction(),
		"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
		"ENABLE_TRACKBACK" => 'N',
		"ENABLE_COMMENTS" => 'Y',
		"VIEWS" => 7,
		"CATEGORY_ID" => GetMessage("BLOG_MESSAGE4_TAGS"), 
		"PERMS_POST" => array(1 => BLOG_PERMS_READ, 2 => BLOG_PERMS_READ),
		"PERMS_COMMENT" => array(1 => BLOG_PERMS_WRITE, 2 => BLOG_PERMS_WRITE)
	), 
	array(
		"TITLE" => GetMessage("BLOG_MESSAGE5_TITLE"),
		"DETAIL_TEXT" => GetMessage("BLOG_MESSAGE5_BODY"),
		"DETAIL_TEXT_TYPE" => "text",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_PUBLISH" => $DB->GetNowFunction(),
		"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
		"ENABLE_TRACKBACK" => 'N',
		"ENABLE_COMMENTS" => 'Y',
		"VIEWS" => 2,
		"CATEGORY_ID" => GetMessage("BLOG_MESSAGE5_TAGS"), 
		"PERMS_POST" => array(1 => BLOG_PERMS_READ, 2 => BLOG_PERMS_READ),
		"PERMS_COMMENT" => array(1 => BLOG_PERMS_WRITE, 2 => BLOG_PERMS_WRITE)
	) 
); 
$arPosts = array_reverse($arPosts);

/********************************************************************
				Create users blog and posts
********************************************************************/
$blogID = CBlog::Add(
	array(
		"NAME" => trim(GetMessage("BLG_NAME")." ".$USER->GetFullName()),
		"DESCRIPTION" => "",
		"GROUP_ID" => $SocNetGroupID,
		"ENABLE_IMG_VERIF" => 'Y',
		"EMAIL_NOTIFY" => 'Y',
		"USE_SOCNET" => 'N',
		"ENABLE_RSS" => "Y",
		"ALLOW_HTML" => "Y",
		"URL" => "admin-blog"."-".WIZARD_SITE_ID."",
		"ACTIVE" => "Y",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_UPDATE" => $DB->GetNowFunction(),
		"OWNER_ID" => 1,
		"PERMS_POST" => array("1" => BLOG_PERMS_READ, "2" => BLOG_PERMS_READ), 
		"PERMS_COMMENT" => array("1" => BLOG_PERMS_WRITE , "2" => BLOG_PERMS_WRITE),
	)
);

$blogUtilID = CBlog::Add(
	array(
		"NAME" => trim(GetMessage("BLG_NAME_UTIL"))." (".WIZARD_SITE_ID.")",
		"DESCRIPTION" => "",
		"GROUP_ID" => $utilGroupID,
		"ENABLE_IMG_VERIF" => 'Y',
		"EMAIL_NOTIFY" => 'Y',
		"USE_SOCNET" => 'N',
		"ENABLE_RSS" => "Y",
		"ALLOW_HTML" => "N",
		"URL" => "util-photo-blog-".WIZARD_SITE_ID,
		"ACTIVE" => "Y",
		"=DATE_CREATE" => $DB->GetNowFunction(),
		"=DATE_UPDATE" => $DB->GetNowFunction(),
		"OWNER_ID" => 1,
		"PERMS_POST" => array("1" => BLOG_PERMS_READ, "2" => BLOG_PERMS_READ), 
		"PERMS_COMMENT" => array("1" => BLOG_PERMS_WRITE , "2" => BLOG_PERMS_WRITE),
	)
);

if($res = $GLOBALS["APPLICATION"]->GetException())
{
	return $res->GetString();
	die();
}
	
$arBlog = CBlog::GetByID($blogID);
$arUtilBlog = CBlog::GetByID($blogUtilID);

$categoryID = array(); 
foreach ($arPosts as $k => $arPost)
{
	$arComments = $arPost["COMMENTS"]; 

	// CATEGORY
	$category = explode(", ", $arPost["CATEGORY_ID"]); 
	$category = array_unique($category);
	$iCategory = array(); 
	foreach ($category as $sCategoryValue)
	{
		if (empty($categoryID[$sCategoryValue]))
			$categoryID[$sCategoryValue] = CBlogCategory::Add(Array("BLOG_ID" => $blogID, "NAME" => $sCategoryValue)); 
		$iCategory[] = $categoryID[$sCategoryValue]; 
	}

	// IMAGES 
	$arImagesToUpdate = array(); 
	if (preg_match_all("/\[IMG\sID\=\#([a-z0-9\_\.]+)\#\]/is", $arPost["DETAIL_TEXT"], $arMatches))
	{
		$image_replacement = array(); 
		foreach ($arMatches[1] as $key_match => $file)
		{
			if (empty($arImages[$file]))
				continue; 
			$arImageFields = array(
				"BLOG_ID" => $blogID,
				"POST_ID" => 0,
				"USER_ID" => 1,
				"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
				"TITLE"		=> $file,
				"IMAGE_SIZE" => $arImages[$file]["size"], 
				"FILE_ID" => ($arImages[$file] + array("MODULE_ID" => "blog", "del" => "Y"))
			);
			$image_replacement[$key_match] = ""; 
			if ($imgID = CBlogImage::Add($arImageFields))
			{
				$image_replacement[$key_match] = "[IMG ID=".$imgID."]"; 
				$arImagesToUpdate[] = $imgID; 
			}
		}
		$arPost["DETAIL_TEXT"] = str_replace($arMatches[0], $image_replacement, $arPost["DETAIL_TEXT"]); 
	}
	// POST
	$arPost["BLOG_ID"] = $blogID; 
	$arPost["AUTHOR_ID"] = 1; 
	$arPost["CATEGORY_ID"] = implode(",", $iCategory); 
	$postID = CBlogPost::Add($arPost);
	// IMAGES UPDATE
	if (!empty($arImagesToUpdate))
	{
		foreach ($arImagesToUpdate as $imgID)
			CBlogImage::Update($imgID, array("POST_ID" => $postID)); 
	}
	// category update
	foreach ($iCategory as $sCategoryValue)
		CBlogPostCategory::Add(array("BLOG_ID" => $blogID, "POST_ID" => $postID, "CATEGORY_ID" => $categoryID[$sCategoryValue]));

	// COMMENTS
	if (!empty($arComments))
	{
		if (!function_exists("__blog_add_comments"))
		{
			function __blog_add_comments($arComments, $parentID, $arParams)
			{
				foreach ($arComments as $res)
				{
					$__arComments = array(); 
					if (!empty($res["COMMENTS"]))
					{
						$__arComments = $res["COMMENTS"]; 
					}

					$arComment = array(
						"POST_TEXT" => $res["TEXT"],
						"BLOG_ID" => $arParams["blogID"],
						"POST_ID" => $arParams["postID"],
						"PARENT_ID" => $parentID,
						"AUTHOR_ID" => $arParams["ownerID"],
						"AUTHOR_NAME" => $res["AUTHOR"], 
						"DATE_CREATE" => ConvertTimeStamp(false, "FULL"), 
						"AUTHOR_IP" => "192.168.0.108",
					); 
					if (!empty($res["AUTHOR"]))
						unset($arComment["AUTHOR_ID"]); 
					$parentID = CBlogComment::Add($arComment);
					if ($parentID <= 0)
						continue; 
					if (!empty($__arComments))
						__blog_add_comments($__arComments, $parentID, $arParams);
				}
			}
		}
		__blog_add_comments($arComments, false, array("postID" => $postID, "blogID" => $blogID, "ownerID" => 1));
	}
}
/********************************************************************
				Create users blog and posts and comments
********************************************************************/

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("BLOG_URL" => $arBlog["URL"], "SEF_FOLDER" => WIZARD_SITE_DIR));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/photo.php", array("PHOTO_BLOG_URL" => $arUtilBlog["URL"]));
CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID."/footer.php", array("BLOG_URL" => $arBlog["URL"]));
?>