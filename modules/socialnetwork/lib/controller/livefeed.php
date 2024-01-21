<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Helper\ServiceComment;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Socialnetwork\CommentAux;
use Bitrix\Socialnetwork\Livefeed\Context\Context;

class Livefeed extends Base
{
	public function configureActions(): array
	{
		$configureActions = parent::configureActions();
		$configureActions['getNextPage'] = [
			'-prefilters' => [
				ActionFilter\Authentication::class,
			]
		];
		$configureActions['refresh'] = [
			'-prefilters' => [
				ActionFilter\Authentication::class,
			]
		];
		$configureActions['mobileLogError'] = [
			'-prefilters' => [
				ActionFilter\Authentication::class,
			]
		];

		return $configureActions;
	}

	public function deleteEntryAction($logId = 0): ?array
	{
		$logId = (int)$logId;
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		if (!\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			$this->addError(new Error('No permissions', 'SONET_CONTROLLER_LIVEFEED_NO_PERMISSIONS'));
			return null;
		}

		return [
			'success' => \CSocNetLog::delete($logId)
		];
	}

	public function getRawEntryDataAction(array $params = []): ?array
	{
		$entityType = (isset($params['entityType']) && $params['entityType'] <> '' ? preg_replace("/[^a-z0-9_]/i", '', $params['entityType']) : false);
		$entityId = (isset($params['entityId']) && (int)$params['entityId'] > 0 ? (int)$params['entityId'] : false);
		$logId = (isset($params['logId']) && (int)$params['logId'] > 0 ? (int)$params['logId'] : false);
		$additionalParams = (isset($params['additionalParams']) && is_array($params['additionalParams']) ? $params['additionalParams'] : []);

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$provider = \Bitrix\Socialnetwork\Livefeed\Provider::init([
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
			'LOG_ID' => $logId,
			'CLONE_DISK_OBJECTS' => true
		]);

		if (!$provider)
		{
			$this->addError(new Error('Cannot find Livefeed entity', 'SONET_CONTROLLER_LIVEFEED_NO_ENTITY'));
			return null;
		}

		$returnFields = [ 'TITLE', 'DESCRIPTION', 'DISK_OBJECTS', 'GROUPS_AVAILABLE', 'LIVEFEED_URL', 'SUFFIX', 'LOG_ID' ];
		if (
			isset($additionalParams['returnFields'])
			&& is_array($additionalParams['returnFields'])
		)
		{
			$returnFields = array_intersect($returnFields, $additionalParams['returnFields']);
		}

		$result = [
			'TITLE' => $provider->getSourceTitle(),
			'DESCRIPTION' => $provider->getSourceDescription(),
			'DISK_OBJECTS' => $provider->getSourceDiskObjects()
		];

		if (
			isset($additionalParams['getSonetGroupAvailable'])
			&& $additionalParams['getSonetGroupAvailable'] === 'Y'
		)
		{
			$feature = $operation = false;
			if (isset($additionalParams['checkPermissions']['operation'], $additionalParams['checkPermissions']['feature']))
			{
				$feature = $additionalParams['checkPermissions']['feature'];
				$operation = $additionalParams['checkPermissions']['operation'];
			}

			$result['GROUPS_AVAILABLE'] = $provider->getSonetGroupsAvailable($feature, $operation);
		}

		if (
			isset($additionalParams['getLivefeedUrl'])
			&& $additionalParams['getLivefeedUrl'] === 'Y'
		)
		{
			$result['LIVEFEED_URL'] = $provider->getLiveFeedUrl();
			if (
				isset($additionalParams['absoluteUrl'])
				&& $additionalParams['absoluteUrl'] === 'Y'
			)
			{
				$serverName = Option::get('main', 'server_name', $_SERVER['SERVER_NAME']);
				$res = \CSite::getById(SITE_ID);
				if (
					($siteFields = $res->fetch())
					&& $siteFields['SERVER_NAME'] <> ''
				)
				{
					$serverName = $siteFields['SERVER_NAME'];
				}

				$protocol = (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http');
				$result['LIVEFEED_URL'] = $protocol . '://' . $serverName . $result['LIVEFEED_URL'];
			}
		}

		$result['SUFFIX'] = $provider->getSuffix();

		if (($logId = $provider->getLogId()))
		{
			$result['LOG_ID'] = $logId;
		}

		return array_filter($result, static function ($key) use ($returnFields) {
			return in_array($key, $returnFields, true);
		}, ARRAY_FILTER_USE_KEY);
	}

	public function createEntityCommentAction(array $params = []): void
	{
		$postEntityType = (isset($params['postEntityType']) && $params['postEntityType'] <> '' ? preg_replace('/[^a-z0-9_]/i', '', $params['postEntityType']) : false);
		$sourceEntityType = (isset($params['sourceEntityType']) && $params['sourceEntityType'] <> '' ? preg_replace('/[^a-z0-9_]/i', '', $params['sourceEntityType']) : false);
		$sourceEntityId = (int)($params['sourceEntityId'] ?? 0);
		$sourceEntityData = (array)($params['sourceEntityData'] ?? []);
		$entityType = (isset($params['entityType']) && $params['entityType'] <> '' ? preg_replace('/[^a-z0-9_]/i', '', $params['entityType']) : false);
		$entityId = (int)($params['entityId'] ?? 0);
		$logId = (int)($params['logId'] ?? 0);

		if (
			!$sourceEntityType
			|| $sourceEntityId <= 0
			|| !$entityType
			|| $entityId <= 0
		)
		{
			return;
		}

		if (in_array($sourceEntityType, [CommentAux\CreateEntity::SOURCE_TYPE_BLOG_POST, CommentAux\CreateEntity::SOURCE_TYPE_BLOG_COMMENT], true))
		{
			ServiceComment::processBlogCreateEntity([
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId,
				'SOURCE_ENTITY_TYPE' => $sourceEntityType,
				'SOURCE_ENTITY_ID' => $sourceEntityId,
				'SOURCE_ENTITY_DATA' => $sourceEntityData,
				'LIVE' => 'Y',
			]);
		}
		else
		{
			ServiceComment::processLogEntryCreateEntity([
				'LOG_ID' => $logId,
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId,
				'POST_ENTITY_TYPE' => $postEntityType,
				'SOURCE_ENTITY_TYPE' => $sourceEntityType,
				'SOURCE_ENTITY_ID' => $sourceEntityId,
				'SOURCE_ENTITY_DATA' => $sourceEntityData,
				'LIVE' => 'Y'
			]);
		}
	}

	/**
	 * @deprecated use socialnetwork.api.livefeed.createEntityComment
	 * @param array $params
	 */
	public function createTaskCommentAction(array $params = []): void
	{
		$postEntityType = (isset($params['postEntityType']) && $params['postEntityType'] <> '' ? preg_replace('/[^a-z0-9_]/i', '', $params['postEntityType']) : false);
		$sourceEntityType = (isset($params['entityType']) && $params['entityType'] <> '' ? preg_replace("/[^a-z0-9_]/i", '', $params['entityType']) : false);
		$sourceEntityId = (isset($params['entityId']) && (int)$params['entityId'] > 0 ? (int)$params['entityId'] : false);
		$taskId = (isset($params['taskId']) && (int)$params['taskId'] > 0 ? (int)$params['taskId'] : false);
		$logId = (isset($params['logId']) && (int)$params['logId'] > 0 ? (int)$params['logId'] : false);

		if (
			!$sourceEntityType
			|| !$sourceEntityId
			|| !$taskId
		)
		{
			return;
		}

		$this->createEntityCommentAction([
			'postEntityType' => $postEntityType,
			'sourceEntityType' => $sourceEntityType,
			'sourceEntityId' => $sourceEntityId,
			'entityType' => CommentAux\CreateEntity::ENTITY_TYPE_TASK,
			'entityId' => $taskId,
			'logId' => $logId,
		]);
	}

	public function changeFavoritesAction($logId, $value): ?array
	{
		global $APPLICATION;

		$result = [
			'success' => false,
			'newValue' => false
		];

		$logId = (int)$logId;
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		if (!(
			Loader::includeModule('socialnetwork')
			&& ($logFields = \CSocNetLog::getById($logId))
		))
		{
			$this->addError(new Error('Cannot get log entry', 'SONET_CONTROLLER_LIVEFEED_EMPTY_LOG_ENTRY'));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();

		if ($res = \CSocNetLogFavorites::change($currentUserId, $logId))
		{
			if ($res === 'Y')
			{
				ComponentHelper::userLogSubscribe([
					'logId' => $logId,
					'userId' => $currentUserId,
					'typeList' => [
						'FOLLOW',
						'COUNTER_COMMENT_PUSH',
					],
					'followDate' => $logFields['LOG_UPDATE'],
				]);
			}
			$result['success'] = true;
			$result['newValue'] = $res;
		}
		else
		{
			$this->addError(new Error((($e = $APPLICATION->getException()) ? $e->getString() : 'Cannot change log entry favorite value'), 'SONET_CONTROLLER_LIVEFEED_FAVORITES_CHANGE_ERROR'));
			return null;
		}

		return $result;
	}

	public function changeFollowAction($logId, $value): ?array
	{
		$result = [
			'success' => false
		];

		$logId = (int)$logId;
		if ($logId <= 0)
		{
			return $result;
		}

		$logId = (int)$logId;
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		$result['success'] = (
			$value === 'Y'
				? ComponentHelper::userLogSubscribe([
					'logId' => $logId,
					'userId' => $currentUserId,
					'typeList' => [ 'FOLLOW', 'COUNTER_COMMENT_PUSH' ]
				])
				: \CSocNetLogFollow::set($currentUserId, 'L' . $logId, 'N')
		);

		return $result;
	}

	public function changeFollowDefaultAction($value): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		return [
			'success' => \CSocNetLogFollow::set($this->getCurrentUser()->getId(), '**', ($value === 'Y' ? 'Y' : 'N'))
		];
	}

	public function changeExpertModeAction($expertModeValue): ?array
	{
		$result = [
			'success' => false
		];

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$viewValue = ($expertModeValue === 'Y' ? 'N' : 'Y');
		\Bitrix\Socialnetwork\LogViewTable::set($this->getCurrentUser()->getId(), 'tasks', $viewValue);
		\Bitrix\Socialnetwork\LogViewTable::set($this->getCurrentUser()->getId(), 'crm_activity_add', $viewValue);
		\Bitrix\Socialnetwork\LogViewTable::set($this->getCurrentUser()->getId(), 'crm_activity_add_comment', $viewValue);

		$result['success'] = true;

		return $result;
	}

	public function readNoTasksNotificationAction(): array
	{
		$result = [
			'success' => false
		];

		if (\CUserOptions::setOption('socialnetwork', '~log_notasks_notification_read', 'Y'))
		{
			$result['success'] = true;
		}

		return $result;
	}

	public function mobileLogErrorAction($message, $url, $lineNumber): array
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			AddMessage2Log("Mobile Livefeed javascript error:\nMessage: ".$message."\nURL: ".$url."\nLine number: ".$lineNumber."\nUser ID: ".$this->getCurrentUser()->getId());
		}

		return [
			'success' => true
		];
	}

	public function mobileGetDetailAction($logId): ?Component
	{
		$logId = (int)$logId;
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		return new Component('bitrix:mobile.socialnetwork.log.ex', '', [
			'LOG_ID' => $logId,
			'SITE_TEMPLATE_ID' => 'mobile_app',
			'TARGET' => 'postContent',
			'PATH_TO_USER' => SITE_DIR.'mobile/users/?user_id=#user_id#'
		]);
	}

	public static function isAdmin(): bool
	{
		global $USER;

		return (
			$USER->isAdmin()
			|| (
				Loader::includeModule('bitrix24')
				&& \CBitrix24::isPortalAdmin($USER->getId())
			)
		);
	}

	private function getComponentReturnWhiteList(): array
	{
		return [ 'LAST_TS', 'LAST_ID', 'EMPTY', 'FILTER_USED', 'FORCE_PAGE_REFRESH' ];
	}

	public function getNextPageAction(array $params = []): Component
	{
		$componentParameters = $this->getUnsignedParameters();
		if (!is_array($componentParameters))
		{
			$componentParameters = [];
		}
		$context = $params['context'] ?? '';
		$requestParameters = [
			'TARGET' => 'page',
			'PAGE_NUMBER' => (isset($params['PAGE_NUMBER']) && (int)$params['PAGE_NUMBER'] >= 1 ? (int)$params['PAGE_NUMBER'] : 1),
			'LAST_LOG_TIMESTAMP' => (isset($params['LAST_LOG_TIMESTAMP']) && (int)$params['LAST_LOG_TIMESTAMP'] > 0 ? (int)$params['LAST_LOG_TIMESTAMP'] : 0),
			'PREV_PAGE_LOG_ID' => ($params['PREV_PAGE_LOG_ID'] ?? ''),
			'CONTEXT' => $context,
			'useBXMainFilter' =>  ($params['useBXMainFilter'] ?? 'N'),
			'siteTemplateId' =>  ($params['siteTemplateId'] ?? 'bitrix24'),
			'preset_filter_top_id' =>  ($params['preset_filter_top_id'] ?? ''),
			'preset_filter_id' =>  ($params['preset_filter_id'] ?? ''),
		];

		if ($context === Context::SPACES)
		{
			$requestParameters['SPACE_USER_ID'] = (int)($params['userId'] ?? $this->userId);
			$requestParameters['GROUP_ID'] = (int)($componentParameters['GROUP_ID'] ?? $params['spaceId']);
		}


		return new Component('bitrix:socialnetwork.log.ex', '', array_merge($componentParameters, $requestParameters), [], $this->getComponentReturnWhiteList());
	}

	public function refreshAction(array $params = []): Component
	{
		$componentParameters = $this->getUnsignedParameters();
		if (!is_array($componentParameters))
		{
			$componentParameters = [];
		}

		$context = $params['context'] ?? '';
		$requestParameters = [
			'TARGET' => 'page',
			'PAGE_NUMBER' => 1,
			'RELOAD' => 'Y',
			'CONTEXT' => $context,
			'composition' => $params['composition'] ?? [],
			'useBXMainFilter' => ($params['useBXMainFilter'] ?? 'N'),
			'siteTemplateId' => ($params['siteTemplateId'] ?? 'bitrix24'),
			'assetsCheckSum' => ($params['assetsCheckSum'] ?? '')
		];

		if ($context === Context::SPACES)
		{
			$requestParameters['SPACE_USER_ID'] = (int)($params['userId'] ?? $this->userId);
			$requestParameters['GROUP_ID'] = (int)($componentParameters['GROUP_ID'] ?? $params['spaceId']);
		}

		return new Component('bitrix:socialnetwork.log.ex', '', array_merge($componentParameters, $requestParameters), [], $this->getComponentReturnWhiteList());
	}

	public function mobileCreateNotificationLinkAction($tag): string
	{
		$params = explode("|", $tag);
		if (empty($params[1]) || empty($params[2]) || !Loader::includeModule('socialnetwork'))
		{
			return '';
		}

		$liveFeedEntity = \Bitrix\SocialNetwork\Livefeed\Provider::init([
			'ENTITY_TYPE' => \Bitrix\Socialnetwork\Livefeed\Provider::DATA_ENTITY_TYPE_FORUM_POST,
			'ENTITY_ID' => $params[2]
		]);

		$suffix = $liveFeedEntity->getSuffix();
		if ($suffix === 'TASK')
		{
			$res = \Bitrix\Socialnetwork\LogTable::getList(array(
				'filter' => array(
					'ID' => $liveFeedEntity->getLogId()
				),
				'select' => [ 'ENTITY_ID', 'EVENT_ID', 'SOURCE_ID' ]
			));
			if ($logEntryFields = $res->fetch())
			{
				if ($logEntryFields['EVENT_ID'] === 'crm_activity_add')
				{
					if (
						Loader::includeModule('crm')
						&& ($activityFields = \CCrmActivity::getById($logEntryFields['ENTITY_ID'], false))
						&& $activityFields['TYPE_ID'] == \CCrmActivityType::Task
					)
					{
						$taskId = (int)$activityFields['ASSOCIATED_ENTITY_ID'];
					}
				}
				else
				{
					$taskId = (int)$logEntryFields['SOURCE_ID'];
				}

				if (isset($taskId) && $taskId > 0 && Loader::includeModule('mobile'))
				{
					return \CMobileHelper::getParamsToCreateTaskLink($taskId);
				}
			}
		}

		return SITE_DIR . "mobile/log/?ACTION=CONVERT&ENTITY_TYPE_ID=FORUM_POST&ENTITY_ID=" . $params[2];
	}
}