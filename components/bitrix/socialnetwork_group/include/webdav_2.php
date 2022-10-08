<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("webdav")):
	//ShowError(GetMessage("SONET_WD_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	//ShowError(GetMessage("SONET_IB_MODULE_IS_NOT_INSTALLED"));
	return 0;
endif;

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/../lang/".LANGUAGE_ID."/include/webdav_2.php")));
__IncludeLang($file);

$obDavEventHandler = CWebDavSocNetEvent::GetRuntime();
$obDavEventHandler->SetSocnetVars($arResult, $arParams);

AddEventHandler("socialnetwork", "OnBeforeSocNetGroupUpdate", array($obDavEventHandler, "SocNetGroupRename"));
?>
