<?
/** Bitrix Framework
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$arResult = array(
	"ERROR" => ""
);

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('scale'))
	$arResult["ERROR"] = Loc::getMessage("SCALE_AJAX_MODULE_NOT_INSTALLED");

$result = false;

if($arResult["ERROR"] == '' && $USER->IsAdmin() && check_bitrix_sessid())
{
	$operation = isset($_REQUEST['params']['operation']) ? trim($_REQUEST['params']['operation']): '';

	switch ($operation)
	{
		case "start":
			$actionId = isset($_REQUEST['params']['actionId']) ? trim($_REQUEST['params']['actionId']): '';
			$serverHostname = isset($_REQUEST['params']['serverHostname']) ? trim($_REQUEST['params']['serverHostname']): "";
			$userParams = isset($_REQUEST['params']['userParams']) ? $_REQUEST['params']['userParams']: array();
			$freeParams = isset($_REQUEST['params']['freeParams']) ? $_REQUEST['params']['freeParams']: array();
			$actonParams = isset($_REQUEST['params']['actionParams']) ? $_REQUEST['params']['actionParams']: array();

			try
			{
				$action = \Bitrix\Scale\ActionsData::getActionObject($actionId, $serverHostname, $userParams, $freeParams, $actonParams);
			}
			catch(Exception $e)
			{
				$arResult["ERROR"] = $e->getMessage();
				break;
			}

			try
			{
				$result = $action->start();
				$arResult["ACTION_RESULT"] = $action->getResult();
				\CUserCounter::Increment($USER->GetID(),'SCALE_ACTIONS_EXECUTED', SITE_ID, false);
			}
			catch(\Bitrix\Scale\NeedMoreUserInfoException $e)
			{
				$arResult["NEED_MORE_USER_INFO"] = array(
					"ACTION_ID" => $actionId,
					"HOSTNAME" =>  $serverHostname,
					"USER_PARAMS" => $userParams,
					"FREE_PARAMS" => $freeParams,
					"ACTION_PARAMS" => $e->getActionParams()
				);

				$result = true;

			}
			catch(Exception $e)
			{
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		case "check_state":

			$bid = isset($_REQUEST['params']['bid']) ? trim($_REQUEST['params']['bid']): '';
			$arResult["ACTION_STATE"] = \Bitrix\Scale\ActionsData::getActionState($bid);

			if(!empty($arResult["ACTION_STATE"]))
				$result = true;

			break;

		case "get_monitoring_values":

			$servers = isset($_REQUEST['params']['servers']) ? $_REQUEST['params']['servers'] : array();
			$result = true;
			$arResult["MONITORING_DATA"] = array();

			foreach($servers as $hostname => $monitoringPartitions)
			{
				$arResult["MONITORING_DATA"][$hostname] = array();

				if(isset($monitoringPartitions["rolesIds"]) && is_array($monitoringPartitions["rolesIds"]))
				{
					foreach($monitoringPartitions["rolesIds"] as $roleId)
					{
						try
						{
							$arResult["MONITORING_DATA"][$hostname]["ROLES_LOADBARS"][$roleId] = \Bitrix\Scale\Monitoring::getLoadBarValue($hostname, $roleId);
						}
						catch(Exception $e)
						{
							$arResult["ERROR"] .= "\n".$e->getMessage();
							continue;
						}
					}
				}

				foreach($monitoringPartitions["monitoringParams"] as $categoryId => $category)
				{
					foreach($category as $paramId)
					{
						try
						{
							$arResult["MONITORING_DATA"][$hostname]["MONITORING_VALUES"][$categoryId][$paramId] = \Bitrix\Scale\Monitoring::getValue($hostname, $categoryId, $paramId);
						}
						catch(Exception $e)
						{
							$arResult["ERROR"] .= "\n".$e->getMessage();
							continue;
						}
					}
				}
			}

			break;

		case "get_providers_list":
			$arResult["PROVIDERS_LIST"] = \Bitrix\Scale\Provider::getList(array("filter" => array("status" => "enabled")));
			$result = true;
			break;

		case "get_provider_configs":
			$providerId = isset($_REQUEST['params']['providerId']) ? trim($_REQUEST['params']['providerId']) : "";
			if($providerId !== "")
			{
				$arResult["PROVIDER_CONFIGS"] = \Bitrix\Scale\Provider::getConfigs($providerId);
				$result = true;
			}
			break;

		case "send_order_to_provider":
			$providerId = isset($_REQUEST['params']['providerId']) ? trim($_REQUEST['params']['providerId']) : "";
			$configId = isset($_REQUEST['params']['configId']) ? trim($_REQUEST['params']['configId']) : "";

			if($providerId !== "" && $configId !== "")
			{
				$arResult["TASK_ID"] = \Bitrix\Scale\Provider::sendOrder($providerId, $configId);
				$result = true;
			}

			break;

		case "upload_files":
			if(!empty($_FILES))
			{
				$tmpDir = \Bitrix\Scale\Helper::getTmpDir();
				$uploadedFiles = array();

				foreach($_FILES as $file)
				{
					if(!is_uploaded_file($file['tmp_name']))
						continue;

					if($file['size'] <= 0)
						continue;

					$uploadFile = $tmpDir.'/'.basename($file['name']);

					if(move_uploaded_file($file['tmp_name'], $uploadFile))
						$uploadedFiles[] = $uploadFile;
				}

				$arResult['FILES'] = $uploadedFiles;
				$result = !empty($arResult['FILES']);
			}

			break;
	}
}
else
{
	if($arResult["ERROR"] == '')
		$arResult["ERROR"] = Loc::getMessage("SCALE_AJAX_ACCESS_DENIED");
}

if(!$result)
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(mb_strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));