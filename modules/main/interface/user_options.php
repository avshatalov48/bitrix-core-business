<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_SECURITY_SESSION_READONLY", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if ($USER->IsAuthorized() && check_bitrix_sessid())
{
	if (
		isset($_GET["action"])
		&& $_GET["action"] === "delete"
		&& isset($_GET["c"])
		&& $_GET["c"] <> ""
		&& isset($_GET["n"])
		&& $_GET["n"] <> ""
	)
	{
		CUserOptions::DeleteOption(
			$_GET["c"],
			$_GET["n"],
			(isset($_GET["common"]) && $_GET["common"] == "Y" && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
		);
	}

	if (isset($_REQUEST["p"]) && is_array($_REQUEST["p"]))
	{
		$arOptions = $_REQUEST["p"];
		CUtil::decodeURIComponent($arOptions);
		CUserOptions::SetOptionsFromArray($arOptions);
	}
}
echo "OK";
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
?>
