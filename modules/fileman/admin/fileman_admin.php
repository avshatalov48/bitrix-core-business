<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

//because Form's action don't changes without client's scripts, so just let's include the file for rights grouping change
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

// We prevent displaying WAF form and redirecting with JS errors
define("BX_SECURITY_SHOW_MESSAGE", true);

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["perms"])>0 && is_array($_POST["files"]) && count($_POST["files"])>0)
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/fileman_access.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$io = CBXVirtualIo::GetInstance();

$path = $APPLICATION->UnJSEscape(trim($_REQUEST['path']));
$site = $_REQUEST['site'];
$site = CFileMan::__CheckSite($site);
$show_perms_for = isset($_REQUEST['show_perms_for']) ? intval($_REQUEST['show_perms_for']) : 0;

if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_GET["fu_action"]) > 0 && check_bitrix_sessid())
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
	CFilemanUtils::Request($_GET["fu_action"], $site);
	die();
}

$searchSess="";

$bSearch = isset($_REQUEST["search"]) && $_REQUEST["search"] == 'Y';
if ($bSearch) // Disable logical
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
	$bReplace = isset($_REQUEST["is_replace"]) && $_REQUEST["is_replace"] == 'Y';
	$searchSess = preg_replace("/[^a-z0-9]/i", "", $_GET['ssess']);
	$logical = "N";
}

$sTableID = "tbl_fileman_admin";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

// Hide filter in the search result mode
if (!$bSearch)
{
	$arFilterFields = Array(
		"find_name",
		"find_timestamp_1",
		"find_timestamp_2",
		"find_type"
	);
	$lAdmin->InitFilter($arFilterFields);

	function CheckFilter() // Check inserted fields
	{
		if (isset($_REQUEST['del_filter']) && $_REQUEST['del_filter']=='Y')
			return false;

		global $strError, $find_timestamp_1, $find_timestamp_2, $lAdmin;
		$str = "";

		if (strlen(trim($find_timestamp_1))>0 || strlen(trim($find_timestamp_2))>0)
		{
			$date_1_ok = false;
			$date1_stm = MkDateTime(FmtDate($find_timestamp_1,"D.M.Y"),"d.m.Y");
			$date2_stm = MkDateTime(FmtDate($find_timestamp_2,"D.M.Y")." 23:59","d.m.Y H:i");
			if (!$date1_stm && strlen(trim($find_timestamp_1))>0)
				$str.= GetMessage("MAIN_WRONG_DATE_FROM")."<br>";
			else $date_1_ok = true;
			if (!$date2_stm && strlen(trim($find_timestamp_2))>0)
				$str.= GetMessage("MAIN_WRONG_DATE_TILL")."<br>";
			elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
				$str.= GetMessage("MAIN_FROM_TILL_DATE")."<br>";
		}
		$strError .= $str;

		if(strlen($str) > 0)
		{
			$lAdmin->AddFilterError($str);
			return false;
		}
		return true;
	}

	if(CheckFilter($arFilterFields))
		$arFilter = Array(
			"NAME" => ($find!='' && $find_type == "name"? $find : $find_name),
			"TIMESTAMP_1"	=> $find_timestamp_1,
			"TIMESTAMP_2"	=> $find_timestamp_2,
			"TYPE" => $find_type
		);
	else
		$arFilter = Array();
}
else
{
	$arFilter = Array();
}

$documentRoot = CSite::GetSiteDocRoot($site);

$arSite = CSite::GetById($site);
$arSite = $arSite->Fetch();

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y" ? '&logical=Y' : '');
$addUrl_s = $addUrl;

if ($bSearch)
	$addUrl_s .= '&search=Y'.($searchSess ? '&ssess='.$searchSess : '');

$path = $io->CombinePath("/", $path);
if (strpos($path, '/..') !== false)
	$path = '';

$absPath = $documentRoot.$path;
// Only for AJAX reuest from Quick Path controll in form - jump to viewing file
if (isset($_GET['check_for_file']) && $_GET['check_for_file'] == 'Y' && $io->FileExists($absPath))
	die('<script>top.location="'."fileman_file_view.php?path=".urlencode($path).'&'.bitrix_sessid_get().'&'.$addUrl.'"</script>');

if (!$io->DirectoryExists($absPath))
{
	$lAdmin->AddGroupError(GetMessage("FILEMAN_ADM_INCORRECT_PATH", array("#PATH#" => $path)));
	$path = "";
	$absPath = $documentRoot;
}

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$arPath = Array($site, $path);
$arFilter["MIN_PERMISSION"] = "R";
$handle_action = true;

CFileMan::SaveLastPath($path);

// Check user rights
if ($lAdmin->EditAction() && ($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_admin_folders')) && is_array($FIELDS))
{
	foreach ($FIELDS as $ID => $arFields)
	{
		if (!$lAdmin->IsUpdated($ID))
			continue;

		// For search results we have full pathes
		$pathFrom = $bSearch ? $ID : $path."/".$ID;
		$arPath_i = Array($site, $pathFrom);

		if (!($USER->CanDoFileOperation('fm_rename_file', $arPath_i) || $USER->CanDoFileOperation('fm_rename_file', $arPath_i)))
		{
			$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ACCESS_DENIED")." \"".$ID."\"", $ID);
			continue;
		}

		if (strlen($arFields["NAME"]) <= 0)
		{
			$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_NEW_NAME")." \"".$ID."\"", $ID);
		}
		else
		{
			$prev_name_i = CFileman::GetFileName($ID);
			$name_i = CFileman::GetFileName($arFields["NAME"]);

			$isPhpFrom = HasScriptExtension($prev_name_i);
			$isPhpTo = HasScriptExtension($name_i);

			if ($bSearch)
			{
				$path_i = substr($ID, 0, strlen($ID) - strlen($prev_name_i)); // substract path from $ID
				$pathTo = Rel2Abs($path_i, $name_i);
			}
			else
			{
				$pathTo = Rel2Abs($path, $name_i);
			}

			if (!($USER->CanDoFileOperation('fm_rename_file', $arPath_i) || $USER->CanDoFileOperation('fm_rename_file', $arPath_i)))
				$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ACCESS_ERROR"), $ID);
			elseif (!$USER->CanDoOperation('edit_php') && (substr($prev_name_i, 0, 1)=="." || substr($name_i, 0, 1)=="." || (!$isPhpFrom && $isPhpTo)))
				$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_TOPHPFILE_ERROR"), $ID);
			elseif (!$USER->CanDoOperation('edit_php') && $isPhpFrom && !$isPhpTo)
				$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_FROMPHPFILE_ERROR"), $ID);
			else
			{
				$pathParsed_tmp = CFileMan::ParsePath(Array($site, $pathTo));
				$strWarningTmp = CFileMan::CreateDir($pathParsed_tmp["PREV"]);

				if (strlen($strWarningTmp) > 0)
				{
					$lAdmin->AddGroupError($strWarningTmp, $ID);
				}
				else
				{
					if (($mess = CFileMan::CheckFileName(str_replace('/', '', $pathTo))) !== true)
					{
						$lAdmin->AddGroupError($mess, $ID);
					}
					elseif (!$io->FileExists($documentRoot.$pathFrom) && !$io->DirectoryExists($documentRoot.$pathFrom))
					{
						$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_FILE")." \"".$pathFrom."\" ".GetMessage("FILEMAN_RENAME_NOT_FOUND"), $ID);
					}
					elseif ($io->FileExists($documentRoot.$pathTo) || $io->DirectoryExists($documentRoot.$pathTo))
					{
						$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ALREADY_EXIST", Array("#FILE_NAME#" => $pathTo)), $ID);
					}
					elseif(!$io->Rename($documentRoot.$pathFrom, $documentRoot.$pathTo))
					{
						$lAdmin->AddGroupError(GetMessage("FILEMAN_RENAME_ERROR")." \"".$pathFrom."\" ".GetMessage("FILEMAN_RENAME_IN")." \"".$pathTo."\"", $ID);
					}
					else
					{
						// File was successfully renamed
						$module_id = "fileman";
						if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
						{
							$res_log['path'] = substr($pathTo, 1);
							if (is_dir($documentRoot.$pathTo))
								CEventLog::Log(
									"content",
									"SECTION_RENAME",
									"fileman",
									"",
									serialize($res_log)
								);
							else
								CEventLog::Log(
									"content",
									"FILE_RENAME",
									"fileman",
									"",
									serialize($res_log)
								);
						}
						if ($bSearch) // Rename item in search result DB
							CFilemanSearch::RenameInSearchResult($searchSess, $pathFrom, $pathTo);

						$APPLICATION->CopyFileAccessPermission($arPath_i, Array($site, $pathTo));
						$APPLICATION->RemoveFileAccessPermission($arPath_i);
					}
				}
			}
		}
	}
	$handle_action = false;
}

// Handling actions: group and single
if (($arID = $lAdmin->GroupAction()) && ($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_admin_folders')) && $handle_action)
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arID = array();
		if ($bSearch)
		{
			$searchRes = CFilemanSearch::GetSearchResult($searchSess);
			for($i = 0, $l = count($searchRes); $i < $l; $i++)
				$arID[] = $searchRes[$i]['path'];
		}
		elseif (!CSite::IsDistinctDocRoots() || strlen($site) > 0 || strlen($path) > 0)
		{
			$DOC_ROOT = CSite::GetSiteDocRoot($site);

			$path = $io->CombinePath("/", $path);
			$arParsedPath = CFileMan::ParsePath(Array($site, $path));
			$abs_path = $DOC_ROOT.$path;

			CFileMan::GetDirList(Array($site, $path), $arDirs, $arFiles, $arFilter, Array($by => $order), "DF",false,true);

			foreach ($arDirs as $Dir)
					$arID[] = $Dir["NAME"];

			foreach ($arFiles as $File)
				$arID[] = $File["NAME"];
		}
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0 || $ID == '.')
			continue;

		// For search results we have full pathes
		$pathEx = $bSearch ? $ID : $path."/".$ID;
		$arPath_i = Array($site, $pathEx);

		switch ($_REQUEST['action'])
		{
			case "delete":

				if (!($USER->CanDoFileOperation('fm_delete_file',$arPath_i) || $USER->CanDoFileOperation('fm_delete_folder',$arPath_i)))
					break;

				$module_id = "fileman";
				if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
				{
					$res_log['file_name'] = $ID;
					$res_log['path'] = substr($path, 1);
					if (is_dir($_SERVER['DOCUMENT_ROOT']."/".$res_log['path']."/".$ID))
					{
						$res_log['path'] = empty($res_log['path']) ? $ID : $res_log['path']."/".$ID;
						CEventLog::Log(
							"content",
							"SECTION_DELETE",
							"fileman",
							"",
							serialize($res_log)
						);
					}
					else
						CEventLog::Log(
							"content",
							"FILE_DELETE",
							"fileman",
							"",
							serialize($res_log)
						);
				}
				@set_time_limit(0);
				$strWarning_tmp = CFileMan::DeleteEx(Array($site, CFileMan::NormalizePath($pathEx)));
				// Delete file from search results, stored in db
				if ($bSearch)
					CFilemanSearch::DelFromSearchResult($searchSess, $pathEx);

				if(strlen($strWarning_tmp) > 0)
					$lAdmin->AddGroupError($strWarning_tmp, $ID);
				break;
			case "copy":
			case "move":
				if (!($USER->CanDoFileOperation('fm_create_new_file',$arPath_i) ||
				$USER->CanDoFileOperation('fm_create_new_folder',$arPath_i)) ||
				(!($USER->CanDoFileOperation('fm_delete_file',$arPath_i) ||
				$USER->CanDoFileOperation('fm_delete_folder',$arPath_i)) &&
				$_REQUEST['action'] == 'move'))
					break;

				if (!CSite::IsDistinctDocRoots() || CFileMan::__CheckSite($copy_to_site) === false)
					$copy_to_site = $site;

				$name_i = $bSearch ? CFileman::GetFileName($ID) : $ID;
				if (($mess = CFileMan::CheckFileName(str_replace('/', '', $copy_to))) !== true)
					$lAdmin->AddGroupError($mess, $ID);
				else
					$strWarning_tmp = CFileMan::CopyEx(Array($site, CFileMan::NormalizePath($pathEx)), Array($copy_to_site, CFileMan::NormalizePath($copy_to."/".$name_i)), ($_REQUEST['action'] == "move" ? true : false));

				if ($bSearch && $_REQUEST['action'] == "move")
					CFilemanSearch::DelFromSearchResult($searchSess, $pathEx);

				if (strlen($strWarning_tmp) > 0)
					$lAdmin->AddGroupError($strWarning_tmp, $ID);

				break;
		}
	}
}

InitSorting();
if (!$bSearch) // Display files and folders list
{
	$arDirs = array();
	$arFiles = array();
	$title = GetMessage("FILEMAN_TITLE");
	if($USER->CanDoFileOperation('fm_view_listing', $arPath))
	{
		CFileMan::GetDirList(Array($site, $path), $arDirs, $arFiles, $arFilter, Array($by => $order), "DF", $logical=='Y',true);

		if(strlen($path) > 0)
		{
			$dname = $path;
			if($logical=="Y")
			{
				if($io->FileExists($absPath."/.section.php"))
				{
					@include($io->GetPhysicalName($absPath."/.section.php"));
					if(strlen($sSectionName)<=0)
						$sSectionName = GetMessage("FILEMAN_ADM_UNTITLED");
					$dname = $sSectionName;
				}
			}

			$lAdmin->onLoadScript = "BX.adminPanel.setTitle('".CUtil::JSEscape($title.": ".$dname)."');";
			$title = $title.": ".$dname;
		}
		else
		{
			$lAdmin->onLoadScript = "BX.adminPanel.setTitle('".CUtil::JSEscape($title)."');";
		}
	}

	$arDirContent_t = array_merge($arDirs, $arFiles);
	$arDirContent = Array();

	for($i=0,$l = count($arDirContent_t);$i<$l;$i++)
	{
		$Elem = $arDirContent_t[$i];
		$arPath = Array($site, $Elem['ABS_PATH']);
		if(($Elem["TYPE"]=="F" && !$USER->CanDoFileOperation('fm_view_file',$arPath)) ||
		($Elem["TYPE"]=="D" && !$USER->CanDoFileOperation('fm_view_listing',$arPath)) ||
		($Elem["TYPE"]=="F" && $Elem["NAME"]==".section.php"))
			continue;
		$arDirContent[] = $Elem;
	}
	unset($arDirContent_t);
}
else // Displaying search result
{
	$arDirContent = Array();
	$date_format = CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL"));

	//CUtil::JSPostUnescape(); http://jabber.bx/view.php?id=32552

	if (isset($_POST['sres']) && CFilemanSearch::CheckSearchSess($searchSess))
		$searchRes = CFilemanSearch::SetSearchResult($_POST['sres'], $searchSess);
	else
		$searchRes = CFilemanSearch::GetSearchResult($searchSess, array($by, $order));


	for($i = 0, $l = count($searchRes); $i < $l; $i++)
	{

		$elPath = $searchRes[$i]['path'];
		$fullPath = $_SERVER["DOCUMENT_ROOT"].$elPath;
		$bIsDir = $io->DirectoryExists($fullPath);

		$arPerm = $APPLICATION->GetFileAccessPermission(Array($site, $elPath), $USER->GetUserGroupArray(), true);

		$arEl = array(
			"PATH" => $fullPath,
			"ABS_PATH" => $elPath,
			"NAME" => CFileman::GetFileName($elPath),
			"PERMISSION" => $arPerm[0],
			"TIMESTAMP" => $searchRes[$i]['time'],
			"DATE" => date($date_format, $searchRes[$i]['time']),
			"SIZE" => $bIsDir ? 0 : $searchRes[$i]['size'],
			"TYPE" => $bIsDir ? "D" : "F"
		);

		if (count($arPerm[1]) > 0)
			$arEl["PERMISSION_EX"] = $arPerm[1];

		$arDirContent[] = $arEl;
	}
}

$db_DirContent = new CDBResult;
$db_DirContent->InitFromArray($arDirContent);
$db_DirContent = new CAdminResult($db_DirContent, $sTableID);
$db_DirContent->NavStart(array(
	"sNavID" => "fileman_admin".$path,
	"nPageSize" => 20,
));

// Init list params

$lAdmin->NavText($db_DirContent->GetNavPrint(GetMessage("FILEMAN_PAGES")));

// List header
if($logical=='Y')
{
	$arHeaders = array(
		array("id"=>"LOGIC_NAME", "content"=>GetMessage("FILEMAN_FILE_NAME"), "default"=>true),
		array("id"=>"NAME", "content"=>GetMessage("FILEMAN_REAL_FILE_NAME"), "sort"=>"name_nat"),
		array("id"=>"SIZE","content"=>GetMessage("FILEMAN_ADMIN_FILE_SIZE"), "sort"=>"size", "default"=>true),
		array("id"=>"DATE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TIMESTAMP'), "sort"=>"timestamp", "default"=>true),
		array("id"=>"TYPE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TYPE'), "sort"=>"", "default"=>true)
	);
}
else
{
	$arHeaders = array(
		array("id"=>"NAME", "content"=>GetMessage("FILEMAN_FILE_NAME"), "sort"=>"name_nat", "default"=>true),
		array("id"=>"SIZE","content"=>GetMessage("FILEMAN_ADMIN_FILE_SIZE"), "sort"=>"size", "default"=>true),
		array("id"=>"DATE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TIMESTAMP'), "sort"=>"timestamp", "default"=>true),
		array("id"=>"TYPE", "content"=>GetMessage('FILEMAN_ADMIN_FILE_TYPE'), "sort"=>"", "default"=>true)
	);
}

if (!CFileMan::IsWindows())
	$arHeaders[] = array("id"=>"PERMS", "content"=>GetMessage('FILEMAN_ADMIN_ACCESS_PERMS'), "sort"=>"", "default"=>true);
$arHeaders[] = array("id"=>"PERMS_B", "content"=>GetMessage('FILEMAN_ADMIN_ACCESS_PERMS_B'), "sort"=>"", "default"=>true);
$lAdmin->AddHeaders($arHeaders);


if(IntVal($show_perms_for) > 0)
	$lAdmin->AddVisibleHeaderColumn("PERMS_B");

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

if(!$bSearch && strlen($path) > 0 && ($logical != "Y" || rtrim($arSite["DIR"], "/") != rtrim($arParsedPath["FULL"], "/")))
{
	$row =& $lAdmin->AddRow(".", array("NAME" => GetMessage("FILEMAN_UP")));

	$dbSitesList = CSite::GetList($b = "lendir", $o = "desc");
	while ($arSite = $dbSitesList->GetNext())
	{
		if ($arSite['DOC_ROOT'] == CSite::GetSiteDocRoot($site) || $arSite['DOC_ROOT'] == '')
		{
			$resSites[] = array(
				'ID' => $arSite['ID'],
				'DIR' => $arSite['DIR'],
				'DOC_ROOT' => $arSite['DOC_ROOT']
			);
		}
	}
	$cnt_resSites = count($resSites);
	for($i = 0; $i < $cnt_resSites; $i++)
	{
		$dir = trim($resSites[$i]["DIR"], "/");
		if (substr(trim($arParsedPath["PREV"], "/"), 0, strlen($dir)) == $dir)
		{
			$site = $resSites[$i]["ID"];
			break;
		}
	}

	if($logical == "Y")
		$showField = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".$site."&path=".urlencode(urlencode($arParsedPath["PREV"]))."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);\"><span class=\"adm-submenu-item-link-icon fileman_icon_folder_up\" alt=\"".GetMessage("FILEMAN_UP")."\"></span>&nbsp;<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".$site."&path=".urlencode(urlencode($arParsedPath["PREV"]))."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);\">..</a>";
	else
		$showField = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".$site."&path=".urlencode(urlencode($arParsedPath["PREV"]))."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);\"><span class=\"adm-submenu-item-link-icon fileman_icon_folder_up\" alt=\"".GetMessage("FILEMAN_UP")."\"></span>&nbsp;<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".$site."&path=".urlencode(urlencode($arParsedPath["PREV"]))."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);\">..</a>";

	$row->AddField("NAME", $showField);
	$row->AddField("LOGIC_NAME", $showField);
	$row->AddField("SIZE", "");
	$row->AddField("DATE", "");
	$row->AddField("TYPE", "");

	if (!CFileMan::IsWindows())
		$row->AddField("PERMS", "");

	$row->AddField("PERMS_B", "");

	$arActions = Array();

	$arActions[] = array(
		"ICON" => "",
		"TEXT" => GetMessage('FILEMAN_N_OPEN'),
		"DEFAULT" => true,
		"ACTION" => "javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".$site."&path=".urlencode($arParsedPath["PREV"])."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);"
	);

	$row->AddActions($arActions);
}

// Building list
while($Elem = $db_DirContent->NavNext(true, "f_"))
{
	$arPath = Array($site, $Elem['ABS_PATH']);
	$fpath = $bSearch ? $Elem['ABS_PATH'] : ($path == "/" ? "" : $path)."/".$Elem["NAME"];
	$fpathUrl = urlencode($fpath);
	for($i = 0; $i < $cnt_resSites; $i++)
	{
		$dir = trim($resSites[$i]["DIR"], "/");
		if (substr(trim($fpath, "/"), 0, strlen($dir)) == $dir)
		{
			$site = $resSites[$i]["ID"];
			break;
		}
	}
	//$fname = $documentRoot.$path."/".$Elem["NAME"];
	$fname = $documentRoot.$fpath;
	$fnameConverted = CBXVirtualIoFileSystem::ConvertCharset($fname); //http://www.jabber.bx/view.php?id=26893
	$bIsDir = $io->DirectoryExists($fname);
	$arrIsDir[$fpath] = $bIsDir;
	if(!file_exists($fnameConverted))
	{
		$lAdmin->AddGroupError(GetMessage("FILEMAN_ADMIN_FLIST_ERROR"));
		break;
	}

	if ($bSearch)
		$f_NAME = $Elem['ABS_PATH'];

	$showFieldIcon = "";
	$showFieldText = "";
	if($Elem["TYPE"] == "D")
	{
		$showFieldIcon = "<a href=\"fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."\" onclick=\"".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);return false;\"><span class='adm-submenu-item-link-icon fileman_icon_folder'></span></a>";
		$showFieldText = "<a href=\"fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."\" onclick=\"".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);return false;\">".$f_NAME."</a>";
	}
	else
	{
		$curFileType = CFileMan::GetFileTypeEx($f_NAME);
		if(preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|php6|phtml)$/', $f_NAME, $regs))
		{
			$showFieldIcon = "";
			$showFieldText = GetMessage("FILEMAN_ADMIN_MENU_TYPE")."&laquo;".htmlspecialcharsbx($regs[1])."&raquo;";
		}
		else
		{
			$showFieldIcon = "<IMG SRC=\"/bitrix/images/fileman/types/".$curFileType.".gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\">";
			$showFieldText = $f_NAME;
		}
	}

	$showField = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"left\">".$showFieldIcon."</td><td align=\"left\">&nbsp;".$showFieldText."</td></tr></table>";

	$row =& $lAdmin->AddRow($f_NAME, $Elem);

	if($row->VarsFromForm() && $_REQUEST["FIELDS"])
		$val = $_REQUEST["FIELDS"][$f_NAME]["NAME"];
	else
		$val = $f_NAME;

	// In the search result mode - we will give to modify only name of files or folders
	if ($bSearch)
		$val = CFileman::GetFileName($val);

	//$editField = "<input type=\"text\" name=\"FIELDS[".$f_NAME."][NAME]\" value=\"".htmlspecialcharsbx($val)."\" size=\"40\"> ";

	if($logical=='Y')
		$row->AddField("NAME", $showField);
	else
	{
		//$row->AddField("NAME", $showField, $editField);
		$row->AddViewField("NAME",$showField);
		$row->AddInputField("NAME", Array('size'=>'40', 'name' => 'FIELDS['.$f_NAME.'][NAME]', 'value' => htmlspecialcharsbx($val)));
	}


	if($logical == 'Y')
	{
		$showFieldIcon = "";
		$showFieldText = "";
		if(strlen($f_LOGIC_NAME)<=0)
			$f_LOGIC_NAME = htmlspecialcharsbx(GetMessage("FILEMAN_ADM_UNTITLED"));

		if($Elem["TYPE"] == "D")
		{
			$showFieldIcon = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);\" title=\"".htmlspecialcharsbx($fpath)."\"><span class='adm-submenu-item-link-icon fileman_icon_folder'></span></a>";
			$showFieldText = "<a href=\"javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);\" title=\"".htmlspecialcharsbx($fpath)."\">".$f_LOGIC_NAME."</a>";
		}
		else
		{
			$curFileType = CFileMan::GetFileTypeEx($f_NAME);
			if(preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/', $f_NAME, $regs))
			{
				$showFieldIcon = "";
				$showFieldText = GetMessage("FILEMAN_ADMIN_MENU_TYPE")."&laquo;".htmlspecialcharsbx($regs[1])."&raquo;";
			}
			else
			{
				$showFieldIcon = "<IMG SRC=\"/bitrix/images/fileman/types/".$curFileType.".gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\"  title=\"".htmlspecialcharsbx($fpath)."\">";
				$showFieldText = $f_LOGIC_NAME;
			}
		}

		$showField = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"left\">".$showFieldIcon."</td><td align=\"left\">&nbsp;".$showFieldText."</td></tr></table>";
		$row->AddViewField("LOGIC_NAME", $showField);
	}

	$row->AddField("SIZE", (($Elem["TYPE"] == "F") ? CFile::FormatSize($f_SIZE) : ""));
	$row->AddField("DATE", $f_DATE);

	$row->AddField("TYPE", ($Elem["TYPE"] == "D") ? GetMessage('FILEMAN_FOLDER') : htmlspecialcharsbx($arFilemanPredifinedFileTypes[$curFileType]["name"]));

	$showField = "";
	if (!CFileMan::IsWindows())
	{
		if(in_array("PERMS", $arVisibleColumns))
		{
			if($USER->CanDoFileOperation('fm_view_permission', $arPath))
			{
				$UnixFP = CFileMan::GetUnixFilePermissions($fname);
				$showField .= '<span title="'.$UnixFP[1].'">'.$UnixFP[0].'</span>';
				if(function_exists("posix_getpwuid") && function_exists("posix_getgrgid"))
				{
					$arrFileOwner = posix_getpwuid(fileowner($fnameConverted));
					$arrFileGroup = posix_getgrgid(filegroup($fnameConverted));
					$showField .= " ".$arrFileOwner['name']." ".$arrFileGroup['name'];
				}
			}
			else
				$showField = "&nbsp;";
		}
		$row->AddField("PERMS", $showField);
	}

	$showField = "";
	if (in_array("PERMS_B", $arVisibleColumns))
	{
		$showField = "&nbsp;";
		if(($USER->CanDoOperation('fileman_view_permissions') || $USER->CanDoOperation('fileman_edit_all_settings')) && $USER->CanDoFileOperation('fm_view_permission', $arPath))
		{
			$arP = $APPLICATION->GetFileAccessPermission(Array($site, $fpath), ((IntVal($show_perms_for) > 0) ? array($show_perms_for) : false), true);

			end($arP);
			$cur_dir_taskId = current($arP);
			if ($cur_dir_taskId)
			{
				$z = CTask::GetById($cur_dir_taskId);
				if ($r = $z->Fetch())
				if ($r['NAME'])
				{
					$showField = GetMessage(strtoupper($r['NAME']));
					if(strlen($showField) <= 0)
						$showField = $r['NAME'];
				}
			}
		}
	}
	$row->AddField("PERMS_B", $showField);

	$arActions = Array();

	if ($Elem["TYPE"] == "F")
	{
		if($USER->CanDoFileOperation('fm_view_listing', $arPath))
		{
			if ($USER->CanDoOperation('fileman_edit_menu_elements') && preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/', $f_NAME, $regs) && $USER->CanDoFileOperation('fm_edit_existent_file', $arPath))
			{
				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_MENU"),
					"DEFAULT" => true,
					"ACTION" => $lAdmin->ActionRedirect("fileman_menu_edit.php?path=".urlencode($path)."&site=".$site."&name=".urlencode($regs[1])."&".$addUrl."&".GetFilterParams("filter_")."")
				);
				if ($USER->CanDoOperation('edit_php') && $USER->CanDoFileOperation('fm_edit_existent_file', $arPath))
				{
					$arActions[] = array(
						"ICON" => "btn_fileman_php",
						"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_PHP"),
						"DEFAULT" => false,
						"ACTION" => $lAdmin->ActionRedirect("fileman_file_edit.php?path=".$fpathUrl."&full_src=Y&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
					);
				}
			}
			else
			{
				$curFilePreType = $arFilemanPredifinedFileTypes[$curFileType]["gtype"];

				if($curFilePreType == "text")
					$defaultEdit = COption::GetOptionString("fileman", "default_edit", "text");
				else
					$defaultEdit = "";

				if($curFilePreType == "text")
				{
					if($USER->CanDoFileOperation('fm_edit_existent_file', $arPath))
					{
						if (!HasScriptExtension($f_NAME) || $USER->CanDoFileOperation('fm_lpa', $arPath) || $USER->CanDoOperation('edit_php'))
						{
							$arActions[] = array(
								"ICON" => "btn_fileman_html",
								"DEFAULT" => (($defaultEdit == "html") ? True : False),
								"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_HTML"),
								"ACTION" => $lAdmin->ActionRedirect("fileman_html_edit.php?path=".$fpathUrl."&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
							);

							$arActions[] = array(
								"ICON" => "btn_fileman_text",
								"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_TEXT"),
								"DEFAULT" => (($defaultEdit == "text") ? True : False),
								"ACTION" => $lAdmin->ActionRedirect("fileman_file_edit.php?path=".$fpathUrl."&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
							);
						}

						if ($USER->CanDoOperation('edit_php'))
						{
							$arActions[] = array(
								"ICON" => "btn_fileman_php",
								"TEXT" => GetMessage("FILEMAN_ADMIN_EDIT_AS_PHP"),
								"DEFAULT" => (($defaultEdit == "php") ? True : False),
								"ACTION" => $lAdmin->ActionRedirect("fileman_file_edit.php?path=".$fpathUrl."&full_src=Y&site=".$site."&".$addUrl."&".GetFilterParams("filter_")."")
							);
						}
					}

					if (CModule::IncludeModule("workflow") && $USER->CanDoFileOperation('fm_edit_in_workflow', $arPath))
					{
						$st = '';
						$sid = '';
						$WFlink = CWorkFlow::GetEditLink(array($site, $fpath), $sid, $st);
						if (strlen($WFlink) > 0)
						{
							$arActions[] = array(
								"ICON" => "btn_fileman_galka",
								"DEFAULT" => (($Elem["PERMISSION"]=="U") ? True : False),
								"TEXT" => GetMessage("FILEMAN_EDIT_IN_WORKFLOW"),
								"ACTION" => $lAdmin->ActionRedirect($WFlink)
							);
						}
					}
				}

				if($USER->CanDoFileOperation('fm_view_file', $arPath) && ($USER->CanDoOperation('edit_php') || $USER->CanDoFileOperation('fm_lpa', $arPath) || !(HasScriptExtension($f_NAME) || substr($Elem["NAME"], 0, 1) == ".")))
				{
					$arActions[] = array(
						"ICON" => "btn_fileman_view",
						"TEXT" => GetMessage("FILEMAN_ADMIN_VIEW"),
						"DEFAULT" => (($curFilePreType != "text" && !$USER->IsAdmin()) ? True : False),
						"ACTION" => $lAdmin->ActionRedirect("fileman_file_view.php?path=".$fpathUrl."&site=".$site."&".$addUrl)
					);
				}

				if(($USER->CanDoFileOperation('fm_download_file', $arPath) && !(HasScriptExtension($f_NAME) || 	substr($Elem["NAME"], 0, 1) == ".")) || $USER->CanDoOperation('edit_php'))
				{
					$arActions[] = array(
						"ICON" => "btn_download",
						"TEXT" => GetMessage("FILEMAN_DOWNLOAD"),
						"ACTION" => $lAdmin->ActionRedirect("fileman_file_download.php?path=".$fpathUrl."&site=".$site."&".$addUrl)." setTimeout(function(){ CloseWaitWindow(); }, 3000);"
					);
				}

			}
		}
	}
	else
	{
		if($USER->CanDoFileOperation('fm_view_listing',$arPath))
		{
			$arActions[] = array(
				"ICON" => "",
				"TEXT" => GetMessage('FILEMAN_N_OPEN'),
				"DEFAULT" => true,
				"ACTION" => "javascript:".$sTableID.".GetAdminList('fileman_admin.php?".$addUrl."&site=".urlencode($site)."&path=".$fpathUrl."&show_perms_for=".IntVal($show_perms_for)."', GALCallBack);"
			);
		}

		if($USER->CanDoFileOperation('fm_edit_existent_folder', $arPath))
		{
			$arActions[] = array(
				"ICON" => "btn_fileman_prop",
				"TEXT" => GetMessage("FILEMAN_ADMIN_FOLDER_PROP"),
				"ACTION" => $lAdmin->ActionRedirect("fileman_folder.php?".$addUrl."&site=".urlencode($site)."&path=".$fpathUrl."")
			);
		}
	}

	$type = $Elem["TYPE"] == "F" ? 'file' : 'folder';
	if ($logical != "Y")
	{
		if ($Elem["TYPE"] == "F" && $USER->CanDoFileOperation('fm_view_file', $arPath) && ($USER->CanDoOperation('edit_php') || $USER->CanDoFileOperation('fm_lpa', $arPath) || !(HasScriptExtension($f_NAME) || substr($Elem["NAME"], 0, 1)==".")) || $Elem["TYPE"] == "D" && $USER->CanDoFileOperation('fm_view_listing', $arPath))
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "pack",
				"TEXT" => GetMessage("FILEMAN_ADMIN_ARC_PACK"),
				"ACTION" => "window.PackUnpackRun([{'path' : '".CUtil::JSEscape($fpath)."', 'isDir' : '".$arrIsDir[$fpath]."'}], true); return false;"
			);

			$is_archive = CBXArchive::IsArchive($fpath);
			if ($is_archive)
			{
				$arActions[] = array(
					"ICON" => "unpack",
					"TEXT" => GetMessage("FILEMAN_ADMIN_ARC_UNPACK"),
					"ACTION" => "window.PackUnpackRun(['".CUtil::JSEscape($fpath)."'], false); return false;"
				);
			}
		}

		if($USER->CanDoFileOperation('fm_rename_'.$type, $arPath))
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "rename",
				"TEXT" => GetMessage("FILEMAN_RENAME_SAVE"),
				"ACTION" => 'setCheckbox(\''.CUtil::JSEscape($f_NAME).'\'); if('.$lAdmin->table_id.'.IsActionEnabled(\'edit\')){document.forms[\'form_'.$lAdmin->table_id.'\'].elements[\'action_button\'].value=\'edit\'; '.$lAdmin->ActionPost().'}else{document.location.href=\'fileman_rename.php?'.$addUrl.'&path='.urlencode($path).'&site='.$site.'&files[]='.CFileman::GetFileName($arPath[1]).'\'}'
			);
		}

		// Copy
		if(($USER->CanDoFileOperation('fm_view_file', $arPath) && ($USER->CanDoOperation('edit_php') || $USER->CanDoFileOperation('fm_lpa', $arPath) || !(HasScriptExtension($f_NAME) || substr($Elem["NAME"], 0, 1)=="."))) && $Elem["TYPE"] == "F" || $Elem["TYPE"] == "D" && $USER->CanDoFileOperation('fm_view_listing', $arPath))
		{
			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("FILEMAN_ADM_COPY"),
				"ACTION" => "window.CopyMoveRun([{'path' : '".CUtil::JSEscape($fpath)."', 'isDir' : '".$arrIsDir[$fpath]."'}], true); return false;"
			);
		}

		// Move
		if($USER->CanDoOperation('fileman_admin_folders') && $USER->CanDoFileOperation('fm_delete_'.$type, $arPath))
		{
			if(($USER->CanDoFileOperation('fm_view_file', $arPath) && ($USER->CanDoOperation('edit_php') || $USER->CanDoFileOperation('fm_lpa', $arPath) || !(HasScriptExtension($f_NAME) || substr($Elem["NAME"], 0, 1)=="."))) && $Elem["TYPE"] == "F" || $Elem["TYPE"] == "D" && $USER->CanDoFileOperation('fm_view_listing', $arPath))
			{
				$arActions[] = array(
					"ICON" => "move",
					"TEXT" => GetMessage("FILEMAN_ADM_MOVE"),
					"ACTION" => "window.CopyMoveRun([{'path' : '".CUtil::JSEscape($fpath)."', 'isDir' : '".$arrIsDir[$fpath]."'}], false); return false;"
				);
			}

			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("FILEMAN_ADMIN_DELETE"),
				"ACTION" => "if(confirm('".GetMessage('FILEMAN_ALERT_DELETE')."')) ".$lAdmin->ActionDoGroup(urlencode($f_NAME), "delete", $addUrl."&site=".urlencode($site)."&path=".urlencode($path)."&show_perms_for=".IntVal($show_perms_for))
			);
		}

		if ($USER->CanDoFileOperation('fm_edit_permission',$arPath))
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array(
				"ICON" => "access",
				"TEXT" => GetMessage("FILEMAN_ADMIN_ACCESS_PERMS_B"),
				"ACTION" => "setCheckbox('".Cutil::JSEscape($f_NAME)."'); setAccess('".Cutil::JSEscape($site)."', '".Cutil::JSEscape(urlencode($path))."');"
			);

			if (!CFileMan::IsWindows())
			{
				// $arActions[] = Array(
					// "ICON" => "access",
					// "TEXT" => GetMessage("FILEMAN_ADMIN_ACCESS_PERMS"),
					// "TITLE" => GetMessage("FM_UTIL_SERVER_PERM_TITLE"),
					// "ACTION" => "setCheckbox('".Cutil::JSEscape($f_NAME)."'); setAccess('".Cutil::JSEscape($site)."', '".Cutil::JSEscape($path)."', true);"
				// );
			}
		}

	}
	$row->AddActions($arActions);
}
$arPath = Array($site, $path);// arPath for current folder

// List's footer
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $db_DirContent->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$strHTML =
	"<input type=\"text\" name=\"copy_to\" size=\"18\" value=\"\" disabled>".
	"<input type=\"button\" name=\"copy_to_button\" value=\"...\" onClick=\"DRList();\" disabled>".
	"<input type=\"hidden\" name=\"copy_to_site\" value=\"\">";

// Show form with add buttons
$arGrActionAr = Array();
if($USER->CanDoFileOperation('fm_delete_'.$type,$arPath))
	$arGrActionAr["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");

if($USER->CanDoFileOperation('fm_edit_permission',$arPath))
{
	$arGrActionAr["access"] = array(
		"action" => "setAccess('".Cutil::JSEscape($site)."', '".Cutil::JSEscape(urlencode($path))."')",
		"value" => "access",
		"name" => GetMessage('FILEMAN_ADMIN_ACCESS_PERMS_B')
	);
	if (!CFileMan::IsWindows())
	{
		// $arGrActionAr["server_access"] = array(
			// "action" => "setAccess('".Cutil::JSEscape($site)."', '".Cutil::JSEscape($path)."', true)",
			// "value" => "server_access",
			// "name" => GetMessage('FILEMAN_ADMIN_ACCESS_PERMS')
		// );
	}
}

if($USER->CanDoFileOperation('fm_create_new_'.$type,$arPath))
{
	//$arGrActionAr["copy"] = GetMessage("FILEMAN_ADM_COPY");
	$arGrActionAr["copy"] = array(
		"action" => "setCopyMove('".Cutil::JSEscape($site)."', '".Cutil::JSEscape($path)."', true, ".CUtil::PhpToJSObject($arrIsDir).")",
		"value" => "copy",
		"name" => GetMessage("FILEMAN_ADM_COPY")
	);

	$arGrActionAr["pack"] = array(
		"action" => "setPackUnpack('".Cutil::JSEscape($site)."', '".Cutil::JSEscape($path)."', true, ".CUtil::PhpToJSObject($arrIsDir).")",
		"value" => "pack",
		"name" => GetMessage("FILEMAN_ADMIN_ARC_PACK"),
	);
}

if($USER->CanDoFileOperation('fm_create_new_'.$type, $arPath) && $USER->CanDoFileOperation('fm_delete_'.$type,$arPath))
{
	//$arGrActionAr["move"] = GetMessage("FILEMAN_ADM_MOVE");
	$arGrActionAr["move"] = array(
		"action" => "setCopyMove('".Cutil::JSEscape($site)."', '".Cutil::JSEscape($path)."', false, ".CUtil::PhpToJSObject($arrIsDir).")",
		"value" => "move",
		"name" => GetMessage("FILEMAN_ADM_MOVE")
	);
}

// if($USER->CanDoFileOperation('fm_create_new_'.$type, $arPath))
// {
	// $arGrActionAr["copy2"] = array(
		// "type" => "html",
		// "value" => "&nbsp;".GetMessage("FILEMAN_ADMIN_IN")."&nbsp;"
	// );
	// $arGrActionAr["copy1"] = array(
		// "type" => "html",
		// "value" => $strHTML
	// );
// }

if ($logical != "Y")
{
	$lAdmin->AddGroupActionTable(
		$arGrActionAr,
		array()
		//array("select_onchange" => "this.form.copy_to_button.disabled=this.form.copy_to.disabled=!(this[this.selectedIndex].value == 'copy' || this[this.selectedIndex].value == 'move')")
	);
}
$defaultEdit = COption::GetOptionString("fileman", "default_edit", "text");

if($USER->CanDoOperation('view_groups') && $USER->CanDoFileOperation('fm_view_permission', $arPath))
{
	$arDDMenu = array();
	$isB = false;
	$dbRes = CGroup::GetDropDownList();
	while ($arRes = $dbRes->Fetch())
	{
		if($show_perms_for == $arRes["REFERENCE_ID"])
			$isB = true;

		$arDDMenu[] = array(
			"TEXT" => $arRes["REFERENCE"],
			"ACTION" => $lAdmin->ActionAjaxReload("fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".urlencode($path)."&show_perms_for=".$arRes["REFERENCE_ID"]).';return false;',
			"ICON" =>	($show_perms_for == $arRes["REFERENCE_ID"] ? "checked" : "" ),
		);
	}

	$arDDMenu[] = array(
		"TEXT" => GetMessage("FILEMAN_ADM_CUR_USER"),
		"ACTION" => $lAdmin->ActionAjaxReload("fileman_admin.php?".$addUrl_s."&site=".urlencode($site)."&path=".urlencode($path)."&show_perms_for=0").';return false;',
		"ICON" =>	(!$isB ? "checked" : "" ),
	);
}

$aContext = Array();
if (!$bSearch) // Only for dir viewing, hide for search result mode
{
	if($USER->CanDoOperation('fileman_admin_folders') && $USER->CanDoFileOperation('fm_create_new_folder',$arPath))
	{
		$aContext[] = Array(
			"TEXT" => GetMessage("FILEMAN_ADMIN_ADD_FOLDER"),
			"ICON" => "btn_new_folder",
			"LINK" => "fileman_newfolder.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
			"TITLE" => GetMessage("FILEMAN_ADMIN_ADD_FOLDER")
		);
	}

	if($USER->CanDoOperation('fileman_admin_files') && $USER->CanDoFileOperation('fm_create_new_file',$arPath))
	{
		$aContext[] = Array(
			"TEXT" => GetMessage("FILEMAN_ADMIN_ADD_FILE"),
			"ICON" => "btn_new_file",
			"LINK" =>
				($defaultEdit == 'html'?
					"fileman_html_edit.php?".$addUrl."&site=".$site."&path=".urlencode($path)."&new=y"
				:
					(
					$defaultEdit == 'php' && $USER->IsAdmin()?
						"fileman_file_edit.php?".$addUrl."&site=".$site."&full_src=Y&path=".urlencode($path)."&new=y"
					:
						"fileman_file_edit.php?".$addUrl."&site=".$site."&path=".urlencode($path)."&new=y"
					)
				),
			"TITLE" => GetMessage("FILEMAN_ADMIN_ADD_FILE")
		);
	}

	if($USER->CanDoOperation('fileman_add_element_to_menu') && $USER->CanDoFileOperation('fm_add_to_menu',$arPath))
	{
		$aContext[] = Array(
			"TEXT" => GetMessage("FILEMAN_ADMIN_MENU_ADD"),
			"ICON" => "btn_new_menu",
			"LINK" => "fileman_menu_edit.php?".$addUrl."&site=".$site."&path=".urlencode($path),
			"TITLE" => GetMessage("FILEMAN_ADMIN_MENU_ADD")
		);
	}

	if(count($aContext) > 1)
	{
		$aContext = array(
			array(
				"TEXT" => GetMessage("FILEMAN_ADMIN_ADD"),
				"ICON" => "btn_new",
				"MENU" => $aContext,
			),
		);
	}

	if($USER->CanDoOperation('fileman_upload_files') && $USER->CanDoFileOperation('fm_upload_file',$arPath))
	{
		$aContext[] = Array(
			"TEXT" => GetMessage("FILEMAN_ADMIN_FILE_UPLOAD"),
			"ICON" => "btn_upload",
			"LINK" => "fileman_file_upload.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
			"TITLE" => GetMessage("FILEMAN_ADMIN_FILE_UPLOAD")
		);
	}

}

//if(count($aContext) > 0)
//	$aContext[] = Array("NEWBAR" => true);

// Only for dir viewing, hide for search result mode

$addProp = array();

if(!$bSearch && $USER->CanDoOperation('fileman_edit_existent_folders') && $USER->CanDoFileOperation('fm_edit_existent_folder', $arPath))
{
	$addProp[] = Array(
		"TEXT" => GetMessage("FILEMAN_ADMIN_FOLDER_PROP"),
		"LINK" => "fileman_folder.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
		"ICON" => "btn_folder_prop",
		"TITLE" => GetMessage("FILEMAN_ADMIN_FOLDER_PROP")
	);
}

if($bSearch)
{
	$aContext[] = Array(
		"TEXT" => GetMessage("FILEMAN_GO_BACK"),
		"LINK" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".urlencode($path)."",
		"ICON" => "btn_list"
	);
}

if ($USER->CanDoOperation('view_groups') && $USER->CanDoFileOperation('fm_view_permission', $arPath) && $USER->CanDoFileOperation('fm_edit_existent_folder',$arPath))
	$addProp[] = Array(
		"TEXT" => GetMessage('FILEMAN_SHOW_PRM_FOR'),
		"TITLE" => GetMessage('FILEMAN_SHOW_PRM_FOR'),
		"MENU" => $arDDMenu
	);


if(count($addProp) > 0)
{
	$aContext[] = array(

			"TEXT" => GetMessage('FILEMAN_ADMIN_FOLDER_EXTRA_PARAM'),
			"TITLE" => GetMessage('FILEMAN_ADMIN_FOLDER_EXTRA_PARAM_TITLE'),
			"ICON" => "btn_folder_prop",
			"MENU" => $addProp
	);
}

if(count($aContext) > 0)
	$aContext[] = Array("NEWBAR" => true);

ob_start();
?>
<table cellspacing="0">
<tr>
	<td style="padding-left:5px;"><?= GetMessage("FILEMAN_FAST_PATH")?></td>
	<td style="padding-left:5px;"><input class="bx-quick-path" type="text" name="quick_path" id="quick_path" size="50" value="<?= htmlspecialcharsbx($path)?>" /></td>
	<td style="padding-left:3px; padding-right:3px;"><input class="form-button" type="button" value="OK" title="<?= GetMessage("FILEMAN_FAST_PATH_BUTTON")?>" OnClick="<?= $sTableID ?>.GetAdminList('fileman_admin.php?<?=$addUrl?>&site=<?= urlencode($site) ?>&path='+jsUtils.urlencode(BX('quick_path').value)+'&show_perms_for=<?= IntVal($show_perms_for) ?>&check_for_file=Y', GALCallBack)"></td>
</tr>
</table>
<script>top.BX.ready(function(){setTimeout(top.InitQuickPath, 100)});</script>
<?
$s = ob_get_contents();
ob_end_clean();
$aContext[] = array("HTML"=>$s);

if (isset($_POST['bx_search_file']))
{
	$sFormValues = CUtil::PhpToJSObject(array(
		'file' => $_POST['bx_search_file'],
		'search_phrase' => $_POST['bx_search_phrase'],
		'replace_phrase' => $_POST['bx_replace_phrase'],
		'dir' => $_POST['bx_search_dir'],
		'subdir' => $_POST['bx_search_subdir'] == "Y",
		'date_sel' => $_POST['bx_search_date_sel'],
		'date_from' => $_POST['bx_search_date_from'],
		'date_to' => $_POST['bx_search_date_to'],
		'size_sel' => $_POST['bx_search_size_sel'],
		'size_from' => intVal($_POST['bx_search_size_from']),
		'size_to' => intVal($_POST['bx_search_size_to']),
		'dirs_too' => $_POST['bx_search_dirs_too'] == 'Y',
		'case_sens' => $_POST['bx_search_case'] == 'Y'
	));
}
else
{
	$sFormValues = 'false';
}

// Search / replace
$aContext[] = Array(
	"TEXT" => $bSearch ? GetMessage("FILEMAN_NEW_SEARCH") : GetMessage("FILEMAN_SEARCH"),
	"ICON" => "btn_fileman_search",
	"LINK" => "javascript: window.SearchReplaceRun('".CUtil::JSEscape($path)."', ".($bSearch ? 'true' : 'false').", '".CUtil::JSEscape($searchSess)."', ".urlencode($sFormValues).");",
	"TITLE" => GetMessage("FILEMAN_SEARCH_TITLE")
);

$lAdmin->AddAdminContextMenu($aContext);
$chain = $lAdmin->CreateChain();

if ($bSearch)
{
	$title = $bReplace ? GetMessage("FILEMAN_REPLACE_RES") : GetMessage("FILEMAN_SEARCH_RES");
	$chain->AddItem(
		array(
			"TEXT" => $title,
			"LINK" => ""
		)
	);
}
else
{
	foreach ($arParsedPath["AR_PATH"] as $chainLevel)
	{
		$chain->AddItem(
			array(
				"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
				"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
				"ONCLICK" => ((strlen($chainLevel["LINK"]) > 0) ? $lAdmin->ActionAjaxReload($chainLevel["LINK"]).';return false;' : ""),
			)
		);
	}
}

$lAdmin->ShowChain($chain);

$lAdmin->CheckListMode();

/***********  MAIN PAGE **********/
$APPLICATION->SetTitle($title);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//File Dialog Init
CAdminFileDialog::ShowScript
(
	Array(
		"event" => "DRList",
		"arResultDest" => Array("FUNCTION_NAME" => "DRListAct"),
		"arPath" => Array("SITE" => $site, "PATH" =>''),
		"select" => 'D',// F - file only, D - folder only
		"operation" => 'O',
		"showUploadTab" => false,
		"showAddToMenuTab" => false,
		"fileFilter" => '',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>

<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
CFilemanUtils::InitScript(array(
	'initSearch' => true,
	'initCopy' => true,
	'initPack' => true,
	'viewMsFilePath' => "fileman_file_view.php?path=#PATH#&".$addUrl."&site=",
	'viewMsFolderPath' => "fileman_admin.php?path=#PATH#&".$addUrl."&site=",
	'site' => $site,
	'arCurValues' => array()
));
?>

<script>
window.InitQuickPath = function()
{
	var pInput = BX("quick_path");
	if (!pInput)
		return setTimeout(top.InitQuickPath, 50);

	new BXFMInpSel({
		id: 'bx_qpath',
		pInput : BX("quick_path"),
		Items: <?= CUtil::PhpToJSObject(CFilemanUtils::GetLastPathes())?>,
		posCorrection: {left: 1, top: 20},
		OnEnterPress: function(res)
		{
			<?= $sTableID ?>.GetAdminList('fileman_admin.php?<?=$addUrl?>&site=<?= urlencode($site) ?>&path=' + res.value + '&show_perms_for=<?= IntVal($show_perms_for) ?>&check_for_file=Y', GALCallBack);
			return false;
		}
	});
}

function DRListAct(filename, path, site)
{
	var
		oForm = document.forms['form_<?= $sTableID ?>'],
		val = path + "/" + filename;
	val = val.replace('//', '/');
	val = val.replace('\\', '/');
	val = val.replace(/[\/]+$/g, "");

	if (val == '')
		val = '/';
	oForm.copy_to.value = val;
	oForm.copy_to_site.value = site;
}

function setAccess(site, path, bServerPermission)
{
	var
		oForm = document.forms['form_<?= $sTableID ?>'],
		expType = oForm.action_target && oForm.action_target.checked,
		par = "";

	if (!expType)
	{
		for (var i = 0, l = oForm.elements.length; i < l; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				if (par.length > 0)
					par = par + "&";

				par = par + "files[]=" + BX.util.urlencode(oForm.elements[i].value);
			}
		}
	}
	else
	{

	<?if ($bSearch) // only for action_target and search results mode
	{
		$par = "";
		for($i = 0, $l = count($searchRes); $i < $l; $i++)
			$par .= '&files[]='.urlencode($searchRes[$i]['path']);
		$par = trim($par, '&');
		?>par += "<?= $par?>";<?
	}?>
	}

	// bServerPermission
	window.location = (bServerPermission ? "fileman_server_access.php" : "fileman_access.php") + "?<?= $addUrl?>&site=" + BX.util.urlencode(site) + "&path=" + BX.util.urlencode(path) + "&" + par + "<?= ($bSearch ? "&search=Y&ssess=".$searchSess : "")?>";
}

function setCopyMove(site, path, bCopy, isDir)
{

	var
		arFiles = [],
		oForm = document.forms['form_<?= $sTableID ?>'],
		expType = oForm.action_target && oForm.action_target.checked,
		i, l,
		par = "";

	if (!expType)
	{
		for (i = 0, l = oForm.elements.length; i < l; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true
				&& oForm.elements[i].value !== '.')
			{
				<?if ($bSearch):?>
					arFiles.push({'path' : oForm.elements[i].value, 'isDir' : isDir[oForm.elements[i].value]});
				<?else:?>
					var pathF = (path == '/' ? '' : path) + '/' + oForm.elements[i].value;
					arFiles.push({'path' : pathF, 'isDir' : isDir[pathF]});
				<?endif;?>
			}
		}
	}
	else
	{
		<?if ($bSearch): // only for action_target and search results mode
			for($i = 0, $l = count($searchRes); $i < $l; $i++):?>
				arFiles.push({'path' : '<?= CUtil::JSEscape($searchRes[$i]['path'])?>', 'isDir' : '<?= CUtil::JSEscape($searchRes[$i]['b_dir'])?>'});
			<?endfor;
		endif;?>
	}

	window.CopyMoveRun(arFiles, bCopy);
}

function setPackUnpack(site, path, bPack, isDir)
{
	var
		arFiles = [],
		oForm = document.forms['form_<?= $sTableID ?>'],
		expType = oForm.action_target && oForm.action_target.checked,
		i, l,
		par = "";

	if (!expType)
	{
		for (i = 0, l = oForm.elements.length; i < l; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true
				&& oForm.elements[i].value !== '.')
			{
				<?if ($bSearch):?>
					arFiles.push({'path' : oForm.elements[i].value, 'isDir' : isDir[oForm.elements[i].value]});
				<?else:?>
					var pathF = (path == '/' ? '' : path) + '/' + oForm.elements[i].value;
					arFiles.push({'path' : pathF, 'isDir' : isDir[pathF]});
				<?endif;?>
			}
		}
	}
	else
	{
		<?if ($bSearch): // only for action_target and search results mode
			for($i = 0, $l = count($searchRes); $i < $l; $i++):?>
				arFiles.push({'path' : '<?= CUtil::JSEscape($searchRes[$i]['path'])?>', 'isDir' : '<?= CUtil::JSEscape($searchRes[$i]['b_dir'])?>'});
			<?endfor;
		else:?>
				arFiles.push('action_target');
		<?endif;?>
	}

	window.PackUnpackRun(arFiles, bPack);
}

function setCheckbox(name)
{
	var el = document.forms['form_<?= $sTableID ?>'].elements;
	for (var i=0; i<el.length; i++)
	{
		if (el[i].value == name)
		{
			if(!el[i].checked)
			{
				BX.fireEvent(el[i], 'click');
				if(el[i].onclick)
					el[i].onclick.apply(el[i]);
			}
		}
		else
			el[i].checked = false;
	}
}

function GALCallBack(url)
{
	if (!url || !document.forms['find_form'])
		return;

	var
		setFilterBut = document.forms['find_form'].elements['set_filter'],
		delFilterBut = document.forms['find_form'].elements['del_filter'],
		sind = url.indexOf('site='),
		site = sind > 0 ? url.substring(sind + 5, url.indexOf('&', sind)) : '',
		lind = url.indexOf('lang='),
		lang = lind > 0 ? url.substring(lind + 5, url.indexOf('&', lind)) : '',
		pind = url.indexOf('path='),
		path = pind > 0 ? (url.indexOf('&', pind) > 0 ? url.substring(pind + 5, url.indexOf('&', pind)) : url.substring(pind + 5)) : '';

	setFilterBut.onclick = function()
	{
		tbl_fileman_admin_filter.OnSet('tbl_fileman_admin', '/bitrix/admin/fileman_admin.php?lang=' + lang + '&site=' + site + '&path=' + path + '&');
		return false;
	};

	delFilterBut.onclick = function()
	{
		tbl_fileman_admin_filter.OnClear('tbl_fileman_admin', '/bitrix/admin/fileman_admin.php?lang=' + lang + '&site=' + site + '&path=' + path + '&');
		return false;
	};
}
</script>

<? if (!$bSearch):?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage('MAIN_F_TIMESTAMP'),
		GetMessage('MAIN_F_TYPE')
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?= GetMessage("MAIN_F_NAME")?>:</b></td>
	<td nowrap>
		<input type="text" name="find_name" value="<?= htmlspecialcharsbx($find_name)?>" size="35">
	</td>
</tr>
<tr>
	<td width="0%" nowrap><?= GetMessage("MAIN_F_TIMESTAMP").":"?></td>
	<td width="0%" nowrap><?= CalendarPeriod("find_timestamp_1", htmlspecialcharsbx($find_timestamp_1), "find_timestamp_2", htmlspecialcharsbx($find_timestamp_2), "find_form","Y")?></td>
</tr>
<tr>
	<td nowrap><?= GetMessage("MAIN_F_TYPE")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("FILEMAN_FILE"), GetMessage("FILEMAN_FOLDER")), "reference_id"=>array("F","D"));
		echo SelectBoxFromArray("find_type", $arr, htmlspecialcharsbx($find_type), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage().'?'.$addUrl_s."&site=".urlencode($site)."&path=".urlencode($path."/".$Elem["NAME"]), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<? endif;?>

<?
if(empty($arGrActionAr))
	$lAdmin->bCanBeEdited = false;

$lAdmin->DisplayList();
?>

<? CFilemanUtils::BuildDialogContent($site);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
