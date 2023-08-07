<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_admin_files'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

$logical = $logical ?? null;
$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$strWarning = "";

$io = CBXVirtualIo::GetInstance();

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = $io->CombinePath("/", $path);
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;
$arPath = Array($site, $path);

$arFiles = Array();
if(is_array($files))
{
	foreach($files as $ind => $file)
	{
		if(!$USER->CanDoFileOperation('fm_rename_file', Array($site, $path."/".$file)))
			$strWarning .= GetMessage("FILEMAN_RENAME_ACCESS_DENIED")." \"".$file."\".\n";
		else
			$arFiles[$ind] = $file;
	}
}

if(!$io->FileExists($abs_path) && !$io->DirectoryExists($abs_path))
	$strWarning .= GetMessage("FILEMAN_FILEORFOLDER_NOT_FOUND");
else
{
	if($REQUEST_METHOD=="POST" && $save <> '' && check_bitrix_sessid())
	{
		$pathTmp = $path;
		foreach($arFiles as $ind => $file)
		{
			$newfilename = $filename[$ind];
			if($newfilename == '')
			{
				$strWarning .= GetMessage("FILEMAN_RENAME_NEW_NAME")." \"".$file."\"!\n";
			}
			elseif (($mess = CFileMan::CheckFileName($newfilename)) !== true)
			{
				$strWarning = $mess;
			}
			else
			{
				$pathto = Rel2Abs($path, $newfilename);
				if(!$USER->CanDoOperation('edit_php') && (mb_substr(CFileman::GetFileName($file), 0, 1) == "." || mb_substr(CFileman::GetFileName($pathto), 0, 1) == "." || (!HasScriptExtension($file) && HasScriptExtension($pathto)))) // if not admin and renaming from non PHP to PHP
					$strWarning .= GetMessage("FILEMAN_RENAME_TOPHPFILE_ERROR")."\n";
				elseif(!$USER->CanDoOperation('edit_php') 	&& HasScriptExtension($file) && !HasScriptExtension($pathto)) // if not admin and renaming from PHP to non PHP
					$strWarning .= GetMessage("FILEMAN_RENAME_FROMPHPFILE_ERROR")."\n";
				else
				{
					$pathparsedtmp = CFileMan::ParsePath(Array($site, $pathto), false, false, "", $logical == "Y");
					$strWarningTmp = CFileMan::CreateDir($pathparsedtmp["PREV"]);

					if($strWarningTmp <> '')
						$strWarning .= $strWarningTmp;
					else
					{
						if(!$io->FileExists($DOC_ROOT.$path."/".$file) && !$io->DirectoryExists($DOC_ROOT.$path."/".$file))
							$strWarning .= GetMessage("FILEMAN_RENAME_FILE")." \"".$path."/".$file."\" ".GetMessage("FILEMAN_RENAME_NOT_FOUND")."!\n";
						elseif($io->FileExists($DOC_ROOT.$pathto) && $DOC_ROOT.rtrim($path,"/")."/".$file !== $DOC_ROOT.$pathto)
							$strWarning .= GetMessage("FILEMAN_RENAME_FILE_EXISTS");
						elseif(!$io->Rename($DOC_ROOT.$path."/".$file, $DOC_ROOT.$pathto))
							$strWarning .= GetMessage("FILEMAN_RENAME_ERROR")." \"".$path."/".$file."\" ".GetMessage("FILEMAN_RENAME_IN")." \"".$pathto."\"!\n";
						else
						{
							$APPLICATION->CopyFileAccessPermission(Array($site, $path."/".$file), Array($site, $pathto));
							$APPLICATION->RemoveFileAccessPermission(Array($site, $path."/".$file));
							$arParsedPathTmp = CFileMan::ParsePath(Array($site, $pathto), false, false, "", $logical == "Y");
							$arFiles[$ind] = $arParsedPathTmp["LAST"];
							$pathTmp = $arParsedPathTmp["PREV"];
						}
					}
				}
			}
		}

		if($strWarning == '')
		{
			$module_id = "fileman";
			if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
			{
				$res_log['path'] = mb_substr($pathto, 1);
				CEventLog::Log(
					"content",
					"FILE_RENAME",
					"fileman",
					"",
					serialize($res_log)
				);
			}
			$path = $pathTmp;
			$arParsedPath = CFileMan::ParsePath(Array($site, $path), false, false, "", $logical == "Y");
			$abs_path = $DOC_ROOT.$path;
			LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
		}
	}
}

isset($file) ? $APPLICATION->SetTitle(GetMessage("FILEMAN_RENAME_TITLE2")." \"".$file."\"") : $APPLICATION->SetTitle(GetMessage("FILEMAN_RENAME_TITLE2"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage($strWarning);

$aTabs = array(
array("DIV" => "edit1", "TAB" => GetMessage('FILEMAN_RENAME_TITLE2'), "ICON" => "fileman", "TITLE" => GetMessage('FILEMAN_RENAME_TITLE2')),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
$tabControl->Begin();
$tabControl->BeginNextTab();?>

<?if(count($arFiles)>0):?>
<form action="fileman_rename.php?lang=<?=LANG?>&path=<?=UrlEncode($path)?>&site=<?=Urlencode($site)?>" method="POST">
	<input type="hidden" name="logical" value="<?=htmlspecialcharsbx($logical)?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="save" value="Y">
	<?foreach($arFiles as $ind => $file):?>
	<input type="hidden" name="files[<?echo htmlspecialcharsbx($ind)?>]" value="<?echo htmlspecialcharsbx($file)?>">
	<input type="text" class="typeinput" name="filename[<?echo htmlspecialcharsbx($ind)?>]" value="<?echo htmlspecialcharsbx($file)?>" size="30" maxlength="255">
	<?endforeach?>
<?else://if(count($arFiles)>0):?>
	<font class="text"><?echo GetMessage("FILEMAN_RENAME_LIST_EMPTY")?></font>
<?endif;//if(count($arFiles)>0):?>
<?
$tabControl->EndTab();
$tabControl->Buttons();?>
<input type="submit" class="adm-btn-save" name="saveb" value="<?=GetMessage("admin_lib_edit_save")?>">&nbsp;<input class="button" type="reset" value="<?=GetMessage("admin_lib_edit_cancel")?>" onclick="javascript:window.location='/bitrix/admin/fileman_admin.php?<?=$addUrl?>&site=<?=UrlEncode($site)?>&path=<?=UrlEncode($path)?>'">
</form>
<?$tabControl->End();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
