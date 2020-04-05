<?
define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if (!$USER->IsAdmin() || ($RIGHT < "W"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_REQUEST["clear"] != "") && check_bitrix_sessid())
{
	CPerfomanceComponent::Clear();
	CPerfomanceSQL::Clear();
	CPerfomanceHit::Clear();
	CPerfomanceError::Clear();
	CPerfomanceCache::Clear();
	$_SESSION["PERFMON_CLEAR_MESSAGE"] = GetMessage("PERFMON_CLEAR_MESSAGE");
	LocalRedirect("/bitrix/admin/perfmon_clear.php?lang=".LANGUAGE_ID);
}

$APPLICATION->SetTitle(GetMessage("PERFMON_CLEAR_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($_SESSION["PERFMON_CLEAR_MESSAGE"])
{
	$message  = new CAdminMessage(array(
		"MESSAGE" => $_SESSION["PERFMON_CLEAR_MESSAGE"],
		"TYPE" => "OK",
	));
	echo $message->Show();
	unset($_SESSION["PERFMON_CLEAR_MESSAGE"]);
}
?>

<form name="clear_form" method="post" action="<?echo $APPLICATION->GetCurPage();?>">
	<?echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="clear" value="<?echo GetMessage("PERFMON_CLEAR_BUTTON");?>">
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
