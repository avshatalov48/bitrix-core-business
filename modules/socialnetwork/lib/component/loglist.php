<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Component\LogList\Gratitude;
use Bitrix\Socialnetwork\Component\LogList\Util;
use Bitrix\Socialnetwork\Component\LogList\Param;
use Bitrix\Socialnetwork\Component\LogList\Assets;
use Bitrix\Socialnetwork\Component\LogList\Path;
use Bitrix\Socialnetwork\Component\LogList\ParamPhotogallery;
use Bitrix\Socialnetwork\Component\LogList\Processor;
use Bitrix\Socialnetwork\Component\LogList\Page;
use Bitrix\Socialnetwork\Component\LogList\Counter;

class LogList extends \Bitrix\Socialnetwork\Component\LogListCommon
{
	protected $extranetSite = false;
	protected $presetFilterTopId = false;
	protected $presetFilterId = false;
	protected $commentsNeeded = false;
	protected $activity2LogList = [];
	protected $diskUFEntityList = [
		'BLOG_POST' => [],
		'SONET_LOG' => []
	];

	protected $gratitudesInstance;
	protected $paramsInstance;
	protected $assetsInstance;
	protected $pathInstance;
	protected $paramsPhotogalleryInstance;
	protected $processorInstance;
	protected $logPageProcessorInstance;
	protected $counterProcessorInstance;

	public $useLogin = false;

	public static $canCurrentUserAddComments = [];

	public function setExtranetSiteValue($value = false): void
	{
		$this->extranetSite = ($value === true);
	}

	public function getExtranetSiteValue(): bool
	{
		return $this->extranetSite;
	}

	public function setPresetFilterTopIdValue($value): void
	{
		$this->presetFilterTopId = $value;
	}

	public function getPresetFilterTopIdValue()
	{
		return $this->presetFilterTopId;
	}

	public function setPresetFilterIdValue($value): void
	{
		$this->presetFilterId = $value;
	}

	public function getPresetFilterIdValue()
	{
		return $this->presetFilterId;
	}

	public function setCommentsNeededValue($value = false): void
	{
		$this->commentsNeeded = ($value === true);
	}

	public function getCommentsNeededValue(): bool
	{
		return $this->commentsNeeded;
	}

	public function setActivity2LogListValue($value = []): void
	{
		$this->activity2LogList = $value;
	}

	public function getActivity2LogListValue()
	{
		return $this->activity2LogList;
	}

	public function setDiskUFEntityListValue($value = []): void
	{
		$this->diskUFEntityList = $value;
	}

	public function getDiskUFEntityListValue()
	{
		return $this->diskUFEntityList;
	}

	protected function processParentParams(&$params): void
	{
		$parent = $this->getParent();
		if (is_object($parent) && $parent->__name <> '')
		{
			$this->getParamsInstance()->prepareParentParams($params);
			$this->getParamsPhotogalleryInstance()->prepareParentPhotogalleryParams($params);

			// parent of 2nd level
			$parent2 = $parent->getParent();
			if (is_object($parent2) && $parent2->__name <> '')
			{
				$this->getParamsInstance()->prepareParent2Params($params);
			}
		}
	}

	protected function getGratitudesInstance(): Gratitude
	{
		if($this->gratitudesInstance === null)
		{
			$this->gratitudesInstance = new Gratitude([
				'component' => $this
			]);
		}

		return $this->gratitudesInstance;
	}

	protected function getParamsInstance(): Param
	{
		if($this->paramsInstance === null)
		{
			$this->paramsInstance = new Param([
				'component' => $this,
				'request' => $this->getRequest()
			]);
		}

		return $this->paramsInstance;
	}

	public function getAssetsInstance(): Assets
	{
		if($this->assetsInstance === null)
		{
			$this->assetsInstance = new Assets([
				'component' => $this
			]);
		}

		return $this->assetsInstance;
	}

	public function getPathInstance(): Path
	{
		if($this->pathInstance === null)
		{
			$this->pathInstance = new Path([
				'component' => $this,
				'request' => $this->getRequest()
			]);
		}

		return $this->pathInstance;
	}

	public function getParamsPhotogalleryInstance(): ParamPhotogallery
	{
		if($this->paramsPhotogalleryInstance === null)
		{
			$this->paramsPhotogalleryInstance = new ParamPhotogallery([
				'component' => $this
			]);
		}

		return $this->paramsPhotogalleryInstance;
	}

	protected function getProcessorInstance(): Processor
	{
		if($this->processorInstance === null)
		{
			$this->processorInstance = new Processor([
				'component' => $this,
				'request' => $this->getRequest()
			]);
		}

		return $this->processorInstance;
	}

	public function getLogPageProcessorInstance(): Page
	{
		if($this->logPageProcessorInstance === null)
		{
			$this->logPageProcessorInstance = new Page([
				'component' => $this,
				'request' => $this->getRequest(),
				'processorInstance' => $this->getProcessorInstance()
			]);
		}

		return $this->logPageProcessorInstance;
	}

	public function getCounterProcessorInstance(): Counter
	{
		if ($this->counterProcessorInstance === null)
		{
			$this->counterProcessorInstance = new Counter([
				'component' => $this,
				'request' => $this->getRequest(),
				'processorInstance' => $this->getProcessorInstance()
			]);
		}

		return $this->counterProcessorInstance;
	}

	public function onPrepareComponentParams($params): array
	{
		global $USER;

		$this->errorCollection = new ErrorCollection();

		$request = $this->getRequest();
		$paramsInstance = $this->getParamsInstance();
		$pathInstance = $this->getPathInstance();

		$this->setExtranetSiteValue((Loader::includeModule('extranet') && \CExtranet::isExtranetSite()));
		$this->setCommentsNeededValue(
			$request->get('log_filter_submit') <> ''
			&& $request->get('flt_comments') === 'Y'
		);

		Util::checkEmptyParamInteger($params, 'LOG_CNT', 0);
		Util::checkEmptyParamInteger($params, 'PAGE_SIZE', 20);
		Util::checkEmptyParamString($params, 'PUBLIC_MODE', 'N');
		Util::checkEmptyParamString($params, 'SHOW_EVENT_ID_FILTER', 'Y');
		Util::checkEmptyParamInteger($params, 'LOG_ID', 0);

		$params['HIDE_EDIT_FORM'] = ($params['LOG_ID'] > 0 ? 'Y' : ($params['HIDE_EDIT_FORM'] ?? 'N'));
		$params['SHOW_EVENT_ID_FILTER'] = ($params['LOG_ID'] > 0 ? 'N' : $params['SHOW_EVENT_ID_FILTER']);
		$params['AUTH'] = (isset($params['AUTH']) && mb_strtoupper($params['AUTH']) === 'Y' ? 'Y' : 'N');
		$params['PAGE_NUMBER'] = (
			isset($params['PAGE_NUMBER'])
			&& (int)$params['PAGE_NUMBER'] > 0
				? (int)$params['PAGE_NUMBER']
				: 1
		);

		$paramsInstance->prepareModeParams($params);
		$paramsInstance->prepareFollowParams($params);

		Util::checkEmptyParamString($params, 'CHECK_PERMISSIONS_DEST', 'N');

		$params['IS_CRM'] = (
			!ModuleManager::isModuleInstalled('crm')
				? 'N'
				: ($params['IS_CRM'] ?? 'N')
		);

		$params['SHOW_LOGIN'] = ($params['SHOW_LOGIN'] ?? 'Y');
		$this->useLogin = ($params['SHOW_LOGIN'] !== 'N');

		$params['SHOW_UNREAD'] = ($USER->isAuthorized() && $params['LOG_ID'] <= 0 && $params['MODE'] !== 'LANDING' ? 'Y' : 'N');

		$paramsInstance->prepareRatingParams($params);
		$paramsInstance->prepareRequestVarParams($params);
		$pathInstance->setPaths($params);
		$paramsInstance->prepareRequestParams($params);
		$paramsInstance->prepareNameTemplateParams($params);
		$paramsInstance->prepareAvatarParams($params);
		$paramsInstance->prepareCommentsParams($params);
		$paramsInstance->prepareDestinationParams($params);
		$paramsInstance->prepareCommentPropertyParams($params);
		$paramsInstance->prepareDateTimeFormatParams($params);
		$paramsInstance->prepareCounterParams($params);
		$paramsInstance->preparePageParams($params);
		$paramsInstance->processPresetFilterParams($params);
		$paramsInstance->prepareDateFilterParams($params);
		$this->processParentParams($params);
		$this->getParamsPhotogalleryInstance()->preparePhotogalleryParams($params);
		$paramsInstance->preparePageTitleParams($params);
		$paramsInstance->prepareBehaviourParams($params);
		$paramsInstance->prepareCommentFormParams($params);

		Util::checkEmptyParamString($params, 'PAGER_TITLE', '');

		return $params;
	}

	protected function prepareData()
	{
		global $USER;

		$request = $this->getRequest();
		$processorInstance = $this->getProcessorInstance();
		$logPageProcessorInstance = $this->getLogPageProcessorInstance();
		$counterProcessorInstance = $this->getCounterProcessorInstance();
		$pathsProcessorInstance = $this->getPathInstance();
		$assetsProcessorInstance = $this->getAssetsInstance();

		$result = [];

		if (!$assetsProcessorInstance->checkRefreshNeeded($result))
		{
			return $result;
		}

		$this->getGratitudesInstance()->prepareGratPostFilter($result);

		$result['isExtranetSite'] = $this->getExtranetSiteValue();
		$result['SHOW_FOLLOW_CONTROL'] = 'Y';
		$result['CAN_DELETE'] = \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false);
		$result['ENTITIES_CORRESPONDENCE'] = [];

		$result['PATH_TO_LOG_TAG'] = $pathsProcessorInstance->getFolderUsersValue().'log/?TAG=#tag#';
		if (
			defined('SITE_TEMPLATE_ID')
			&& SITE_TEMPLATE_ID === 'bitrix24'
		)
		{
			$result['PATH_TO_LOG_TAG'] .= '&apply_filter=Y';
		}

		$result['AJAX_CALL'] = (
			!empty($this->arParams['TARGET'])
			|| $request->get('logajax') <> ''
		);
		$result['bReload'] = (
			$result['AJAX_CALL']
			&& (
				$request->get('RELOAD') === 'Y'
				|| (
					isset($this->arParams['RELOAD'])
					&& $this->arParams['RELOAD'] === 'Y'
				)
			)
		);
		$result['SHOW_UNREAD'] = $this->arParams['SHOW_UNREAD'];
		$result['currentUserId'] = (int)$USER->getId();

		$assetsProcessorInstance->getAssetsCheckSum($result);

		$logPageProcessorInstance->preparePrevPageLogId();
		$this->setCurrentUserAdmin(\CSocNetUser::isCurrentUserModuleAdmin());
		$processorInstance->getMicroblogUserId($result);

		$result['TZ_OFFSET'] = \CTimeZone::getOffset();
		$result['FILTER_ID'] = (
		!empty($this->arParams['FILTER_ID'])
			? $this->arParams['FILTER_ID']
			: 'LIVEFEED'.(!empty($this->arParams['GROUP_ID']) ? '_SG'.$this->arParams['GROUP_ID'] : '')
		);

		\CSocNetTools::initGlobalExtranetArrays();

		if (
			$this->arParams['AUTH'] === 'Y'
			|| Util::checkUserAuthorized()
		)
		{
			$result['IS_FILTERED'] = false;

			$processorInstance->prepareContextData($result);
			$this->setTitle([
				'GROUP' => ($result['Group'] ?? []),
			]);

			$result['Events'] = false;

			$processorInstance->processFilterData($result);
			$processorInstance->processNavData($result);
			$processorInstance->processOrderData();
			$counterProcessorInstance->processCounterTypeData($result);
			$processorInstance->processLastTimestamp($result);
			$processorInstance->processListParams($result);
			$logPageProcessorInstance->getLogPageData($result);
			$processorInstance->setListFilter($result);
			$processorInstance->processSelectData($result);

			$this->getEntriesData($result);

			$processorInstance->processFavoritesData($result);
			$processorInstance->processDiskUFEntities();
			$processorInstance->processCrmActivities($result);

			$logPageProcessorInstance->deleteLogPageData($result);

			$processorInstance->processNextPageSize($result);
			$processorInstance->processEventsList($result, 'main');
			$processorInstance->processEventsList($result, 'pinned');

			$processorInstance->warmUpStaticCache($result);

			if (
				$this->arParams['LOG_ID'] > 0
				&& (
					!is_array($result['Events'])
					|| count($result['Events']) <= 0
				)
			)
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SONET_LOG_LIST_ENTRY_NOT_FOUND'));
				return false;
			}

			$processorInstance->processContentList($result);

			$result['WORKGROUPS_PAGE'] = $pathsProcessorInstance->getFolderWorkgroupsValue();

			$counterProcessorInstance->setLogCounter($result);
			$processorInstance->getExpertModeValue($result);
			$logPageProcessorInstance->setLogPageData($result);

			$processorInstance->getUnreadTaskCommentsIdList($result);
			$processorInstance->getResultTaskCommentsIdList($result);

			$counterProcessorInstance->clearLogCounter($result);
			$this->processLogFormComments($result);

			$result['bGetComments'] = $this->getCommentsNeededValue();
			$result['GET_COMMENTS'] = ($this->getCommentsNeededValue() ? 'Y' : 'N');

			$processorInstance->getSmiles($result);
		}
		else
		{
			$result['NEED_AUTH'] = 'Y';
		}

		// compatibility with old component/template
		$this->arParams['SHOW_UNREAD'] = $result['SHOW_UNREAD'];

		return $result;
	}

	protected function getEntriesData(&$result): void
	{
		$result['arLogTmpID'] = [];

		$processorInstance = $this->getProcessorInstance();
		$logPageProcessorInstance = $this->getLogPageProcessorInstance();
		if (
			!$processorInstance
			|| !$logPageProcessorInstance
		)
		{
			return;
		}

		$params = $this->arParams;

		if (empty($result['RETURN_EMPTY_LIST']))
		{
			$queryResultData = $this->getEntryIdList($result);

			if (
				$queryResultData['countAll'] < (int)$params['PAGE_SIZE']
				&& !empty($processorInstance->getFilterKey('>=LOG_UPDATE'))
			)
			{
				$result['arLogTmpID'] = [];
				$processorInstance->setEventsList([]);

				$logPageProcessorInstance->setDateLastPageStart(null);
				$processorInstance->unsetFilterKey('>=LOG_UPDATE');
				$this->getEntryIdList($result);
			}
		}

		$this->getPinnedIdList($result);
	}

	protected function processEvent(&$result, &$cnt, array $eventFields = [], array $options = []): void
	{
		if ($eventFields['MODULE_ID'] === 'crm_shared')
		{
			$eventFields['MODULE_ID'] = 'crm';
		}

		static $timemanInstalled = null;
		static $tasksInstalled = null;
		static $listsInstalled = null;

		if ($timemanInstalled === null)
		{
			$timemanInstalled = ModuleManager::isModuleInstalled('timeman');
		}
		if ($tasksInstalled === null)
		{
			$tasksInstalled = ModuleManager::isModuleInstalled('tasks');
		}
		if ($listsInstalled === null)
		{
			$listsInstalled = ModuleManager::isModuleInstalled('lists');
		}

		if (
			!ModuleManager::isModuleInstalled('bitrix24')
			&& (
				(
					!empty($eventFields['MODULE_ID'])
					&& !ModuleManager::isModuleInstalled($eventFields['MODULE_ID'])
				)
				||
				(
					in_array($eventFields['EVENT_ID'], [ 'timeman_entry', 'report' ])
					&& !$timemanInstalled
				)
				|| (
					$eventFields['EVENT_ID'] === 'tasks'
					&& !$tasksInstalled
				)
				|| (
					$eventFields['EVENT_ID'] === 'lists_new_element'
					&& !$listsInstalled
				)
			)
		)
		{
			return;
		}

		$processorInstance = $this->getProcessorInstance();
		if (!$processorInstance)
		{
			return;
		}

		if ($eventFields['EVENT_ID'] === 'crm_activity_add')
		{
			$activity2LogList = $this->getActivity2LogListValue();
			$activity2LogList[$eventFields['ENTITY_ID']] = $eventFields['ID'];
			$this->setActivity2LogListValue($activity2LogList);
			unset($activity2LogList);
		}
		elseif ($eventFields['EVENT_ID'] === 'tasks')
		{
			$task2LogList = $this->getTask2LogListValue();
			$task2LogList[(int)$eventFields['SOURCE_ID']] = (int)$eventFields['ID'];
			$this->setTask2LogListValue($task2LogList);
			unset($task2LogList);
		}

		$cnt++;
		if (isset($options['type']))
		{
			if ($options['type'] === 'main')
			{
				$result['arLogTmpID'][] = $eventFields['ID'];
				$processorInstance->appendEventsList($eventFields);
			}
			elseif ($options['type'] === 'pinned')
			{
				$contentId = \Bitrix\Socialnetwork\Livefeed\Provider::getContentId($eventFields);

				if (!empty($contentId['ENTITY_TYPE']))
				{
					$postProvider = \Bitrix\Socialnetwork\Livefeed\Provider::init([
						'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
						'ENTITY_ID' => $contentId['ENTITY_ID'],
						'LOG_ID' => $eventFields['ID']
					]);

					if ($postProvider)
					{
						$result['pinnedIdList'][] = $eventFields['ID'];
						$eventFields['PINNED_PANEL_DATA'] = [
							'TITLE' => $postProvider->getPinnedTitle(),
							'DESCRIPTION' => $postProvider->getPinnedDescription()
						];
						$processorInstance->appendEventsList($eventFields, 'pinned');
					}
				}
			}
		}

		$livefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost();

		if (
			(int)$eventFields['SOURCE_ID'] > 0
			&& in_array($eventFields['EVENT_ID'], array_merge($livefeedProvider->getEventId(), ['idea']), true)
		)
		{
			$diskUFEntityList = $this->getDiskUFEntityListValue();
			$diskUFEntityList['BLOG_POST'][] = $eventFields['SOURCE_ID'];
			$this->setDiskUFEntityListValue($diskUFEntityList);
			unset($diskUFEntityList);
		}
		elseif (!in_array($eventFields['EVENT_ID'], [ 'data', 'photo', 'photo_photo', 'bitrix24_new_user', 'intranet_new_user', 'news' ]))
		{
			$diskUFEntityList = $this->getDiskUFEntityListValue();
			$diskUFEntityList['SONET_LOG'][] = $eventFields['ID'];
			$this->setDiskUFEntityListValue($diskUFEntityList);
			unset($diskUFEntityList);
		}
	}

	protected function getEntryIdList(&$result): array
	{
		global $NavNum;

		$returnResult = [
			'countAll' => 0
		];

		$processorInstance = $this->getProcessorInstance();
		if (!$processorInstance)
		{
			return $returnResult;
		}

		if ($processorInstance->getListParamsKey('EMPTY_LIST') === 'Y')
		{
			$result['arLogTmpID'] = [];
			return $returnResult;
		}

		$res = \CSocNetLog::getList(
			$processorInstance->getOrder(),
			$processorInstance->getFilter(),
			false,
			$processorInstance->getNavParams(),
			$processorInstance->getSelect(),
			$processorInstance->getListParams()
		);

		if ($processorInstance->getFirstPage())
		{
			$result['NAV_STRING'] = '';
			$result['PAGE_NAVNUM'] = $NavNum+1;
			$result['PAGE_NAVCOUNT'] = 1000000;
		}
		else
		{
			$navComponentObject = false;
			$result['NAV_STRING'] = $res->getPageNavStringEx($navComponentObject, Loc::getMessage('SONET_LOG_LIST_NAV'), '', false);
			$result['PAGE_NUMBER'] = $res->NavPageNomer;
			$result['PAGE_NAVNUM'] = $res->NavNum;
			$result['PAGE_NAVCOUNT'] = $res->NavPageCount;
		}

		$cnt = 0;
		while ($eventFields = $res->getNext())
		{
			$this->processEvent($result, $cnt, $eventFields, [
				'type' => 'main',
				'pageNumber' => $res->NavPageNomer
			]);
		}

		$returnResult['countAll'] = $res->selectedRowsCount();

		return $returnResult;
	}

	protected function getPinnedIdList(&$result): void
	{
		$result['pinnedEvents'] = [];
		$result['pinnedIdList'] = [];

		if ($result['USE_PINNED'] !== 'Y')
		{
			return;
		}

		$processorInstance = $this->getProcessorInstance();
		if (!$processorInstance)
		{
			return;
		}

		$logUpdateFilterValue = $processorInstance->getFilterKey('>=LOG_UPDATE');
		$processorInstance->unsetFilterKey('>=LOG_UPDATE');

		/* filter without >=LOG_UPDATE field */
		$filter = $processorInstance->getFilter();
		$processorInstance->setFilterKey('>=LOG_UPDATE', $logUpdateFilterValue);

		$filter['PINNED_USER_ID'] = $result['currentUserId'];

		$select = $processorInstance->getSelect();
		unset($select['TMP_ID'], $select['PINNED_USER_ID']);

		$res = \CSocNetLog::getList(
			[
				'PINNED_DATE' => 'DESC'
			],
			$filter,
			false,
			[
				'nTopCount' => 50
			],
			$select,
			[
				'CHECK_RIGHTS' => 'Y',
				'USE_PINNED' => 'Y',
				'USE_FOLLOW' => 'N'
			]
		);
		$cnt = 0;
		while ($eventFields = $res->getNext())
		{
			$this->processEvent($result, $cnt, $eventFields, [
				'type' => 'pinned'
			]);
		}
	}

	protected function processLogFormComments(&$result): void
	{
		global $USER_FIELD_MANAGER;

		$params = $this->arParams;

		if (
			!$result['AJAX_CALL']
			&& empty($this->getErrors())
			&& Util::checkUserAuthorized()
		)
		{
			$cache = new \CPHPCache;
			$cacheId = 'log_form_comments'.serialize($params['COMMENT_PROPERTY']);
			$cachePath = '/sonet/log_form/comments';
			$ttl = (defined('BX_COMP_MANAGED_CACHE') ? 2592000 : 600);

			if ($cache->initCache($ttl, $cacheId, $cachePath))
			{
				$cacheVars = $cache->getVars();
				$result['COMMENT_PROPERTIES'] = $cacheVars['comment_props'];
				$cache->output();
			}
			else
			{
				$cache->startDataCache($ttl, $cacheId, $cachePath);

				$result['COMMENT_PROPERTIES'] = [ 'SHOW' => 'N' ];
				if (
					!empty($params['COMMENT_PROPERTY'])
					&& is_array($params['COMMENT_PROPERTY'])
				)
				{
					$arPostFields = $USER_FIELD_MANAGER->getUserFields('SONET_COMMENT', 0, LANGUAGE_ID);
					foreach ($arPostFields as $fieldName => $fieldData)
					{
						if (!in_array($fieldName, $params['COMMENT_PROPERTY'], true))
						{
							continue;
						}

						$fieldData['EDIT_FORM_LABEL'] = $fieldData['EDIT_FORM_LABEL'] <> '' ? $fieldData['EDIT_FORM_LABEL'] : $fieldData['FIELD_NAME'];
						$fieldData['~EDIT_FORM_LABEL'] = $fieldData['EDIT_FORM_LABEL'];
						$fieldData['EDIT_FORM_LABEL'] = htmlspecialcharsEx($fieldData['EDIT_FORM_LABEL']);
						$result['COMMENT_PROPERTIES']['DATA'][$fieldName] = $fieldData;
					}

					if (!empty($result['COMMENT_PROPERTIES']['DATA']))
					{
						$result['COMMENT_PROPERTIES']['SHOW'] = 'Y';
					}
				}

				$cache->endDataCache([ 'comment_props' => $result['COMMENT_PROPERTIES'] ]);
			}
		}
	}

	public static function getGratitudesIblockId()
	{
		return Gratitude::getGratitudesIblockId();
	}

	public static function getGratitudesIblockData(array $params = []): array
	{
		return LogList\Gratitude::getGratitudesIblockData($params);
	}

	public static function getGratitudesBlogData(array $params = []): array
	{
		return LogList\Gratitude::getGratitudesBlogData($params);
	}
}
