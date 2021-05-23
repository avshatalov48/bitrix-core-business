<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

use \Bitrix\Main\UI\FileInputUtility;

$arParams['MAX_FILE_SIZE'] = intval($arParams['MAX_FILE_SIZE']);
$arParams['MODULE_ID'] = $arParams['MODULE_ID'] && IsModuleInstalled($arParams['MODULE_ID']) ? $arParams['MODULE_ID'] : false;
$arParams['CONTROL_ID'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : '';
// ALLOW_UPLOAD = 'A'll files | 'I'mages | 'F'iles with selected extensions | 'N'one
// ALLOW_UPLOAD_EXT = comma-separated list of allowed file extensions (ALLOW_UPLOAD='F')
if ($arParams['ALLOW_UPLOAD'] == 'N' || $arParams['ALLOW_UPLOAD'] === false)
{
	$arParams['ALLOW_UPLOAD'] = 'N';
}
elseif (
	$arParams['ALLOW_UPLOAD'] != 'I' &&
	(
		$arParams['ALLOW_UPLOAD'] != 'F' || strlen($arParams['ALLOW_UPLOAD_EXT']) <= 0
	)
)
{
	$arParams['ALLOW_UPLOAD'] = 'A';
}

if ($arParams['ALLOW_UPLOAD'] == 'I')
{
	$arParams["sign"] = new \Bitrix\Main\Security\Sign\Signer;
}

if ($_POST['mfi_mode'] &&
	(
		!array_key_exists("controlID", $_REQUEST) || // for custom templates
		$arParams['CONTROL_ID'] != '' && $arParams['CONTROL_ID'] == $_REQUEST["controlID"] || // for named controls
		$arParams['CONTROL_ID'] == '' && substr($_REQUEST["controlID"], 0, 3) == 'mfi') // for nonamed controls
	)
{
	$APPLICATION->RestartBuffer();
	while(ob_end_clean()); // hack!

	$cid = trim($_REQUEST['cid']);
	if (!$cid || !preg_match('/^[a-f01-9]{32}$/', $cid) || !check_bitrix_sessid())
		die();

	header('Content-Type: text/html; charset='.LANG_CHARSET);

	if ($_POST["mfi_mode"] == "upload" && $arParams['ALLOW_UPLOAD'] != 'N')
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

			if (strlen($res) <= 0)
			{
				$fileID = CFile::SaveFile($arFile, $mid);

				$tmp = array(
					"fileName" => $fileName,
					"fileID" => $fileID
				);

				if ($fileID)
				{
					FileInputUtility::instance()->registerFile($cid, $fileID);

					$file = CFile::GetFileArray($fileID);
					if ($file)
					{
						$tmp["fileContentType"] = $file["CONTENT_TYPE"];
						$query = array(
							"mfi_mode" => "down",
							"fileID" => $fileID,
							"cid" => $cid,
							"sessid" => bitrix_sessid());
						if (array_key_exists("sign", $arParams))
							$query["s"] = $arParams["sign"]->sign($cid, "main.file.input");;
						$tmp['fileURL'] = "/bitrix/components/bitrix/main.file.input/file.php?" . http_build_query($query);
						$tmp["fileSize"] = CFile::FormatSize($file['FILE_SIZE']);
					}
				}

				foreach(GetModuleEvents("main", "main.file.input.upload", true) as $arEvent)
				{
					$eventResult = ExecuteModuleEventEx($arEvent, array(&$tmp));
				}
				$arResult[] = $tmp;
			}
			else
			{
				$arResult[] = array(
					"fileName" => $fileName,
					"error" => $res
				);
			}
		}

		$uid = intval($_POST["uniqueID"]);
?>
<script type="text/javascript">
parent.FILE_UPLOADER_CALLBACK_<?=$uid?>(<?=CUtil::PhpToJsObject($arResult);?>, <?=$uid;?>);
</script>
<?
	}
	elseif ($_POST['mfi_mode'] == 'delete')
	{
		$fid = intval($_POST["fileID"]);

		if (FileInputUtility::instance()->unRegisterFile($cid, $fid))
		{
			CFile::Delete($fid);
		}
	}

	die();
}

if ($_GET['mfi_mode'] === "down")
{
	if (array_key_exists("sign", $arParams))
		$_REQUEST["s"] = $arParams["sign"]->sign($_REQUEST["cid"], "main.file.input");;

	require(__DIR__."/file.php");
}

if ($arParams['SILENT'])
	return;

if (substr($arParams['INPUT_NAME'], -2) == '[]')
	$arParams['INPUT_NAME'] = substr($arParams['INPUT_NAME'], 0, -2);
if (substr($arParams['INPUT_NAME_UNSAVED'], -2) == '[]')
	$arParams['INPUT_NAME_UNSAVED'] = substr($arParams['INPUT_NAME_UNSAVED'], 0, -2);
if (!is_array($arParams['INPUT_VALUE']) && intval($arParams['INPUT_VALUE']) > 0)
	$arParams['INPUT_VALUE'] = array($arParams['INPUT_VALUE']);

$arParams['INPUT_NAME'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME']) ? $arParams['INPUT_NAME'] : false;
$arParams['INPUT_NAME_UNSAVED'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME_UNSAVED']) ? $arParams['INPUT_NAME_UNSAVED'] : '';
$arResult['CONTROL_ID'] = $arParams['CONTROL_ID'] = ($arParams['CONTROL_ID'] != '' ? $arParams['CONTROL_ID'] : 'mfi'.randString(5));

$arParams['INPUT_CAPTION'] = $arParams['INPUT_CAPTION'] ? $arParams['INPUT_CAPTION'] : GetMessage('MFI_INPUT_CAPTION_DEFAULT');

$arParams['MULTIPLE'] = $arParams['MULTIPLE'] == 'N' ? 'N' : 'Y';

if (!$arParams['INPUT_NAME'])
{
	showError(GetMessage('MFI_ERR_NO_INPUT_NAME'));
	return false;
}

if (!$arParams['INPUT_NAME_UNSAVED'])
{
	$arParams['INPUT_NAME_UNSAVED'] = $arParams['INPUT_NAME'].'_'.RandString(8);
}

$arResult['CONTROL_UID'] = FileInputUtility::instance()->registerControl($arParams['CONTROL_ID']);

$arResult['FILES'] = array();

if (is_array($arParams['INPUT_VALUE']) && strlen(implode(",", $arParams["INPUT_VALUE"])) > 0)
{
	$dbRes = CFile::GetList(array(), array("@ID" => implode(",", $arParams["INPUT_VALUE"])));
	while ($arFile = $dbRes->GetNext())
	{
		$query = array(
			"mfi_mode" => "down",
			"fileID" => $arFile['ID'],
			"cid" => $arResult['CONTROL_UID'],
			"sessid" => bitrix_sessid());
		if (array_key_exists("sign", $arParams))
			$query["s"] = $arParams["sign"]->sign($arResult["CONTROL_UID"], "main.file.input");;

		$arFile['URL'] = "/bitrix/components/bitrix/main.file.input/file.php?" . http_build_query($query);
		$arFile['FILE_SIZE_FORMATTED'] = CFile::FormatSize($arFile['FILE_SIZE']);
		$arResult['FILES'][$arFile['ID']] = $arFile;

		FileInputUtility::instance()->registerFile($arResult['CONTROL_UID'], $arFile['ID']);
	}
}

CJSCore::Init(array('ajax'));

$this->IncludeComponentTemplate();

return $arParams['CONTROL_ID'];
