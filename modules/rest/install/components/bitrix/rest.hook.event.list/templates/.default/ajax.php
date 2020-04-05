<?
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["action"])>0 && check_bitrix_sessid())
{
	if ($_POST["action"] == "delete" && CModule::IncludeModule("rest"))
	{
		$APPLICATION->RestartBuffer();

		Header('Content-Type: application/json');

		$res = false;
		$apId = 0;

		if (isset($_POST["apId"]) && intval($_POST["apId"]))
		{
			$apId = intval($_POST["apId"]);
		}

		if($apId > 0)
		{
			$dbRes = \Bitrix\Rest\EventTable::getByPrimary($apId);
			$event = $dbRes->fetch();

			if($event && $event['USER_ID'] == $USER->GetID() && intval($event['APP_ID']) <= 0)
			{
				$result = \Bitrix\Rest\EventTable::delete($event['ID']);

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
}

CMain::FinalActions();
?>
