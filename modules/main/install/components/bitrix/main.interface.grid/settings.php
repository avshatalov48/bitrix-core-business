<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 *
 * @global CUser $USER
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if($USER->IsAuthorized() && check_bitrix_sessid() && isset($_REQUEST["GRID_ID"]) && $_REQUEST["GRID_ID"] <> '')
{
	//get saved columns and sorting from user settings
	$gridOptions = new CGridOptions($_REQUEST["GRID_ID"]);

	$_REQUEST["action"] = $_REQUEST["action"] ?? null;
	if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "showcolumns")
	{
		$gridOptions->SetColumns($_REQUEST["columns"]);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "settheme")
	{
		$gridOptions->SetTheme($_REQUEST["theme"]);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "savesettings")
	{
		$gridOptions->SetViewSettings($_POST['view_id'], $_POST);

		if(
			isset($_REQUEST["set_default_settings"])
			&& $_REQUEST["set_default_settings"] == "Y"
			&& $USER->CanDoOperation('edit_other_settings')
		)
		{
			$gridOptions->SetDefaultView($_POST);
			if (isset($_REQUEST["delete_users_settings"]) && $_REQUEST["delete_users_settings"] == "Y")
			{
				$gridOptions->ResetDefaultView();
			}
		}
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "delview")
	{
		$gridOptions->DeleteView($_REQUEST['view_id']);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "setview")
	{
		$gridOptions->SetView($_REQUEST["view_id"]);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "filterrows")
	{
		$gridOptions->SetFilterRows($_REQUEST["rows"], $_REQUEST['filter_id']);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "savefilter")
	{
		$gridOptions->SetFilterSettings($_POST['filter_id'], $_POST);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "delfilter")
	{
		$gridOptions->DeleteFilter($_REQUEST['filter_id']);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "filterswitch")
	{
		$gridOptions->SetFilterSwitch($_REQUEST["show"]);
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "savesort")
	{
		$gridOptions->SetSorting($_REQUEST["by"], $_REQUEST["order"]);
	}

	$gridOptions->Save();
}
echo "OK";
