<?
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"])>0 && check_bitrix_sessid())
{
	if ($_POST["action"] == "delete" && CModule::IncludeModule("rest") && CRestUtil::isAdmin())
	{
		$APPLICATION->RestartBuffer();

		Header('Content-Type: application/json');

		$res = false;
		$appId = "";

		if (isset($_POST["appId"]) && intval($_POST["appId"]))
			$appId = intval($_POST["appId"]);

		$app = \Bitrix\Rest\AppTable::getByClientId($appId);
		if($app["ID"])
		{
			$result = \Bitrix\Rest\AppTable::delete($app['ID']);
/*
			//delete app from cloud
			if (preg_match("/bitrix24-cdn\.com/i", $app["URL"]))
			{
				$appUrl = preg_replace("/index\.html/i", "", $app["URL"]);

				$appObj = new CB24MarketplaceLocalApp();
				$appObj->deleteAppFromCloud($appUrl);
			}
			$res = \CB24MarketplaceLocalApp::UninstallApp($appId);
*/
			if(!$result->isSuccess())
			{
				echo \Bitrix\Main\Web\Json::encode(array("error" => "Y"));
			}
			else
			{
				echo \Bitrix\Main\Web\Json::encode(array("success" => "Y"));
			}
		}
		else
		{
			echo \Bitrix\Main\Web\Json::encode(array("error" => "Y"));
		}

	}
}

CMain::FinalActions();
?>
