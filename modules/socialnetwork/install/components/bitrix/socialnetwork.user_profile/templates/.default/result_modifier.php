<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php");
if (!$arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
{
	$arResult["FatalError"] = GetMessage("SONET_C39_USER_ACCESS_DENIED");
}
?>