<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_upload_files'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$strWarning = "";

$io = CBXVirtualIo::GetInstance();

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = $io->CombinePath("/", $path);
$arPath = Array($site, $path);
$arParsedPath = CFileMan::ParsePath($arPath, true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;

$bCan = false;

// Check permissions
if(!$USER->CanDoFileOperation('fm_upload_file',$arPath))
	$strWarning = GetMessage("ACCESS_DENIED");
else
{
	$bCan = true;
	if($REQUEST_METHOD=="POST" && $save <> '' && check_bitrix_sessid())
	{
		$nums = intval($nums);
		if($nums > 0)
		{
			for($i = 1; $i <= $nums; $i++)
			{
				$arFile = $_FILES["file_".$i];
				if($arFile["name"] == '' || $arFile["tmp_name"]=="none")
					continue;

				$arFile["name"] = CFileman::GetFileName($arFile["name"]);
				$filename = ${"filename_".$i};
				if($filename == '')
					$filename = $arFile["name"];

				$pathto = Rel2Abs($path, $filename);
				if(!$USER->CanDoFileOperation('fm_upload_file',Array($site, $pathto)))
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_ACCESS_DENIED")." \"".$pathto."\"\n";
				}
				elseif($arFile["error"] == 1 || $arFile["error"] == 2)
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_SIZE_ERROR", Array('#FILE_NAME#' => $pathto))."\n";
				}
				elseif(($mess = CFileMan::CheckFileName(str_replace('/', '', $pathto))) !== true)
				{
					$strWarning .= $mess.".\n";
				}
				else if($io->FileExists($DOC_ROOT.$pathto))
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_FILE_EXISTS1")." \"".$pathto."\" ".GetMessage("FILEMAN_FILEUPLOAD_FILE_EXISTS2").".\n";
				}
				elseif(!$USER->IsAdmin() && (HasScriptExtension($pathto) || mb_substr(CFileman::GetFileName($pathto), 0, 1) == "."))
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_PHPERROR")." \"".$pathto."\".\n";
				}
				else
				{
					$bQuota = true;
					if (COption::GetOptionInt("main", "disk_space") > 0)
					{
						$f = $io->GetFile($arFile["tmp_name"]);
						$bQuota = false;
						$size = $f->GetFileSize();
						$quota = new CDiskQuota();
						if ($quota->checkDiskQuota(array("FILE_SIZE" => $size)))
							$bQuota = true;
					}

					if ($bQuota)
					{
						if(!$io->Copy($arFile["tmp_name"], $DOC_ROOT.$pathto))
							$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_FILE_CREATE_ERROR")." \"".$pathto."\"\n";
						elseif(COption::GetOptionInt("main", "disk_space") > 0)
							CDiskQuota::updateDiskQuota("file", $size, "copy");
						$f = $io->GetFile($DOC_ROOT.$pathto);
						$f->MarkWritable();
						$module_id = 'fileman';
						if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
						{
							$res_log['path'] = mb_substr($pathto, 1);
							CEventLog::Log(
								"content",
								"FILE_ADD",
								"main",
								"",
								serialize($res_log)
							);
						}
					}
					else
						$strWarning .= $quota->LAST_ERROR."\n";
				}
			}
		}

		if($strWarning == '')
		{
			if (!empty($_POST["apply"]))
				LocalRedirect("/bitrix/admin/fileman_file_upload.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
			else
				LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
		}
	}
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

$APPLICATION->SetTitle(GetMessage("FILEMAN_FILE_UPLOAD_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?CAdminMessage::ShowMessage($strWarning);?>

<?if($strWarning == '' || $bCan):?>
	<script>
	function NewFileName(ob)
	{
		var
			str_filename,
			filename,
			str_file = ob.value,
			num = ob.name;

		num  = num.substr(num.lastIndexOf("_")+1);
		str_file = str_file.replace(/\\/g, '/');
		filename = str_file.substr(str_file.lastIndexOf("/")+1);
		document.ffilemanupload["filename_"+num].value = filename;
		if(document.ffilemanupload.nums.value==num)
		{
			num++;
			var tbl = BX("bx-upload-tbl");
			var cnt = tbl.rows.length;
			var oRow = tbl.insertRow(cnt);
			var oCell = oRow.insertCell(0);
			oCell.className = "adm-detail-content-cell-l";
			oCell.innerHTML = '<input type="text" name="filename_'+num+'" size="30" maxlength="255" value="">';
			var oCell = oRow.insertCell(1);
			oCell.className = "adm-detail-content-cell-r";
			oCell.innerHTML = '<input type="file" name="file_'+num+'" size="30" maxlength="255" value="" onChange="NewFileName(this)">';

			document.ffilemanupload.nums.value = num;
		}

		BX.adminPanel.modifyFormElements(BX("bx-upload-tbl"));
	}
	</script>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()."?".$addUrl."&site=".$site."&path=".UrlEncode($path);?>" name="ffilemanupload" enctype="multipart/form-data">
	<input type="hidden" name="logical" value="<?=htmlspecialcharsbx($logical)?>">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="save" value="Y">
	<?=bitrix_sessid_post()?>

	<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage('FILEMAN_UPL_TAB'), "ICON" => "fileman", "TITLE" => GetMessage('FILEMAN_UPL_TAB_ALT')),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>

	<tr><td colspan="2" align="left">
		<input type="hidden" name="nums" value="5">
		<table id="bx-upload-tbl">
			<tr class="heading">
				<td style="text-align: right!important;" width="40%">
					<span style="display: inline-block; width: 200px; text-align: left;"><?= GetMessage("FILEMAN_FILEUPLOAD_NAME")?></span>
				</td>
				<td style="text-align: left!important;" width="60%">
					<?= GetMessage("FILEMAN_FILEUPLOAD_FILE")?>
				</td class="adm-detail-content-cell-r">
			</tr>
		<?for($i=1; $i<=5; $i++):?>
			<tr>
			<td class="adm-detail-content-cell-l">
				<input type="text" name="filename_<?echo $i?>" size="30" maxlength="255" value="">
			</td>
			<td class="adm-detail-content-cell-r">
				<input type="file" name="file_<?echo $i?>" size="30" maxlength="255" value="" onChange="NewFileName(this)">
			</td>
			</tr>
		<?endfor?>
	</table></td></tr>
	<?$tabControl->EndTab();
	$tabControl->Buttons(
		array(
			"disabled" => false,
			"back_url" => "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
		)
	);
	$tabControl->End();
	?>
	</form>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>