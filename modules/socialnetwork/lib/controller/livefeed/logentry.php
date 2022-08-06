<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Socialnetwork\Controller\Base;
use Bitrix\Socialnetwork\LogPinnedTable;
use Bitrix\Socialnetwork\LogTable;

class LogEntry extends Base
{
	public function configureActions()
	{
		return [
			'getPinData' => [
				'+prefilters' => [
					new ActionFilter\Token(static function () {
						$requestParams = \Bitrix\Main\Context::getCurrent()->getRequest()->getPost('params');

						return (
							is_array($requestParams)
							&& isset($requestParams['logId'])
								? (string)$requestParams['logId']
								: ''
						);
					}),
				],
			],
		];
	}

	public function getHiddenDestinationsAction(array $params = []): ?array
	{
		$logId = (int)($params['logId'] ?? 0);
		$createdById = (int)($params['createdById'] ?? 0);
		$destinationLimit = (int)($params['destinationLimit'] ?? 100);

		$pathToUser = ($params['pathToUser'] ?? '');
		$pathToWorkgroup = ($params['pathToWorkgroup'] ?? '');
		$pathToDepartment = ($params['pathToDepartment'] ?? '');
		$nameTemplate = ($params['nameTemplate'] ?? \CSite::getNameFormat());
		$showLogin = ($params['showLogin'] ?? (ModuleManager::isModuleInstalled('intranet') ? 'Y' : 'N'));

		if ($logId <= 0)
		{
			$this->addError(new Error('Empty Log ID.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_EMPTY_LOG_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_MODULE_NOT_INSTALLED'));
			return null;
		}

		\CSocNetTools::initGlobalExtranetArrays();

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
				isset($destinationFields['TYPE'], $destinationFields['ID'])
				&& (
					(
						$destinationFields['TYPE'] === 'SG'
						&& !in_array((int)$destinationFields['ID'], $availableWorkgroupsIdList)
					)
					|| (
						in_array($destinationFields['TYPE'], ['CRMCOMPANY', 'CRMLEAD', 'CRMCONTACT', 'CRMDEAL'])
						&& Loader::includeModule('crm')
						&& !\Bitrix\Crm\Security\EntityAuthorization::checkReadPermission(
							\CCrmLiveFeedEntity::resolveEntityTypeID($destinationFields['TYPE']),
							$destinationFields['ID']
						)
					)
					|| (
						in_array($destinationFields['TYPE'], ['DR', 'D'])
						&& $currentExtranetUser
					)
					|| (
						$destinationFields['TYPE'] === 'U'
						&& is_array($visibleUserIdList)
						&& !in_array((int)$destinationFields['ID'], $visibleUserIdList)
					)
					|| (
						$destinationFields['TYPE'] === 'U'
						&& isset($destinationFields['IS_EXTRANET'])
						&& $destinationFields['IS_EXTRANET'] === 'Y'
						&& is_array($availableExtranetUserIdList)
						&& !in_array((int)$destinationFields['ID'], $availableExtranetUserIdList)
					)
				)
			)
			{
				unset($destinationList[$key]);
				$hiddenDestinationsCount++;
			}
		}

		return [
			'destinationList' => array_slice($destinationList, $destinationLimit),
			'hiddenDestinationsCount' => $hiddenDestinationsCount
		];
	}

	public function pinAction(array $params = []): ?array
	{
		$logId = (int)($params['logId'] ?? 0);

		if ($logId <= 0)
		{
			$this->addError(new Error('Empty Log ID.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_EMPTY_LOG_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_MODULE_NOT_INSTALLED'));
			return null;
		}

		LogPinnedTable::set([
			'logId' => $logId,
			'userId' => (int)$this->getCurrentUser()->getId(),
		]);

		return [
			'success' => true
		];
	}

	public function unpinAction(array $params = []): ?array
	{
		$logId = (int)($params['logId'] ?? 0);

		if ($logId <= 0)
		{
			$this->addError(new Error('Empty Log ID.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_EMPTY_LOG_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_MODULE_NOT_INSTALLED'));
			return null;
		}

		LogPinnedTable::delete([
			'LOG_ID' => $logId,
			'USER_ID' => (int)$this->getCurrentUser()->getId(),
		]);

		return [
			'success' => true
		];
	}

	public function getPinDataAction(array $params = []): ?array
	{
		$logId = (isset($params['logId']) ? (int)$params['logId'] : 0);

		if ($logId <= 0)
		{
			$this->addError(new Error('Empty Log ID.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_EMPTY_LOG_ID'));
			return null;
		}

		$res = LogTable::getList([
			'filter' => [
				'=ID' => $logId
			],
			'select' => [ 'ID', 'ENTITY_ID', 'EVENT_ID', 'SOURCE_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID' ]
		]);
		if (!($logEntryFields = $res->fetch()))
		{
			$this->addError(new Error('Log entry not found.', 'SONET_CONTROLLER_LIVEFEED_LOGENTRY_NOT_FOUND'));
			return null;
		}

		$contentId = \Bitrix\Socialnetwork\Livefeed\Provider::getContentId($logEntryFields);
		if (!$contentId)
		{
			$this->addError(new Error('Content entity not found.', 'SONET_CONTROLLER_LIVEFEED_CONTENT_NOT_FOUND'));
			return null;
		}

		if (empty($contentId['ENTITY_TYPE']))
		{
			$this->addError(new Error('Content entity not found.', 'SONET_CONTROLLER_LIVEFEED_CONTENT_NOT_FOUND'));
			return null;
		}

		$postProvider = \Bitrix\Socialnetwork\Livefeed\Provider::init([
			'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
			'ENTITY_ID' => $contentId['ENTITY_ID'],
			'LOG_ID' => $logEntryFields['ID']
		]);

		return [
			'TITLE' => htmlspecialcharsEx($postProvider->getPinnedTitle()),
			'DESCRIPTION' => htmlspecialcharsEx($postProvider->getPinnedDescription())
		];
	}
}

