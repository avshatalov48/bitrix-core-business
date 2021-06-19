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
				$_REQUEST["iblock_type_id"] = $IBLOCK_TYPE_ID;
			}
		}
		else
		    $IBLOCK_TYPE_ID = $_REQUEST["iblock_type_id"];

		if ($IBLOCK_TYPE_ID)
		{
			$DB->StartTransaction();

			$arFields = Array(
				"ACTIVE"=>"Y",
				"NAME"=>$_REQUEST["iblock_name"],
				"IBLOCK_TYPE_ID"=>$IBLOCK_TYPE_ID,
				"LID"=>array(),
				"DETAIL_PAGE_URL" => "#SITE_DIR#/$IBLOCK_TYPE_ID/#EXTERNAL_ID#/",
				"SECTION_PAGE_URL" => "#SITE_DIR#/$IBLOCK_TYPE_ID/category:#EXTERNAL_ID#/",
				"LIST_PAGE_URL" => "#SITE_DIR#/$IBLOCK_TYPE_ID/",
				"GROUP_ID" => Array("1" => "X", "2" => "R", "3" => "W")
			);

			if (IsModuleInstalled("bizproc"))
			{
				$arFields['WORKFLOW'] = 'N';
				$arFields['BIZPROC'] = 'Y';
			}

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

	if (!$bVarsFromForm && $_REQUEST["forum"] == "Y" && IsModuleInstalled("forum") && $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W")
	{
		CModule::IncludeModule("forum");

		$arFields = Array(
			"ACTIVE" => "Y",
			"NAME" => $_REQUEST["forum_name"],
			"GROUP_ID" => array(1 => "Y", 2 => 'E', 3 => 'M'),
			"SITES" => array()
		);
		$db_res = CSite::GetList();
		while ($res = $db_res->Fetch()):
			if (IsModuleInstalled("intranet"))
		    	$arFields["SITES"][$res["LID"]] = "/community/forum/forum#FORUM_ID#/topic#TOPIC_ID#/";
		    else
		    	$arFields["SITES"][$res["LID"]] = "/communication/forum/forum#FORUM_ID#/topic#TOPIC_ID#/";
		endwhile;

		$FORUM_ID = CForumNew::Add($arFields);
	}

	if (!$bVarsFromForm && IsModuleInstalled("socialnetwork"))
	{
		CModule::IncludeModule("socialnetwork");

		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/wiki/include.php');

		if ($_REQUEST["socnet_iblock"] == "Y" && $GLOBALS["APPLICATION"]->GetGroupRight("iblock") >= "W")
		{
			if ($_REQUEST["create_socnet_iblock_type"] == "Y")
			{
				if ($_REQUEST["create_iblock_type"] == "N" || ($_REQUEST["create_iblock_type"] == "Y" && $_REQUEST["socnet_iblock_type_name"] != $_REQUEST["iblock_type_name"]))
				{
					$arIBTLang = array();
					$arLang = array();
					$l = CLanguage::GetList();
					while($ar = $l->ExtractFields("l_"))
						$arIBTLang[]=$ar;

					for($i=0; $i<count($arIBTLang); $i++)
						$arLang[$arIBTLang[$i]["LID"]] = array("NAME" => $_REQUEST["socnet_iblock_type_name"]);

					$arFields = array(
						"ID" => $_REQUEST["socnet_iblock_type_name"],
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
						$_REQUEST["create_socnet_iblock_type"] = "N";
						$_REQUEST["socnet_iblock_type_name"] = "";
						$_REQUEST["socnet_iblock_type_id"] = $IBLOCK_TYPE_ID;
					}
				}
			}
			else
			    $IBLOCK_TYPE_ID = $_REQUEST["socnet_iblock_type_id"];

			if ($IBLOCK_TYPE_ID)
			{
				$DB->StartTransaction();

				$arFields = Array(
					"ACTIVE"=>"Y",
					"NAME"=>$_REQUEST["socnet_iblock_name"],
					"IBLOCK_TYPE_ID"=>$IBLOCK_TYPE_ID,
					"LID"=>array(),
					"DETAIL_PAGE_URL" => "",
					"SECTION_PAGE_URL" => "",
					"LIST_PAGE_URL" => "",
					"INDEX_ELEMENT" => "N",
					"INDEX_SECTION" => "N",
					"GROUP_ID" => Array('1' => 'X', "2" => "R", "3" => "W")
				);

				if (IsModuleInstalled('bizproc'))
				{
					$arFields['WORKFLOW'] = 'N';
					$arFields['BIZPROC'] = 'Y';
				}

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

				$SOCNET_ID = $ib->Add($arFields);
				if($SOCNET_ID <= 0)
				{
					$strWarning .= $ib->LAST_ERROR."<br>";
					$bVarsFromForm = true;
					$DB->Rollback();
				}
				else
				{
					$DB->Commit();
					$_REQUEST["new_socnet_iblock_name"] = "";
					$_REQUEST["new_socnet_iblock"] = "created";
					COption::SetOptionString("wiki", "socnet_iblock_type_id", $IBLOCK_TYPE_ID);
					COption::SetOptionString("wiki", "socnet_iblock_id", $SOCNET_ID);
					COption::SetOptionString("wiki", "socnet_enable", "Y");
					CWikiSocnet::EnableSocnet(true);
				}
			}
		}

		if (!$bVarsFromForm && $_REQUEST["socnet_forum"] == "Y" && IsModuleInstalled("forum") && $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W")
		{
			CModule::IncludeModule("forum");

			$arFields = Array(
				"ACTIVE" => "Y",
				"NAME" => $_REQUEST["socnet_forum_name"],
				"GROUP_ID" => array(1 => "Y", 2 => "E", 3 => "M"),
				"SITES" => array()
			);
			$db_res = CSite::GetList();
			while ($res = $db_res->Fetch()):
				if (IsModuleInstalled("intranet"))
			    	$arFields["SITES"][$res["LID"]] = "/community/forum/forum#FORUM_ID#/topic#TOPIC_ID#/";
			    else
			    	$arFields["SITES"][$res["LID"]] = "/communication/forum/forum#FORUM_ID#/topic#TOPIC_ID#/";
			endwhile;

			$SOCNET_FORUM_ID = CForumNew::Add($arFields);
			COption::SetOptionString("wiki", "socnet_forum_id", $SOCNET_FORUM_ID);
			COption::SetOptionString("wiki", "socnet_use_review", "Y");
		}
	}

	if ($bVarsFromForm)
	{
		ShowError($strWarning);
		include("step.php");
	}
	else
	{
		?>
		<script>
		window.location='/bitrix/admin/module_admin.php?step=3&lang=<?=LANGUAGE_ID."&id=wiki&install=y&".bitrix_sessid_get()?>';
		</script>
		<?
	}

?>