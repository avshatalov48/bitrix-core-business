<?
namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class LogEntry extends \Bitrix\Socialnetwork\Controller\Base
{
	public function getHiddenDestinationsAction(array $params = [])
	{
		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$createdById = (isset($params['createdById']) ? intval($params['createdById']) : 0);
		$destinationLimit = (isset($params['destinationLimit']) ? intval($params['destinationLimit']) : 0);

		$pathToUser = (isset($params['pathToUser']) ? $params['pathToUser'] : '');
		$pathToWorkgroup = (isset($params['pathToWorkgroup']) ? $params['pathToWorkgroup'] : '');
		$pathToDepartment = (isset($params['pathToDepartment']) ? $params['pathToDepartment'] : '');
		$nameTemplate = (isset($params['nameTemplate']) ? $params['nameTemplate'] : \CSite::getNameFormat());
		$showLogin = (isset($params['showLogin']) ? $params['showLogin'] : (ModuleManager::isModuleInstalled('intranet') ? 'Y' : 'N'));

		if ($logId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_LOGENTRY_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_EMPTY'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_LOGENTRY_MODULE_NOT_INSTALLED'), 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_MODULE_NOT_INSTALLED'));
			return null;
		}

		\CSocNetTools::initGlobalExtranetArrays();
		$currentUserAdmin = \CSocNetUser::isCurrentUserModuleAdmin();

		$extranetInstalled = Loader::includeModule("extranet");
		$currentExtranetUser = ($extranetInstalled && !\CExtranet::isIntranetUser());
		$extranetAdmin = ($extranetInstalled && \CExtranet::isExtranetAdmin());
		$visibleUserIdList = $availableExtranetUserIdList = false;

		if ($currentExtranetUser)
		{
			$visibleUserIdList = \CExtranet::getMyGroupsUsersSimple(SITE_ID);
		}
		elseif (
			$extranetInstalled
			&& !$extranetAdmin
		)
		{
			$availableExtranetUserIdList = \CExtranet::getMyGroupsUsersSimple(\CExtranet::getExtranetSiteID());
		}

		$destinationList = [];

		$rightsList = [];
		$skipGetRights = false;

		$res = GetModuleEvents('socialnetwork', 'OnBeforeSocNetLogEntryGetRights');
		while ($event = $res->fetch())
		{
			if (ExecuteModuleEventEx(
					$event,
					[
						[ 'LOG_ID' => $logId ],
						&$rightsList
					]
				) === false
			)
			{
				$skipGetRights = true;
				break;
			}
		}

		if (!$skipGetRights)
		{
			$res = \CSocNetLogRights::getList([], [ 'LOG_ID' => $logId ]);
			while ($rightFields = $res->fetch())
			{
				$rightsList[] = $rightFields['GROUP_CODE'];
			}
		}

		$destinationParams = [
			'PATH_TO_USER' => $pathToUser,
			'PATH_TO_GROUP' => $pathToWorkgroup,
			'PATH_TO_CONPANY_DEPARTMENT' => $pathToDepartment,
			'NAME_TEMPLATE' => $nameTemplate,
			'SHOW_LOGIN' => $showLogin,
			'DESTINATION_LIMIT' => 100,
			'CHECK_PERMISSIONS_DEST' => 'N'
		];

		if ($createdById > 0)
		{
			$destinationParams["CREATED_BY"] = $createdById;
		}

		$moreCount = 0;
		$destinationList = \CSocNetLogTools::formatDestinationFromRights($rightsList, $destinationParams, $moreCount);
		$hiddenDestinationsCount = 0;

		$availableWorkgroupsIdList = \CSocNetLogTools::getAvailableGroups();

		foreach($destinationList as $key => $destinationFields)
		{
			if (
				isset($destinationFields['TYPE'])
				&& isset($destinationFields['ID'])
				&& (
					(
						$destinationFields['TYPE'] == 'SG'
						&& !in_array(intval($destinationFields['ID']), $availableWorkgroupsIdList)
					)
					|| (
						in_array($destinationFields['TYPE'], [ 'CRMCOMPANY', 'CRMLEAD', 'CRMCONTACT', 'CRMDEAL' ])
						&& Loader::includeModule('crm')
						&& !\Bitrix\Crm\Security\EntityAuthorization::checkReadPermission(
							\CCrmLiveFeedEntity::resolveEntityTypeID($destinationFields['TYPE']),
							$destinationFields['ID']
						)
					)
					|| (
						in_array($destinationFields['TYPE'], [ 'DR', 'D' ])
						&& $currentExtranetUser
					)
					|| (
						$destinationFields['TYPE'] == 'U'
						&& is_array($visibleUserIdList)
						&& !in_array(intval($destinationFields['ID']), $visibleUserIdList)
					)
					|| (
						$destinationFields['TYPE'] == 'U'
						&& isset($destinationFields['IS_EXTRANET'])
						&& $destinationFields['IS_EXTRANET'] == 'Y'
						&& is_array($availableExtranetUserIdList)
						&& !in_array(intval($destinationFields['ID']), $availableExtranetUserIdList)
					)
				)
			)
			{
				unset($destinationList[$key]);
				$hiddenDestinationsCount++;
			}
		}

		return [
			'destinationList' => $destinationList,
			'hiddenDestinationsCount' => $hiddenDestinationsCount
		];
	}
}

