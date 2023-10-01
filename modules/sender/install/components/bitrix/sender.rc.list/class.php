<?

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Message;
use Bitrix\Sender\Security;
use Bitrix\Sender\UI\PageNavigation;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class RcListSenderComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	protected function initParams()
	{
		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_RC_GRID';

		$this->arParams['CAN_PAUSE_START_STOP'] = $this->arParams['CAN_PAUSE_START_STOP']??
												$this->getAccessController()->check
												(ActionDictionary::ACTION_RC_PAUSE_START_STOP);

		parent::initParams();
	}


	protected function getSenderMessages()
	{
		$list = array();
		$messages = Message\Factory::getReturnCustomerMessages();
		$pathToAdd = $this->arParams['PATH_TO_ADD'];
		$uri = new Uri($pathToAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToAdd = $uri->getLocator();

		foreach ($messages as $message)
		{
			$message = new Message\Adapter($message);
			$list[] = array(
				'CODE' => $message->getCode(),
				'NAME' => $message->getName(),
				'IS_AVAILABLE' => $message->isAvailable(),
				'IS_HIDDEN' => $message->isHidden(),
				'URL' => str_replace(
					array('#code#', urlencode('#code#')),
					$message->getCode(),
					$pathToAdd
				)
			);
		}

		return $list;
	}

	protected function preparePost()
	{
		$ids = $this->request->get('ID');
		$action = $this->request->get('action_button_' . $this->arParams['GRID_ID']);
		switch ($action)
		{
			case 'delete':
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				foreach ($ids as $id)
				{
					Entity\Rc::removeById($id);
				}
				break;
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_LETTER_LIST_COMP_TITLE'));
		}

		if (!Security\Access::getInstance()->canViewRc())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$this->arResult['STATE_LIST'] = Dispatch\State::getList();

		$this->arResult['MESSAGES'] = $this->getSenderMessages();
		$this->arResult['CAMPAIGNS'] = array();
		$campaigns = Entity\Campaign::getList(array('select' => array('ID', 'NAME')));
		foreach ($campaigns as $campaign)
		{
			$this->arResult['CAMPAIGNS'][$campaign['ID']] = $campaign['NAME'];
		}

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}


		// set ui filter
		$this->setUiFilter();
		$this->setUiFilterPresets();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$pageSizes = [];
		foreach ([5, 10, 20, 30, 50, 100] as $index)
		{
			$pageSizes[] = ['NAME' => $index, 'VALUE' => $index];
		}

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$navData = $gridOptions->getNavParams(['nPageSize' => 10]);
		$nav = new PageNavigation("page-sender-rc");
		$nav->allowAllRecords(true)
			->setPageSize($navData['nPageSize'])
			->setPageSizes($pageSizes)
			->initFromUri();

		// get rows
		$selectParameters = array(
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		);
		$this->addOptionsFilter($selectParameters);

		$list = Entity\Rc::getList($selectParameters);
		$letter = new Entity\Rc();
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			try
			{
				$letter->loadByArray($item);
				$item['MESSAGE_CODE'] = $letter->getMessage()->getCode();
				$item['MESSAGE_NAME'] = $letter->getMessage()->getName();
			}
			catch (\Bitrix\Main\SystemException $exception)
			{
				continue;
			}

			$item['TITLE'] = $letter->get('TITLE');
			$item['COUNT'] = array(
				'all' => $letter->getCounter()->getAll(),
				'sent' => $letter->getCounter()->getSent(),
				'unsent' => $letter->getCounter()->getUnsent(),
			);

			$item['HAS_STATISTICS'] = $letter->hasStatistics();
			$item['DURATION'] = $letter->getDuration()->getFormattedInterval();
			$item['STATE_NAME'] = Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_STATUS_'.$item['STATUS'].'_'.mb_strtoupper($letter->getMessage()->getCode()));
			$item['STATE_NAME'] = $item['STATE_NAME'] ?: Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_STATUS_' . $item['STATUS']);
			$item['STATE_NAME'] = $item['STATE_NAME'] ?: $letter->getState()->getName();
			$item['STATE'] = array(
				'dateSend' => $this->formatDate($letter->getState()->getDateSend()),
				'datePause' => $this->formatDate($letter->getState()->getDatePause()),
				'dateSent' => $this->formatDate($letter->getState()->getDateSent()),
				'dateCreate' => $this->formatDate($letter->getState()->getDateCreate()),
				'datePlannedSend' => $this->formatDate($letter->getState()->getPlannedDateSend()),
				'dateLastExecuted' => $this->formatDate($letter->getState()->getLastExecutedDate()),
				'isWaiting' => $letter->getState()->isWaiting(),
				'isHalted' => $letter->getState()->isHalted(),
				'isSending' => $letter->getState()->isSending(),
				'isPlanned' => $letter->getState()->isPlanned(),
				'isPaused' => $letter->getState()->isPaused(),
				'isFinished' => $letter->getState()->isFinished(),
				'isStopped' => $letter->getState()->isStopped(),
				'isSent' => $letter->getState()->isSent(),
				'wasStartedSending' => $letter->getState()->wasStartedSending(),
				'canSend' => $letter->getState()->canSend(),
				'canPause' => $letter->getState()->canPause(),
				'canStop' => $letter->getState()->canStop(),
				'canWait' => $letter->getState()->canWait(),
				'canHalt' => $letter->getState()->canHalt(),
				'canResume' => $letter->getState()->canResume(),
				'isSendingLimitExceeded' => $letter->getState()->isSendingLimitExceeded(),
			);

			$item['URLS'] = array(
				'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
				'STAT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_STAT']),
			);

			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		Integration\Bitrix24\Service::initLicensePopup();

		return true;
	}

	protected function formatDate(\Bitrix\Main\Type\DateTime $dateTime = null)
	{
		if (!$dateTime)
		{
			return '';
		}
		$dateTime = clone $dateTime;
		return PrettyDate::formatDateTime($dateTime);
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString = $filterOptions->getSearchString();

		$filter = array('=IS_TRIGGER' => 'N');
		if (isset($requestFilter['NAME']) && $requestFilter['TITLE'])
		{
			$filter['TITLE'] = '%' . $requestFilter['TITLE'] . '%';
		}
		if ($searchString)
		{
			Entity\Rc::getSearchBuilder()->applyFilter($filter, $searchString);

		}
		if (isset($requestFilter['CREATED_BY']) && $requestFilter['CREATED_BY'])
		{
			$filter['=CREATED_BY'] = $requestFilter['CREATED_BY'];
		}
		if (isset($requestFilter['STATE']) && $requestFilter['STATE'])
		{
			$filter['=STATUS'] = $requestFilter['STATE'];
		}
		if (isset($requestFilter['CAMPAIGN_ID']) && $requestFilter['CAMPAIGN_ID'])
		{
			$filter['=CAMPAIGN_ID'] = $requestFilter['CAMPAIGN_ID'];
		}
		if (isset($requestFilter['MESSAGE_CODE']) && $requestFilter['MESSAGE_CODE'])
		{
			$filter['=MESSAGE_CODE'] = $requestFilter['MESSAGE_CODE'];
		}
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}
		if (isset($requestFilter['POSTING_DATE_SENT_from']) && $requestFilter['POSTING_DATE_SENT_from'])
		{
			$filter['>=POSTING.DATE_SENT'] = $requestFilter['POSTING_DATE_SENT_from'];
		}
		if (isset($requestFilter['POSTING_DATE_SENT_to']) && $requestFilter['POSTING_DATE_SENT_to'])
		{
			$filter['<=POSTING.DATE_SENT'] = $requestFilter['POSTING_DATE_SENT_to'];
		}
		if (isset($requestFilter['REITERATE']) && $requestFilter['REITERATE'] == 'Y')
		{
			$filter['=REITERATE'] = true;
		}
		if (isset($requestFilter['REITERATE']) && $requestFilter['REITERATE'] == 'N')
		{
			$filter['=REITERATE'] = false;
		}

		return $filter;
	}

	protected function addOptionsFilter(&$selectParameters)
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);

		$rc = new Entity\Rc();
		$rc->set('MESSAGE_CODE', \Bitrix\Sender\Integration\Crm\ReturnCustomer\MessageDeal::CODE);
		$options = $rc->getMessage()->getConfiguration()->getOptions();
		foreach ($options as $option)
		{
			if ($option->getShowInFilter())
			{
				if (!isset($selectParameters['runtime']))
				{
					$selectParameters['runtime'] = [];
				}
				$fieldName = 'OPTION_'.$option->getCode();
				$filterValue = $requestFilter[$option->getCode()] ?? '';
				if($filterValue <> '')
				{
					if($filterValue == 'last')
					{
						$filterValue = '';
					}
					elseif($filterValue == 'common')
					{
						$filterValue = '0';
					}
					$selectParameters['runtime'][] = new \Bitrix\Main\Entity\ReferenceField(
						$fieldName,
						\Bitrix\Sender\Internals\Model\MessageFieldTable::class,
						['=this.MESSAGE_ID' => 'ref.MESSAGE_ID', 'ref.CODE' => new \Bitrix\Main\DB\SqlExpression('?', $option->getCode())],
						['join_type' => 'INNER']
					);
					$selectParameters['filter']['='.$fieldName.'.VALUE'] = $filterValue;
				}
			}
		}
	}

	protected function getGridOrder()
	{
		$defaultSort = array('ID' => 'DESC');

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$sorting = $gridOptions->getSorting(array('sort' => $defaultSort));

		$by = key($sorting['sort']);
		$order = mb_strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

		$list = array();
		foreach ($this->getUiGridColumns() as $column)
		{
			if (!isset($column['sort']) || !$column['sort'])
			{
				continue;
			}

			$list[] = $column['sort'];
		}

		if (!in_array($by, $list))
		{
			return $defaultSort;
		}

		return array($by => $order);
	}

	protected function setUiGridColumns()
	{
		$this->arResult['COLUMNS'] = $this->getUiGridColumns();
	}

	protected function getUiGridColumns()
	{
		return array(
			array(
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"sort" => "DATE_INSERT",
				"default" => false
			),
			array(
				"id" => "TITLE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_TITLE'),
				"sort" => "TITLE",
				"default" => true
			),
			array(
				"id" => "USER",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_USER'),
				"sort" => "CREATED_BY",
				"default" => false,
			),
			array(
				"id" => "ACTIONS",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_ACTIONS'),
				"sort" => "ID",
				"default" => true
			),
			array(
				"id" => "STATUS",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_STATUS'),
				"sort" => "STATUS",
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
		$messageCodes = array();
		foreach ($this->arResult['MESSAGES'] as $message)
		{
			$messageCodes[$message['CODE']] = $message['NAME'];
		}

		$this->arResult['FILTERS'] = array(
			array(
				"id" => "TITLE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_TITLE'),
				"default" => true
			),
			array(
				"id" => "STATE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_STATE'),
				"type" => "list",
				"default" => true,
				'params' => array('multiple' => 'Y'),
				"items" => $this->arResult['STATE_LIST']
			),
			array(
				"id" => "CREATED_BY",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_USER'),
				"type" => "list",
				"default" => true,
				"items" => $this->getFilterUserList(Entity\Letter::getList(array(
					'select' => array(
						'USER_NAME' => 'CREATED_BY_USER.NAME',
						'USER_LAST_NAME' => 'CREATED_BY_USER.LAST_NAME',
						'USER_ID' => 'CREATED_BY',
					),
					'filter' => array('!=CREATED_BY' => null, '=IS_ADS' => 'N'),
					'group' => array('USER_NAME', 'USER_LAST_NAME', 'USER_ID'),
					'cache' => array('ttl' => 3600),
				))->fetchAll())
			),
			array(
				"id" => "MESSAGE_CODE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_MESSAGE_CODE'),
				"type" => "list",
				"items" => $messageCodes
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT2'),
				"type" => "date",
				"default" => true,
			),
			array(
				"id" => "POSTING_DATE_SENT",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_SENT'),
				"type" => "date",
				"default" => true,
			),
			array(
				"id" => "REITERATE",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_REITERATE'),
				"type" => "list",
				"items" => [
					'Y' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_REITERATE_YES'),
					'N' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_REITERATE_NO')
				],
				"default" => false,
			),
		);
		$rc = new Entity\Rc();
		$rc->set('MESSAGE_CODE', \Bitrix\Sender\Integration\Crm\ReturnCustomer\MessageDeal::CODE);
		$options = $rc->getMessage()->getConfiguration()->getOptions();
		foreach ($options as $option)
		{
			if ($option->getShowInFilter())
			{
				$items = [];
				if ('CATEGORY_ID' == $option->getCode())
				{
					foreach ($option->getItems() as $item)
					{
						$key = $item['code'];
						if ($key == '')
						{
							$key = 'last';
						}
						elseif ($key == 0)
						{
							$key = 'common';
						}
						$items[$key] = $item['value'];
					}
				}
				$this->arResult['FILTERS'][] = [
					"id" => $option->getCode(),
					"name" => $option->getName(),
					"type" => $option->getType(),
					"items" => $items,
					"default" => false,
				];
			}
		}
	}

	protected function getFilterUserList(array $list)
	{
		$result = array();
		foreach ($list as $data)
		{
			$result[$data['USER_ID']] = CUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => $data['USER_LOGIN'] ?? '',
					'NAME' => $data['USER_NAME'] ?? '',
					'LAST_NAME' => $data['USER_LAST_NAME'] ?? '',
					'SECOND_NAME' => $data['USER_SECOND_NAME'] ?? ''
				),
				true, false
			);
		}

		return $result;
	}

	protected function getUiFilterPresets()
	{
		return array(
			'filter_rc_my' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_MY'),
				'fields' => array(
					'CREATED_BY' => $GLOBALS['USER']->GetID(),
				)
			),
			'filter_rc_working' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_WORKING'),
				'default' => true,
				'fields' => array(
					'STATE' => array_merge(
						Dispatch\Semantics::getReadyStates(),
						Dispatch\Semantics::getWorkStates()
					),
				)
			),
			'filter_rc_finished' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_FINISHED'),
				'fields' => array(
					'STATE' => Dispatch\Semantics::getFinishStates(),
				)
			),
			'filter_rc_all' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_ALL'),
				'fields' => array()
			),
		);
	}

	protected function setUiFilterPresets()
	{
		$this->arResult['FILTER_PRESETS'] = $this->getUiFilterPresets();
	}

	protected function setRowColumnUser(array &$data)
	{
		$data['USER'] = '';
		$data['USER_PATH'] = '';
		if (!$data['USER_ID'])
		{
			return;
		}

		$data['USER_PATH'] = str_replace('#id#', $data['USER_ID'], $this->arParams['PATH_TO_USER_PROFILE']);
		$data['USER'] = CUser::FormatName(
			$this->arParams['NAME_TEMPLATE'],
			array(
				'LOGIN' => $data['USER_LOGIN'] ?? '',
				'NAME' => $data['USER_NAME'] ?? '',
				'LAST_NAME' => $data['USER_LAST_NAME'] ?? '',
				'SECOND_NAME' => $data['USER_SECOND_NAME'] ?? ''
			),
			true, false
		);
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		parent::executeComponent();
		$this->prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_RC_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_RC_VIEW;
	}
}