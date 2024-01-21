<?php

use Bitrix\Main\Context;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Map\MailingAction;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Message;
use Bitrix\Sender\Stat\Statistics;
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

class SenderLetterListComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		parent::initParams();

		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_LETTER_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_VIEW_CLIENT'] = $this->arParams['CAN_VIEW_CLIENT']??
			$this->getAccessController()->check(ActionDictionary::ACTION_MAILING_CLIENT_VIEW);

		$this->arParams['CAN_PAUSE_START_STOP'] = $this->arParams['CAN_PAUSE_START_STOP']??
			$this->getAccessController()->check(ActionDictionary::ACTION_MAILING_PAUSE_START_STOP);

		$this->arParams['SHOW_CAMPAIGNS'] = isset($this->arParams['SHOW_CAMPAIGNS'])
			?
			$this->arParams['SHOW_CAMPAIGNS']
			:
			Integration\Bitrix24\Service::isCampaignsAvailable();

		$this->arParams['IS_BX24_INSTALLED'] = Integration\Bitrix24\Service::isCloud();
		$this->arParams['IS_PHONE_CONFIRMED'] = \Bitrix\Sender\Integration\Bitrix24\Limitation\Verification::isPhoneConfirmed();

		$templatesFilesSyncInstalled = 1 === \COption::GetOptionInt("sender", "sender_files_sync_installed", 0);
		if (!$templatesFilesSyncInstalled)
		{
			\CAgent::AddAgent(
				'\\Bitrix\\Sender\\Install\\FileTableInstaller::installAgent();',
				"sender",
				"N",
				60,
				"",
				"Y",
				\ConvertTimeStamp(time()+\CTimeZone::GetOffset()+250),
				"FULL"
			);
			COption::SetOptionInt("sender", "sender_files_sync_installed", 1);
		}
	}

	protected function getSenderMessages()
	{
		$list = array();
		$messages = Message\Factory::getMailingMessages();
		$pathToAdd = $this->arParams['PATH_TO_ADD'];
		$uri = new Uri($pathToAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToAdd = $uri->getLocator();

		foreach ($messages as $message)
		{
			$message = new Message\Adapter($message);

			if(!$this->getAccessController()->check(
				MailingAction::getMap()[$message->getCode()]
			))
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
				if (!Security\Access::getInstance()->canModifyLetters())
				{
					return;
				}
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				foreach ($ids as $id)
				{
					Entity\Letter::removeById($id);
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
		if ($this->arParams['SHOW_CAMPAIGNS'])
		{
			$this->arResult['CAMPAIGN_LIST'] = [];
			$campaigns = Entity\Campaign::getList();
			foreach ($campaigns as $campaign)
			{
				$this->arResult['CAMPAIGN_LIST'][$campaign['ID']] = $campaign['NAME'];
			}
		}


		$this->arResult['MESSAGES'] =$this->getSenderMessages();
		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}


		// set ui filter
		$this->setUiFilter();
		$this->setUiFilterPresets();

		// set ui grid columns
		$this->setUiGridColumns();

		// export
		$isExportMode = !!$this->request->get('export');

		// create nav
		$pageSizes = [];
		foreach ([5, 10, 20, 30, 50, 100] as $index)
		{
			$pageSizes[] = ['NAME' => $index, 'VALUE' => $index];
		}

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$navData = $gridOptions->getNavParams(['nPageSize' => 10]);
		$nav = new PageNavigation("page-sender-letters");
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
			'order' => $this->getGridOrder(),
			'select' => array_merge(
				Entity\Letter::getDefaultSelectFields(),
				[	// fields for statistic:
					'COUNT_READ' => 'CURRENT_POSTING.COUNT_READ',
					'COUNT_CLICK' => 'CURRENT_POSTING.COUNT_CLICK',
					'COUNT_UNSUB' => 'CURRENT_POSTING.COUNT_UNSUB',
				]
			)
		);
		if ($isExportMode)
		{
			unset($selectParameters['offset']);
			unset($selectParameters['limit']);
		}

		$list = Entity\Letter::getListWithMessageFields($selectParameters);
		$letter = new Entity\Letter();
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			try
			{
				$letter->loadByArray($item);
				$approveConfirmation = $letter->getMessage()->getConfiguration()->getOption('APPROVE_CONFIRMATION');
				$approveConfirmation = $approveConfirmation ? $approveConfirmation->getValue() : null;
				$item['MESSAGE_CODE'] = $letter->getMessage()->getCode();
				$item['MESSAGE_NAME'] = $letter->getMessage()->getName();
				$item['CONSENT_SUPPORT'] = $approveConfirmation === 'Y';
				$message = $letter->getMessage();
				$options = $message->getConfiguration()->getOptions();
				foreach ($options as $option)
				{
					if (!$option->getShowInList())
					{
						continue;
					}

					$optionCode = $option->getCode();
					$item[$optionCode] = $message->getConfiguration()->getReadonlyView($optionCode);
					if (!$isExportMode)
					{
						$item[$optionCode] = htmlspecialcharsbx($item[$optionCode]);
					}
				}

				if (!isset($item['EMAIL_FROM']) || !$item['EMAIL_FROM'])
				{
					if (isset($item['SENDER']) && $item['SENDER'])
					{
						$item['EMAIL_FROM'] = $item['SENDER'];
					}
					elseif (isset($item['OUTPUT_NUMBER']) && $item['OUTPUT_NUMBER'])
					{
						$item['EMAIL_FROM'] = $item['OUTPUT_NUMBER'];
					}
				}
			}
			catch (SystemException $exception)
			{
				continue;
			}

			$item['TITLE'] = $letter->get('TITLE');
			$item['COUNT'] = array(
				'all' => $letter->getCounter()->getAll(),
				'sent' => $letter->getCounter()->getSent(),
				'unsent' => $letter->getCounter()->getUnsent(),
			);

			$trackMail = $letter->getMessage()->getConfiguration()->getOption('TRACK_MAIL');
			$item['TRACK_MAIL'] = $trackMail ? $trackMail->getValue() : 'Y';

			$item['HAS_STATISTICS'] = $letter->hasStatistics();
			if ($item['HAS_STATISTICS'])
			{
				$item['STATS'] = [];
				$postingStat = Statistics::create()->initFromArray($item);
				$counters = $postingStat->getCounters();
				foreach ($counters as $counter)
				{
					$item['STATS'][$counter['CODE']] = $counter['PERCENT_VALUE_DISPLAY'];
				}

			}
			$item['DURATION'] = $letter->getDuration()->getFormattedInterval();
			$item['STATE_NAME'] = $item['WAITING_RECIPIENT'] === 'N'
				? $letter->getState()->getName()
				: Loc::getMessage('SENDER_DISPATCH_STATE_M')
			;
			if ($isExportMode)
			{
				$item['STATUS'] = $item['STATE_NAME'];
				if (!$item['HAS_STATISTICS'])
				{
					$item['COUNT_READ'] = '';
					$item['COUNT_CLICK'] = '';
					$item['COUNT_UNSUB'] = '';
				}
			}
			else
			{
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
					'wasStartedSending' => $letter->getState()->wasStartedSending(),
					'canSend' => $letter->getState()->canSend(),
					'canPause' => $letter->getState()->canPause(),
					'canStop' => $letter->getState()->canStop(),
					'canResume' => $letter->getState()->canResume(),
					'isSendingLimitExceeded' => $letter->getState()->isSendingLimitExceeded(),
					'isSendingLimitTemporary' => $letter->getState()->isSendingLimitTemporary(),
					'isSendingLimitWaiting' => $letter->getState()->isSendingLimitWaiting(),
				);

				if ($item['STATE']['isSendingLimitWaiting'])
				{
					$this->prepareTimeLimitationMessage($letter, $item);
				}

				$item['URLS'] = array(
					'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
					'STAT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_STAT']),
					'RECIPIENT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_RECIPIENT']),
				);
			}

			if ($this->arParams['SHOW_CAMPAIGNS'])
			{
				$item['CAMPAIGN_NAME'] = $this->arResult['CAMPAIGN_LIST'][$item['CAMPAIGN_ID']];
			}

			$this->arResult['ROWS'][] = $item;
		}
		$this->getExportGridColumns();
		if ($isExportMode)
		{
			\Bitrix\Sender\Internals\DataExport::toCsv(
				$this->getExportGridColumns(),
				$this->arResult['ROWS']
			);
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;


		Integration\Bitrix24\Service::initLicensePopup();

		return true;
	}

	private function prepareTimeLimitationMessage($letter, &$item)
	{
		$currentTime = strtotime((new DateTime())->format("H:i:s"));
		$configuration = $letter->getMessage()->getConfiguration();
		$sendingStart = strtotime($configuration->get('SENDING_START'));

		$day = $currentTime > $sendingStart
			? Loc::getMessage('SENDER_LETTER_LIST_LETTER_SENDING_TOMORROW')
			: Loc::getMessage('SENDER_LETTER_LIST_LETTER_SENDING_TODAY')
		;

		$item['LIMITATION']['DAY'] = $day;

		$item['LIMITATION']['TIME'] = (new \DateTime())
			->setTimestamp($sendingStart)
			->format(Context::getCurrent()
				->getCulture()
				->getShortTimeFormat());
		;
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
		if (isset($requestFilter['TITLE']) && $requestFilter['TITLE'])
		{
			$filter['TITLE'] = '%' . $requestFilter['TITLE'] . '%';
		}
		if ($searchString)
		{
			Entity\Letter::getSearchBuilder()->applyFilter($filter, $searchString);
		}
		if (
			((int) $requestFilter['CREATED_BY'] > 0)
			&& isset($requestFilter['CREATED_BY'])
			&& $requestFilter['CREATED_BY'])
		{
			$requestFilter['CREATED_BY'] = (int) $requestFilter['CREATED_BY'];
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
		else
		{
			$messageCodes = [];
			$messages = Message\Factory::getMailingMessages();
			foreach ($messages as $message)
			{
				$messageCodes[] = $message->getCode();
			}
			$filter['=MESSAGE_CODE'] = $messageCodes;
		}
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}
		if (isset($requestFilter['CAMPAIGN_ID']) && $requestFilter['CAMPAIGN_ID'])
		{
			$filter['=CAMPAIGN_ID'] = $requestFilter['CAMPAIGN_ID'];
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
		$list = array(
			array(
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			),
			array(
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT2'),
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
				"sort" => "DATE_UPDATE",
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
		if (Integration\Bitrix24\Service::isCloud())
		{
			$list[] = [
				"id" => "CONSENT_SUPPORT",
				"name"=> Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_CONSENT_SUPPORT'),
				"default" => true
			];
		}
		if ($this->arParams['SHOW_CAMPAIGNS'])
		{
			$list[] = [
				"id" => "CAMPAIGN_NAME",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_CAMPAIGN'),
				"sort" => "CAMPAIGN.NAME",
				"default" => true,
			];
		}

		$letter = new Entity\Letter();
		$options = $letter->getMessage()->getConfiguration()->getOptions();
		foreach ($options as $option)
		{
			if ($option->getShowInList())
			{
				$list[] = [
					"id" => $option->getCode(),
					"name" => $option->getName(),
					"default" => false,
				];
			}
		}

		return $list;
	}

	protected function setUiFilter()
	{
		$messageCodes = array();
		foreach ($this->arResult['MESSAGES'] as $message)
		{
			$messageCodes[$message['CODE']] = $message['NAME'];
		}

		$this->arResult['FILTERS'] = [
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
		];

		if ($this->arParams['SHOW_CAMPAIGNS'])
		{
			$this->arResult['FILTERS'][] = [
				"id" => "CAMPAIGN_ID",
				"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_CAMPAIGN'),
				"type" => "list",
				"default" => true,
				'params' => array('multiple' => 'Y'),
				"items" => $this->arResult['CAMPAIGN_LIST']
			];
		}

		$this->arResult['FILTERS'][] = [
			"id" => "MESSAGE_CODE",
			"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_MESSAGE_CODE'),
			"type" => "list",
			"items" => $messageCodes
		];
	}

	protected function getFilterUserList(array $list)
	{
		$result = array();
		foreach ($list as $data)
		{
			$result[$data['USER_ID']] = \CUser::FormatName(
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
		$data['USER'] = \CUser::FormatName(
			$this->arParams['NAME_TEMPLATE'] ?? '',
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
		parent::prepareResultAndTemplate();
	}

	protected function getExportGridColumns()
	{
		$columns = $this->getUiGridColumns();
		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$defaultColumnsIds = [];
		foreach ($columns as $column)
		{
			if ($column['default'])
			{
				$defaultColumnsIds[] = $column['id'];
			}
		}
		$visibleColumnsIds = $gridOptions->getUsedColumns($defaultColumnsIds);
		$hiddenColumnsIds = ['ACTIONS'];
		$visibleColumnsIds = array_diff($visibleColumnsIds, $hiddenColumnsIds);

		$columns = array_filter($this->getUiGridColumns(), function ($item) use ($visibleColumnsIds)
		{
			return in_array($item['id'], $visibleColumnsIds);
		});
		$result = [];
		foreach ($columns as $index=>$column)
		{
			if ($column['id'] == 'STATS')
			{
				$result[] = [
					"id" => "COUNT_SEND_ALL",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_COUNT_SEND_ALL'),
				];
				$result[] = [
					"id" => "COUNT_SEND_SUCCESS",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_COUNT_SEND_SUCCESS'),
				];
				$result[] = [
					"id" => "COUNT_READ",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_COUNT_READ'),
				];
				$result[] = [
					"id" => "COUNT_CLICK",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_COUNT_CLICK'),
				];
				$result[] = [
					"id" => "COUNT_UNSUB",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_COUNT_UNSUB'),
				];
			}
			else
			{
				$result[] = $column;
			}
			if ($column['id'] == 'TITLE')
			{
				$result[] = [
					"id" => "MESSAGE_NAME",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_MESSAGE_CODE'),
				];
			}
			if ($column['id'] == 'DATE_INSERT')
			{
				$result[] = [
					"id" => "DATE_SEND",
					"name" => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_SENT'),
				];
			}
		}
		return $result;
	}

	protected function canEdit()
	{
		$this->arParams['CAN_EDIT'] = $this->arParams['CAN_EDIT'] ?? Security\Access::getInstance()->canModifyLetters();
	}

	public function getEditAction()
	{
		return $this->getViewAction();
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_MAILING_VIEW;
	}
}
