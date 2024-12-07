<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

$arParams['MAX_FILE_SIZE'] = intval($arParams['MAX_FILE_SIZE']);
$arParams['MODULE_ID'] = $arParams['MODULE_ID'] && IsModuleInstalled($arParams['MODULE_ID']) ? $arParams['MODULE_ID'] : false;
// ALLOW_UPLOAD = 'A'll files | 'I'mages | 'F'iles with selected extensions
// ALLOW_UPLOAD_EXT = comma-separated list of allowed file extensions (ALLOW_UPLOAD='F')

if (
	$arParams['ALLOW_UPLOAD'] != 'I' &&
	(
		$arParams['ALLOW_UPLOAD'] != 'F' || $arParams['ALLOW_UPLOAD_EXT'] == ''
	)
)
	$arParams['ALLOW_UPLOAD'] = 'A';

if ($_POST['mfi_mode'])
{
	$APPLICATION->RestartBuffer();
	while(ob_end_clean()); // hack!

	$cid = trim($_REQUEST['cid']);
	if (!$cid || !preg_match('/^[a-f01-9]{32}$/', $cid) || !check_bitrix_sessid())
		die();

	header('Content-Type: text/html; charset='.LANG_CHARSET);
	$appCode = ($_REQUEST["app_code"]) ? $_REQUEST["app_code"] : false;
	if ($_POST["mfi_mode"] == "upload")
	{
		$count = sizeof($_FILES["mfi_files"]["name"]);
		$mid = $arParams['MODULE_ID'];
		$max_file_size = $arParams['MAX_FILE_SIZE'];

		if (!$mid || !IsModuleInstalled($mid))
			$mid = 'main';
		for($i = 0; $i < $count; $i++)
		{
			$fileName = CUtil::ConvertToLangCharset($_FILES["mfi_files"]["name"][$i]);
			$arFile = array(
				"name" => $fileName,
				"size" => $_FILES["mfi_files"]["size"][$i],
				"tmp_name" => $_FILES["mfi_files"]["tmp_name"][$i],
				"type" => $_FILES["mfi_files"]["type"][$i],
				"MODULE_ID" => $mid
			);

			$res = '';

			if ($arParams["ALLOW_UPLOAD"] == "I"):
				$res = CFile::CheckImageFile($arFile, $max_file_size, 0, 0);
			elseif ($arParams["ALLOW_UPLOAD"] == "F"):
				$res = CFile::CheckFile($arFile, $max_file_size, false, $arParams["ALLOW_UPLOAD_EXT"]);
			else:
				$res = CFile::CheckFile($arFile, $max_file_size, false, false);
			endif;

			if ($res == '')
			{
				$fileID = CFile::SaveFile($arFile, $mid);

				$tmp = array(
					"fileName" => $fileName,
					"fileID" => $fileID
				);

				if ($fileID)
				{
					if (!isset($_SESSION["MFI_UPLOADED_FILES_".$cid]))
					{
						$_SESSION["MFI_UPLOADED_FILES_".$cid] = array($fileID);
					}
					else
					{
						$_SESSION["MFI_UPLOADED_FILES_".$cid][] = $fileID;
					}
					$file = CFile::GetFileArray($fileID);
					if ($file)
					{
						$tmp["fileContentType"] = $file["CONTENT_TYPE"];
						$tmp["fileURL"] = CHTTP::URN2URI($APPLICATION->GetCurPageParam("mfi_mode=down&fileID=".$fileID."&cid=".$cid."&".bitrix_sessid_get(), array("mfi_mode", "fileID", "cid")));
						$tmp["fileSize"] = CFile::FormatSize($file['FILE_SIZE']);
					}
				}

				foreach(GetModuleEvents("mobileapp", "onDesignerFileUploaded", true) as $arEvent)
				{
					$eventResult = ExecuteModuleEventEx($arEvent, array(&$tmp, $appCode));
				}
				$arResult[] = $tmp;
			}
		}

		$uid = intval($_POST["uniqueID"]);
?>
<script>
parent.FILE_UPLOADER_CALLBACK_<?=$uid?>(<?=CUtil::PhpToJsObject($arResult);?>, <?=$uid;?>, <?="\"".$res."\""?>);
</script>
<?
	}
	elseif ($_POST['mfi_mode'] == 'delete')
	{
		$fid = intval($_POST["fileID"]);
		CFile::Delete($_POST["fileID"]);
		foreach (GetModuleEvents("mobileapp", "onDesignerFileRemoved", true) as $arEvent)
		{
			$eventResult = ExecuteModuleEventEx($arEvent, array($fid, $appCode));
		}
	}

	die();
}

if ($_GET['mfi_mode'] === 'down')
{
	$fid = intval($_GET["fileID"]);

	$cid = trim($_REQUEST['cid']);
	if (!$cid || !preg_match('/^[a-f01-9]{32}$/', $cid) || !check_bitrix_sessid())
		die();

	if ($fid > 0 && isset($_SESSION["MFI_UPLOADED_FILES_".$cid]) && in_array($fid, $_SESSION["MFI_UPLOADED_FILES_".$cid]))
	{
		$arFile = CFile::GetFileArray($fid);
		if ($arFile)
		{
			$APPLICATION->RestartBuffer();
			while(ob_end_clean()); // hack!

			if ($arParams['ALLOW_UPLOAD'] == 'I')
				CFile::ViewByUser($arFile, array("content_type" => $arFile["CONTENT_TYPE"]));
			else
				CFile::ViewByUser($arFile, array("force_download" => true));

			die();
		}
	}
}

if ($arParams['SILENT'])
	return;

if (mb_substr($arParams['INPUT_NAME'], -2) == '[]')
	$arParams['INPUT_NAME'] = mb_substr($arParams['INPUT_NAME'], 0, -2);
if (mb_substr($arParams['INPUT_NAME_UNSAVED'], -2) == '[]')
	$arParams['INPUT_NAME_UNSAVED'] = mb_substr($arParams['INPUT_NAME_UNSAVED'], 0, -2);
if (!is_array($arParams['INPUT_VALUE']) && intval($arParams['INPUT_VALUE']) > 0)
	$arParams['INPUT_VALUE'] = array($arParams['INPUT_VALUE']);

$arParams['INPUT_NAME'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME']) ? $arParams['INPUT_NAME'] : false;
$arParams['INPUT_NAME_UNSAVED'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME_UNSAVED']) ? $arParams['INPUT_NAME_UNSAVED'] : '';
$arParams['CONTROL_ID'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : randString(5);

$arParams['INPUT_CAPTION'] = $arParams['INPUT_CAPTION'] ? $arParams['INPUT_CAPTION'] : GetMessage('MFI_INPUT_CAPTION_DEFAULT');

$arParams['MULTIPLE'] = $arParams['MULTIPLE'] == 'N' ? 'N' : 'Y';

if (!$arParams['INPUT_NAME'])
{
	showError(GetMessage('MFI_ERR_NO_INPUT_NAME'));
	return false;
}

$arResult['CONTROL_UID'] = md5(randString(15));

$_SESSION["MFI_UPLOADED_FILES_".$arResult['CONTROL_UID']] = array();
$arResult['FILES'] = array();

if (is_array($arParams['INPUT_VALUE']) && implode(",", $arParams["INPUT_VALUE"]) <> '')
{
	$dbRes = CFile::GetList(array(), array("@ID" => implode(",", $arParams["INPUT_VALUE"])));
	while ($arFile = $dbRes->GetNext())
	{
		$arFile['URL'] = CHTTP::URN2URI($APPLICATION->GetCurPageParam("mfi_mode=down&fileID=".$arFile['ID']."&cid=".$arResult['CONTROL_UID']."&".bitrix_sessid_get(), array("mfi_mode", "fileID", "cid")));
		$arFile['FILE_SIZE_FORMATTED'] = CFile::FormatSize($arFile['FILE_SIZE']);
		$arResult['FILES'][$arFile['ID']] = $arFile;
		$_SESSION["MFI_UPLOADED_FILES_".$arResult['CONTROL_UID']][] = $arFile['ID'];
	}
}

CUtil::InitJSCore(array('ajax'));

$this->IncludeComponentTemplate();

return $arParams['CONTROL_ID'];
