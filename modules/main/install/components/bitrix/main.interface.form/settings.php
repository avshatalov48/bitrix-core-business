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

if($USER->IsAuthorized() && check_bitrix_sessid())
{
	//get saved columns and sorting from user settings
	$aOptions = CUserOptions::GetOption("main.interface.form", $_REQUEST["FORM_ID"], array());

	if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "expand")
	{
		$aOptions["expand_tabs"] = ($_REQUEST["expand"] == "Y"? "Y":"N");
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "enable")
	{
		$aOptions["settings_disabled"] = ($_REQUEST["enabled"] == "Y"? "N":"Y");
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "settheme")
	{
		$aOptions["theme"] = $_REQUEST["theme"];
		if (!empty($_REQUEST["GRID_ID"]))
		{
			$aGridOptions = CUserOptions::GetOption("main.interface.grid", $_REQUEST["GRID_ID"], array());
			$aGridOptions["theme"] = $_REQUEST["theme"];
			CUserOptions::SetOption("main.interface.grid", $_REQUEST["GRID_ID"], $aGridOptions);
		}
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "savesettings")
	{
		CUtil::decodeURIComponent($_POST);
		$aOptions["tabs"] = $_POST["tabs"];

		if($_REQUEST["set_default_settings"] == "Y" && $USER->CanDoOperation('edit_other_settings'))
		{
			if (isset($_REQUEST["delete_users_settings"]) && $_REQUEST["delete_users_settings"] == "Y")
			{
				CUserOptions::DeleteOptionsByName("main.interface.form", $_REQUEST["FORM_ID"]);
			}
			CUserOptions::SetOption("main.interface.form", $_REQUEST["FORM_ID"], $aOptions, true);
		}
	}

	CUserOptions::SetOption("main.interface.form", $_REQUEST["FORM_ID"], $aOptions);
}
echo "OK";
