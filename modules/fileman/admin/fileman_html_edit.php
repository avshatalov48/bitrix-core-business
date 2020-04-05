<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix       #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/admin/fileman_html_edit.php");

$strWarning = "";
$site_template = false;
$rsSiteTemplates = CSite::GetTemplateList($site);
while($arSiteTemplate = $rsSiteTemplates->Fetch())
{
	if(strlen($arSiteTemplate["CONDITION"]) <= 0)
	{
		$site_template = $arSiteTemplate["TEMPLATE"];
		break;
	}
}

$io = CBXVirtualIo::GetInstance();

$path = $io->CombinePath("/", $path);
$path_list = GetDirPath($path);

$bVarsFromForm = false; //  if 'true' - we will get content  and variables from form, if 'false' - from saved file
$filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : '';
$oldname = isset($_REQUEST['oldname']) ? $_REQUEST['oldname'] : '';

if (strlen($filename) > 0 && ($mess = CFileMan::CheckFileName($filename)) !== true)
{
	$filename2 = $filename;
	$filename = '';
	$strWarning = $mess;
	$bVarsFromForm = true;
}

$originalPath = $path;
$new = (isset($new) && strtolower($new) == 'y') ? 'y' : '';

if ($new == 'y' && strlen($filename) > 0)
	$path = $path."/".$filename;

$site = CFileMan::__CheckSite($site);
if(!$site)
	$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

$DOC_ROOT = CSite::GetSiteDocRoot($site);
$abs_path = $io->CombinePath($DOC_ROOT, $path);
$arPath = Array($site, $path);

if(GetFileType($abs_path) == "IMAGE")
	$strWarning = GetMessage("FILEMAN_FILEEDIT_FILE_IMAGE_ERROR");

if($new == '' && strlen($filename) <= 0 && strlen($oldname) <= 0 && !$io->FileExists($abs_path))
{
	$p = strrpos($path, "/");
	if($p !== false)
	{
		$new = "y";
		$filename = substr($path, $p+1);
		$path = substr($path, 0, $p);
	}
}

$useEditor3 = COption::GetOptionString('fileman', "use_editor_3", "Y") == "Y";
$bFullPHP = ($full_src == "Y") && $USER->CanDoOperation('edit_php');
$NEW_ROW_CNT = 1;

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$isScriptExt = HasScriptExtension($path);

//Check access to file
if
(
	(
		$new == 'y'
		&&
		!(
			$USER->CanDoOperation('fileman_admin_files')
			&&
			$USER->CanDoFileOperation('fm_create_new_file', $arPath)
		)
	)
	||
	(
		$new == ''
		&&
		!(
			$USER->CanDoOperation('fileman_edit_existent_files')
			&&
			$USER->CanDoFileOperation('fm_edit_existent_file', $arPath)
		)
	)
)
{
	$strWarning = GetMessage("ACCESS_DENIED");
}
elseif(strlen($strWarning) <= 0)
{
	if($new == 'y' && strlen($filename) > 0 && $io->FileExists($abs_path)) // if we want to create new file, but the file with same name is alredy exists - lets abuse

	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_FILE_EXISTS");
		$bEdit = false;
		$bVarsFromForm = true;
		$path = $io->CombinePath("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $io->CombinePath($DOC_ROOT, $path);
	}
	elseif(!$USER->IsAdmin() && substr(CFileman::GetFileName($abs_path), 0, 1)==".")
	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_BAD_FNAME");
		$bEdit = false;
		$bVarsFromForm = true;
		$path = $io->CombinePath("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $io->CombinePath($DOC_ROOT, $path);
	}
	elseif($new == 'y')
	{
		if (strlen($filename) < 0)
			$strWarning = GetMessage("FILEMAN_FILEEDIT_FILENAME_EMPTY");

		$bEdit = false;
	}
	else
	{
		if(!$io->FileExists($abs_path))
			$strWarning = GetMessage("FILEMAN_FILEEDIT_FOLDER_EXISTS")." ";
		else
			$bEdit = true;
	}

	$limit_php_access = ($USER->CanDoFileOperation('fm_lpa', $arPath) && !$USER->CanDoOperation('edit_php'));
	if ($limit_php_access)
	{
		//OFP - 'original full path' used for restorin' php code fragments in limit_php_access mode
		if (!isset($_SESSION['arOFP']))
			$_SESSION['arOFP'] = Array();

		if(isset($_POST['ofp_id']))
		{
			$ofp_id = $_POST['ofp_id'];
		}
		else
		{
			$ofp_id = substr(md5($site.'|'.$path),0,8);
			if(!isset($_SESSION['arOFP'][$ofp_id]))
				$_SESSION['arOFP'][$ofp_id] = $path;
		}
	}
}

$bFullScreen = ($_REQUEST['fullscreen'] ? $_REQUEST['fullscreen']=='Y' : COption::GetOptionString("fileman", "htmleditor_fullscreen", "N")=="Y");

if(strlen($back_url)>0 && strpos($back_url, "/bitrix/admin/fileman_file_edit.php")!==0)
	$url = "/".ltrim($back_url, "/");
else
	$url = "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($arParsedPath["PREV"]);

$module_id = "fileman";
$localRedirectUrl = '';

if(strlen($strWarning)<=0)
{
	if($bEdit)
	{
		$oFile = $io->GetFile($abs_path);
		$filesrc_tmp = $oFile->GetContents();
	}
	else
	{
		$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));
		if(strlen($template) > 0)
		{
			$len = count($arTemplates);
			for ($i = 0; $i < $len; $i++)
			{
				if($arTemplates[$i]["file"] == $template)
				{
					$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[$i]["file"],LANGUAGE_ID, array($site_template));
					break;
				}
			}
		}
		else
		{
			$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[0]["file"], LANGUAGE_ID, array($site_template));
		}
	}

	if($REQUEST_METHOD == "POST" && strlen($save) > 0 && strlen($propeditmore) <= 0)
	{
		if(!check_bitrix_sessid())
		{
			$strWarning = GetMessage("FILEMAN_SESSION_EXPIRED");
			$bVarsFromForm = true;
		}
		elseif((CFileman::IsPHP($filesrc) || $isScriptExt) && !($USER->CanDoOperation('edit_php') || $limit_php_access)) //check rights
		{
			$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE");
			$bVarsFromForm = true;
			if($new == 'y' && strlen($filename) > 0)
			{
				$bEdit = false;
				$path = $io->CombinePath("/", $arParsedPath["PREV"]);
				$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
				$abs_path = $io->CombinePath($DOC_ROOT, $path);
			}
		}
		else
		{
			if($limit_php_access)
			{
				// ofp - original full path :)
				$ofp = $_SESSION['arOFP'][$ofp_id];
				$ofp = $io->CombinePath("/", $ofp);
				$abs_ofp = $io->CombinePath($DOC_ROOT, $ofp);

				$oFile = $io->GetFile($abs_ofp);
				$fileContentTmp = $oFile->GetContents();

				$old_res = CFileman::ParseFileContent($fileContentTmp, true);
				$old_filesrc = $old_res["CONTENT"];
				$filesrc = CMain::ProcessLPA($filesrc, $old_filesrc);
			}

			if(!$bFullPHP)
			{
				$res = CFileman::ParseFileContent($filesrc_tmp, true);
				$prolog = CFileman::SetTitle($res["PROLOG"], $title);
				for ($i = 0; $i<=$maxind; $i++)
				{
					if(strlen(Trim($_POST["CODE_".$i]))>0)
					{
						if($_POST["CODE_".$i] != $_POST["H_CODE_".$i])
						{
							$prolog = CFileman::SetProperty($prolog, Trim($_POST["H_CODE_".$i]), "");
							$prolog = CFileman::SetProperty($prolog, Trim($_POST["CODE_".$i]), Trim($_POST["VALUE_".$i]));
						}
						else
							$prolog = CFileman::SetProperty($prolog, Trim($_POST["CODE_".$i]), Trim($_POST["VALUE_".$i]));
					}
					else
						$prolog = CFileman::SetProperty($prolog, Trim($_POST["H_CODE_".$i]), "");
				}
				$epilog = $res["EPILOG"];
				$filesrc_for_save = $prolog.$filesrc.$epilog;
			}
			else
			{
				$filesrc_for_save = $filesrc;
			}
		}

		if(strlen($strWarning) <= 0)
		{
			if (!CFileMan::CheckOnAllowedComponents($filesrc_for_save))
			{
				$str_err = $APPLICATION->GetException();
				if($str_err && ($err = $str_err ->GetString()))
					$strWarning .= $err;
				$bVarsFromForm = true;
			}
		}

		if(strlen($strWarning) <= 0)
		{
			if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				if($str_err = $APPLICATION->GetException())
				{
					if ($err = $str_err ->GetString())
						$strWarning = $err;

					$path = $io->CombinePath("/", $arParsedPath["PREV"]);
					$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
					$abs_path = $io->CombinePath($DOC_ROOT, $path);
				}

				if (empty($strWarning))
					$strWarning = GetMessage("FILEMAN_FILE_SAVE_ERROR");

				$bVarsFromForm = true;
			}
			else
			{
				if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
				{
					$res_log['path'] = substr($path, 1);
					if ($new == 'y' && strlen($filename) > 0)
						CEventLog::Log(
							"content",
							"FILE_ADD",
							"fileman",
							"",
							serialize($res_log)
						);
					else
						CEventLog::Log(
							"content",
							"FILE_EDIT",
							"fileman",
							"",
							serialize($res_log)
						);
				}
				// menu saving
				if($add_to_menu=="Y" && strlen($menutype)>0 && $USER->CanDoOperation('fileman_add_element_to_menu') && $USER->CanDoFileOperation('fm_add_to_menu',$arPath))
				{
					$menu_path = $io->CombinePath("/", $arParsedPath["PREV"], ".".$menutype.".menu.php");

					if($USER->CanDoFileOperation('fm_edit_existent_file',Array($site,$menu_path)))
					{
						$res = CFileMan::GetMenuArray($DOC_ROOT.$menu_path);
						$aMenuLinksTmp = $res["aMenuLinks"];
						$sMenuTemplateTmp = $res["sMenuTemplate"];

						$menuitem = IntVal($menuitem);
						if($itemtype=="e") //means in exist item
						{
							$menuitem = $menuitem - 1;
							if($menuitem < count($aMenuLinksTmp)) // number of item must be in bounds of amount of current menu
								$aMenuLinksTmp[$menuitem][2][] = $path;
						}
						else //else in new
						{
							$menuitem = $newppos-1;
							// if number of item goes out from bounds of amount of current menu
							if($menuitem < 0 || $menuitem >= count($aMenuLinksTmp))
								$menuitem = count($aMenuLinksTmp);

							for($i=count($aMenuLinksTmp)-1; $i>=$menuitem; $i--)//shift to the right all items > our
								$aMenuLinksTmp[$i+1] = $aMenuLinksTmp[$i];
							$aMenuLinksTmp[$menuitem] = Array($newp, $path, Array(), Array(), "");
						}
						CFileMan::SaveMenu(Array($site, $menu_path), $aMenuLinksTmp, $sMenuTemplateTmp);

						if(COption::GetOptionString("main", "event_log_menu", "N") === "Y")
						{
							$mt = COption::GetOptionString("fileman", "menutypes", $default_value, $site);
							$mt = unserialize(str_replace("\\", "", $mt));
							$res_log['menu_name'] = $mt[$menutype];
							$res_log['path'] = substr(dirname($path), 1);
							CEventLog::Log(
								"content",
								"MENU_EDIT",
								"fileman",
								"",
								serialize($res_log)
							);
						}
					}
				}

				if(strlen($strWarning)<=0 && strlen($apply)<=0 && strlen($apply2)<=0)
					$localRedirectUrl = $url;
				else
					$localRedirectUrl = "/bitrix/admin/fileman_html_edit.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($path)."&back_url=".UrlEncode($back_url)."&fullscreen=".($bFullScreen?"Y":"N")."&tabControl_active_tab=".urlencode($tabControl_active_tab);
			}

			$filesrc_tmp = $filesrc_for_save;
			$path = $io->CombinePath("/", $path);
			$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
			$abs_path = $io->CombinePath($DOC_ROOT, $path);
		}
	}
}

if(strlen($propeditmore) > 0)
	$bVarsFromForm = True;

$bEditProps = false;
if(!$bVarsFromForm)
{
	if(!$bEdit && strlen($filename) <= 0)
		$filename = ($USER->CanDoOperation('edit_php') || $limit_php_access) ? "untitled.php" : "untitled.html";

	if(!$bFullPHP)
	{
		$res = CFileman::ParseFileContent($filesrc_tmp, true);
		$filesrc = $res["CONTENT"];

		// ###########  L  P  A  ############
		if ($limit_php_access)
		{
			$arPHP = PHPParser::ParseFile($filesrc);
			$l = count($arPHP);
			if ($l > 0)
			{
				$new_filesrc = '';
				$end = 0;
				$php_count = 0;
				for ($n = 0; $n<$l; $n++)
				{
					$start = $arPHP[$n][0];
					$new_filesrc .= substr($filesrc, $end, $start - $end);
					$end = $arPHP[$n][1];

					//Trim php tags
					$src = $arPHP[$n][2];
					if (SubStr($src, 0, 5) == "<?"."php")
						$src = SubStr($src, 5);
					else
						$src = SubStr($src, 2);
					$src = SubStr($src, 0, -2);

					//If it's Component 2, keep the php code. If it's component 1 or ordinary PHP - than replace code by #PHPXXXX# (XXXX - count of PHP scripts)
					$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';
					if (strtoupper(substr($src,0, strlen($comp2_begin))) == $comp2_begin)
						$new_filesrc .= $arPHP[$n][2];
					else
						$new_filesrc .= '#PHP'.str_pad(++$php_count, 4, "0", STR_PAD_LEFT).'#';
				}
				$new_filesrc .= substr($filesrc,$end);
				$filesrc = $new_filesrc;
			}
		}

		$bEditProps = strlen($res["PROLOG"]) > 0;
		$title = $res["TITLE"];
		$page_properties = $res["PROPERTIES"];
	}
	else
	{
		$filesrc = $filesrc_tmp;
	}

	if((CFileman::IsPHP($filesrc) || $isScriptExt) && !($USER->CanDoOperation('edit_php') || $limit_php_access))
		$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE_ACCESS");
}
elseif($prop_edit=="Y")
	$bEditProps = true;

if($bEdit)
	$APPLICATION->SetTitle(GetMessage("FILEMAN_FILEEDIT_PAGE_TITLE")." \"".htmlspecialcharsbx($arParsedPath["LAST"])."\"");
else
	$APPLICATION->SetTitle(GetMessage("FILEMAN_NEWFILEEDIT_TITLE"));

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_H_EDIT_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("FILEMAN_H_EDIT_TAB2"));

if($bEditProps)
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("FILEMAN_H_EDIT_RTAB2"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("FILEMAN_H_EDIT_TAB2_TITLE"));

if ($USER->CanDoOperation('fileman_add_element_to_menu') && $USER->CanDoFileOperation('fm_add_to_menu',$arPath))
	$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("FILEMAN_H_EDIT_TAB3"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("FILEMAN_H_EDIT_TAB3_TITLE"));
$tabControl = new CAdminTabControl("tabControl", $aTabs);

// We have to redirect after TabControl for normal work of autosave methods
if ($localRedirectUrl !== '')
{
	LocalRedirect($localRedirectUrl);
}


foreach($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
		)
	);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?CAdminMessage::ShowMessage($strWarning);?>

<?if(strlen($strWarning) <=0 || $bVarsFromForm):
//$aMenu = array();
$aMenu = array(
	array(
		"TEXT" => GetMessage("FILEMAN_BACK"),
		"LINK" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path_list),
		"ICON" => "btn_list"
	)
);

if ($bEdit)
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("FILEMAN_FILE_VIEW"),
		"LINK"=>"fileman_file_view.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path)
	);
}

$ismenu = preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/i', $arParsedPath["LAST"], $regs);
$aDDMenuEdit = array();
if (!$ismenu)
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_TXT"),
		"ACTION" => "window.location='fileman_file_edit.php?".$addUrl.
					"&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path).
					($new == 'y' ? "&amp;new=Y":"").
					(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"").
					(strlen($template)>0? "&amp;template=".urlencode($template):"").
					(strlen($template)>0? "&amp;template=".urlencode($template):"").
					(strlen($templateID)>0? "&amp;templateID=".urlencode($templateID):"")."';",
	);
}

if($USER->CanDoOperation('edit_php'))
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_PHP"),
		"ACTION" => "window.location='fileman_file_edit.php?".$addUrl."&amp;site=".Urlencode($site).
					"&amp;path=".UrlEncode($path)."&amp;full_src=Y".($new == 'y' ? "&amp;new=Y":"").
					(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"").
					(strlen($template)>0? "&amp;template=".urlencode($template):"").
					(strlen($template)>0? "&amp;template=".urlencode($template):"").
					(strlen($templateID)>0? "&amp;templateID=".urlencode($templateID):"")."';",
	);
}

if ($ismenu)
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_MENU"),
		"ACTION" => "window.location='fileman_menu_edit.php?".$addUrl.
					"&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($arParsedPath["PREV"]).
					"&amp;name=".UrlEncode($regs[1]).($new == 'y' ? "&amp;new=Y":"").
					(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"")."';"
	);
}

$aDDMenuEdit[] = array(
	"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_HTML"),
	"ACTION" => "return;",
	"ICON" =>	"checked"
);

$aMenu[] = array(
	"TEXT" => GetMessage("FILEMAN_FILE_EDIT"),
	"TITLE" => GetMessage("FILEMAN_FILE_EDIT"),
	"MENU" => $aDDMenuEdit
);


if($bEdit)
{
	if($USER->CanDoFileOperation('fm_rename_file',$arPath))
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("FILEMAN_FILEEDIT_RENAME"),
			"LINK"=>"fileman_rename.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($arParsedPath["PREV"])."&amp;files[]=".UrlEncode($arParsedPath["LAST"])
		);
	}

	if(($USER->CanDoFileOperation('fm_download_file', $arPath) && !(HasScriptExtension($path) || substr(CFileman::GetFileName($path), 0, 1)==".")) || $USER->CanDoOperation('edit_php'))
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("FILEMAN_FILEEDIT_DOWNLOAD"),
			"LINK"=>"fileman_file_download.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path)
		);
	}

	if($USER->CanDoFileOperation('fm_delete_file', $arPath))
	{
		$folder_path = substr($path, 0, strrpos($path, "/"));
		$id = GetFileName($path);
		$aMenu[] = array(
			"TEXT" => GetMessage("FILEMAN_FILE_DELETE"),
			"LINK" => "javascript:if(confirm('".GetMessage("FILEMAN_FILE_DELETE_CONFIRM")."')) window.location='/bitrix/admin/fileman_admin.php?ID=".urlencode($id)."&action=delete&".$addUrl."&site=".urlencode($site)."&path=".urlencode($folder_path)."&".bitrix_sessid_get()."';",
			"TITLE"	=> GetMessage("FILEMAN_FILE_DELETE")
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

global $__fd_path;

$__fd_path = $bEdit ? $arParsedPath["PREV"] : $path;

$arContextTemplates = Array();

$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));
$cntTempl = count($arTemplates);
for($i = 0; $i < $cntTempl; $i++)
{
	$arContextTemplates[] = Array(
			"TEXT"=>htmlspecialcharsbx($arTemplates[$i]["name"]),
			"ONCLICK" => "__NewDocTempl('".AddSlashes(htmlspecialcharsbx($arTemplates[$i]["file"]))."')",
		);
}

$u = new CAdminPopup("new_doc_list", "new_doc_list", $arContextTemplates);

CAdminFileDialog::ShowScript(Array
	(
		"event" => "__bx_fd_save_as",
		"arResultDest" => Array("FUNCTION_NAME" => "OnSaveAs"),
		"arPath" => Array('SITE'=>$site, 'PATH'=>$_REQUEST['path']), //http://jabber.bx/view.php?id=27769
		"select" => 'F',
		"operation" => 'S',
		"showUploadTab" => false,
		"showAddToMenuTab" => true,
		"fileFilter" => 'php,html,htm,phtml',
		"allowAllFiles" => true,
		"saveConfig" => false
	)
);

?>
<script type="text/javascript">
BX.addCustomEvent(window, 'onAfterFileDialogShow', function(){
	var _filenameDialogInput = BX("__bx_file_path_bar");
	var _filenamePageInput = BX('filename');
	if(_filenamePageInput && _filenameDialogInput)
		_filenameDialogInput.value = _filenamePageInput.value;
});
</script>
<?
$u->Show();
?>
<form action="fileman_html_edit.php?lang=<?=LANG?>" method="post" enctype="multipart/form-data" name="ffilemanedit" id="ffilemanedit">
<input type="hidden" name="site" id="site" value="<?=htmlspecialcharsbx($site)?>">
<input type="hidden" name="path" id="path" value="<?=htmlspecialcharsbx($originalPath)?>">
<input type="hidden" name="logical" value="<?=htmlspecialcharsbx($logical)?>">
<span style="display:none;"><input type="submit" name="saveb" value="Y" style="width:0px;height:0px"></span>
<input type="hidden" name="save" value="Y">
<input type="hidden" name="fullscreen" id="fullscreen" value="<?=($bFullScreen?"Y":"N")?>">
<input type="hidden" name="template" value="<?= htmlspecialcharsbx($template)?>">
<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($back_url)?>">
<?=bitrix_sessid_post()?>
<?
$tabControl->Begin();
//********************
//Posting issue
//********************
$tabControl->BeginNextTab();
?>
	<?if(!$bEdit):?>
		<tr><td><label for="bx_template"><?= GetMessage("FILEMAN_FILEEDIT_TEMPLATE")?></label></td>
		<td>
		<input type="hidden" name="new" id="new" value="y">
		<?$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));?>

		<script>
		function templateOnChange(_this)
		{
			var _name = BX('filename').value;
			if (_name)
				_name = '&oldname='+encodeURIComponent(_name);

			var _title = BX('title').value;
			if (_title)
				_title = '&oldtitle='+encodeURIComponent(_title);

			<?
			$logic = ( $logical == "Y"  ? '&logical=Y' : '' );

			$folderPath = $_REQUEST['path'];
			?>

			window.location='/bitrix/admin/fileman_html_edit.php?lang=<?= LANG?><?=$logic?>&site=<?=Urlencode($site)?>&path=<?= UrlEncode($folderPath)?>&new=y&template='+encodeURIComponent(_this[_this.selectedIndex].value)+_name+_title;
		}
		</script>

		<?
		if (isset($_GET['oldtitle']) && strlen($_GET['oldtitle']) > 0 && !$bVarsFromForm)
			$title = $GLOBALS["APPLICATION"]->ConvertCharset($_GET['oldtitle'], "UTF-8", LANG_CHARSET);
		if (isset($_GET['oldname']) && strlen($_GET['oldname']) > 0 && !$bVarsFromForm)
			$filename = $GLOBALS["APPLICATION"]->ConvertCharset($_GET['oldname'], "UTF-8", LANG_CHARSET);
		?>
		<select id="bx_template" name="template" onchange="templateOnChange(this);">
			<?
			$cntTemp = count($arTemplates);
			for($i = 0; $i < $cntTemp; $i++):?>
			<option value="<?= htmlspecialcharsbx($arTemplates[$i]["file"])?>"<?if($template==$arTemplates[$i]["file"])echo " selected"?>><?= htmlspecialcharsbx($arTemplates[$i]["name"])?></option>
			<?endfor;?>
		</select></td></tr>
		<tr>
			<td width="30%"><label for="title"><?= GetMessage("FILEMAN_FILEEDIT_TITLE")?></label></td>
			<td width="70%"><input type="text" id="title" name="title" size="60" maxlength="255" value="<?= htmlspecialcharsbx($title)?>"></td>
		</tr>
		<tr>
			<td><label for="filename"><?= GetMessage("FILEMAN_FILEEDIT_NAME")?></td>
			<td>
				<?if (isset($filename2))
					$filename = $filename2;?>
				<input type="text" name="filename" id="filename" style="float: left;" size="60" maxlength="255" value="<?= htmlspecialcharsbx($filename)?>" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td style="padding: 0 0 3px!important;">
				<table id='jserror_name' style="visibility:hidden"><tr><td valign="top">
							<IMG src="/bitrix/themes/.default/images/icon_warn.gif" title="<?=GetMessage("FILEMAN_NAME_ERR");?>">
						</td><td id="jserror" class="jserror"></td></tr></table>
				<script>
					var oInput = BX('filename'),
						erTable = BX('jserror_name'),
						mess = BX('jserror'),
						form = document.forms.ffilemanedit,
						fNameError = '<?=GetMessage("FILEMAN_NAME_ERR");?>',
						fNameEmpty = '<?=GetMessage("FILEMAN_NAME_EMPTY");?>';
					oInput.oninput = function()
					{
						var _this = this,
							saveBut = BX.findChild(form, {tag: 'INPUT', attr: {'name': 'save', 'type':'submit'}}, true);
						setTimeout(function()
							{
								var val = _this.value;
								var new_val = val.replace(/[\\\/:*?\"\'<>|]/i, '');
								if (val !== new_val)
								{
									erTable.style.visibility = 'visible';
									mess.innerHTML = fNameError;
									form.apply.disabled = true;
									saveBut.disabled = true;
								}
								else if (val.trim().length <= 0)
								{
									erTable.style.visibility = 'visible';
									mess.innerHTML = fNameEmpty;
									form.apply.disabled = true;
									saveBut.disabled = true;
								}
								else
								{
									erTable.style.visibility = 'hidden';
									mess.innerHTML = '';
									form.apply.disabled = false;
									saveBut.disabled = false;
								}
							}, 1
						);
					}
				</script>
			</td></tr>
	<?else:?>
		<tr>
			<td width="30%"><label for="title"><?= GetMessage("FILEMAN_FILEEDIT_TITLE")?></label></td>
			<td width="70%"><input type="text" id="title" name="title" size="60" maxlength="255" value="<?= htmlspecialcharsbx($title)?>">

			<input type="hidden" name="new" id="new" value="n">
			<input type="hidden" name="filename" id="filename" value="<?=htmlspecialcharsbx($arParsedPath["LAST"])?>">
			<input type="hidden" name="ofp_id" id="ofp_id" value="<?=htmlspecialcharsbx($ofp_id)?>">
			</td>
		</tr>
	<tr>
	<?endif?>
	<tr>
	<td colspan="2">
		<? /* Transliteration - only for new files*/
		if (!$bEdit && COption::GetOptionString("fileman", "use_translit", true))
		{
			$bLinked = !isset($_REQUEST['filename']) && $_REQUEST['bxfm_linked'] != "N" && $filename != 'index.php';
			?>
			<input type="hidden" name="bxfm_linked" id="bxfm_linked" value="<? echo $bLinked ? "Y" : "N";?>)" />
			<?
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
			CFilemanTransliterate::Init(array(
				'fromInputId' => 'title',
				'toInputId' => 'filename',
				'linkedId' => 'bxfm_linked',
				'linked' => $bLinked,
				'linkedTitle' => GetMessage('FILEMAN_FILE_TRANS_LINKED'),
				'unlinkedTitle' => GetMessage('FILEMAN_FILE_TRANS_UNLINKED'),
				'ext' => $USER->CanDoOperation('edit_php') || $limit_php_access ? 'php' : 'html'
			));
		}
		?>

		<script>
		var apply = false;
		function OnSaveAs(filename, path, site, title, menu)
		{
			var
				pPath = BX('path'),
				pFilename = BX('filename'),
				pTitle = BX('title');

			var bOldPath = (pPath.value == path);
			<?if(!$bEdit):?>
				pPath.value = path;
				pFilename.value = filename;
			<?else:?>
				pPath.value = path;
				pFilename.value = filename;
				BX('new').value = "y";
			<?endif?>

			BX('site').value = site;
			if (pTitle)
				pTitle.value = title;

			if(!menu['type'])
				BX('add_to_menu').checked = false;
			else
			{
				BX('add_to_menu').checked = true;
				BX('menutype').value = menu['type'];
				__heAddToMenu();
				chtyp();
				BX('itemtype_n').disabled = false;
				BX('itemtype_e').disabled = false;
				if(menu['menu_add_new'])
				{
					BX('itemtype_n').checked = true;
					chitemtype();
					BX('newp').value = menu['menu_add_name'];
					if(!bOldPath)
					{
						while(BX('newppos').length>0)
							BX('newppos').remove(0);

						var oOption = new Option(menu['menu_add_pos'], menu['menu_add_pos'], false, false);
						BX('newppos').options.add(oOption);
					}
					else
						BX('newppos').value = ""+parseInt(menu['menu_add_pos']);
				}
				else
				{
					BX('itemtype_n').checked = false;
					BX('itemtype_e').checked = true;
					chitemtype();
					if(!bOldPath)
					{
						while(BX('menuitem').length>0)
							BX('menuitem').remove(0);
						var oOption = new Option(menu['menu_add_pos'], menu['menu_add_pos'], false, false);
						BX('menuitem').options.add(oOption);
					}
					else
						BX('menuitem').value = ''+parseInt(menu['menu_add_pos']);
				}
			}

			var pMainObj = GLOBAL_pMainObj["filesrc"];
			if (pMainObj)
			{
				if(apply)
					BX("apply2").value = 'Y';
				pMainObj.SaveContent(true);
				pMainObj.isSubmited = true;
				pMainObj.pForm.submit();
			}
		}
		</script>
		<?
		AddEventHandler("fileman", "OnBeforeHTMLEditorScriptsGet", "__FE_editorScripts");
		function __FE_editorScripts($editorName, $arEditorParams){return array("JS" => array('html_edit_editor.js'));}
		?>
		<script>
		FE_MESS = {};
		FE_MESS.FILEMAN_HTMLED_WARNING = "<?=GetMessage("FILEMAN_HTMLED_WARNING")?>";
		FE_MESS.FILEMAN_HTMLED_MANAGE_TB = "<?=GetMessage("FILEMAN_HTMLED_MANAGE_TB")?>";
		window.bEditProps = <?= $bEditProps ? 'true' : 'false'?>;

		var _bEdit = <?echo ($bEdit) ? 'true' : 'false'; ?>
		</script>
		<?if ($useEditor3):?>
			<?
			$relPath = isset($path) ? $path : "/";
			$site = isset($site) ? $site : "";
			$__path = Rel2Abs("/", $relPath);
			$site = CFileMan::__CheckSite($site);
			if($site)
			{
				$DOC_ROOT = CSite::GetSiteDocRoot($site);
				$abs_path = $DOC_ROOT.$__path;
				$io = CBXVirtualIo::GetInstance();
				if ($io->FileExists($abs_path))
				{
					$relPath = substr($relPath, 0, strrpos($relPath,"/"));
					if ($relPath=="")
						$relPath = "/";
				}
			}

			$Editor = new CHTMLEditor;
			$Editor->Show(array(
				'name' => 'filesrc',
				'id' => 'filesrc',
				'width' => '100%',
				'height' => '650',
				'content' => $filesrc,
				'bAllowPhp' => $USER->CanDoOperation('edit_php'),
				"limitPhpAccess" => $limit_php_access,
				"relPath" => $relPath
			));

			CUtil::InitJSCore(array('translit'));
			?>
			<script>
			BX.addCustomEvent('OnGetDefaultUploadImageName', function(nameObj)
			{
				if (BX('title', true) && BX('title', true).value !== '')
				{
					var name = BX.translit(BX('title', true).value, {replace_space: '-'});
					if (name != '')
					{
						nameObj.value = name + '-img';
					}
				}
			});
			</script>
		<?else:?>
			<? CFileman::ShowHTMLEditControl("filesrc", $filesrc, Array(
				"site"=>$site,
				"templateID"=>$templateID,
				"bUseOnlyDefinedStyles"=>COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
				"bWithoutPHP"=>(!$USER->CanDoOperation('edit_php')),
				"toolbarConfig" => CFileman::GetEditorToolbarConfig("filesrc"),
				"arToolbars"=>Array("manage", "standart", "style", "formating", "source", "template"),
				"arTaskbars"=>Array("BXComponentsTaskbar", "BXComponents2Taskbar", "BXPropertiesTaskbar", "BXSnippetsTaskbar"),
				"sBackUrl"=>$url,
				"fullscreen"=>($bFullScreen=='Y'),
				"path" => $path,
				'width' => '100%',
				'height' => '650px',
				"limit_php_access" => $limit_php_access
				)
			);?>
		<?endif;?>
	</td></tr>
	<?if($bEditProps):?>
	<?$tabControl->BeginNextTab();?>
	<tr>
		<td>
			<input type="hidden" name="prop_edit" value="Y">
			<!-- FILE PROPS -->
			<script>
				function _MoreRProps(code, value)
				{
					var prt = BX("proptab");
					var cnt = parseInt(BX("maxind").value)+1;
					var r = prt.insertRow(prt.rows.length-1);
					var c = r.insertCell(-1);
					c.innerHTML = '<input type="hidden" id="H_CODE_'+cnt+'" name="H_CODE_'+cnt+'" value="'+(code?bxhtmlspecialchars(code):'')+'"><input type="text" id="CODE_'+cnt+'" name="CODE_'+cnt+'" value="'+(code?bxhtmlspecialchars(code):'')+'" size="30">:';
					c = r.insertCell(-1);
					c.innerHTML = '<input type="text" name="VALUE_'+cnt+'" id="VALUE_'+cnt+'" value="'+(value?bxhtmlspecialchars(value):'')+'" size="60">';
					BX("maxind").value = cnt;

					if (document.forms.ffilemanedit.BXAUTOSAVE)
					{
						document.forms.ffilemanedit.BXAUTOSAVE.RegisterInput('CODE_'+cnt);
						document.forms.ffilemanedit.BXAUTOSAVE.RegisterInput('VALUE_'+cnt);
					}
				}
			</script>
			<table border="0" cellspacing="1" cellpadding="3" id="proptab"  class="internal">
				<tr class="heading">
					<td><?= GetMessage("FILEMAN_H_EDIT_PROP")?></td>
					<td><?= GetMessage("FILEMAN_EDIT_PROPSVAL")?></td>
				</tr>
				<?
				$arPropTypes = CFileMan::GetPropstypes($site);
				$tag_prop_name = '';
				$search_exist = false;
				if(CModule::IncludeModule("search"))
				{
					$tag_prop_name = COption::GetOptionString("search", "page_tag_property","tags");
					$arPropTypes[$tag_prop_name] = GetMessage('FILEMAN_TAGS');
					$search_exist = true;
				}
				$arPropTypes_tmp = $arPropTypes;

				$ind=-1;
				$arAllPropFields = Array();


				if(is_array($page_properties))
				{
					foreach($page_properties as $f_CODE => $f_VALUE)
					{
						$ind++;
						if($bVarsFromForm)
						{
							$f_CODE = $_POST["CODE_".$ind];
							$f_VALUE = $_POST["VALUE_".$ind];
						}

						if(is_set($arPropTypes, $f_CODE))
						{
							$arAllPropFields[] = Array("CODE"=>$f_CODE, "VALUE"=>$f_VALUE, "NAME"=>$arPropTypes[$f_CODE]);
							unset($arPropTypes[$f_CODE]);
						}
						else
							$arAllPropFields[] = Array("CODE"=>$f_CODE, "VALUE"=>$f_VALUE);
					}
				}

				foreach($arPropTypes as $key => $value)
				{
					$ind++;
					$arAllPropFields[] = Array("CODE"=>$key, "NAME"=>$value, "VALUE"=>"");
				}

				if($bVarsFromForm)
				{
					$maxind = $_REQUEST['maxind'];
					for($i=$ind+1; $i<=$maxind; $i++)
					{
						$ind++;
						$arAllPropFields[] = Array("CODE"=>$f_CODE, "VALUE"=>$f_VALUE);
					}
				}

				//Sorting ....
				$arAllPropFields_tmp = Array();
				$arDefProps = Array();
				foreach($arAllPropFields as $k => $v)
				{
					if(isset($arPropTypes_tmp[$v['CODE']]))
					{
						$arDefProps[$v['CODE']] = $v;
						unset($arAllPropFields[$k]);
					}
				}

				foreach($arPropTypes_tmp as $k=>$v)
				{
					if(is_set($arDefProps, $k))
						$arAllPropFields_tmp[] = $arDefProps[$k];
				}

				if(is_array($arAllPropFields))
				{
					foreach($arAllPropFields as $v)
						$arAllPropFields_tmp[] = $v;
				}
				$arAllPropFields = $arAllPropFields_tmp;
				unset($arPropTypes_tmp);
				unset($arAllPropFields_tmp);
				unset($arDefProps);
				$documentSite = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);
				$cntProp = count($arAllPropFields);
				for($i = 0; $i < $cntProp; $i++)
				{
					$arProp = $arAllPropFields[$i];
					?>
					<tr>
						<td  valign="top" >
							<input type="hidden" id="H_CODE_<?=$i;?>" name="H_CODE_<?=$i;?>" value="<?=htmlspecialcharsbx($arProp["CODE"])?>">
							<?if($arProp["NAME"]):?>
								<input type="hidden" id="CODE_<?=$i;?>" name="CODE_<?=$i;?>" value="<?=htmlspecialcharsbx($arProp["CODE"])?>">
								<input type="hidden" id="NAME_<?=$i;?>" name="NAME_<?=$i;?>" value="<?=htmlspecialcharsbx($arProp["NAME"]);?>">
								<?=htmlspecialcharsbx($arProp["NAME"]);?>:
							<?else:?>
								<input type="text" name="CODE_<?=$i?>" id="CODE_<?=$i?>" value="<?echo htmlspecialcharsbx((isset($_POST["CODE_$i"])) ? $_POST["CODE_$i"] : $arProp["CODE"]);?>" size="30">:
							<?endif;?>
						</td>
						<td>
							<?
							$value_ = (isset($_POST["VALUE_$i"])) ? $_POST["VALUE_$i"] : $arProp["VALUE"];
							if($arProp["CODE"] == $tag_prop_name && $search_exist):
								echo InputTags("VALUE_".$i, $value_, array($documentSite), 'size="55"', "VALUE_".$i);
							else:?>
								<input type="text" name="VALUE_<?=$i?>" id="VALUE_<?=$i?>" value="<?=htmlspecialcharsbx($value_);?>" size="60">
							<?endif;
							if($APPLICATION->GetDirProperty($arProp["CODE"], Array($site, $path)))
							{
								?><br><small><b><?=GetMessage("FILEMAN_FILE_EDIT_FOLDER_PROP")?></b> <?echo htmlspecialcharsbx($APPLICATION->GetDirProperty($arProp["CODE"], Array($site, $path)));?></small><?
							}?>
						</td>
					</tr>
					<?
				}
				?>
				<tr>
					<td colspan="2">
						<input type="hidden" id="maxind" name="maxind" value="<?echo $ind; ?>">
						<input type="button" name="propeditmore"  value="<?= GetMessage("FILEMAN_EDIT_PROPSMORE")?>" onClick="_MoreRProps()">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- END FILE PROPS -->
	<?endif;?>
	<?
	if ($USER->CanDoOperation('fileman_add_element_to_menu') && $USER->CanDoFileOperation('fm_add_to_menu',$arPath)):
	$tabControl->BeginNextTab();
	$add_to_menu_check = true;
	?>
	<tr>
		<td width="40%"><label for="add_to_menu"><?= GetMessage("FILEMAN_H_EDIT_ADD")?></label></td>
		<td width="60%"><input type="checkbox" id="add_to_menu" name="add_to_menu" value="Y" onclick="__heAddToMenu()" <?if($_POST['add_to_menu'] == 'Y') echo 'checked';?>></td>
	</tr>

	<tr id="ex"<?if($_POST['add_to_menu']!='Y') echo ' style="display:none;"';?>>
		<td><?= GetMessage("FILEMAN_H_EDIT_TMENU")?></td>
		<td>
			<select id="menutype" name="menutype" onChange="chtyp()">
			<?
				$armt = GetMenuTypes($site);
				$arAllItems = Array();
				$strSelected = "";
				foreach ($armt as $key => $title)
				{
					if(!$USER->CanDoFileOperation('fm_edit_existent_file',Array($site, $__fd_path."/.".$key.".menu.php")))
						continue;

					$arItems = Array();
					$res = CFileMan::GetMenuArray($DOC_ROOT.$__fd_path."/.".$key.".menu.php");
					$aMenuLinksTmp = $res["aMenuLinks"];
					if(!is_array($aMenuLinksTmp))
						$aMenuLinksTmp = Array();
					$itemcnt = 0;
					$cntMenu = count($aMenuLinksTmp);
					for($j = 0; $j < $cntMenu; $j++)
					{
						$aMenuLinksItem = $aMenuLinksTmp[$j];
						$arItems[] = htmlspecialcharsbx($aMenuLinksItem[0]);
					}
					$arAllItems[$key] = $arItems;
					if($strSelected=="")
						$strSelected = $key;
					?><option value="<?= htmlspecialcharsex($key)?>"
					<?if(isset($_POST['menutype']) && $_POST['menutype'] == $key) echo 'selected';?>>
					<?= htmlspecialcharsex($title." [".$key."]")?></option><?
				}
			?>
			</select>
		</td>
	</tr>

<script>
function __heAddToMenu()
{
	var add_to_menu = BX("add_to_menu");
	if(add_to_menu.checked)
	{
		__CHRow(BX("ex"));
		__CHRow(BX("e0"));
		__CHRow(BX("e1"));
		__CHRow(BX("e2"));
		__CHRow(BX("e3"));
		chtyp();
	}
	else
	{
		BX("ex").style.display = 'none';
		BX("e0").style.display = 'none';
		BX("e1").style.display = 'none';
		BX("e2").style.display = 'none';
		BX("e3").style.display = 'none';
	}
}
<?
$arTypes = array_keys($arAllItems);
$strTypes = "";
$strItems = "";
$cntTypes = count($arTypes);
for($i = 0; $i < $cntTypes; $i++)
{
	if($i>0)
	{
		$strTypes .= ",";
		$strItems .= ",";
	}
	$strTypes .= "'".CUtil::JSEscape($arTypes[$i])."'";
	$arItems = $arAllItems[$arTypes[$i]];
	$strItems .= "[";
	$cntItems = count($arItems);
	for($j = 0; $j < $cntItems; $j++)
	{
		if($j > 0)
			$strItems .= ",";
		$strItems.="'".CUtil::JSEscape($arItems[$j])."'";
	}
	$strItems .= "]";
}
?>
function __CHRow(row)
{
	try{row.style.display = 'table-row';}
	catch(e){row.style.display = 'block';}
}

var arTypes = Array(<?= $strTypes?>);
var arItems = Array(<?= $strItems?>);
function chtyp(strInitValue1, strInitValue2)
{
	var cur = BX("menutype")[BX("menutype").selectedIndex].value;
	var i;
	for(i=0; i<arTypes.length; i++)
		if(cur==arTypes[i])
			break;

	var itms = arItems[i];

	var list = BX("menuitem");
	var oOption;
	while(list.length>0)
		list.remove(0);
	for(i=0; i<itms.length; i++)
	{
		oOption = new Option(itms[i], i+1, false, false);
		list.options.add(oOption);
	}

	if(strInitValue1)
		list.value=strInitValue1;
	else
		list.selectedIndex=0;

	chitemtype();

	list = BX("newppos");
	while(list.length>0)
		list.remove(0);
	for(i=0; i<itms.length; i++)
	{
		oOption = new Option(itms[i], i+1, false, false);
		list.options.add(oOption);
		oOption.innerText = itms[i];
		oOption.value = i+1;
	}

	oOption = new Option("<?=GetMessage('FILEMAN_H_EDIT_MENU_LAST')?>", 0, false, false);
	list.options.add(oOption);
	if(strInitValue2)
		list.value=strInitValue2;
	else
		list.selectedIndex=list.length-1;
}

function chitemtype()
{
	var cur = BX("menutype")[BX("menutype").selectedIndex].value;
	for(var i=0; i<arTypes.length; i++)
		if(cur==arTypes[i])
			break;

	var ffilemanedit = BX("ffilemanedit");
	var itms = arItems[i];
	if(itms.length<=0)
	{
		ffilemanedit.itemtype[0].checked = true;
		ffilemanedit.itemtype[1].disabled = true;
	}
	else
		ffilemanedit.itemtype[1].disabled = false;

	var x1=BX('e1');
	var x2=BX('e2');
	var x3=BX('e3');
	if(ffilemanedit.itemtype[0].checked)
	{
		__CHRow(x1);
		__CHRow(x2);
		x3.style.display='none';
	}
	else
	{
		x1.style.display='none';
		x2.style.display='none';
		__CHRow(x3);
	}
}

function __NewDocTempl(id)
{
	window.location='/bitrix/admin/fileman_html_edit.php?lang=<?= LANG?>&site=<?=Urlencode($site)?>&path=<?= UrlEncode($path)?>&new=y&template='+id;
	new_doc_list.PopupHide();
}

BX.ready(function() {
	BX.addCustomEvent(document.forms.ffilemanedit, 'onAutoSavePrepare', function (ob, handler)
	{
		BX.bind(document.forms.ffilemanedit.propeditmore, 'click', handler);
	});

	BX.addCustomEvent(document.forms.ffilemanedit, 'onAutoSaveRestore', function (ob, data)
	{
		var i = <?=count($arAllPropFields)?>;
		while (typeof data['CODE_' + i] != 'undefined')
		{
			_MoreRProps(data['CODE_' + i], data['VALUE_' + i]);
			i++;
		}
	});
})
</script>
<tr id="e0"<?if($_REQUEST['add_to_menu']!='Y')echo ' style="display:none;"';?>>
	<td valign="top"><?= GetMessage("FILEMAN_H_EDIT_MENUIT")?></td>
	<td>
		<input type="radio" name="itemtype" id="itemtype_n" value="n" onclick="chitemtype()"
		<?if($n = (!isset($_POST['itemtype']) || $_POST['itemtype'] != 'e')) echo 'checked';?>> <label for="itemtype_n"><?= GetMessage("FILEMAN_H_EDIT_MENUITNEW")?></label><br>
		<input type="radio" name="itemtype" id="itemtype_e" value="e" onclick="chitemtype()"<?if(!$n) echo 'checked';?>> <label for="itemtype_e"><?echo GetMessage("FILEMAN_H_EDIT_MENUITEX")?></label>
	</td>
</tr>
<tr id="e1"<?if($_REQUEST['add_to_menu']!='Y')echo ' style="display:none;"';?>>
	<td><?echo GetMessage("FILEMAN_H_EDIT_MENU_NEW_NAME")?></td>
	<td><input type="text" name="newp" id="newp" value="<?if(isset($_POST['newp'])) echo htmlspecialcharsbx($_POST['newp']);?>"></td>
</tr>
<tr id="e2"<?if($_REQUEST['add_to_menu']!='Y')echo ' style="display:none;"';?>>
	<td><?echo GetMessage("FILEMAN_H_EDIT_MENU_INS_BEFORE")?></td>
	<td>
		<select name="newppos" id="newppos"><?
			$arItems = $arAllItems[$strSelected];
			$l = count($arItems);
			for($i = 0; $i < $l; $i++):
				?><option value="<?= $i + 1?>" <?if(isset($_POST['newppos']) && $_POST['newppos'] == $i + 1) echo 'selected';?>><?= $arItems[$i]?></option><?
			endfor;
			?><option value="0" <?if(isset($_POST['newppos']) && $_POST['newppos'] == 0) echo 'selected';?>><?echo GetMessage("FILEMAN_H_EDIT_MENU_LAST")?></option>
		</select>
	</td>
</tr>
<tr id="e3"<?if($_REQUEST['add_to_menu']!='Y')echo ' style="display:none;"';?>>
	<td><?echo GetMessage("FILEMAN_H_EDIT_MENU_ITEM")?></td>
	<td>
		<select name="menuitem" id="menuitem"><?
			$arItems = $arAllItems[$strSelected];
			$l = count($arItems);
			for($i = 0; $i < $l; $i++):
			?><option value="<?= $i + 1?>" <?if(isset($_POST['menuitem']) && $_POST['menuitem'] == $i + 1) echo 'selected';?>><?= $arItems[$i]?></option><?
			endfor;
		?></select>
			<input type="hidden" name="apply2" id="apply2" value="">
			<input type="hidden" name="save" value="Y">
	</td>
</tr>
<?
else:
	$add_to_menu_check = false;
endif; //if "menu adding tab" show
$tabControl->Buttons(array("disabled"=>false, "back_url"=>$url));
$tabControl->End();
?>
	</form>
<?endif;//if(strlen($strWarning)<=0 || $bVarsFromForm):?>

<?if($_REQUEST['add_to_menu']=='Y' && $add_to_menu_check):?>
<script>chtyp();</script>
<? endif;?>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>