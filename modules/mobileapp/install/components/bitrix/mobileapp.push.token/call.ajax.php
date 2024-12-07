<?
use Bitrix\Main\Web\Json;

define('BX_SECURITY_SHOW_MESSAGE', 1);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_FILE_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

class MobileAppPushTokenUpdater
{
	const TOKEN_SET_FAILED = 0;
	const TOKEN_UPDATED = 1;
	const TOKEN_CREATED = 2;
	const TOKEN_REMOVED = 3;

	public function register(array $data)
	{
		$dbres = CPullPush::GetList(Array(), Array("DEVICE_ID" => $data["DEVICE_ID"]));
		$arToken = $dbres->Fetch();


		if ($arToken["ID"])
		{
			CPullPush::Update($arToken["ID"], $data);
			return self::TOKEN_UPDATED;
		}
		else
		{
			if ($res = CPullPush::Add($data))
			{
				return self::TOKEN_CREATED;
			}
		}


		return self::TOKEN_SET_FAILED;
	}

	public function remove(array $data)
	{
		$userId = $data["user_id"];
		$dbres = CPullPush::GetList(Array(), $data);
		$token = $dbres->Fetch();

		if ($token["ID"] && $userId == $token["USER_ID"])
		{
			CPullPush::Delete($token["ID"]);
			if (CPullPush::Delete($token["ID"]))
			{
				return self::TOKEN_REMOVED;
			}
		}

		return self::TOKEN_SET_FAILED;
	}
}

$result = Array(
	"status" => "failed",
);
$action = $_REQUEST["action"];

$tokenUpdater = new MobileAppPushTokenUpdater();

if (!\Bitrix\Main\Loader::includeModule("pull"))
{
	$result["error"] = "Module 'pull' is not installed";
}
else
{
	/**
	 * @var $DB CDatabase
	 * @var $USER CUser
	 */

	$userId = $USER->GetID();
	if(!$userId)
	{
		$userId = 0;
	}

	if ($_REQUEST["device_token"])
	{
		$res = array("status" => "failed", "error" => "some unknown error");
		switch ($action)
		{
			case "register":
				$data = array(
					"DEVICE_TOKEN" => $_REQUEST["device_token"],
					"DEVICE_ID" => $_REQUEST["uuid"],
					"DEVICE_TYPE" => $_REQUEST["device_type"],
					"APP_ID" => $_REQUEST["app_id"],
					"DATE_AUTH" => ConvertTimeStamp(microtime(true), "FULL"),
					"USER_ID" => $userId
				);

				$status = $tokenUpdater->register($data);
				$result = array(
					"status" => ($status > 0) ? "success" : "failed",
					"token_status" => $status
				);

				break;
			case "remove":

				$data = array(
					"DEVICE_TOKEN" => $_REQUEST["device_token"],
					"DEVICE_ID" => $_REQUEST["device_uuid"],
					"APP_ID" => $_REQUEST["app_id"],
					"USER_ID" => $userId
				);
				$status = $tokenUpdater->remove($data);
				$result = array(
					"status" => ($status > 0) ? "success" : "failed",
					"token_status" => $status
				);

				break;
		}

	}
	else
	{
		$result["error"] = "user is not authorized";
	}
}

$result["data"] = $data != null ? $data : array();

header("Content-Type: application/x-javascript");
echo Json::encode($result);
die();
