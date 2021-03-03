<?php
namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Socialnetwork\LogViewTable;

class Processor
{
	protected $component;
	protected $request;
	protected $logPageProcessorInstance = null;

	protected $filter = [];
	protected $order = [ 'LOG_DATE' => 'DESC' ];
	protected $select = [];
	protected $listParams = [];
	protected $filterData = false;
	protected $filterContent = false;
	protected $navParams = false;
	protected $firstPage = false;
	protected $eventsList = [];
	protected $tasksCount = 0;
	protected $showPinnedPanel = true;

	public function __construct($params)
	{
		if(!empty($params['component']))
		{
			$this->component = $params['component'];
		}

		if(!empty($params['request']))
		{
			$this->request = $params['request'];
		}
		else
		{
			$this->request = Util::getRequest();;
		}
	}

	public function getRequest()
	{
		return $this->request;
	}

	protected function getComponent()
	{
		return $this->component;
	}

	protected function getLogPageProcessorInstance()
	{
		if ($this->logPageProcessorInstance === null)
		{
			if ($this->getComponent())
			{
				$this->logPageProcessorInstance = $this->getComponent()->getLogPageProcessorInstance();
			}
		}
		return $this->logPageProcessorInstance;
	}

	public function setFilter(array $value = [])
	{
		$this->filter = $value;
	}
	public function getFilter()
	{
		return $this->filter;
	}
	public function setFilterKey($key = '', $value = false)
	{
		if ($key == '')
		{
			return;
		}
		$this->filter[$key] = $value;
	}
	public function unsetFilterKey($key = '')
	{
		if ($key == '')
		{
			return;
		}
		unset($this->filter[$key]);
	}
	public function getFilterKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return (isset($this->filter[$key]) ? $this->filter[$key] : false);
	}

	public function setFilterData(array $value = [])
	{
		$this->filterData = $value;
	}
	public function getFilterData()
	{
		return $this->filterData;
	}
	public function getFilterDataKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return (isset($this->filterData[$key]) ? $this->filterData[$key] : false);
	}

	public function setFilterContent($value = false)
	{
		$this->filterContent = $value;
	}
	public function getFilterContent()
	{
		return $this->filterContent;
	}

	public function setNavParams($value = false)
	{
		$this->navParams = $value;
	}
	public function getNavParams()
	{
		return $this->navParams;
	}

	public function setFirstPage($value = false)
	{
		$this->firstPage = $value;
	}
	public function getFirstPage()
	{
		return $this->firstPage;
	}

	public function setOrder(array $value = [])
	{
		$this->order = $value;
	}
	public function setOrderKey($key = '', $value = false)
	{
		if ($key == '')
		{
			return;
		}
		$this->order[$key] = $value;
	}
	public function getOrder()
	{
		return $this->order;
	}
	public function getOrderKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return (isset($this->order[$key]) ? $this->order[$key] : false);
	}

	public function setListParams(array $value = [])
	{
		$this->listParams = $value;
	}
	public function setListParamsKey($key = '', $value = false)
	{
		if ($key == '')
		{
			return;
		}
		$this->listParams[$key] = $value;
	}
	public function getListParams()
	{
		return $this->listParams;
	}
	public function getListParamsKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return (isset($this->listParams[$key]) ? $this->listParams[$key] : false);
	}

	public function setEventsList(array $value = [], $type = 'main')
	{
		if (!isset($this->eventsList[$type]))
		{
			$this->eventsList[$type] = [];
		}
		$this->eventsList[$type] = $value;
	}
	public function setEventsListKey($key = '', array $value = [], $type = 'main')
	{
		if ($key == '')
		{
			return;
		}

		if (!isset($this->eventsList[$type]))
		{
			$this->eventsList[$type] = [];
		}

		$this->eventsList[$type][$key] = $value;
	}
	public function appendEventsList(array $value = [], $type = 'main')
	{
		if (!isset($this->eventsList[$type]))
		{
			$this->eventsList[$type] = [];
		}

		$this->eventsList[$type][] = $value;
	}
	public function unsetEventsListKey($key = '', $type = 'main')
	{
		if ($key == '')
		{
			return;
		}

		if (!isset($this->eventsList[$type]))
		{
			return;
		}

		unset($this->eventsList[$type][$key]);
	}
	public function getEventsList($type = 'main')
	{
		$result = [];

		if (!isset($this->eventsList[$type]))
		{
			return $result;
		}

		return $this->eventsList[$type];
	}

	public function incrementTasksCount()
	{
		$this->tasksCount++;
	}
	public function getTasksCount()
	{
		return $this->tasksCount;
	}

	public function setSelect($value = [])
	{
		$this->select = $value;
	}
	public function getSelect()
	{
		return $this->select;
	}

	public function makeTimeStampFromDateTime($value, $type = 'FULL')
	{
		static $siteDateFormatShort = null;
		static $siteDateFormatFull = null;

		if ($siteDateFormatShort === null)
		{
			$siteDateFormatShort = \CSite::getDateFormat('SHORT');
		}
		if ($siteDateFormatFull === null)
		{
			$siteDateFormatFull = \CSite::getDateFormat('FULL');
		}

		return makeTimeStamp($value, ($type == 'SHORT' ? $siteDateFormatShort : $siteDateFormatFull));
	}

	public function prepareContextData(&$result)
	{
		$params = $this->getComponent()->arParams;

		if (
			$params['SET_TITLE'] == 'Y'
			|| $params['SET_NAV_CHAIN'] != 'N'
			|| $params['GROUP_ID'] > 0
		)
		{
			if ($params['ENTITY_TYPE'] == SONET_ENTITY_USER)
			{
				$res = \CUser::getById($params['USER_ID']);
				$result['User'] = $res->fetch();
			}
			elseif ($params['ENTITY_TYPE'] == SONET_ENTITY_GROUP)
			{
				$result['Group'] = \CSocNetGroup::getById($params['GROUP_ID']);
				if (
					$result['Group']['OPENED'] == 'Y'
					&& Util::checkUserAuthorized()
					&& !$this->getComponent()->getCurrentUserAdmin()
					&& !in_array(\CSocNetUserToGroup::getUserRole($result['currentUserId'], $result['Group']['ID']), \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember())
				)
				{
					$result['Group']['READ_ONLY'] = 'Y';
				}
			}
		}
	}

	public function processFilterData(&$result)
	{
		global $USER;

		$params = $this->getComponent()->arParams;

		if ($params['LOG_ID'] > 0)
		{
			$this->setFilterKey('ID', $params['LOG_ID']);
			$this->showPinnedPanel = false;
		}

		$turnFollowModeOff = false;

		if(isset($params['DISPLAY']))
		{
			$result['SHOW_UNREAD'] = 'N';

			if(in_array($params['DISPLAY'], [ 'forme', 'my']))
			{
				$accessCodesList = $USER->getAccessCodes();
				foreach($accessCodesList as $i => $code)
				{
					if(!preg_match('/^(U|D|DR)/', $code)) //Users and Departments
					{
						unset($accessCodesList[$i]);
					}
				}
				$this->setFilterKey('LOG_RIGHTS', $accessCodesList);
			}

			if($params['DISPLAY'] === 'forme')
			{
				$this->setFilterKey('!USER_ID', $result['currentUserId']);
			}
			elseif($params['DISPLAY'] === 'mine')
			{
				$this->setFilterKey('USER_ID', $result['currentUserId']);
			}
			elseif($params['DISPLAY'] > 0)
			{
				$this->setFilterKey('USER_ID', intval($params['DISPLAY']));
			}

			if(
				in_array($params['DISPLAY'], [ 'forme', 'mine'])
				|| $params['DISPLAY'] > 0
			)
			{
				$result['IS_FILTERED'] = true;
			}
			$this->showPinnedPanel = false;
		}

		if (
			!empty($params['DESTINATION'])
			&& is_array($params['DESTINATION'])
		)
		{
			$this->setFilterKey('LOG_RIGHTS', $params['DESTINATION']);
			if (count($params['DESTINATION']) == 1)
			{
				$code = array_shift($params['DESTINATION']);
				if(preg_match('/^U(\d+)$/', $code, $matches))
				{
					$this->setFilterKey('!USER_ID', $matches[1]);
				}
			}

			if (
				$params['MODE'] == 'LANDING'
				&& !empty($params['DESTINATION_AUTHOR_ID'])
				&& intval($params['DESTINATION_AUTHOR_ID']) > 0
			) // landing author filter
			{
				$this->setFilterKey('USER_ID', intval($params['DESTINATION_AUTHOR_ID']));
			}
		}
		elseif ($params['GROUP_ID'] > 0)
		{
			$this->setFilterKey('LOG_RIGHTS', 'SG'.$params['GROUP_ID']);

			if (
				isset($result['Group'])
				&& $result['Group']['OPENED'] == 'Y'
			)
			{
				$this->setFilterKey('LOG_RIGHTS_SG', 'OSG'.$params['GROUP_ID'].'_'.(Util::checkUserAuthorized() ? SONET_ROLES_AUTHORIZED : SONET_ROLES_ALL));
			}

			$result['SHOW_FOLLOW_CONTROL'] = 'N';
			$result['SHOW_UNREAD'] = 'N';
			$this->showPinnedPanel = false;
		}
		elseif ($params['TO_USER_ID'] > 0)
		{
			$this->setFilterKey('LOG_RIGHTS', 'U'.$params['TO_USER_ID']);
			$this->setFilterKey('!USER_ID', $params['TO_USER_ID']);

			$result['SHOW_FOLLOW_CONTROL'] = 'N';

			$res = UserTable::getList([
				'filter' => [
					'=ID' => $params['TO_USER_ID']
				],
				'select' => [ 'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN' ]
			]);
			if ($userFields = $res->fetch())
			{
				$result['ToUser'] = [
					'ID' => $userFields['ID'],
					'NAME' => \CUser::formatName($params['NAME_TEMPLATE'], $userFields, $this->getComponent()->useLogin)
				];
			}
		}
		elseif ($params['USER_ID'] > 0)
		{
			$this->setFilterKey('ENTITY_TYPE', SONET_ENTITY_USER);
			$this->setFilterKey('ENTITY_ID',  $params['USER_ID']);
		}
		elseif ($params['ENTITY_TYPE'] <> '')
		{
			$this->setFilterKey('ENTITY_TYPE', $params['ENTITY_TYPE']);
		}

		if ($params['TAG'] <> '')
		{
			$this->setFilterKey('=TAG', $params['TAG']);
			$turnFollowModeOff = true;
		}
		elseif ($params['FIND'] <> '')
		{
			$operation = \Bitrix\Socialnetwork\LogIndexTable::getEntity()->fullTextIndexEnabled('CONTENT') ? '*' : '*%';
			$this->setFilterKey($operation.'CONTENT', \Bitrix\Socialnetwork\Item\LogIndex::prepareToken($params['FIND']));
			/*
			$this->setListParamsKey('FILTER_BY_CONTENT', [
				$operation.'CONTENT' => \Bitrix\Socialnetwork\Item\LogIndex::prepareToken($params['FIND'])
			]);
			*/
			$turnFollowModeOff = true;
			$this->showPinnedPanel = false;
		}

		if (
			isset($params['!EXACT_EVENT_ID'])
			&& $params['!EXACT_EVENT_ID'] <> ''
		)
		{
			$this->setFilterKey('!EVENT_ID', $params['!EXACT_EVENT_ID']);
			$turnFollowModeOff = true;
		}

		if (
			isset($params['EXACT_EVENT_ID'])
			&& $params['EXACT_EVENT_ID'] <> ''
		)
		{
			$this->setFilterKey('EVENT_ID', [ $params['EXACT_EVENT_ID'] ]);
			$turnFollowModeOff = true;
		}
		elseif (is_array($params['EVENT_ID']))
		{
			if (!in_array('all', $params['EVENT_ID']))
			{
				$eventIdList = [];
				foreach($params['EVENT_ID'] as $eventId)
				{
					$eventIdList = array_merge($eventIdList, \CSocNetLogTools::findFullSetByEventID($eventId));
				}

				if (!empty($eventIdList))
				{
					$this->setFilterKey('EVENT_ID', array_unique($eventIdList));
				}
				$turnFollowModeOff = true;
			}
		}
		elseif ($params['EVENT_ID'] <> '')
		{
			$this->setFilterKey('EVENT_ID', \CSocNetLogTools::findFullSetByEventID($params['EVENT_ID']));
			$turnFollowModeOff = true;
		}
		elseif ($this->getComponent()->getPresetFilterIdValue() === 'extranet')
		{
			$turnFollowModeOff = true;
		}

		if ($params['CREATED_BY_ID'] > 0)
		{
			if ($this->getComponent()->getCommentsNeededValue())
			{
				$this->setFilterKey('USER_ID|COMMENT_USER_ID', $params['CREATED_BY_ID']);
			}
			else
			{
				$this->setFilterKey('USER_ID', $params['CREATED_BY_ID']);
			}
			$this->unsetFilterKey('!USER_ID');
			$turnFollowModeOff = true;
		}

		if ($params['GROUP_ID'] > 0)
		{
			$result['IS_FILTERED'] = true;
		}

		if (
			isset($params['FLT_ALL'])
			&& $params['FLT_ALL'] === 'Y'
		)
		{
			$this->setFilterKey('ALL', 'Y');
		}

		if (isset($params['FILTER_SITE_ID']))
		{
			$this->setFilterKey('SITE_ID', $params['FILTER_SITE_ID']);
		}
		elseif ($params['MODE'] !== 'LANDING')
		{
			$this->setFilterKey('SITE_ID', (
				$result['isExtranetSite']
					? SITE_ID
					: [ SITE_ID, false ]
			));
		}

		if (
			isset($params['LOG_DATE_FROM'])
			&& $params['LOG_DATE_FROM'] <> ''
			&& $this->makeTimeStampFromDateTime($params['LOG_DATE_FROM'], 'SHORT') < time() + $result['TZ_OFFSET']
		)
		{
			$this->setFilterKey('>=LOG_DATE', $params['LOG_DATE_FROM']);
			$turnFollowModeOff = true;
		}
		else
		{
			unset($_REQUEST['flt_date_from']);
		}

		if (
			isset($params['LOG_DATE_TO'])
			&& $params['LOG_DATE_TO'] <> ''
			&& $this->makeTimeStampFromDateTime($params['LOG_DATE_TO'], 'SHORT') < time() + $result['TZ_OFFSET']
		)
		{
			$this->setFilterKey('<=LOG_DATE', convertTimeStamp($this->makeTimeStampFromDateTime($params['LOG_DATE_TO'], 'SHORT')+86399, 'FULL'));
			$turnFollowModeOff = true;
		}
		else
		{
			$this->setFilterKey('<=LOG_DATE', 'NOW');
			unset($_REQUEST['flt_date_to']);
		}

		$this->processMainUIFilterData($result);

		if ($params['IS_CRM'] === 'Y')
		{
			if (Loader::includeModule('crm'))
			{
				$result['CRM_ENTITY_TYPE_NAME'] = \CCrmOwnerType::resolveName(\CCrmLiveFeedEntity::resolveEntityTypeID($params['CRM_ENTITY_TYPE']));
				$result['CRM_ENTITY_ID'] = $params['CRM_ENTITY_ID'];
			}

			if (
				$params['CRM_ENTITY_TYPE'] <> ''
				|| $this->getComponent()->getPresetFilterTopIdValue()
			)
			{
				$result['SHOW_UNREAD'] = 'N';
			}
			$this->showPinnedPanel = false;
		}

		$result['presetFilterTopIdValue'] = $this->getComponent()->getPresetFilterTopIdValue();
		$result['presetFilterIdValue'] = $this->getComponent()->getPresetFilterIdValue();

		if (
			(
				!isset($params['USE_FAVORITES'])
				|| $params['USE_FAVORITES'] !== 'N'
			)
			&& isset($params['FAVORITES'])
			&& $params['FAVORITES'] === 'Y'
		)
		{
			$this->setFilterKey('>FAVORITES_USER_ID', 0);
			$result['SHOW_UNREAD'] = 'N';
		}

		if ($turnFollowModeOff)
		{
			$result['SHOW_UNREAD'] = 'N';
			$result['SHOW_FOLLOW_CONTROL'] = 'N';
			$result['IS_FILTERED'] = true;
		}

		if (
			$params["IS_CRM"] !== "Y"
			&& !\Bitrix\Socialnetwork\ComponentHelper::checkLivefeedTasksAllowed()
		)
		{
			$eventIdFilter = $this->getFilterKey('EVENT_ID');
			$notEventIdFilter = $this->getFilterKey('!EVENT_ID');

			if (empty($notEventIdFilter))
			{
				$notEventIdFilter = [];
			}
			elseif(!is_array($notEventIdFilter))
			{
				$notEventIdFilter = [ $notEventIdFilter ];
			}

			if (empty($eventIdFilter))
			{
				$eventIdFilter = [];
			}
			elseif(!is_array($eventIdFilter))
			{
				$eventIdFilter = [ $eventIdFilter ];
			}

			if (ModuleManager::isModuleInstalled('tasks'))
			{
				$notEventIdFilter = array_merge($notEventIdFilter, [ 'tasks' ]);
				$eventIdFilter = array_filter($eventIdFilter, function($eventId) { return ($eventId !== 'tasks'); });
			}
			if (
				ModuleManager::isModuleInstalled('crm')
				&& Option::get('crm', 'enable_livefeed_merge', 'N') === 'Y'
			)
			{
				$notEventIdFilter = array_merge($notEventIdFilter, [ 'crm_activity_add' ]);
				$eventIdFilter = array_filter($eventIdFilter, function($eventId) { return ($eventId !== 'crm_activity_add'); });
			}

			if (!empty($notEventIdFilter))
			{
				$this->setFilterKey('!EVENT_ID', $notEventIdFilter);
			}
			$this->setFilterKey('EVENT_ID', $eventIdFilter);
		}

		$result['USE_PINNED'] = 'N';
		$result['SHOW_PINNED_PANEL'] = 'N';

		if (
			$result['currentUserId'] > 0
			&& $params['MODE'] !== 'LANDING'
		)
		{
			$result['USE_PINNED'] = 'Y';

			if ($this->showPinnedPanel)
			{
				$this->setFilterKey('PINNED_USER_ID', 0);
				$result['SHOW_PINNED_PANEL'] = 'Y';
			}
		}
	}

	protected function processMainUIFilterData(&$result)
	{
		$request = $this->getRequest();
		$params = $this->getComponent()->arParams;

		if (
			(
				(
					defined('SITE_TEMPLATE_ID')
					&& SITE_TEMPLATE_ID === 'bitrix24'
				)
				|| (
					isset($params['siteTemplateId'])
					&& in_array($params['siteTemplateId'], [ 'bitrix24', 'landing24' ])
				)
			)
			&& (
				$request->get('useBXMainFilter') === 'Y'
				|| $params['useBXMainFilter'] === 'Y'
			)
			&& (int)$params['LOG_ID'] <= 0
		)
		{
			$filtered = false;
			$filterOption = new \Bitrix\Main\UI\Filter\Options($result['FILTER_ID']);
			$filterData = $filterOption->getFilter();
			$this->setFilterData($filterData);

			if (
				!empty($filterData['GROUP_ID'])
				&& preg_match('/^SG(\d+)$/', $filterData['GROUP_ID'], $matches)
			)
			{
				$this->setFilterKey('LOG_RIGHTS', 'SG'.intval($matches[1]));
			}

			if (
				!empty($filterData['AUTHOR_ID'])
				&& preg_match('/^U(\d+)$/', $filterData['AUTHOR_ID'], $matches)
			)
			{
				$this->setFilterKey('USER_ID', intval($matches[1]));
			}

			if (
				!empty($filterData['CREATED_BY_ID'])
				&& preg_match('/^U(\d+)$/', $filterData['CREATED_BY_ID'], $matches)
			)
			{
				$filtered = true;
				$this->setFilterKey('USER_ID', intval($matches[1]));
			}

			if (!empty($filterData['TO']))
			{
				if (preg_match('/^U(\d+)$/', $filterData['TO'], $matches))
				{
					$this->setFilterKey('LOG_RIGHTS', 'U'.intval($matches[1]));
					if (empty($this->getFilterKey('USER_ID')))
					{
						$this->setFilterKey('!USER_ID', intval($matches[1]));
					}
				}
				elseif (preg_match('/^SG(\d+)$/', $filterData['TO'], $matches))
				{
					$this->setFilterKey('LOG_RIGHTS', 'SG'.intval($matches[1]));
				}
				elseif (preg_match('/^DR(\d+)$/', $filterData['TO'], $matches))
				{
					$this->setFilterKey('LOG_RIGHTS', 'DR'.intval($matches[1]));
				}
				elseif ($filterData['TO'] == 'UA')
				{
					$this->setFilterKey('LOG_RIGHTS', 'G2');
				}

				$filtered = !empty($this->getFilterKey('LOG_RIGHTS'));
			}

			if (
				!empty($filterData['EXACT_EVENT_ID'])
				&& !is_array($filterData['EXACT_EVENT_ID'])
			)
			{
				$filtered = true;
				$this->setFilterKey('EVENT_ID', [ $filterData['EXACT_EVENT_ID'] ]);
			}

			if (
				!empty($filterData['EVENT_ID'])
				&& is_array($filterData['EVENT_ID'])
			)
			{
				$filtered = true;
				$this->setFilterKey('EVENT_ID', []);

				$eventIdFilterValue = $this->getFilterKey('EVENT_ID');
				foreach($filterData['EVENT_ID'] as $filterEventId)
				{
					// if specific blog_post event (important, vote, grat)
					if (in_array($filterEventId, [ 'blog_post_important', 'blog_post_grat', 'blog_post_vote' ]))
					{
						$eventIdFilterValue[] = $filterEventId;
					}
					else
					{
						$eventIdFilterValue = array_merge($eventIdFilterValue, \CSocNetLogTools::findFullSetByEventID($filterEventId));
					}
				}
				$this->setFilterKey('EVENT_ID',  array_unique($eventIdFilterValue));
			}

			if (
				!empty($filterData['FAVORITES_USER_ID'])
				&& $filterData['FAVORITES_USER_ID'] == 'Y'
			)
			{
				$filtered = true;
				$this->setFilterKey('>FAVORITES_USER_ID',  0);
			}

			if (!empty(trim($filterData['TAG'])))
			{
				$filtered = true;
				$this->setFilterKey('=TAG',  trim($filterData['TAG']));
			}

			$this->setFilterContent(trim($filterData['FIND']));
			if (!empty($this->getFilterContent()))
			{
				$filtered = true;

				$operation = \Bitrix\Socialnetwork\LogIndexTable::getEntity()->fullTextIndexEnabled('CONTENT') ? '*' : '*%';
				$this->setFilterKey($operation.'CONTENT',  \Bitrix\Socialnetwork\Item\LogIndex::prepareToken($this->getFilterContent()));
				/*
				$this->setListParamsKey('FILTER_BY_CONTENT', [
					$operation.'CONTENT' => \Bitrix\Socialnetwork\Item\LogIndex::prepareToken($this->getFilterContent())
				]);
				*/
			}

			if (
				!empty($filterData['EXTRANET'])
				&& $filterData['EXTRANET'] == 'Y'
				&& Loader::includeModule('extranet')
			)
			{
				$filtered = true;
				$this->setFilterKey('SITE_ID',  \CExtranet::getExtranetSiteID());
				$this->setFilterKey('!EVENT_ID',  [ 'lists_new_element', 'tasks', 'timeman_entry', 'report', 'crm_activity_add' ]);
			}

			if (!empty($filterData['DATE_CREATE_from']))
			{
				$filtered = true;
				if (!empty($this->getFilterContent()))
				{
					$this->setFilterKey('>=CONTENT_DATE_CREATE', $filterData['DATE_CREATE_from']);
//					$this->listParams['FILTER_BY_CONTENT_DATE']['>=DATE_CREATE'] = $filterData['DATE_CREATE_from'];
				}
				else
				{
					$this->setFilterKey('>=LOG_DATE', $filterData['DATE_CREATE_from']);
				}
			}

			if (!empty($filterData['DATE_CREATE_to']))
			{
				$filtered = true;
				$dateCreateToValue = convertTimeStamp($this->makeTimeStampFromDateTime($filterData['DATE_CREATE_to'], 'SHORT') + 86399, 'FULL');

				if (!empty($this->getFilterContent()))
				{
					$this->setFilterKey('<=CONTENT_DATE_CREATE', $dateCreateToValue);
//					$this->listParams['FILTER_BY_CONTENT_DATE']['<=DATE_CREATE'] = $dateCreateToValue;
				}
				else
				{
					$this->setFilterKey('<=LOG_DATE', $dateCreateToValue);
				}
			}

			if ($filtered)
			{
				// extraordinal case, we cannot set arParams earlier
				$params['SET_LOG_COUNTER'] = 'N';
				$params['SET_LOG_PAGE_CACHE'] = 'N';
				$params['USE_FOLLOW'] = 'N';
				$params['SHOW_UNREAD'] = 'N';

				$this->getComponent()->arParams = $params;

				$result['SHOW_UNREAD'] = 'N';
				$result['IS_FILTERED'] = true;
				$this->showPinnedPanel = false;
			}
		}
		elseif (
			(
				defined('SITE_TEMPLATE_ID')
				&& SITE_TEMPLATE_ID === 'bitrix24'
			)
			|| $params['MODE'] == 'LANDING'
		)
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($result['FILTER_ID']);
			$filterOption->reset();
		}

		if (
			(
				$params['TAG'] !== ''
				|| $params['FIND'] !== ''
			)
			&& $this->getRequest()->get('apply_filter') === 'Y'
		)
		{
			$this->getComponent()->arParams['useBXMainFilter'] = 'Y';
		}
	}

	public function processNavData(&$result)
	{
		global $NavNum;

		$request = $this->getRequest();
		$params = $this->getComponent()->arParams;

		$this->setNavParams([
			'nPageSize' => $params['PAGE_SIZE'],
			'bShowAll' => false,
			'iNavAddRecords' => 1,
			'bSkipPageReset' => true,
			'nRecordCount' => 1000000
		]);
		if ($params['LOG_CNT'] > 0)
		{
			$this->setNavParams([
				'nTopCount' => $params['LOG_CNT']
			]);
			$result['PAGE_NUMBER'] = 1;
			$this->setFirstPage(true);
		}
		elseif (
			!$result['AJAX_CALL']
			|| $result['bReload']
		)
		{
			$this->setNavParams([
				'nTopCount' => $params['PAGE_SIZE']
			]);
			$result['PAGE_NUMBER'] = 1;
			$this->setFirstPage(true);
		}
		elseif (intval($request->get('PAGEN_'.($NavNum + 1))) > 0)
		{
			$result['PAGE_NUMBER'] = intval($request->get('PAGEN_'.($NavNum + 1)));
		}
		elseif (intval($params['PAGE_NUMBER']) > 0)
		{
			$result['PAGE_NUMBER'] = intval($params['PAGE_NUMBER']);
			$navParams = $this->getNavParams();
			$navParams['iNumPage'] = $result['PAGE_NUMBER'];
			$this->setNavParams($navParams);
		}
	}

	public function processOrderData()
	{
		$params = $this->getComponent()->arParams;

		if (
			!empty($params['ORDER'])
			&& is_array($params['ORDER'])
		)
		{
			$this->setOrder($params['ORDER']);
		}
		elseif ($this->getComponent()->getCommentsNeededValue())
		{
			$this->setOrder(
				!empty($this->getFilterContent())
					? []
					: [ 'LOG_UPDATE' => 'DESC' ]
			);
		}
		elseif ($params['USE_FOLLOW'] == 'Y')
		{
			$this->setOrder([ 'DATE_FOLLOW' => 'DESC' ]);
		}
		elseif ($params['USE_COMMENTS'] == 'Y')
		{
			$this->setOrder(
				!empty($this->getFilterContent())
					? [ 'CONTENT_LOG_UPDATE' => 'DESC' ]
					: [ 'LOG_UPDATE' => 'DESC' ]
			);
//			$this->setOrder(!empty($this->->getProcessorInstance()->getFilterContent()) ? [] : [ 'LOG_UPDATE' => 'DESC' ]);
		}

		$this->setOrderKey('ID', 'DESC');
		$order = $this->getOrder();
		$res = getModuleEvents('socialnetwork', 'OnBuildSocNetLogOrder');
		while ($eventFields = $res->fetch())
		{
			executeModuleEventEx($eventFields, [ &$order, $params ]);
		}
		$this->setOrder($order);
	}

	public function processLastTimestamp(&$result)
	{
		$request = $this->getRequest();
		$params = $this->getComponent()->arParams;

		$result['LAST_LOG_TS'] = (isset($params['LAST_LOG_TIMESTAMP']) ? intval($params['LAST_LOG_TIMESTAMP']) : intval($request->get('ts')));

		if (
			$params['LOG_ID'] <= 0
			&& (
				!$result['AJAX_CALL']
				|| $result['bReload']
			)
		)
		{
			$result['LAST_LOG_TS'] = \CUserCounter::getLastDate($result['currentUserId'], $result['COUNTER_TYPE']);

			if($result['LAST_LOG_TS'] == 0)
			{
				$result['LAST_LOG_TS'] = 1;
			}
			else
			{
				//We substruct TimeZone offset in order to get server time
				//because of template compatibility
				$result['LAST_LOG_TS'] -= $result['TZ_OFFSET'];
			}
		}
	}

	public function processListParams(&$result)
	{
		$params = $this->getComponent()->arParams;

		if ($params['IS_CRM'] == 'Y')
		{
			$this->setListParams([
				'IS_CRM' => 'Y',
				'CHECK_CRM_RIGHTS' => 'Y'
			]);

			$filterParams = [
				'ENTITY_TYPE' => $params['CRM_ENTITY_TYPE'],
				'ENTITY_ID' => $params['CRM_ENTITY_ID'],
				'AFFECTED_TYPES' => [],
				'OPTIONS' => [
					'CUSTOM_DATA' => (
					isset($params['CUSTOM_DATA'])
					&& is_array($params['CUSTOM_DATA'])
						? $params['CUSTOM_DATA']
						: []
					)
				]
			];

			$res = getModuleEvents('socialnetwork', 'OnBuildSocNetLogFilter'); // crm handler used
			while ($eventFields = $res->fetch())
			{
				$filter = $this->getFilter();
				executeModuleEventEx($eventFields, [ &$filter, &$filterParams, &$params ]);
				$this->setFilter($filter);
				$this->getComponent()->arParams = $params;
			}

			$this->listParams['CUSTOM_FILTER_PARAMS'] = $filterParams;
		}
		else
		{
			if (
				ModuleManager::isModuleInstalled('crm')
				&& $params['PUBLIC_MODE'] != 'Y'
			)
			{
				$this->setFilterKey('!MODULE_ID', (  // can't use !@MODULE_ID because of null
					Option::get('crm', 'enable_livefeed_merge', 'N') == 'Y'
					|| (
						!empty($this->getFilterKey('LOG_RIGHTS'))
						&& !is_array($this->getFilterKey('LOG_RIGHTS'))
						&& preg_match('/^SG(\d+)$/', $this->getFilterKey('LOG_RIGHTS'), $matches)
					)
						? [ 'crm']
						: [ 'crm', 'crm_shared' ]
				));
			}

			$this->setListParamsKey('CHECK_RIGHTS', ($params['MODE'] != 'LANDING' ? 'Y' : 'N'));

			if (
				$params['MODE'] != 'LANDING'
				&& $params['LOG_ID'] <= 0
				&& empty($this->getFilterDataKey('EVENT_ID'))
			)
			{
				$this->setListParamsKey('CHECK_VIEW', 'Y');
			}
		}

		if (
			$params['USE_FOLLOW'] != 'N'
			&& !ModuleManager::isModuleInstalled('intranet')
			&& Util::checkUserAuthorized()
		) // BSM
		{
			$result['USE_SMART_FILTER'] = 'Y';
			$this->setListParamsKey('MY_GROUPS_ONLY', (
				\CSocNetLogSmartFilter::getDefaultValue($result['currentUserId']) == 'Y'
					? 'Y'
					: 'N'
			));
		}

		if (
			$result['isExtranetSite']
			|| $this->getComponent()->getPresetFilterIdValue() == 'extranet'
			|| $this->getFilterDataKey('EXTRANET') == 'Y'
		)
		{
			$this->setListParamsKey('MY_GROUPS_ONLY', 'Y');
		}

		$result['MY_GROUPS_ONLY'] = $this->getListParamsKey('MY_GROUPS_ONLY');

		if ($this->getComponent()->getCurrentUserAdmin())
		{
			$this->setListParamsKey('USER_ID', 'A');
		}

		if ($params['USE_FOLLOW'] === 'Y')
		{
			$this->setListParamsKey('USE_FOLLOW', 'Y');
		}
		else
		{
			$this->setListParamsKey('USE_FOLLOW', 'N');
			$this->setListParamsKey('USE_SUBSCRIBE', 'N');
		}

		if (
			isset($params['USE_FAVORITES'])
			&& $params['USE_FAVORITES'] === 'N'
		)
		{
			$this->setListParamsKey('USE_FAVORITES', 'N');
		}

		if (
			empty($result['RETURN_EMPTY_LIST'])
			&& !empty($params['EMPTY_EXPLICIT'])
			&& $params['EMPTY_EXPLICIT'] === 'Y'
		)
		{
			$this->setListParamsKey('EMPTY_LIST', 'Y');
		}

		if ($result['USE_PINNED'] === 'Y')
		{
			$this->setListParamsKey('USE_PINNED', 'Y');
		}
	}

	public function setListFilter(array $componentResult = [])
	{
		if (!empty($componentResult['GRAT_POST_FILTER']))
		{
			$this->setFilterKey('EVENT_ID', 'blog_post_grat');
			$this->setFilterKey('SOURCE_ID', $componentResult['GRAT_POST_FILTER']);
		}
	}

	public function processSelectData(&$result)
	{
		$params = $this->getComponent()->arParams;

		$select = [
			'ID', 'TMP_ID', 'MODULE_ID',
			'LOG_DATE', 'LOG_UPDATE', 'DATE_FOLLOW',
			'ENTITY_TYPE', 'ENTITY_ID', 'EVENT_ID', 'SOURCE_ID', 'USER_ID', 'FOLLOW',
			'RATING_TYPE_ID', 'RATING_ENTITY_ID',
			'LOG_DATE_TS',
		];

		if (
			!isset($params['USE_FAVORITES'])
			|| $params['USE_FAVORITES'] != 'N'
		)
		{
			$select[] = 'FAVORITES_USER_ID';
		}

		if ($result['currentUserId'] > 0)
		{
			$select[] = 'PINNED_USER_ID';
		}

		$this->setSelect($select);
	}

	public function processDiskUFEntities()
	{
		$diskUFEntityList = $this->getComponent()->getDiskUFEntityListValue();
		if (
			!empty($diskUFEntityList['SONET_LOG'])
			|| !empty($diskUFEntityList['BLOG_POST'])
		)
		{
			$res = getModuleEvents('socialnetwork', 'OnAfterFetchDiskUfEntity');
			while ($eventFields = $res->fetch())
			{
				executeModuleEventEx($eventFields, [ $diskUFEntityList ]);
			}
		}
	}

	public function processCrmActivities($result)
	{
		$activity2LogList = $this->getComponent()->getActivity2LogListValue();

		if (
			!empty($activity2LogList)
			&& Loader::includeModule('crm')
			&& Loader::includeModule('tasks')
		)
		{
			$res = \CCrmActivity::getList(
				[],
				[
					'@ID' => array_keys($activity2LogList),
					'TYPE_ID' => \CCrmActivityType::Task,
					'CHECK_PERMISSIONS' => 'N'
				],
				false,
				false,
				[ 'ID', 'ASSOCIATED_ENTITY_ID' ]
			);
			while(
				($activityFields = $res->fetch())
				&& ((int)$activityFields['ASSOCIATED_ENTITY_ID'] > 0)
			)
			{
				try
				{
					$taskItem = new \CTaskItem((int)$activityFields['ASSOCIATED_ENTITY_ID'], $result['currentUserId']);
					if (!$taskItem->checkCanRead())
					{
						$activity2LogList = $this->getComponent()->getActivity2LogListValue();
						unset($activity2LogList[$activityFields['ID']]);
						$this->getComponent()->setActivity2LogListValue($activity2LogList);
						unset($activity2LogList);
					}
				}
				catch (\CTaskAssertException $e)
				{
				}
			}
		}
	}

	public function processNextPageSize(&$result)
	{
		$request = $this->getRequest();
		$params = $this->getComponent()->arParams;
		$filter = $this->getFilter();

		$result['NEXT_PAGE_SIZE'] = 0;

		if (
			count($result['arLogTmpID']) < $params['PAGE_SIZE']
			&& isset($filter['>=LOG_UPDATE'])
		)
		{
			$result['NEXT_PAGE_SIZE'] = count($result['arLogTmpID']);
		}
		elseif (intval($request->get('pagesize')) > 0)
		{
			$result['NEXT_PAGE_SIZE'] = intval($request->get('pagesize'));
		}
	}

	public function processContentList(&$result)
	{
		$contentIdList = [];
		if (is_array($result['Events']))
		{
			foreach ($result['Events'] as $key => $eventFields)
			{
				if ($contentId = Livefeed\Provider::getContentId($eventFields))
				{
					$contentIdList[] = $result['Events'][$key]['CONTENT_ID'] = $contentId['ENTITY_TYPE'].'-'.$contentId['ENTITY_ID'];
				}
			}
		}

		$result['ContentViewData'] = (
			!empty($contentIdList)
				? \Bitrix\Socialnetwork\Item\UserContentView::getViewData([
					'contentId' => $contentIdList
				])
				: []
		);
	}

	public function processEventsList(&$result, $type = 'main')
	{
		$params = $this->getComponent()->arParams;
		$activity2LogList = $this->getComponent()->getActivity2LogListValue();

		$eventsList = $this->getEventsList($type);

		$prevPageLogIdList = [];
		if ($type == 'main')
		{
			$logPageProcessorInstance = $this->getLogPageProcessorInstance();
			if (!$logPageProcessorInstance)
			{
				return;
			}

			$prevPageLogIdList = $logPageProcessorInstance->getPrevPageLogIdList();
		}

		foreach ($eventsList as $key => $eventFields)
		{
			if (
				$eventFields['EVENT_ID'] == 'crm_activity_add'
				&& !empty($activity2LogList)
				&& !in_array($eventFields['ID'], $activity2LogList)
			)
			{
				$this->unsetEventsListKey($key);
			}
			elseif (
				empty($prevPageLogIdList)
				|| !in_array($eventFields['ID'], $prevPageLogIdList)
			)
			{
				$eventFields['EVENT_ID_FULLSET'] = \CSocNetLogTools::findFullSetEventIDByEventID($eventFields['EVENT_ID']);
				$this->setEventsListKey($key, $eventFields, $type);

				if (
					$type == 'main'
					&& $eventFields['EVENT_ID'] == 'tasks'
				)
				{
					$this->incrementTasksCount();
				}

				if (
					$type == 'main'
					&& $key == 0
				)
				{
					if ($eventFields['DATE_FOLLOW'])
					{
						$logPageProcessorInstance->setDateFirstPageTimestamp($this->makeTimeStampFromDateTime($eventFields['DATE_FOLLOW'], 'FULL'));
					}
					elseif (
						$params['USE_FOLLOW'] == 'N'
						&& $eventFields['LOG_UPDATE']
					)
					{
						$logPageProcessorInstance->setDateFirstPageTimestamp($this->makeTimeStampFromDateTime($eventFields['LOG_UPDATE'], 'FULL'));
					}
				}
			}
			else
			{
				$this->unsetEventsListKey($key, $type);
			}
		}

		if ($type === 'main')
		{
			$result['Events'] = $this->getEventsList($type);
		}
		elseif ($type === 'pinned')
		{
			$result['pinnedEvents'] = $this->getEventsList($type);
		}
	}

	public function processFavoritesData($result)
	{
		$params = $this->getComponent()->arParams;

		$idList = array_merge($result['arLogTmpID'], $result['pinnedIdList']);

		if (
			!empty($idList)
			&& $result['currentUserId'] > 0
			&& (
				!isset($params['USE_FAVORITES'])
				|| $params['USE_FAVORITES'] !== 'N'
			)
		)
		{
			$favLogIdList = [];
			$res = \Bitrix\Socialnetwork\LogFavoritesTable::getList([
				'filter' => [
					'@LOG_ID' => $idList,
					'USER_ID' => $result['currentUserId']
				],
				'select' => [ 'LOG_ID' ]
			]);
			while($favEntry = $res->fetch())
			{
				$favLogIdList[] = $favEntry['LOG_ID'];
			}

			$eventsList = $this->getEventsList();
			foreach($eventsList as $key => $entry)
			{
				$entry['FAVORITES_USER_ID'] = $entry['!FAVORITES_USER_ID'] = (
					in_array($entry['ID'], $favLogIdList)
						? $result['currentUserId']
						: 0
				);
				$this->setEventsListKey($key, $entry);
			}
		}
	}

	public function getMicroblogUserId(&$result)
	{
		$params = $this->getComponent()->arParams;

		$result['MICROBLOG_USER_ID'] = (
			$result['currentUserId'] > 0
			&& (
				$params['ENTITY_TYPE'] != SONET_ENTITY_GROUP
				|| (
					\CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'full_post', $this->getComponent()->getCurrentUserAdmin())
					|| \CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'write_post')
					|| \CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'moderate_post')
					|| \CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'premoderate_post')
				)
			)
				? $result['currentUserId']
				: 0
		);
	}

	public function getSmiles(&$result)
	{
		global $CACHE_MANAGER;

		if (!empty($this->getComponent()->getErrors()))
		{
			return;
		}

		if (Loader::includeModule('forum'))
		{
			$result['Smiles'] = Option::get('forum', 'smile_gallery_id', 0);
		}
		else
		{
			$cacheId = 'b_sonet_smile_'.LANGUAGE_ID;

			if($CACHE_MANAGER->read(604800, $cacheId))
			{
				$result['Smiles'] = $CACHE_MANAGER->get($cacheId);
			}
			else
			{
				$result['Smiles'] = [];

				$res = \CSocNetSmile::getList(
					[ 'SORT' => 'ASC' ],
					[
						'SMILE_TYPE' => 'S',
						'LANG_LID' => LANGUAGE_ID
					],
					false,
					false,
					[ 'ID', 'IMAGE', 'DESCRIPTION', 'TYPING', 'SMILE_TYPE', 'SORT', 'LANG_NAME' ]
				);
				while ($smileFields = $res->fetch())
				{
					list($type) = explode(' ', $smileFields['TYPING']);
					$smileFields['TYPE'] = str_replace("'", "\'", $type);
					$smileFields['TYPE'] = str_replace("\\", "\\\\", $smileFields['TYPE']);
					$smileFields['NAME'] = $smileFields['LANG_NAME'];
					$smileFields['IMAGE'] = '/bitrix/images/socialnetwork/smile/'.$smileFields['IMAGE'];

					$result['Smiles'][] = $smileFields;
				}

				$CACHE_MANAGER->set($cacheId, $result['Smiles']);
			}
		}
	}

	public function getExpertModeValue(&$result)
	{
		$params = $this->getComponent()->arParams;

		if (
			Util::checkUserAuthorized()
			&& $params['USE_TASKS'] == 'Y'
		)
		{
			$result['EXPERT_MODE'] = 'N';

			$res = LogViewTable::getList([
				'order' => [],
				'filter' => [
					'USER_ID' => $result['currentUserId'],
					'EVENT_ID' => 'tasks'
				],
				'select' => [ 'TYPE' ]
			]);
			if ($logViewFields = $res->fetch())
			{
				$result['EXPERT_MODE'] = ($logViewFields['TYPE'] == 'N' ? 'Y' : 'N');
			}
		}
	}

	public function warmUpStaticCache($result)
	{
		$logEventsData = [];

		if (is_array($result['Events']))
		{
			foreach($result['Events'] as $eventFields)
			{
				$logEventsData[(int)$eventFields['ID']] = $eventFields['EVENT_ID'];
			}
		}
		if (is_array($result['pinnedEvents']))
		{
			foreach($result['pinnedEvents'] as $eventFields)
			{
				$logEventsData[(int)$eventFields['ID']] = $eventFields['EVENT_ID'];
			}
		}

		$forumPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
		$forumPostLivefeedProvider->warmUpAuxCommentsStaticCache([
			'logEventsData' => $logEventsData
		]);
	}
}
?>