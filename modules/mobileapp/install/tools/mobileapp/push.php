<?php
use Bitrix\Main\Web\Json;

define('BX_SECURITY_SHOW_MESSAGE', 1);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_FILE_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$result = Array(
	"status" => "failed",
);

if (\Bitrix\Main\Loader::includeModule("pull"))
{
	$result["error"] = "Module 'pull' is not installed";
}
else
{
	/**
	 * @var $DB CDatabase
	 * @var $USER CUser
	 */
	if ($USER->IsAuthorized() && $_REQUEST["device_token"])
	{
		$token = $_REQUEST["device_token"];
		$uuid = $_REQUEST["uuid"];
		$app_id = $_REQUEST["app_id"];
		$data = array(
			"status" => "failed",
			"token" => $token,
			"error" => "some unknown error",
			"user_id" => $USER->GetID()
		);


		$dbres = CPullPush::GetList(Array(), Array("DEVICE_ID" => $uuid));
		$arToken = $dbres->Fetch();

		$arFields = Array(
			"USER_ID" => $USER->GetID(),
			"DEVICE_NAME" => $_REQUEST["device_name"],
			"DEVICE_TYPE" => $_REQUEST["device_type"],
			"DEVICE_ID" => $uuid,
			"DEVICE_TOKEN" => $token,
			"DATE_AUTH" => ConvertTimeStamp(microtime(true), "FULL"),
			"APP_ID" => $app_id
		);

		if ($arToken["ID"])
		{
			$res = CPullPush::Update($arToken["ID"], $arFields);
			$data["register_token"] = "updated";
		}
		else
		{
			$res = CPullPush::Add($arFields);
			if ($res)
			{
				$data["register_token"] = "created";
			}
		}
	}
}

header("Content-Type: application/x-javascript");
echo Json::encode($result);
die();
