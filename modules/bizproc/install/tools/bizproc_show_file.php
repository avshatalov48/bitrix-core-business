<?
define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * @global CUser $USER
 */

if(!CModule::IncludeModule("bizproc"))
	die();

if (!$USER->IsAuthorized())
	die();

$fileName = preg_replace("/[^A-Za-z0-9_.-]+/i", "", trim($_REQUEST["f"]));
$fileNameHash = isset($_REQUEST['hash']) ? preg_replace("/[^A-Za-z0-9_.-]+/i", "", trim($_REQUEST["hash"])) : '';
$fileId = intval($_REQUEST["i"]);
$fileAction = (isset($_REQUEST["act"]) && $_REQUEST["act"] == "v" ? "view" : "download");
$filePathHash = trim($_REQUEST["h"]);

if ($fileId <= 0 || $fileName == '' && $fileNameHash == '')
	die("Error1");

$bpId = !empty($_REQUEST['bp_id']) ? (int)$_REQUEST['bp_id'] : 0;
$iblockId = !empty($_REQUEST['iblock_id']) ? (int)$_REQUEST['iblock_id'] : 0;

$options = array('cache_time' => 0);
if ($fileAction == "download")
{
	$options["force_download"] = true;
}

if ($bpId > 0 && $iblockId > 0 && $fileName !== '')
{
	$fields = CBPVirtualDocument::GetDocumentFields("type_".$iblockId);
	if (isset($fields[$fileName]) && $fields[$fileName]["BaseType"] == "file")
	{
		list($dbRecordsList, $dbRecordsList1) = CBPVirtualDocument::GetList(
			array(),
			array("ID" => $bpId, "IBLOCK_ID" => $iblockId, "CHECK_BP_VIRTUAL_PERMISSIONS" => "read"),
			false,
			false,
			array($fileName)
		);
		$row = $dbRecordsList->fetch();
		if (!$row)
			die("Error: BP not found");
		$files = (array)$row[$fileName];
		$key = array_search($fileId, $files);
		if ($key !== false)
		{
			set_time_limit(0);
			CFile::ViewByUser($files[$key], $options);
		}
		else
			die("Error: File not found");
	}
	die();
}
$arImg = CFile::GetFileArray($fileId);
if (!$arImg)
	die("Error2: File not found");

if ($fileNameHash)
{
	$rawName = $fileNameHash;
	$checkName = md5($arImg["FILE_NAME"]);
}
else
{
	$rawName = str_replace(' ', '+', trim($_REQUEST["f"]));
	$checkName = str_replace(' ', '+', trim($arImg["FILE_NAME"]));
}

if (strcmp($checkName, $rawName) !== 0)
{
	die("Error3");
}

if ($arImg["SUBDIR"] == '')
	die("Error4");

if (mb_substr($arImg["SUBDIR"], 0, mb_strlen("bizproc_wf/")) != "bizproc_wf/"
	&& ($filePathHash == '' || $filePathHash != md5($arImg["SUBDIR"])))
	die("Error5");

set_time_limit(0);

CFile::ViewByUser($arImg, $options);
\Bitrix\Main\Application::getInstance()->end();
