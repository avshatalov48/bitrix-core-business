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
$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
			GET | POST handlers
****************************************************************************/
// there is document ID
if($ID > 0 && check_bitrix_sessid())
{
	// check if document exists in database
	$z = $DB->Query("SELECT ID FROM b_workflow_document WHERE ID = ".intval($ID), false, $err_mess.__LINE__);
	if (!($zr=$z->Fetch()))
	{
		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$aMenu = array(
			array(
				"ICON" => "btn_list",
				"TEXT"	=> GetMessage("FLOW_RECORDS_LIST"),
				"LINK"	=> "workflow_list.php?lang=".LANGUAGE_ID,//"&ID=".$ID
				"TITLE"	=> GetMessage("FLOW_RECORDS_LIST"),
			)
		);
		$context = new CAdminContextMenu($aMenu);
		$context->Show();

		CAdminMessage::ShowMessage(GetMessage("FLOW_DOCUMENT_NOT_FOUND"));

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
	else
	{
		$filename = CWorkflow::GetUniquePreview($ID);
		// save preview file
		$z = CWorkflow::GetByID($ID);
		$zr = $z->Fetch();
		$prolog = $zr["PROLOG"];
		if (strlen($prolog)>0)
		{
			$title = $zr["TITLE"];
			$prolog = SetPrologTitle($prolog, $title);
		}
		$content = ($zr["BODY_TYPE"]=="text") ? TxtToHTML($zr["BODY"]) : $zr["BODY"];
		$epilog = $zr["EPILOG"];
		$filesrc = $prolog.PathToWF($content,$ID).$epilog;
		SavePreviewContent($_SERVER["DOCUMENT_ROOT"].$filename, $filesrc);
		// store file to database
		$arFields = array(
			"DOCUMENT_ID"	=> $ID,
			"TIMESTAMP_X"	=> $DB->GetNowFunction(),
			"FILENAME"		=> "'".$DB->ForSql($filename,255)."'"
			);
		$DB->Insert("b_workflow_preview",$arFields, $err_mess.__LINE__);
		// redirect to preview saved
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$filename))
			LocalRedirect($filename);
		else
			LocalRedirect("/bitrix/admin/workflow_list.php?lang=".LANG);
	}
}

?>
