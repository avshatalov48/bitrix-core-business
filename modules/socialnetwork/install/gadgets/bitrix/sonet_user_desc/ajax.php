<?define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["action"]) && check_bitrix_sessid())
{
	$action = trim($_POST["action"]);
	$arJsonData = array();

	$userId = trim($_POST["userId"]);

	switch ($action)
	{
		case "deactivate":
			if (CModule::IncludeModule("security"))
			{
				$numDays = intval($_POST["numDays"]);
				$res = CSecurityUser::DeactivateUserOtp($userId, $numDays);
				if ($res)
					$arJsonData["success"] = "Y";
				else
					$arJsonData["error"] = "Y";
			}
			else
			{
				$arJsonData["error"] = "Y";
			}
			break;

		case "activate":
			if (CModule::IncludeModule("security"))
			{
				$res = CSecurityUser::ActivateUserOtp($userId);
				if ($res)
					$arJsonData["success"] = "Y";
				else
					$arJsonData["error"] = "Y";
			}
			else
			{
				$arJsonData["error"] = "Y";
			}
			break;

		case "defer":
			if (CModule::IncludeModule("security"))
			{
				$numDays = intval($_POST["numDays"]);
				$res = CSecurityUser::DeferUserOtp($userId, $numDays);
				if ($res)
					$arJsonData["success"] = "Y";
				else
					$arJsonData["error"] = "Y";
			}
			else
			{
				$arJsonData["error"] = "Y";
			}
			break;
	}

	echo \Bitrix\Main\Web\Json::encode($arJsonData);
}
?>
