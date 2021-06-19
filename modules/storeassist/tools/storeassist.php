<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);

if(!\Bitrix\Main\Loader::includeModule('storeassist'))
	return;

if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["action"] <> '' && check_bitrix_sessid())
{
	$action = $_POST["action"];
	$arJsonData = array();

	switch ($action)
	{
		case "setOption":
			if (isset($_POST["pageId"]) && isset($_POST["status"]))
			{
				CStoreAssist::setSettingOption($_POST["pageId"], $_POST["status"]);
				$arJsonData["success"] = "Y";
			}
			else
			{
				$arJsonData["error"] = "Y";
			}
			break;
	}

	$APPLICATION->RestartBuffer();
	echo \Bitrix\Main\Web\Json::encode($arJsonData);
	die();
}
?>