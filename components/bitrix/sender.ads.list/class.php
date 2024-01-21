<?php

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Map\AdsAction;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Message;
use Bitrix\Sender\UI\PageNavigation;
use Bitrix\Sender\Security;

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

class SenderAdsListComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	private const ADS_MANAGER_URL = 'https://www.facebook.com/adsmanager/manage/campaigns';
	protected function initParams()
	{
		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ?
			$this->arParams['GRID_ID'] : 'SENDER_ADS_GRID';
		parent::initParams();

		$this->arParams['CAN_PAUSE_START_STOP'] = $this->arParams['CAN_PAUSE_STOP_START'] ??
			$this->getAccessController()->check(ActionDictionary::ACTION_ADS_PAUSE_START_STOP);

		$this->arParams['CAN_EDIT_ADV'] = $this->arParams['CAN_EDIT_ADV'] ?? $this->getEditList();

		$this->arParams['CAN_VIEW_CLIENT'] = $this->arParams['CAN_VIEW_CLIENT'] ??
			$this->getAccessController()->check(ActionDictionary::ACTION_ADS_CLIENT_VIEW);

		$this->arParams['CAN_CONNECT_CABINET'] = $this->arParams['CAN_CONNECT_CABINET'] ??
			$this->getAccessController()->check(
			ActionDictionary::ACTION_ADS_CONNECT_CABINET
			);

	}
	private function getEditList(): array
	{
		$result = [];
		$map = AdsAction::getMap();
		foreach ($map as $code => $item)
		{
			$result[$code] = $this->getAccessController()
								->check($item);
		}

		return $result;
	}

	protected function getSenderMessages()
	{
		$list = array();
		$messages = array_merge(Message\Factory::getAdsMessages(), Message\Factory::getMarketingMessages());
		$pathToAdd = $this->arParams['PATH_TO_ADD'];
		$uri = new Uri($pathToAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToAdd = $uri->getLocator();

		foreach ($messages as $message)
		{
			$message = new Message\Adapter($message);
			$permissionEntity = $this->arParams['CAN_EDIT_ADV'][$message->getCode()];
			if(isset($permissionEntity) && !$permissionEntity)
			{
				continue;
			}

			$list[] = array(
				'CODE' => $message->getCode(),
				'NAME' => $message->getName(),
				'IS_AVAILABLE' => $message->isAvailable(),
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
				if (!Security\Access::getInstance()->canModifyAds())
				{
					return;
				}
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				foreach ($ids as $id)
				{
					Entity\Ad::removeById($id);
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
		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		$this->arResult['STATE_LIST'] = Dispatch\State::getList();
		unset($this->arResult['STATE_LIST'][Dispatch\State::WAITING]);

		$this->arResult['MESSAGES'] =$this->getSenderMessages();
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
		$nav = new PageNavigation("page-sender-ads");
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

		$list = Entity\Ad::getList($selectParameters);
		$letter = new Entity\Letter();
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

			if ($item['DATE_INSERT'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$item['DATE_INSERT'] = clone $item['DATE_INSERT'];
			}
			$isError = ($item['ERROR_MESSAGE'] <> '');

			$item['DURATION'] = $letter->getDuration()->getFormattedInterval();
			$item['STATE_NAME'] = $isError ? Loc::getMessage('SENDER_LETTER_LIST_STATE_ERROR') : $letter->getState()->getName();

			$item['STATE'] = array(
				'dateSend' => $this->formatDate($letter->getState()->getDateSend()),
				'datePause' => $this->formatDate($letter->getState()->getDatePause()),
				'dateSent' => $this->formatDate($letter->getState()->getDateSent()),
				'dateCreate' => $this->formatDate($letter->getState()->getDateCreate()),
				'datePlannedSend' => $this->formatDate($letter->getState()->getPlannedDateSend()),
				'isSending' => $letter->getState()->isSending(),
				'isPlanned' => $letter->getState()->isPlanned(),
				'isPaused' => $letter->getState()->isPaused(),
				'isFinished' => $letter->getState()->isFinished(),
				'isStopped' => $letter->getState()->isStopped(),
				'isSent' => $letter->getState()->isSent(),
				'isError' => $isError,
				'wasStartedSending' => $letter->getState()->wasStartedSending(),
				'canSend' => $letter->getState()->canSend(),
				'canPause' => $letter->getState()->canPause(),
				'canStop' => $letter->getState()->canStop(),
				'canResume' => $letter->getState()->canResume(),
				'isSendingLimitExceeded' => $letter->getState()->isSendingLimitExceeded(),
			);

			$item['URLS'] = array(
				'EDIT' => in_array(
					$letter->getMessage()->getCode(),
					['facebook', 'instagram']
				) ? [
						'EXTERNAL' => self::ADS_MANAGER_URL
					] :
					str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
				'STAT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_STAT']),
				'RECIPIENT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_RECIPIENT']),
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
			Entity\Ad::getSearchBuilder()->applyFilter($filter, $searchString);
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
		return $filter;
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
			array(
				"id" => "STATS",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_STATS'),
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
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
				"items" => $this->getFilterUserList(Entity\Ad::getList(array(
					'select' => array(
						'USER_NAME' => 'CREATED_BY_USER.NAME',
						'USER_LAST_NAME' => 'CREATED_BY_USER.LAST_NAME',
						'USER_ID' => 'CREATED_BY',
					),
					'filter' => array('!=CREATED_BY' => null, '=IS_ADS' => 'Y'),
					'group' => array('USER_NAME', 'USER_LAST_NAME', 'USER_ID'),
					'cache' => array('ttl' => 3600),
				))->fetchAll())
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
		);
	}

	protected function getFilterUserList(array $list)
	{
		$result = array();
		foreach ($list as $data)
		{
			$result[$data['USER_ID']] = CUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => $data['USER_LOGIN'],
					'NAME' => $data['USER_NAME'],
					'LAST_NAME' => $data['USER_LAST_NAME'],
					'SECOND_NAME' => $data['USER_SECOND_NAME']
				),
				true, false
			);
		}

		return $result;
	}

	protected function getUiFilterPresets()
	{
		return array(
			'filter_letters_my' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_MY'),
				'fields' => array(
					'CREATED_BY' => $GLOBALS['USER']->GetID(),
				)
			),
			'filter_letters_working' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_WORKING'),
				'default' => true,
				'fields' => array(
					'STATE' => array_merge(
						Dispatch\Semantics::getReadyStates(),
						Dispatch\Semantics::getWorkStates()
					),
				)
			),
			'filter_letters_finished' => array(
				'name' => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_FINISHED'),
				'fields' => array(
					'STATE' => Dispatch\Semantics::getFinishStates(),
				)
			),
			'filter_letters_all' => array(
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
				'LOGIN' => $data['USER_LOGIN'],
				'NAME' => $data['USER_NAME'],
				'LAST_NAME' => $data['USER_LAST_NAME'],
				'SECOND_NAME' => $data['USER_SECOND_NAME']
			),
			true, false
		);

		$data['USER'] = htmlspecialcharsbx($data['USER']);
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
		return ActionDictionary::ACTION_ADS_VIEW;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_ADS_VIEW;
	}

	protected function canEdit()
	{
		$this->arParams['CAN_EDIT'] = $this->arParams['CAN_EDIT'] ?? Security\Access::getInstance()->canModifyAds();
	}
}