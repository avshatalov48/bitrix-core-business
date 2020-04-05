<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
if(!CModule::IncludeModule("vote"))
	return;

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/install/index.php");
$vote = new vote();
$vote->InstallUserFields();
?>
