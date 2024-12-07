<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('fileman');
IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

$strWarning = "";

$logical = $logical ?? null;
$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$io = CBXVirtualIo::GetInstance();
$path = (string)($request->get('path') ?? '');
$path = $io->CombinePath("/", $path);

$bVarsFromForm = false;		//flag, witch points were get content from: file or post form
$filename = $filename ?? null;
if ($filename <> '' && ($mess = CFileMan::CheckFileName($filename)) !== true)
{
	$filename2 = $filename;
	$filename = '';
	$strWarning = $mess;
	$bVarsFromForm = true;
}

//if new file & sets new name
$new = $new ?? null;
if($new <> '' && $filename <> '')
	$path = $path."/".$filename; // let's set this new name to us path

$site ??= $_REQUEST['site'] ?? null;
$site = CFileMan::__CheckSite($site);
if(!$site)
	$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

$DOC_ROOT = CSite::GetSiteDocRoot($site);
$abs_path = $DOC_ROOT.$path;
$arPath = Array($site, $path);

if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files') || $USER->CanDoFileOperation('fm_view_file', $arPath)))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$site_template = false;
$rsSiteTemplates = CSite::GetTemplateList($site);
while($arSiteTemplate = $rsSiteTemplates->Fetch())
{
	if($arSiteTemplate["CONDITION"] == '')
	{
		$site_template = $arSiteTemplate["TEMPLATE"];
		break;
	}
}

if(($new == '' || $filename == '') && !$io->FileExists($abs_path))
{
	$p = mb_strrpos($path, "/");
	if($p!==false)
	{
		$new = "Y";
		$filename = mb_substr($path, $p + 1);
		//$path = substr($path, 0, $p);
		//$abs_path = $DOC_ROOT.$path;
	}
}
$originalPath = $path;

$full_src = $full_src ?? null;
$bFullPHP = ($full_src=="Y") && $USER->CanDoOperation('edit_php');
$NEW_ROW_CNT = 1;

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$isScriptExt = HasScriptExtension($path);

$only_read = false;
$arPath = Array($site, $path);

if(($new <> '' &&
!($USER->CanDoOperation('fileman_admin_files') &&
$USER->CanDoFileOperation('fm_create_new_file',$arPath))) ||
($new == '' &&
(!$USER->CanDoOperation('fileman_edit_existent_files') ||
!$USER->CanDoFileOperation('fm_edit_existent_file',$arPath) ||
!$USER->CanDoFileOperation('fm_view_file',$arPath))))
{
	$strWarning = GetMessage("ACCESS_DENIED");
}
else
{
	if ($new == "" &&
	$USER->CanDoFileOperation('fm_view_file',$arPath) &&
	!$USER->CanDoFileOperation('fm_edit_existent_file',$arPath))
		$only_read = true;

	if($new <> '' && $filename <> '' && $io->FileExists($abs_path))		// if we want to make new file , but the file with the same name alredy exist, let's abuse
	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_FILE_EXISTS")." ";
		$bEdit = false;
		?><script id="ajax-script-file-exists">top.strWarning = '<?= CUtil::JSEscape($strWarning)?>';</script><?
		$bVarsFromForm = true;
		$path = $io->CombinePath("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $DOC_ROOT.$path;
	}
	elseif(!$USER->CanDoOperation('edit_php') && mb_substr(CFileman::GetFileName($abs_path), 0, 1) == ".")
	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_BAD_FNAME")." ";
		$bEdit = false;
		$bVarsFromForm = true;
		$path = $io->CombinePath("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $DOC_ROOT.$path;
	}
	elseif($new <> '')
	{
		$bEdit = false;
	}
	else
	{
		if($io->DirectoryExists($abs_path))
			$strWarning = GetMessage("FILEMAN_FILEEDIT_FOLDER_EXISTS")." ";
		else
			$bEdit = true;
	}

	$limit_php_access = ($USER->CanDoFileOperation('fm_lpa',$arPath) && !$USER->CanDoOperation('edit_php'));
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
			$ofp_id = mb_substr(md5($site.'|'.$path), 0, 8);
			if(!isset($_SESSION['arOFP'][$ofp_id]))
				$_SESSION['arOFP'][$ofp_id] = $path;
		}
	}
}

$propeditmore = $propeditmore ?? null;
$back_url = $back_url ?? null;
$template = $template ?? null;
if($strWarning == '')
{
	if($bEdit)
	{
		$filesrc_tmp = $APPLICATION->GetFileContent($abs_path);
	}
	else
	{
		$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));
		if($template <> '')
		{
			for ($i = 0, $l = count($arTemplates); $i < $l; $i++)
			{
				if($arTemplates[$i]["file"] == $template)
				{
					$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[$i]["file"], LANGUAGE_ID, array($site_template));
					break;
				}
			}
		}
		else
			$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[0]["file"], LANGUAGE_ID, array($site_template));
	}

	if($_SERVER['REQUEST_METHOD'] == "POST" && $save <> '' && $propeditmore == '' && !$only_read)
	{
		if(!check_bitrix_sessid())
		{
			$strWarning = GetMessage("FILEMAN_SESSION_EXPIRED");
			$bVarsFromForm = true;
		}
		elseif(!$bFullPHP)
		{
			if((CFileman::IsPHP($filesrc) || $isScriptExt) && !($USER->CanDoOperation('edit_php') || $limit_php_access))
			{
				$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE")." ";
				$bVarsFromForm = true;
				if($new <> '' && $filename <> '')
				{
					$bEdit = false;
					$path = Rel2Abs("/", $arParsedPath["PREV"]);
					$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
					$abs_path = $DOC_ROOT.$path;
				}
			}
			else
			{
				if($limit_php_access)
				{
					// ofp - original full path :)
					$ofp = $_SESSION['arOFP'][$ofp_id];
					$ofp = $io->CombinePath("/", $ofp);
					$old_res = CFileman::ParseFileContent($APPLICATION->GetFileContent($DOC_ROOT.$ofp));
					$old_filesrc = $old_res["CONTENT"];
					$filesrc = CMain::ProcessLPA($filesrc, $old_filesrc);
				}

				$res = CFileman::ParseFileContent($filesrc_tmp);
				$prolog = CFileman::SetTitle($res["PROLOG"], $title);
				for ($i = 0; $i <= $maxind; $i++)
				{
					if(Trim($_POST["CODE_".$i]) <> '')
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
		}
		else
			$filesrc_for_save = $filesrc;


		if($strWarning == '')
		{
			if (!CFileMan::CheckOnAllowedComponents($filesrc_for_save))
			{
				$str_err = $APPLICATION->GetException();
				if($str_err && ($err = $str_err ->GetString()))
					$strWarning .= $err;
				$bVarsFromForm = true;
			}
		}

		if($strWarning == '')
		{
			if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				if($APPLICATION->GetException())
				{
					$str_err = $APPLICATION->GetException();
					if ($str_err && ($err = $str_err ->GetString()))
						$strWarning = $err;

					$bVarsFromForm = true;
					$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
					$path = $originalPath = $io->CombinePath("/", $arParsedPath["PREV"]);
					$abs_path = $DOC_ROOT.$path;
				}

				if (empty($strWarning))
				{
					$strWarning = GetMessage("FILEMAN_FILE_SAVE_ERROR")." ";
				}
			}
			else
			{
				$module_id = "fileman";
				if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
				{
					$res_log['path'] = mb_substr($path, 1);
					if ($new <> '' && mb_strtolower($new) == 'y' && $filename <> '')
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
				$bEdit = true;
			}

			if(isset($_POST['AJAX_APPLY']))
			{
				$APPLICATION->RestartBuffer();
				?><script id="ajax-script-apply">top.strWarning = '<?= CUtil::JSEscape($strWarning)?>';</script><?
				die();
			}

			if ($strWarning == '')
			{
				if(($apply ?? null) == '')
				{
					if($back_url <> '' && mb_strpos($back_url, "/bitrix/admin/fileman_file_edit.php") !== 0)
						LocalRedirect("/".ltrim($back_url, "/"));
					LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($arParsedPath["PREV"]));
				}
				else
				{
					LocalRedirect("/bitrix/admin/fileman_file_edit.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($path).($full_src=="Y"?"&full_src=Y":""));
				}
			}

			$filesrc_tmp = $filesrc_for_save;

			$path = $io->CombinePath("/", $path);
			$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
			$abs_path = $DOC_ROOT.$path;
		}
	}
}

if($propeditmore <> '')
	$bVarsFromForm = True;

$bEditProps = false;
if(!$bVarsFromForm)
{
	if(!$bEdit && $filename == '')
		$filename = ($USER->CanDoOperation('edit_php') || $limit_php_access) ? "untitled.php" : "untitled.html";

	$filesrc = $filesrc_tmp;
	if(!$bFullPHP)
	{
		$res = CFileman::ParseFileContent($filesrc);
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
					$new_filesrc .= mb_substr($filesrc, $end, $start - $end);
					$end = $arPHP[$n][1];

					//Trim php tags
					$src = $arPHP[$n][2];
					if (mb_substr($src, 0, 5) == "<?"."php")
						$src = mb_substr($src, 5);
					else
						$src = mb_substr($src, 2);
					$src = mb_substr($src, 0, -2);

					//If it's Component 2, keep the php code. If it's component 1 or ordinary PHP - than replace code by #PHPXXXX# (XXXX - count of PHP scripts)
					$comp2_begin = '$APPLICATION->INCLUDECOMPONENT(';
					if (mb_strtoupper(mb_substr($src, 0, mb_strlen($comp2_begin))) == $comp2_begin)
						$new_filesrc .= $arPHP[$n][2];
					else
						$new_filesrc .= '#PHP'.str_pad(++$php_count, 4, "0", STR_PAD_LEFT).'#';
				}
				$new_filesrc .= mb_substr($filesrc, $end);
				$filesrc = $new_filesrc;
			}
		}

		$bEditProps = (mb_strpos($res["PROLOG"], "prolog_before") > 0) || (mb_strpos($res["PROLOG"], "header.php") > 0);
		$title = $res["TITLE"];
		$page_properties = $res["PROPERTIES"];
	}

	if((CFileman::IsPHP($filesrc) || $isScriptExt) && !($USER->CanDoOperation('edit_php') || $limit_php_access))
		$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE_ACCESS");
}
elseif($prop_edit=="Y")
	$bEditProps = true;

if($bEdit)
	$APPLICATION->SetTitle(GetMessage("FILEMAN_FILEEDIT_PAGE_TITLE")." \"".$arParsedPath["LAST"]."\"");
else
	$APPLICATION->SetTitle(GetMessage("FILEMAN_NEWFILEEDIT_TITLE"));

if(count($arParsedPath["AR_PATH"]) == 1)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($DOC_ROOT),
			"LINK" => "fileman_admin.php?lang=".LANGUAGE_ID."&site=".urlencode($site)."&path=/"
		)
	);
}

foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => (($chainLevel["LINK"] <> '') ? $chainLevel["LINK"] : ""),
		)
	);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?CAdminMessage::ShowMessage($strWarning);?>

<?if($strWarning == '' || $bVarsFromForm):?>
<?
$aMenu = array();
if($bEdit)
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("FILEMAN_FILE_VIEW"),
		"LINK"=>"fileman_file_view.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path)
	);
}

$mode = ($bFullPHP && $USER->CanDoOperation('edit_php')) ? 'php' : 'text';
$ismenu = preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/i', $arParsedPath["LAST"], $regs);

$aDDMenuEdit = array();
$templateID = $templateID ?? null;
if (!$ismenu)
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_TXT"),
		"ACTION" => ($mode == 'text' ? "return; " : "" )."window.location='fileman_file_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($originalPath).($new <> ''? "&amp;new=Y":"").($back_url <> ''? "&amp;back_url=".urlencode($back_url):"").($template <> ''? "&amp;template=".urlencode($template):"").($templateID <> ''? "&amp;templateID=".urlencode($templateID):"")."';",
		"ICON" =>	($mode == 'text' ? "checked" : "" )
	);
}

if($USER->CanDoOperation('edit_php'))
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_PHP"),
		"ACTION" => ($mode == 'php' ? "return; " : "" )."window.location='fileman_file_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($originalPath)."&amp;full_src=Y".($new <> ''? "&amp;new=Y":"").($back_url <> ''? "&amp;back_url=".urlencode($back_url):"").($template <> ''? "&amp;template=".urlencode($template):"").($template <> ''? "&amp;template=".urlencode($template):"").($templateID <> ''? "&amp;templateID=".urlencode($templateID):"")."';",
		"ICON" =>	($mode == 'php' ? "checked" : "" )
	);
}

if ($ismenu)
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_MENU"),
		"ACTION" => "window.location='"."fileman_menu_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($arParsedPath["PREV"])."&amp;name=".UrlEncode($regs[1]).($new <> ''? "&amp;new=Y":"").($back_url <> ''? "&amp;back_url=".urlencode($back_url):"")."';"
	);
}
else
{
	$aDDMenuEdit[] = array(
		"TEXT" => GetMessage("FILEMAN_FILEEDIT_AS_HTML"),
		"ACTION" => "window.location='"."fileman_html_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($originalPath).($new <> ''? "&amp;new=Y":"").($back_url <> ''? "&amp;back_url=".urlencode($back_url):"").($template <> ''? "&amp;template=".urlencode($template):"").($template <> ''? "&amp;template=".urlencode($template):"").($templateID <> ''? "&amp;templateID=".urlencode($templateID):"")."';"
	);
}

$aMenu[] = array(
	"TEXT" => GetMessage("FILEMAN_FILE_EDIT"),
	"TITLE" => GetMessage("FILEMAN_FILE_EDIT"),
	"MENU" => $aDDMenuEdit
);

if($bEdit)
{
	if($USER->CanDoFileOperation('fm_rename_file', $arPath))
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("FILEMAN_FILEEDIT_RENAME"),
			"LINK"=>"fileman_rename.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($arParsedPath["PREV"])."&amp;files[]=".UrlEncode($arParsedPath["LAST"])
		);
	}

	if(($USER->CanDoFileOperation('fm_download_file', $arPath) && !(HasScriptExtension($path) || mb_substr(CFileman::GetFileName($path), 0, 1) == ".")) || $USER->CanDoOperation('edit_php'))
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("FILEMAN_FILEEDIT_DOWNLOAD"),
			"LINK"=>"fileman_file_download.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path)
		);
	}
	if($USER->CanDoFileOperation('fm_delete_file', $arPath))
	{
		$folder_path = mb_substr($path, 0, mb_strrpos($path, "/"));
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
?>

<form method="POST" action="<?= $APPLICATION->GetCurPage().'?'.$addUrl."&amp;path=".UrlEncode($path).($new <> ''? "&amp;new=Y":"")?>" name="ffilemanedit">
<input type="hidden" name="logical" value="<?=htmlspecialcharsbx($logical)?>">
<?= GetFilterHiddens("filter_");?>
<input type="hidden" name="site" value="<?= htmlspecialcharsbx($site) ?>">
<input type="hidden" name="path" value="<?= htmlspecialcharsbx($path)?>">
<input type="hidden" name="save" value="Y">
<input type="hidden" name="lang" value="<?= LANG?>">
<?=bitrix_sessid_post()?>
<?if(!$bEdit):?>
	<input type="hidden" name="new" value="y">
<?endif?>
<input type="hidden" name="save" value="Y">
<?if($full_src=="Y"):?>
	<input type="hidden" name="full_src" value="Y">
<?endif?>
<input type="hidden" name="template" value="<?= htmlspecialcharsbx($template)?>">
<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($back_url)?>">

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => (($bEdit) ? GetMessage('FILEMAN_EDIT_TAB') : GetMessage('FILEMAN_EDIT_TAB1')), "ICON" => "fileman", "TITLE" => (($bEdit) ? GetMessage('FILEMAN_EDIT_TAB_ALT') : GetMessage('FILEMAN_EDIT_TAB_ALT1'))),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();?>

	<?if(!$bEdit):?>
		<?$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));?>
		<tr>
			<td width="30%"><?= GetMessage("FILEMAN_FILEEDIT_TEMPLATE")?></td>
			<td width="70%">
				<select name="template" onchange="window.location='/bitrix/admin/fileman_file_edit.php?lang=<?= LANG?>&site=<?=Urlencode($site)?>&path=<?= UrlEncode($path)?><? echo ($full_src=="Y" ? "&full_src=Y" : "")?>&new=y&template='+escape(this[this.selectedIndex].value)">
					<?for($i = 0, $l = count($arTemplates); $i < $l; $i++):?>
					<option value="<?= htmlspecialcharsbx($arTemplates[$i]["file"])?>"<?if($template==$arTemplates[$i]["file"])echo " selected"?>><?= htmlspecialcharsbx($arTemplates[$i]["name"])?></option>
					<?endfor;?>
				</select>
			</td>
		</tr>
		<?if(!$bFullPHP):?>
		<tr>
			<td width="30%"><label for="bxfm_title"><?= GetMessage("FILEMAN_FILEEDIT_TITLE")?></label></td>
			<td width="70%"><input id="bxfm_title" type="text" name="title" size="50" maxlength="255" value="<?= htmlspecialcharsbx($title)?>"></td>
		</tr>
		<?endif;?>
		<tr>
			<td><label for="bxfm_filename"><?= GetMessage("FILEMAN_FILEEDIT_NAME")?></label></td>
			<td>
				<?if (isset($filename2))
					$filename = $filename2;
				if(mb_strpos($filename, '.') === false)
					$filename .= ($USER->CanDoOperation('edit_php') || $limit_php_access) ? '.php' : '.html';
				?>
				<input type="text" name="filename" id="bxfm_filename" style="float: left;" size="50" maxlength="255" value="<?= htmlspecialcharsbx($filename)?>"></td>
		</tr>
		<tr><td></td><td style="padding: 0!important;">
			<table id='jserror_name' style="visibility:hidden"><tr><td valign="top">
			<IMG src="/bitrix/themes/.default/images/icon_warn.gif" title="<?=GetMessage("FILEMAN_NAME_ERR");?>">
			</td><td id="jserror" class="jserror"></td></tr></table>
			<script>
			var oInput = BX('bxfm_filename'),
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
	<?elseif(!$bFullPHP):?>
		<tr>
			<td width="30%"><label for="bxfm_title"><?= GetMessage("FILEMAN_FILEEDIT_TITLE")?></label></td>
			<td width="70%"><input id="bxfm_title" type="text" name="title" size="50" maxlength="255" value="<?= htmlspecialcharsbx($title)?>"></td>
		</tr>
	<?endif?>

	<?if(!$bFullPHP):?>
		<? /* Transliteration - only for new files*/
		if (!$bEdit && COption::GetOptionString("fileman", "use_translit", true))
		{
			?>
			<input type="hidden" name="bxfm_linked" id="bxfm_linked" value="N" />
			<?
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
			CFilemanTransliterate::Init(array(
				'fromInputId' => 'bxfm_title',
				'toInputId' => 'bxfm_filename',
				'linkedId' => 'bxfm_linked',
				'linked' => ($_REQUEST['bxfm_linked'] ?? null) == "Y",
				'linkedTitle' => GetMessage('FILEMAN_FILE_TRANS_LINKED'),
				'unlinkedTitle' => GetMessage('FILEMAN_FILE_TRANS_UNLINKED'),
				'ext' => $USER->CanDoOperation('edit_php') || $limit_php_access ? 'php' : 'html'
			));
		}
		?>

		<?if($bEditProps):?>
			<input type="hidden" name="prop_edit" value="Y">
			<!-- FILE PROPS -->
			<script>
				function _MoreRProps(code, value)
				{
					var prt = BX("proptab");
					var cnt = parseInt(BX("maxind", true).value)+1;
					var r = prt.insertRow(prt.rows.length-1);
					var c = r.insertCell(-1);
					c.innerHTML = '<input type="hidden" id="H_CODE_'+cnt+'" name="H_CODE_'+cnt+'" value="'+(code?BX.util.htmlspecialchars(code):'')+'"><input type="text" id="CODE_'+cnt+'" name="CODE_'+cnt+'" value="'+(code?BX.util.htmlspecialchars(code):'')+'" size="30">:';
					c = r.insertCell(-1);
					c.innerHTML = '<input type="text" name="VALUE_'+cnt+'" id="VALUE_'+cnt+'" value="'+(value?BX.util.htmlspecialchars(value):'')+'" size="60">';
					BX("maxind", true).value = cnt;

					if (document.forms.ffilemanedit.BXAUTOSAVE)
					{
						document.forms.ffilemanedit.BXAUTOSAVE.RegisterInput('CODE_'+cnt);
						document.forms.ffilemanedit.BXAUTOSAVE.RegisterInput('VALUE_'+cnt);
					}
				}
			</script>
			<tr>
				<td colSpan="2">
					<table border="0" cellspacing="1" cellpadding="3" id="proptab"  class="internal">
						<tr class="heading">
							<td><?= GetMessage("FILEMAN_EDIT_PROPSCODE")?></td>
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
						$tagFindPath = $_SERVER["DOCUMENT_ROOT"].$path.'/_';
						$documentSite = CSite::GetSiteByFullPath($tagFindPath);
						for($i = 0, $l = count($arAllPropFields); $i < $l; $i++)
						{
							$arProp = $arAllPropFields[$i];
							$arProp["NAME"] = $arProp["NAME"] ?? null;
							?>
							<tr>
								<td  valign="top" <?if(!$arProp["NAME"]) echo 'nowrap';?>>
									<input type="hidden" id="H_CODE_<?=$i;?>" name="H_CODE_<?=$i;?>" value="<?=htmlspecialcharsbx($arProp["CODE"])?>">
									<?if($arProp["NAME"]):?>
										<input type="hidden" id="CODE_<?=$i;?>" name="CODE_<?=$i;?>" value="<?=htmlspecialcharsbx($arProp["CODE"])?>">
										<input type="hidden" id="NAME_<?=$i;?>" name="NAME_<?=$i;?>" value="<?=htmlspecialcharsbx($arProp["NAME"]);?>">
										<?=htmlspecialcharsbx($arProp["NAME"]);?>:
									<?else:?>
										<input type="text" name="CODE_<?=$i?>" id="CODE_<?=$i?>" value="<?= htmlspecialcharsbx((isset($_POST["CODE_$i"])) ? $_POST["CODE_$i"] : $arProp["CODE"]);?>" size="30">:
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
										?><br><small><b><?=GetMessage("FILEMAN_FILE_EDIT_FOLDER_PROP")?></b> <?= htmlspecialcharsbx($APPLICATION->GetDirProperty($arProp["CODE"], Array($site, $path)));?></small><?
									}?>
								</td>
							</tr>
							<?
						}
						?>
<script>

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
						<tr>
							<td colspan="2">
								<input type="hidden" id="maxind" name="maxind" value="<?= $ind;?>">
								<input type="button" name="propeditmore"  value="<?= GetMessage("FILEMAN_EDIT_PROPSMORE")?>" onClick="_MoreRProps()">
							</td>
						</tr>
					</table>
					<br>
				</td>
			</tr>
			<!-- END FILE PROPS -->
		<?endif?>
	<?endif?>
		<tr><td colspan="2">
			<textarea id="bx-filesrc" name="filesrc" rows="37" style="width:100%; overflow:auto;" wrap="OFF"><?= htmlspecialcharsbx($filesrc)?></textarea></td></tr>

<?
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"disabled" => $only_read,
		"back_url" => ($back_url <> '' && mb_strpos($back_url, "/bitrix/admin/fileman_file_edit.php") !== 0) ? htmlspecialcharsbx($back_url) : "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($arParsedPath["PREV"])
	)
);

$tabControl->End();
?>
</form>
<script>
function AjaxApply(e)
{
	var
		form = document.forms.ffilemanedit,
		target = form.target,
		cancelBut = BX.findChild(form, {tag: 'INPUT', attr: {'name': 'cancel', 'type':'button'}}, true),
		saveBut = BX.findChild(form, {tag: 'INPUT', attr: {'name': 'save', 'type':'submit'}}, true),
		applyBut = form.apply;

	var applyInp = BX('bx-ffilemanedit-ajax-apply');
	if (!applyInp)
		applyInp = form.appendChild(BX.create('INPUT', {props: {id: 'bx-ffilemanedit-ajax-apply', type: 'hidden', name: 'AJAX_APPLY', value: 'Y'}}));

	applyBut.value1 = applyBut.value;
	applyBut.value = '<?= GetMessage('FILEMAN_APPLY_PROCESS')?>...';
	applyBut.disabled = true;
	if (cancelBut)
		cancelBut.disabled = true;
	if (saveBut)
		saveBut.disabled = true;

	BX.ajax.submit(form, function()
	{
		applyBut.disabled = false;
		applyBut.value = applyBut.value1;
		if (cancelBut)
			cancelBut.disabled = false;
		if (saveBut)
			saveBut.disabled = false;

		form.target = target;
		if (applyInp)
			BX.cleanNode(applyInp, true);

		if (top.strWarning && top.strWarning.length > 0)
		{
			alert(top.strWarning);
		}
		else
		{
			var filename = BX('bxfm_filename');
			if (filename)
			{
				document.location.href = '<?$APPLICATION->getCurPage()?>?<?=CUtil::JSEscape($addUrl."&site=".Urlencode($site)
				."&path=".UrlEncode($path."/"))?>'+encodeURIComponent(filename.value)+'<?if($full_src=="Y") echo "&full_src=Y";?>';
			}
		}

		BX.focus(BX('bx-filesrc'));
	});


	return BX.PreventDefault(e || window.event);
}

BX.ready(function() {
	BX.bind(document.forms.ffilemanedit.apply, 'click', AjaxApply);
});
</script>

<?
if(COption::GetOptionString('fileman', "use_code_editor", "Y") == "Y")
{
	$forceSyntax = false;
	$ext = mb_strtolower(CFileMan::GetFileExtension($path));
	if ($ext == 'sql')
		$forceSyntax = 'sql';
	elseif($ext == 'js')
		$forceSyntax = 'js';
	elseif($ext == 'css')
		$forceSyntax = 'css';

	CCodeEditor::Show(
		array(
			'textareaId' => 'bx-filesrc',
			'height' => 500,
			'forceSyntax' => $forceSyntax
		));
}

// $hkInst = CHotKeys::getInstance();
// $arExecs = $hkInst->GetCodeByClassName("admin_file_edit_apply");
// echo $hkInst->PrintJSExecs($arExecs);
?>

<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>