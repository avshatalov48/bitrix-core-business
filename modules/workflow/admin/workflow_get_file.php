<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# https://www.bitrixsoft.com          #
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
	$ext = mb_strtolower(GetFileExtension($fname));
	$arrExt = explode(",", mb_strtolower(CFile::GetImageExtensions()));
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