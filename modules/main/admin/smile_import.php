<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID = intval($_REQUEST['ID'] ?? 0);
$arError = $arSmile = $arFields = $arLang = array();
$message = null;

/* LANGS */
$arLangTitle = array("reference_id" => array(), "reference" => array());
$db_res = CLanguage::GetList();
while ($res = $db_res->Fetch())
{
	$arLang[$res["LID"]] = $res;
	$arLangTitle["reference_id"][] = $res["LID"];
	$arLangTitle["reference"][] = htmlspecialcharsbx($res["NAME"]);
}

$bInitVars = false;
$bImportComplete = false;
$APPLICATION->SetTitle(GetMessage("SMILE_IMPORT_TITLE"));

$fileName = '';
if ($REQUEST_METHOD == "POST" && (!empty($_POST['save']) || !empty($_POST['apply'])))
{
	$fileName = 'import'.$USER->GetID().time().'.zip';

	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad_sessid",
			"text" => GetMessage("ERROR_BAD_SESSID"));
	}
	elseif (!empty($_FILES["IMPORT"]["tmp_name"]))
	{
		$sUploadDir = CTempFile::GetDirectoryName(1);
		CheckDirPath($sUploadDir);

		$res = CFile::CheckFile($_FILES["IMPORT"], 0, false, 'zip');
		if ($res <> '')
		{
			$arError[] = array(
				"id" => "IMPORT",
				"text" => $res
			);
		}
		elseif (file_exists($sUploadDir.$fileName))
		{
			$arError[] = array(
				"id" => "IMPORT",
				"text" => GetMessage("ERROR_EXISTS_FILE")
			);
		}
		elseif (!@copy($_FILES["IMPORT"]["tmp_name"], $sUploadDir.$fileName))
		{
			$arError[] = array(
				"id" => "IMPORT",
				"text" => GetMessage("ERROR_COPY_FILE"));
		}
		else
		{
			@chmod($sUploadDir.$fileName, BX_FILE_PERMISSIONS);
		}
	}
	elseif (empty($_FILES["IMPORT"]["tmp_name"]))
	{
		$arError[] = array(
			"id" => "IMPORT",
			"text" => GetMessage("ERROR_EXISTS_FILE")
		);
	}

	if (empty($arError))
	{
		$GLOBALS["APPLICATION"]->ResetException();

		$importCount = CSmile::import(array(
			'FILE' => $sUploadDir.$fileName,
			'SET_ID' => intval($_REQUEST['SET_ID'])
		));
		if ($e = $GLOBALS["APPLICATION"]->GetException())
		{
			$arError[] = array(
				"id" => "",
				"text" => $e->getString()
			);
			@unlink($sUploadDir.$fileName);
		}
		else
		{
			@unlink($sUploadDir.$fileName);
			$bImportComplete = true;
		}
	}
	$e = new CAdminException($arError);
	$message = new CAdminMessage(GetMessage("ERROR_IMPORT_SMILE"), $e);
	$bInitVars = true;
}


$arSmile = array(
	"SET_ID" => $_REQUEST['SET_ID'] ?? 0,
	"GALLERY_ID" => $_REQUEST['GALLERY_ID'] ?? 0,
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($bImportComplete)
{
	CAdminMessage::ShowMessage(array(
		"MESSAGE"=>GetMessage("IM_IMPORT_COMPLETE"),
		"DETAILS"=>GetMessage("IM_IMPORT_TOTAL", Array('#COUNT#' => $importCount)),
		"HTML"=>true,
		"TYPE"=>"OK",
	));
	LocalRedirect("smile.php?SET_ID=".$arSmile['SET_ID']."&lang=".LANG);
}
else if (isset($message) && $message)
	echo $message->Show();
?>
	<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="smile_import" enctype="multipart/form-data">
	<input type="hidden" name="Update" value="Y" />
	<input type="hidden" name="lang" value="<?=LANG?>" />
	<input type="hidden" name="ID" value="<?=$ID?>" />
	<?=bitrix_sessid_post()?>
<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SMILE_TAB_SMILE"), "ICON" => "smile", "TITLE" => GetMessage("SMILE_TAB_SMILE_DESCR"))
	);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$arSmileSetDisabled = false;
$arSmileSet = CSmileSet::getListForForm($arSmile['GALLERY_ID']);
if (empty($arSmileSet))
{
	$arSmileSetDisabled = true;
	$arSmileSet = Array('' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
}

$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("SMILE_SET_ID")?>:</td>
		<td width="60%">
			<select name="SET_ID" <?=($arSmileSetDisabled? 'disabled="true"':'')?>>
			<?foreach ($arSmileSet as $key => $value):?>
				<option value="<?=$key?>" <?=($arSmile["SET_ID"] == $key ? "selected" : "")?>><?=$value;?></option>
			<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr<?if ($ID <= 0){ ?> class="adm-detail-required-field"<? }?>>
		<td>
			<?=GetMessage("SMILE_FILE")?>:<br><small><?=GetMessage("SMILE_FILE_NOTE")?></small></td>
		<td>
			<input type="file" name="IMPORT" size="30" />
		</td>
	</tr>
<?
$tabControl->EndTab();

$tabControl->Buttons(array(
	"btnApply" => false,
));
?>
</form>
<?
$tabControl->End();
$tabControl->ShowWarnings("smile_import", $message);

?>
<?=BeginNote();?>
<div><?=GetMessage('IM_IMPORT_HELP_1', Array('#LINK_START#'=>'<a href="/bitrix/admin/fileman_admin.php?lang='.LANG.'&path=%2Fbitrix%2Fmodules%2Fmain%2Finstall%2Fsmiles">', '#LINK_END#'=>'</a>'))?></div>
<div style="padding-top:5px"><?=GetMessage('IM_IMPORT_HELP_2')?></div>
<div style="padding-top:5px"><?=GetMessage('IM_IMPORT_HELP_3')?></div>
<div style="padding-top:15px"><?=GetMessage('IM_IMPORT_HELP_4')?></div>
<?=EndNote();?>
<?require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");?>
