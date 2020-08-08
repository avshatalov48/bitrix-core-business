<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include_once(dirname(__FILE__)."/init_vars.php");
include(GetLangFileName(dirname(__FILE__)."/lang/", "/mp3.php"));

if (CModule::IncludeModule("sale"))
{
	$bCanAccess = False;
	if ($USER->IsAuthorized())
	{
		$FILE_PERM = $APPLICATION->GetFileAccessPermission($DIR."/files/".$fname, $USER->GetUserGroupArray());
		$FILE_PERM = (($FILE_PERM <> '') ? $FILE_PERM : "D");
		if ($FILE_PERM >= "R")
			if (CSaleAuxiliary::CheckAccess($USER->GetID(), $mp3AuxiliaryPrefix.$fname, $mp3AccessTimeLength, $mp3AccessTimeType))
				$bCanAccess = True;
	}

	if ($bCanAccess)
	{
		LocalRedirect($DIR."/".urlencode($fname));
	}
	else
	{
		$m = GetMessage("MP3_ACCESS_DENIED");
		$arAuthResult["MESSAGE"] = $m;

		$APPLICATION->AuthForm($m);
	}
}
else
{
	$APPLICATION->SetTitle(GetMessage("MP3_ERROR"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_after.php");
	?><font class="text"><?= GetMessage("MP3_NO_SALE_MODULE") ?></font><?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
}
?>