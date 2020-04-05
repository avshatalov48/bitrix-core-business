<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");
$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if($WORKFLOW_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");

IncludeModuleLangFile(__FILE__);

$fname = $_REQUEST["fname"];
$path = "";
$filename = "";
if($USER->IsAdmin() || !in_array(GetFileExtension($fname), GetScriptFileExt()))
{
	$z = CWorkflow::GetFileByID($did, $fname);
	if($zr = $z->Fetch())
	{
		$path = CWorkflow::GetTempDir().$zr["TEMP_FILENAME"];
		if(file_exists($path))
		{
			$io = CBXVirtualIo::GetInstance();
			$filename = $io->RandomizeInvalidFilename(basename($zr["FILENAME"]));
		}
	}
}
else
	$strError = GetMessage("FLOW_ACCESS_DENIED_PHP_DOWNLOAD");

if(strlen($strError)<=0)
{
	$fsize=filesize($path);
	header("Content-Type: application/force-download; name=\"".$filename."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$fsize);
	header("Content-Disposition: attachment; filename=\"".$filename."\"");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	readfile($path);
	die();
}
$APPLICATION->SetTitle(GetMessage("FLOW_DOWNLOAD_FILE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
CAdminMessage::ShowMessage($strError);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>