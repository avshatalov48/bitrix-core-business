<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
define("STOP_STATISTICS", "Y");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");

$fname = $_REQUEST["fname"];
if ($APPLICATION->GetGroupRight("workflow")>="R")
{
	session_write_close();
	$src = CWorkflow::GetFileContent($did, $fname, $wf_path, $site);
	$ext = strtolower(GetFileExtension($fname));
	$arrExt = explode(",", strtolower(CFile::GetImageExtensions()));
	if(in_array($ext, $arrExt))
	{
		if ($ext=="jpg") $ext = "jpeg";
		header("Content-type: image/".$ext);
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Pragma: public");
		echo $src;
		die();
	}
	echo TxtToHtml($src);
}
die();
?>