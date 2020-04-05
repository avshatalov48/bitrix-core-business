<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 - 2011 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("form"))
	die();

if (strlen($_REQUEST["hash"]) > 0)
{
	$arFile = CFormResult::GetFileByHash($_REQUEST["rid"], $_REQUEST["hash"]);
	if ($arFile)
	{
		set_time_limit(0);

		$options = array();
		if ($_REQUEST["action"] == "download")
		{
			$options["force_download"] = true;
		}

		CFile::ViewByUser($arFile, $options);
	}
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
ShowError(GetMessage("FORM_ERROR_FILE_NOT_FOUND"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
