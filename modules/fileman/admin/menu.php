<?
IncludeModuleLangFile(__FILE__);
if(!method_exists($USER, "CanDoOperation"))
	return false;

if($USER->CanDoOperation('fileman_view_file_structure'))
{
	if(!function_exists("__fileman_mnu_gen"))
	{
		function __fileman_fmnu_fldr_cmp($a, $b)
		{
			return strcmp(mb_strtoupper($a["sSectionName"]), mb_strtoupper($b["sSectionName"]));
		}

		function __fileman_mnu_gen($bLogical, $bFullList, $site, $path, $sShowOnly, $arSiteDirs=Array(), $bCountOnly = false, $arSitesDR_= Array(), $siteList = Array())
		{
			global $APPLICATION, $USER, $DB, $MESS;
			global $__tmppath;
			global $_fileman_menu_dist_dr;
			$aMenu = Array();
			if(count($siteList) <= 0)
			{
				$dbSitesList = CSite::GetList("lendir", "desc");
				$siteList = array();
				while ($arSite = $dbSitesList->GetNext())
				{
					if ($arSite['DOC_ROOT'] == CSite::GetSiteDocRoot($site) || $arSite['DOC_ROOT'] == '')
					{
						$siteList[] = array(
							'ID' => $arSite['ID'],
							'DIR' => $arSite['DIR'],
							'DOC_ROOT' => $arSite['DOC_ROOT']
						);
					}
				}
			}
			$path = preg_replace("'[\\/]+'", "/", $path);
			$bCheckFolders = $path == '' && $sShowOnly == '';

			$io = CBXVirtualIo::GetInstance();

			if(!$bCountOnly && mb_substr($sShowOnly, 0, mb_strlen($path)) != $path)
				return Array();

			$arFldrs = Array();
			$DOC_ROOT = CSite::GetSiteDocRoot($site);
			$foldersExists = false;

			$dir = $io->GetDirectory($DOC_ROOT.$path);
			$arChildren = $dir->GetChildren();
			foreach ($arChildren as $child)
			{
				if(!$child->IsDirectory())
					continue;

				$file = $child->GetName();
				if($bLogical && $arSiteDirs[$path.'/'.$file])
					continue;

				if(!$bCountOnly && !$bFullList && $sShowOnly != $path && mb_substr($sShowOnly, 0, mb_strlen($path.'/'.$file)) != $path.'/'.$file)
					continue;

				if(!$USER->CanDoFileOperation('fm_view_file',Array($site, $path."/".$file)) ||
				!$USER->CanDoFileOperation('fm_view_listing',Array($site, $path."/".$file)))
					continue;

				if($bLogical)
				{
					if(!$io->FileExists($DOC_ROOT.$path."/".$file."/.section.php"))
						continue;

					$sSectionName = "";
					include($io->GetPhysicalName($DOC_ROOT.$path."/".$file."/.section.php"));
					if($sSectionName == '')
						$sSectionName = GetMessage("FILEMAN_MNU_WN");
				}
				else
					$sSectionName = $file;

				$arFldrs[] = Array("sSectionName"=>$sSectionName, "file"=>$file);
			}

			usort($arFldrs, "__fileman_fmnu_fldr_cmp");

			for($i = 0, $l = count($arFldrs); $i < $l; $i++)
			{
				extract($arFldrs[$i]);

				if($bCountOnly)
					return Array('');

				$dynamic = true;
				for($ii = 0, $ll = count($siteList); $ii < $ll; $ii++)
				{
					$dir = trim($siteList[$ii]["DIR"], "/");
					if (mb_substr(trim($path.'/'.$file, "/"), 0, mb_strlen($dir)) == $dir)
					{
						$site_ = $siteList[$ii]["ID"];
						break;
					}
				}
				if(!$bCheckFolders && ($sShowOnly == $path || $bFullList))
				{
					$items = __fileman_mnu_gen($bLogical, $bFullList, $site, $path.'/'.$file, '', $arSiteDirs, true, $arSitesDR_, $siteList);
					if(count($items) <= 0)
						$dynamic = false;
				}

				$addUrl = "path=".urlencode($path.'/'.$file);
				$addUrl .= "&site=".$site_;
				if ($bLogical)
				{
					$addUrl .= "&logical=Y";
					if (count($arSitesDR_)>1)
					{
						$site_ = $site;
						foreach($arSitesDR_ as $k=>$s)
						{
							if ($k == mb_substr($DOC_ROOT.$path.'/'.$file, 0, mb_strlen($k)))
								$site_ = $s;
						}
					}
				}

				$more_urls = Array(
					"fileman_admin.php?".$addUrl,
					"fileman_access.php?".$addUrl,
					"fileman_file_upload.php?".$addUrl,
					"fileman_html_edit.php?".$addUrl,
					"fileman_file_edit.php?".$addUrl,
					"fileman_folder.php?".$addUrl,
					"fileman_menu_edit.php?".$addUrl,
					"fileman_newfolder.php?".$addUrl,
					"fileman_rename.php?".$addUrl,
				);

				if($__tmppath == $path.'/'.$file && ((!$bLogical && $_REQUEST["logical"]!="Y") || ($bLogical && $_REQUEST["logical"]=="Y")))
				{
					$more_urls[] = "fileman_html_edit.php";
					$more_urls[] = "fileman_file_view.php";
					$more_urls[] = "fileman_file_edit.php";
				}

				if ($bCheckFolders)
					$dynamic = __check_folder($site, $path.'/'.$file);

				$aMenu[] =
					array(
						"text" => htmlspecialcharsex($sSectionName),
						"url" => "fileman_admin.php?lang=".LANG."&".htmlspecialcharsex($addUrl),
						"dynamic" => $dynamic,
						"icon"=>"fileman_menu_icon_sections",
						"skip_chain"=>true,
						"module_id"=>"fileman",
						"more_url" => $more_urls,
						"items_id" => ($bLogical ? "menu_fileman_site_".$site."_".$path."/".$file : "menu_fileman_file_".$site."_".$path."/".$file),
						"title" => htmlspecialcharsex($sSectionName." (".$path.'/'.$file.")"),
						"items" => $bCheckFolders ? array() :  __fileman_mnu_gen($bLogical, $bFullList, $site, $path.'/'.$file, $sShowOnly, $arSiteDirs, false, $arSitesDR_, $siteList)
					);
			}
			return $aMenu;
		}

		function __check_folder($site, $path)
		{
			$DOC_ROOT = CSite::GetSiteDocRoot($site);
			$foldersExists = false;

			$io = CBXVirtualIo::GetInstance();
			$dir = $io->GetDirectory($DOC_ROOT.$path);
			$arChildren = $dir->GetChildren();
			foreach ($arChildren as $child)
			{
				if(!$child->IsDirectory())
					continue;

				return true;
			}
			return false;
		}

		function __add_site_logical_structure($arSites, $oMenu, $hide_physical_struc = false)
		{
			$sShowOnly = false;
			$bFullList = false;
			$site = $_REQUEST['site'];

			if(method_exists($oMenu, "IsSectionActive") && $oMenu->IsSectionActive("menu_fileman_site_".$arSites["ID"]."_"))
				$sShowOnly = rtrim($arSites["DIR"], "/");
			if(isset($_REQUEST['admin_mnu_menu_id']))
			{
				if($_REQUEST['admin_mnu_menu_id']=="menu_fileman_site_".$arSites["ID"]."_")
					$sShowOnly = rtrim($arSites["DIR"], "/");
				elseif(mb_substr($_REQUEST['admin_mnu_menu_id'], 0, mb_strlen("menu_fileman_site_".$arSites["ID"]."_")) == "menu_fileman_site_".$arSites["ID"]."_")
					$sShowOnly = mb_substr($_REQUEST['admin_mnu_menu_id'], mb_strlen("menu_fileman_site_".$arSites["ID"]."_"));
			}
			elseif(isset($_REQUEST['path']))
			{
				if($arSites["ID"] == $site)
				{
					$sShowOnly = rtrim($_REQUEST['path'], "/");
					$bFullList = true;
				}
			}
			$SITE_DIR = rtrim($arSites["DIR"], "/");

			return array(
				"text" => $arSites["NAME"],
				"url" => "fileman_admin.php?lang=".LANG.'&site='.$arSites["ID"].'&logical=Y&path='.urlencode($arSites["DIR"]),
				"dynamic"=>true,
				"module_id"=>"fileman",
				"more_url" => array(
					"fileman_admin.php?lang=".LANG.'&site='.$arSites["ID"].'&logical=Y&path='.urlencode($arSites["DIR"]),
					"fileman_access.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_admin.php?logical=Y&site=".$arSites["ID"],
					"fileman_file_download.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_file_edit.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_file_upload.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_file_view.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_folder.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_html_edit.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_menu_edit.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_newfolder.php?site=".$arSites["ID"].'&logical=Y',
					"fileman_rename.php?site=".$arSites["ID"].'&logical=Y',
				),
				"items_id" => "menu_fileman_site_".$arSites["ID"]."_",
				"title" => GetMessage("FILEMAN_MNU_STRUC").": ".$arSites["NAME"],
				"items" => ($sShowOnly !== false ? __fileman_mnu_gen(true, $bFullList, $arSites["ID"], $SITE_DIR, $sShowOnly, $arSiteDirs) : Array()),
			);
		}
	}

	global $site;
	global $_fileman_menu_dist_dr;
	global $__tmppath;

	$__tmppath = $_REQUEST['path'];
	switch($GLOBALS["APPLICATION"]->GetCurPage())
	{
		case "/bitrix/admin/fileman_file_edit.php":
		case "/bitrix/admin/fileman_file_view.php":
		case "/bitrix/admin/fileman_html_edit.php":
			if($_REQUEST['path'] && $_REQUEST['new']!='y')
				$__tmppath = dirname($_REQUEST['path']);
			break;
	}

	$aMenu = array(
		"parent_menu" => "global_menu_content",
		"section" => "fileman",
		"sort" => 100,
		"text" => GetMessage("FM_MENU_TITLE"),
		"title" => GetMessage("FM_MENU_DESC"),
		"icon" => "fileman_menu_icon",
		"page_icon" => "fileman_page_icon",
		"items_id" => "menu_fileman",
		"more_url" => array(
			"fileman_admin.php",
			"fileman_file_edit.php",
			"fileman_file_view.php",
			"fileman_folder.php",
			"fileman_html_edit.php",
			"fileman_menu_edit.php",
			"fileman_newfolder.php",
			"fileman_rename.php"
		),
		"items" => array()
	);

	$arSiteDirs = Array();
	$arSites = Array();
	$arSitesDR = Array();
	$arSitesDR_ = Array();
	$dbSitesList = CSite::GetList();
	while($arSites = $dbSitesList->GetNext())
	{
		$arSite[] = $arSites;
		$arSiteDirs[rtrim($arSites["DIR"], "/")] = true;
		$arSitesDR_[$arSites["ABS_DOC_ROOT"].rtrim($arSites["DIR"], "/")] = $arSites["ID"];
		if (!isset($arSitesDR[$arSites["ABS_DOC_ROOT"]]))
			$arSitesDR[$arSites["ABS_DOC_ROOT"]] = $arSites["ID"];
	}

	$_fileman_menu_dist_dr = (count($arSitesDR)>1);
	$hide_physical_struc = COption::GetOptionString("fileman", "hide_physical_struc", false);
	$site_count = count($arSite);

	for($i = 0; $i < $site_count; $i++)
		$aMenu["items"][] = __add_site_logical_structure($arSite[$i], $this, $hide_physical_struc);

	if (!$hide_physical_struc)
	{
		$addUrl = "path=".urlencode($path.'/'.$file);
		if(count($arSitesDR) > 1)
		{
			$arSMenu = Array();
			foreach($arSitesDR as $k=>$site_id)
			{
				$sShowOnly = false;
				if(method_exists($this, "IsSectionActive") && $this->IsSectionActive("menu_fileman_file_".$site_id."_"))
					$sShowOnly = "";
				if(isset($_REQUEST['admin_mnu_menu_id']))
				{
					if($_REQUEST['admin_mnu_menu_id']=="menu_fileman_file_".$site_id."_")
						$sShowOnly = "";
					elseif(mb_substr($_REQUEST['admin_mnu_menu_id'], 0, mb_strlen("menu_fileman_file_".$site_id."_")) == "menu_fileman_file_".$site_id."_")
						$sShowOnly = mb_substr($_REQUEST['admin_mnu_menu_id'], mb_strlen("menu_fileman_file_".$site_id."_"));
				}
				elseif(isset($_REQUEST['path']))
				{
					if($k == CSite::GetSiteDocRoot($site))
					{
						$sShowOnly = rtrim($_REQUEST['path'], "/");
						$bFullList = true;
					}
				}
				$maxl = 60;
				$arSMenu[] = array(
						"text" => (mb_strlen($k) <= $maxl ? $k : mb_substr($k, 0, 3).'...'.mb_substr($k, -($maxl - 6))),
						"url" => "fileman_admin.php?lang=".LANG.'&site='.$site_id.'&'.$addUrl,
						"more_url" => array(
							"fileman_access.php?site=".$site_id.'&'.$addUrl,
							"fileman_admin.php?site=".$site_id.'&'.$addUrl,
							"fileman_file_download.php?site=".$site_id.'&'.$addUrl,
							"fileman_file_edit.php?site=".$site_id.'&'.$addUrl,
							"fileman_file_upload.php?site=".$site_id.'&'.$addUrl,
							"fileman_file_view.php?site=".$site_id.'&'.$addUrl,
							"fileman_folder.php?site=".$site_id.'&'.$addUrl,
							"fileman_html_edit.php?site=".$site_id.'&'.$addUrl,
							"fileman_menu_edit.php?site=".$site_id.'&'.$addUrl,
							"fileman_newfolder.php?site=".$site_id.'&'.$addUrl,
							"fileman_rename.php?site=".$site_id.'&'.$addUrl
						),
						"dynamic" => true,
						"items_id" => "menu_fileman_file_".$site_id."_",
						"icon"=>"fileman_menu_icon_sections",
						"page_icon"=>"fileman_menu_page_icon_sections",
						"module_id" => "fileman",
						"title" => $k,
						"items" => ($sShowOnly !== false ? __fileman_mnu_gen(false, $bFullList, $site_id, "", $sShowOnly) : Array())
					);
			}

			$aMenu["items"][] = array(
				"text" => GetMessage("FILEMAN_MNU_F_AND_F"),
				"url" => "fileman_doc_roots.php?lang=".LANG,
				"items_id" => "menu_fileman_file_",
				"module_id"=> "fileman",
				"more_url" => array(
					'fileman_admin.php?lang='.LANG,
					"fileman_admin.php?lang=".LANG."&path=%2F",
					"fileman_admin.php",
					"fileman_file_edit.php",
					"fileman_file_view.php",
					"fileman_folder.php",
					"fileman_html_edit.php",
					"fileman_menu_edit.php",
					"fileman_newfolder.php",
					"fileman_rename.php"
				),
				"title" => GetMessage("FILEMAN_MNU_F_AND_F_TITLE"),
				"items" => $arSMenu
			);
		}
		else
		{
			$site_id = current($arSitesDR);

			$sShowOnly = false;
			if(isset($_REQUEST['admin_mnu_menu_id']))
			{
				if($_REQUEST['admin_mnu_menu_id']=="menu_fileman_file_".$site_id."_")
					$sShowOnly = "";
				elseif(mb_substr($_REQUEST['admin_mnu_menu_id'], 0, mb_strlen("menu_fileman_file_".$site_id."_")) == "menu_fileman_file_".$site_id."_")
					$sShowOnly = mb_substr($_REQUEST['admin_mnu_menu_id'], mb_strlen("menu_fileman_file_".$site_id."_"));
			}
			elseif(isset($_REQUEST['path']))
			{
				$sShowOnly = rtrim($_REQUEST['path'], "/");
				$bFullList = true;
			}

			$aMenu["items"][] = array(
				"text" => GetMessage("FILEMAN_MNU_F_AND_F"),
				"url" => "fileman_admin.php?lang=".LANG.'&'.$addUrl,
				"dynamic"=>true,
				"items_id" => "menu_fileman_file_".$site_id."_",
				"module_id"=>"fileman",
				"more_url" => array(
					"fileman_admin.php?lang=".LANG,
					"fileman_admin.php?lang=".LANG."&".$addUrl,
					"fileman_access.php?".$addUrl,
					"fileman_admin.php?".$addUrl,
					"fileman_file_download.php?".$addUrl,
					"fileman_file_edit.php?".$addUrl,
					"fileman_html_edit.php?".$addUrl,
					"fileman_file_upload.php?".$addUrl,
					"fileman_file_view.php?".$addUrl,
					"fileman_folder.php?".$addUrl,
					"fileman_menu_edit.php?".$addUrl,
					"fileman_newfolder.php?".$addUrl
				),
				"title" => GetMessage("FILEMAN_MNU_F_AND_F_TITLE"),
				"items" => ($sShowOnly!==false?__fileman_mnu_gen(false, $bFullList, $site_id, "", $sShowOnly, Array(),false,$arSitesDR_) : Array()),
			);
		}
	}
}

if (COption::GetOptionString('fileman', "use_medialib", "Y") != "N" && CModule::IncludeModule("fileman") && CMedialib::CanDoOperation('medialib_view_collection', 0, false, true))
{
	if (!is_array($aMenu))
	{
		$aMenu = array(
			"parent_menu" => "global_menu_content",
			"section" => "fileman",
			"sort" => 100,
			"text" => GetMessage("FM_MENU_TITLE"),
			"title" => GetMessage("FM_MENU_DESC"),
			"url" => "",
			"icon" => "fileman_menu_icon",
			"page_icon" => "fileman_page_icon",
			"items_id" => "menu_fileman",
			"more_url" => array(
				"fileman_admin.php",
				"fileman_file_edit.php",
				"fileman_file_view.php",
				"fileman_folder.php",
				"fileman_html_edit.php",
				"fileman_menu_edit.php",
				"fileman_newfolder.php",
				"fileman_rename.php"
			),
			"items" => array()
		);
	}

	$arMLTypes = CMedialib::GetTypes();
	$arItemTypes = array();
	for ($i = 0, $l = count($arMLTypes); $i < $l; $i++)
	{
		$arItemTypes[] = array(
			"text" => htmlspecialcharsex($arMLTypes[$i]["name"]),
			"url" => "fileman_medialib_admin.php?lang=".LANGUAGE_ID."&type=".$arMLTypes[$i]["id"],
			"dynamic" => false,
			"items_id" => "menu_medialib_".$arMLTypes[$i]["code"],
			"module_id"=>"fileman",
			"more_url" => array(
				"fileman_medialib_upload.php?lang=".LANGUAGE_ID."&type=".$arMLTypes[$i]["id"],
				"fileman_medialib_access.php?lang=".LANGUAGE_ID."&type=".$arMLTypes[$i]["id"]
			)
		);
	}

	$aMenu["items"][] = array(
		"text" => GetMessage("FM_MENU_MEDIALIB"),
		"title" => GetMessage("FM_MENU_MEDIALIB_TITLE"),
		"url" => "fileman_medialib_admin.php?lang=".LANGUAGE_ID,
		"dynamic" => false,
		"items_id" => "menu_medialib",
		"module_id"=>"fileman",
		"more_url" => array(
			"fileman_medialib_upload.php",
			"fileman_medialib_access.php"
		),
		"items" => $arItemTypes
	);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/sticker.php");
if (CSticker::CanDoOperation('sticker_view'))
{
	$aMenuStickers = array(
		"parent_menu" => "global_menu_services",
		"section" => "stickers",
		"sort" => 100,
		"text" => GetMessage("FMST_STICKERS"),
		"title" => GetMessage("FMST_STICKERS_TITLE"),
		"url" => "fileman_stickers_admin.php?lang=".LANG,
		"icon"=>"fileman_sticker_icon",
		"page_icon"=>"fileman_sticker_icon_sections",
		"items_id" => "menu_stickers",
		"more_url" => array(
			"fileman_stickers_admin.php"
		),
		"items" => array()
	);

	$aMenu = array(
		$aMenu,
		$aMenuStickers
	);
}

return $aMenu;
?>