<?	
//	ClearVars();
	if(!check_bitrix_sessid() || !CModule::IncludeModule("iblock")) 
		return;
		
	$strWarning = "";
	$bVarsFromForm = false;
	$arUGroupsEx = Array();
	$dbUGroups = CGroup::GetList();
	while($arUGroups = $dbUGroups -> Fetch())
	{
		if ($arUGroups["ANONYMOUS"] == "Y")
			$arUGroupsEx[$arUGroups["ID"]] = "R";
	}
	

	if ($_REQUEST["iblock"] == "Y" && $GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W")
	{
		if ($_REQUEST["create_iblock_type"] == "Y")
		{
			$arIBTLang = array();
			$arLang = array();
			$l = CLanguage::GetList();
			while($ar = $l->ExtractFields("l_"))
				$arIBTLang[]=$ar;
			
			for($i=0; $i<count($arIBTLang); $i++)
				$arLang[$arIBTLang[$i]["LID"]] = array("NAME" => $_REQUEST["iblock_type_name"]);
			
			$arFields = array(
				"ID" => $_REQUEST["iblock_type_name"],
				"LANG" => $arLang,
				"SECTIONS" => "Y");

			$GLOBALS["DB"]->StartTransaction();
			$obBlocktype = new CIBlockType;
			$IBLOCK_TYPE_ID = $obBlocktype->Add($arFields);
			if ($IBLOCK_TYPE_ID == '')
			{
				$strWarning .= $obBlocktype->LAST_ERROR;
				$GLOBALS["DB"]->Rollback();
				$bVarsFromForm = true;
			}
			else
			{
				$GLOBALS["DB"]->Commit();
				$_REQUEST["create_iblock_type"] = "N";
				$_REQUEST["iblock_type_name"] = "";
				$_REQUEST["iblock_type_id"] = $IBLOCK_TYPE_ID;
			}
		}
		
		$IBLOCK_TYPE_ID = $_REQUEST["iblock_type_id"];
		
		if ($IBLOCK_TYPE_ID)
		{
			$DB->StartTransaction();

			$arFields = Array(
				"ACTIVE"=>"Y",
				"NAME"=>$_REQUEST["iblock_name"],
				"IBLOCK_TYPE_ID"=>$IBLOCK_TYPE_ID,
				"LID"=>array());
			$ib = new CIBlock;
			
			$db_sites = CSite::GetList();
			while ($ar_sites = $db_sites->Fetch())
			{
				if ($ar_sites["ACTIVE"] == "Y")
					$arFields["LID"][] = $ar_sites["LID"];
				$arSites[] = $ar_sites;
			}
			
			if (empty($arFields["LID"]))
				$arFields["LID"][] = $ar_sites[0]["LID"];
			if (!empty($arUGroupsEx))
				$arFields["GROUP_ID"] = $arUGroupsEx;

			$ID = $ib->Add($arFields);
			if($ID <= 0)
			{
				$strWarning .= $ib->LAST_ERROR."<br>";
				$bVarsFromForm = true;
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
				$_REQUEST["new_iblock_name"] = "";
				$_REQUEST["new_iblock"] = "created";
			}
		}
	}
	
	if (!$bVarsFromForm && $_REQUEST["blog"] == "Y" && IsModuleInstalled("blog") && $GLOBALS["APPLICATION"]->GetGroupRight("blog") >= "W")
	{
		CModule::IncludeModule("blog");
		
		if ($_REQUEST["create_blog_group"] == "Y")
		{
			$arFields = array(
				"NAME" => $_REQUEST["blog_group_name"],
				"SITE_ID" => "");

			$arSites = array();
			$db_sites = CLang::GetList();
			while ($ar_sites = $db_sites->Fetch())
			{
				if ($ar_sites["DEF"] == "Y")
					$arFields["SITE_ID"] = $ar_sites["LID"];
				$arSites[] = $ar_sites;
			}
			if (empty($arFields["SITE_ID"]))
				$arFields["SITE_ID"] = $arSites[0]["LID"];
		
			$BLOG_GROUP_ID = CBlogGroup::Add($arFields);
			if ($BLOG_GROUP_ID <= 0)
			{
				$bVarsFromForm = true;

				if ($ex = $APPLICATION->GetException())
					$strWarning .= $ex->GetString().". <br />";
				else
					$strWarning .= "Error creating blog group.  <br />";
			}
			else
			{
				$arBlogGroupTmp = CBlogGroup::GetByID($BLOG_GROUP_ID);
				BXClearCache(True, "/".$arBlogGroupTmp["SITE_ID"]."/blog/blog_groups/");
				$_REQUEST["create_blog_group"] = "N";
				$_REQUEST["blog_group_id"] = $BLOG_GROUP_ID;
				$_REQUEST["blog_group_name"] = "";
			}
		}
		
		if (!$bVarsFromForm)
		{
			$arFields = array(
				"ACTIVE" => "N",
				"NAME" => $_REQUEST["blog_name"],
				"DESCRIPTION" => $_REQUEST["blog_description"],
				"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"URL" => $_REQUEST["blog_url"],
				"OWNER_ID" => $GLOBALS["USER"]->GetId(),
				"GROUP_ID" => $_REQUEST["blog_group_id"],
				"ENABLE_COMMENTS" => "Y", 
				"ENABLE_IMG_VERIF" => "Y", 
				"EMAIL_NOTIFY" => "N", 
				"ENABLE_RSS" => "N", 
				"ALLOW_HTML" => "N", 
				"PERMS_POST" => array("1" => "I", "2" => "I"),
				"PERMS_COMMENT" => array("1" => "P", "2" => "P"));
	
			$ID = CBlog::Add($arFields);
			
			if (intval($ID) <= 0)
			{
				$bVarsFromForm = true;
				if ($ex = $APPLICATION->GetException())
					$strWarning .= $ex->GetString()."<br />";
				else
					$strWarning .= "Error creating blog. <br />";
			}
		}
	}
	
	if ($bVarsFromForm)
	{
		ShowError($strWarning);
		include("step1.php");
	}
	else
	{
?>
<script>
window.location='/bitrix/admin/module_admin.php?step=3&lang=<?=LANGUAGE_ID."&id=photogallery&install=y&".bitrix_sessid_get()?>';
</script>
<?
	}
	
?>