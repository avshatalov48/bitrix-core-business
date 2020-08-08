<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\ComponentHelper;

class Livefeed extends \Bitrix\Main\Engine\Controller
{
	public function deleteEntryAction($logId = 0)
	{
		global $APPLICATION;

		$logId = intval($logId);
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

	public function getRawEntryDataAction(array $params = [])
	{
		$entityType = (isset($params['entityType']) && $params['entityType'] <> '' ? preg_replace("/[^a-z0-9_]/i", '', $params['entityType']) : false);
		$entityId = (isset($params['entityId']) && intval($params['entityId']) > 0 ? intval($params['entityId']) : false);
		$logId = (isset($params['logId']) && intval($params['logId']) > 0 ? intval($params['logId']) : false);
		$additionalParams = (isset($params['additionalParams']) && is_array($params['additionalParams']) ? $params['additionalParams'] : []);

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$provider = \Bitrix\Socialnetwork\Livefeed\Provider::init(array(
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
			'LOG_ID' => $logId,
			'CLONE_DISK_OBJECTS' => true
		));

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
			&& $additionalParams['getSonetGroupAvailable'] == 'Y'
		)
		{
			$feature = $operation = false;
			if (
				isset($additionalParams['checkPermissions'])
				&& isset($additionalParams['checkPermissions']['feature'])
				&& isset($additionalParams['checkPermissions']['operation'])
			)
			{
				$feature = $additionalParams['checkPermissions']['feature'];
				$operation = $additionalParams['checkPermissions']['operation'];
			}

			$result['GROUPS_AVAILABLE'] = $provider->getSonetGroupsAvailable($feature, $operation);
		}

		if (
			isset($additionalParams['getLivefeedUrl'])
			&& $additionalParams['getLivefeedUrl'] == 'Y'
		)
		{
			$result['LIVEFEED_URL'] = $provider->getLiveFeedUrl();
			if (
				isset($additionalParams['absoluteUrl'])
				&& $additionalParams['absoluteUrl'] == 'Y'
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

				$protocol = (\CMain::IsHTTPS() ? "https" : "http");
				$result['LIVEFEED_URL'] = $protocol."://".$serverName.$result['LIVEFEED_URL'];
			}
		}

		if ($provider->getType() == \Bitrix\Socialnetwork\Livefeed\Provider::TYPE_COMMENT)
		{
			$result['SUFFIX'] = $provider->getSuffix();
		}

		if (($logId = $provider->getLogId()))
		{
			$result['LOG_ID'] = $logId;
		}

		$result = array_filter($result, function($key) use($returnFields) {
			return in_array($key, $returnFields);
		}, ARRAY_FILTER_USE_KEY);

		return $result;
	}

	public function createTaskCommentAction(array $params = [])
	{
		$postEntityType = (isset($params['postEntityType']) && $params['postEntityType'] <> '' ? preg_replace('/[^a-z0-9_]/i', '', $params['postEntityType']) : false);
		$entityType = (isset($params['entityType']) && $params['entityType'] <> '' ? preg_replace("/[^a-z0-9_]/i", '', $params['entityType']) : false);
		$entityId = (isset($params['entityId']) && intval($params['entityId']) > 0 ? intval($params['entityId']) : false);
		$taskId = (isset($params['taskId']) && intval($params['taskId']) > 0 ? intval($params['taskId']) : false);
		$logId = (isset($params['logId']) && intval($params['logId']) > 0 ? intval($params['logId']) : false);

		if (
			$entityType
			&& $entityId
			&& $taskId
		)
		{
			if (in_array($entityType, [ 'BLOG_POST', 'BLOG_COMMENT' ]))
			{
				ComponentHelper::processBlogCreateTask([
					'TASK_ID' => $taskId,
					'SOURCE_ENTITY_TYPE' => $entityType,
					'SOURCE_ENTITY_ID' => $entityId,
					'LIVE' => 'Y'
				]);
			}
			else
			{
				ComponentHelper::processLogEntryCreateTask([
					'LOG_ID' => $logId,
					'TASK_ID' => $taskId,
					'POST_ENTITY_TYPE' => $postEntityType,
					'SOURCE_ENTITY_TYPE' => $entityType,
					'SOURCE_ENTITY_ID' => $entityId,
					'LIVE' => 'Y'
				]);
			}
		}
	}

	public function changeFavoritesAction($logId, $value)
	{
		global $APPLICATION;

		$result = [
			'success' => false,
			'newValue' => false
		];

		$logId = intval($logId);
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
			if ($res == "Y")
			{
				ComponentHelper::userLogSubscribe(array(
					'logId' => $logId,
					'userId' => $currentUserId,
					'typeList' => array(
						'FOLLOW',
						'COUNTER_COMMENT_PUSH'
					),
					'followDate' => $logFields["LOG_UPDATE"]
				));
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

	public function changeFollowAction($logId, $value)
	{
		$result = [
			'success' => false
		];

		$logId = intval($logId);
		if ($logId <= 0)
		{
			return $result;
		}

		$logId = intval($logId);
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
			$value == "Y"
				? ComponentHelper::userLogSubscribe([
					'logId' => $logId,
					'userId' => $currentUserId,
					'typeList' => [ 'FOLLOW', 'COUNTER_COMMENT_PUSH' ]
				])
				: \CSocNetLogFollow::set($currentUserId, "L".$logId, "N")
		);

		return $result;
	}

	public function changeFollowDefaultAction($value)
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		return [
			'success' => \CSocNetLogFollow::set($this->getCurrentUser()->getId(), "**", ($value == "Y" ? "Y" : "N"))
		];
	}

	public function changeExpertModeAction($value)
	{
		$result = [
			'success' => false
		];

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		\Bitrix\Socialnetwork\LogViewTable::set($this->getCurrentUser()->getId(), 'tasks', ($value == "Y" ? "N" : "Y"));
		$result['success'] = true;

		return $result;
	}

	public function readNoTasksNotificationAction()
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

	public function mobileLogErrorAction($message, $url, $lineNumber)
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled("bitrix24"))
		{
			AddMessage2Log("Mobile Livefeed javascript error:\nMessage: ".$message."\nURL: ".$url."\nLine number: ".$lineNumber."\nUser ID: ".$this->getCurrentUser()->getId());
		}

		return [
			'success' => true
		];
	}

	public function mobileGetDetailAction($logId)
	{
		$logId = intval($logId);
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		return new \Bitrix\Main\Engine\Response\Component('bitrix:mobile.socialnetwork.log.ex', '', [
			'LOG_ID' => $logId,
			'SITE_TEMPLATE_ID' => 'mobile_app',
			'TARGET' => 'postContent',
		]);
	}

	public static function isAdmin()
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
	
	private function getComponentReturnWhiteList()
	{
		return [ 'LAST_TS', 'LAST_ID', 'EMPTY' ];
	}

	public function getNextPageAction(array $params = [])
	{
		$componentParameters = $this->getUnsignedParameters();
		$requestParameters = [
			'TARGET' => 'page',
			'PAGE_NUMBER' => (isset($params['PAGE_NUMBER']) && intval($params['PAGE_NUMBER']) >= 1 ? intval($params['PAGE_NUMBER']) : 1),
			'LAST_LOG_TIMESTAMP' => (isset($params['LAST_LOG_TIMESTAMP']) && intval($params['LAST_LOG_TIMESTAMP']) > 0 ? intval($params['LAST_LOG_TIMESTAMP']) : 0),
			'PREV_PAGE_LOG_ID' => (isset($params['PREV_PAGE_LOG_ID']) ? $params['PREV_PAGE_LOG_ID'] : ''),
			'useBXMainFilter' =>  (isset($params['useBXMainFilter']) ? $params['useBXMainFilter'] : 'N'),
			'siteTemplateId' =>  (isset($params['siteTemplateId']) ? $params['siteTemplateId'] : 'bitrix24')
		];

		$componentResponse = new \Bitrix\Main\Engine\Response\Component('bitrix:socialnetwork.log.ex', '', array_merge($componentParameters, $requestParameters), [], $this->getComponentReturnWhiteList());

		return $componentResponse;
	}

	public function refreshAction(array $params = [])
	{
		$componentParameters = $this->getUnsignedParameters();
		$requestParameters = [
			'TARGET' => 'page',
			'PAGE_NUMBER' => 1,
			'RELOAD' => 'Y',
			'useBXMainFilter' =>  (isset($params['useBXMainFilter']) ? $params['useBXMainFilter'] : 'N'),
			'siteTemplateId' =>  (isset($params['siteTemplateId']) ? $params['siteTemplateId'] : 'bitrix24')
		];

		$componentResponse = new \Bitrix\Main\Engine\Response\Component('bitrix:socialnetwork.log.ex', '', array_merge($componentParameters, $requestParameters), [], $this->getComponentReturnWhiteList());

		return $componentResponse;
	}
}	