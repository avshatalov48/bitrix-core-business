<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\Web\Uri;
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

Loc::loadMessages(__FILE__);

class SenderYandexTolokaListComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();

			return;
		}

		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();

			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();

			return;
		}

		$this->includeComponentTemplate();
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_LIST']         = isset($this->arParams['PATH_TO_LIST'])
			? $this->arParams['PATH_TO_LIST'] : '';
		$this->arParams['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE'])
			? $this->arParams['PATH_TO_USER_PROFILE'] : '';
		$this->arParams['NAME_TEMPLATE']        = empty($this->arParams['NAME_TEMPLATE'])? \CAllSite::GetNameFormat(
			false
		) : str_replace(["#NOBR#", "#/NOBR#"], ["", ""], $this->arParams["NAME_TEMPLATE"]);

		$this->arParams['GRID_ID']   = isset($this->arParams['GRID_ID'])? $this->arParams['GRID_ID']
			: 'SENDER_TOLOKA_R_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID'])? $this->arParams['FILTER_ID']
			: $this->arParams['GRID_ID'].'_FILTER';

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE'])? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT']  = isset($this->arParams['CAN_EDIT'])
			? $this->arParams['CAN_EDIT'] : Security\Access::current()
				->canModifyLetters();

	}

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain */
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_TOLOKA_LIST_COMP_TITLE'));
		}

		if (!Security\Access::current()
			->canViewAds())
		{
			Security\AccessChecker::addError($this->errors);

			return false;
		}

		$this->arResult['ERRORS'] = [];
		$this->arResult['ROWS']   = [];

		$this->arResult['ACTION_URI'] = $this->getPath().'/ajax.php';

		$this->arResult['STATE_LIST'] = Dispatch\State::getList();
		unset($this->arResult['STATE_LIST'][Dispatch\State::WAITING]);

		$this->arResult['MESSAGES']  = $this->getSenderMessages();
		$this->arResult['CAMPAIGNS'] = [];
		$campaigns                   = Entity\Campaign::getList(['select' => ['ID', 'NAME']]);
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
		$navData     = $gridOptions->getNavParams(['nPageSize' => 10]);
		$nav         = new PageNavigation("page-sender-ads");
		$nav->allowAllRecords(true)
			->setPageSize($navData['nPageSize'])
			->setPageSizes($pageSizes)
			->initFromUri();

		// get rows
		$selectParameters = [
			'filter'      => $this->getDataFilter(),
			'offset'      => $nav->getOffset(),
			'limit'       => $nav->getLimit(),
			'count_total' => true,
			'order'       => $this->getGridOrder()
		];

		$list   = Entity\Toloka::getList($selectParameters);
		$letter = new Entity\Letter();
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			try
			{
				$letter->loadByArray($item);
				$item['MESSAGE_CODE'] = $letter->getMessage()
					->getCode();
				$item['MESSAGE_NAME'] = $letter->getMessage()
					->getName();
			}
			catch (\Bitrix\Main\SystemException $exception)
			{
				continue;
			}

			$item['TITLE'] = $letter->get('TITLE');
			$item['COUNT'] = [
				'all'    => $letter->getCounter()
					->getAll(),
				'sent'   => $letter->getCounter()
					->getSent(),
				'unsent' => $letter->getCounter()
					->getUnsent(),
			];

			if ($item['DATE_INSERT'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$item['DATE_INSERT'] = clone $item['DATE_INSERT'];
			}
			$isError = (strlen($item['ERROR_MESSAGE']) > 0);

			$item['DURATION']   = $letter->getDuration()
				->getFormattedInterval();
			$item['STATE_NAME'] = $isError
				? Loc::getMessage('SENDER_LETTER_LIST_STATE_ERROR')
				: $letter->getState()
					->getName();

			$item['STATE'] = [
				'dateSend'               => $this->formatDate(
					$letter->getState()
						->getDateSend()
				),
				'datePause'              => $this->formatDate(
					$letter->getState()
						->getDatePause()
				),
				'dateSent'               => $this->formatDate(
					$letter->getState()
						->getDateSent()
				),
				'dateCreate'             => $this->formatDate(
					$letter->getState()
						->getDateCreate()
				),
				'datePlannedSend'        => $this->formatDate(
					$letter->getState()
						->getPlannedDateSend()
				),
				'isSending'              => $letter->getState()
					->isSending(),
				'isPlanned'              => $letter->getState()
					->isPlanned(),
				'isPaused'               => $letter->getState()
					->isPaused(),
				'isFinished'             => $letter->getState()
					->isFinished(),
				'isStopped'              => $letter->getState()
					->isStopped(),
				'isSent'                 => $letter->getState()
					->isSent(),
				'isError'                => $isError,
				'wasStartedSending'      => $letter->getState()
					->wasStartedSending(),
				'canSend'                => $letter->getState()
					->canSend(),
				'canPause'               => $letter->getState()
					->canPause(),
				'canStop'                => $letter->getState()
					->canStop(),
				'canResume'              => $letter->getState()
					->canResume(),
				'isSendingLimitExceeded' => $letter->getState()
					->isSendingLimitExceeded(),
			];

			$item['URLS'] = [
				'EDIT'      => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
				'STAT'      => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_STAT']),
				'RECIPIENT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_RECIPIENT']),
			];

			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;

		Integration\Bitrix24\Service::initLicensePopup();

		return true;
	}

	protected function getSenderMessages()
	{
		$list      = [];
		$messages  = Message\Factory::getTolokaMessages();
		$pathToAdd = $this->arParams['PATH_TO_ADD'];
		$uri       = new Uri($pathToAdd);
		$uri->addParams(['code' => '#code#']);
		$pathToAdd = $uri->getLocator();

		foreach ($messages as $message)
		{
			$message = new Message\Adapter($message);
			$list[]  = [
				'CODE'         => $message->getCode(),
				'NAME'         => $message->getName(),
				'IS_AVAILABLE' => $message->isAvailable(),
				'URL'          => str_replace(
					['#code#', urlencode('#code#')],
					$message->getCode(),
					$pathToAdd
				)
			];
		}

		return $list;
	}

	protected function preparePost()
	{
		$ids    = $this->request->get('ID');
		$action = $this->request->get('action_button_'.$this->arParams['GRID_ID']);
		switch ($action)
		{
			case 'delete':
				if (!is_array($ids))
				{
					$ids = [$ids];
				}

				foreach ($ids as $id)
				{
					Entity\Toloka::removeById($id);
				}
				break;
		}
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = [
			[
				"id"      => "TITLE",
				"name"    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_TITLE'),
				"default" => true
			],
			[
				"id"      => "CREATED_BY",
				"name"    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_USER'),
				"type"    => "list",
				"default" => true,
				"items"   => $this->getFilterUserList(
					Entity\Toloka::getList(
						[
							'select' => [
								'USER_NAME'      => 'CREATED_BY_USER.NAME',
								'USER_LAST_NAME' => 'CREATED_BY_USER.LAST_NAME',
								'USER_ID'        => 'CREATED_BY',
							],
							'filter' => ['!=CREATED_BY' => null, '=IS_ADS' => 'N'],
							'group'  => ['USER_NAME', 'USER_LAST_NAME', 'USER_ID'],
							'cache'  => ['ttl' => 3600],
						]
					)
						->fetchAll()
				)
			],
			[
				"id"      => "DATE_INSERT",
				"name"    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT2'),
				"type"    => "date",
				"default" => true,
			]
		];
	}

	protected function getFilterUserList(array $list)
	{
		$result = [];
		foreach ($list as $data)
		{
			$result[$data['USER_ID']] = \CAllUser::FormatName(
				$this->arParams['NAME_TEMPLATE'],
				[
					'LOGIN'       => $data['USER_LOGIN'],
					'NAME'        => $data['USER_NAME'],
					'LAST_NAME'   => $data['USER_LAST_NAME'],
					'SECOND_NAME' => $data['USER_SECOND_NAME']
				],
				true,
				false
			);
		}

		return $result;
	}

	protected function setUiFilterPresets()
	{
		$this->arResult['FILTER_PRESETS'] = $this->getUiFilterPresets();
	}

	protected function getUiFilterPresets()
	{
		return [
			'filter_toloka_my'       => [
				'name'   => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_MY'),
				'fields' => [
					'CREATED_BY' => $GLOBALS['USER']->GetID(),
				]
			],
			'filter_toloka_working'  => [
				'name'    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_WORKING'),
				'default' => true,
				'fields'  => [
					'STATE' => array_merge(
						Dispatch\Semantics::getReadyStates(),
						Dispatch\Semantics::getWorkStates()
					),
				]
			],
			'filter_toloka_finished' => [
				'name'   => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_FINISHED'),
				'fields' => [
					'STATE' => Dispatch\Semantics::getFinishStates(),
				]
			],
			'filter_toloka_all'      => [
				'name'   => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_PRESET_ALL'),
				'fields' => []
			],
		];
	}

	protected function setUiGridColumns()
	{
		$this->arResult['COLUMNS'] = $this->getUiGridColumns();
	}

	protected function getUiGridColumns()
	{
		return [
			[
				"id"      => "ID",
				"name"    => "ID",
				"sort"    => "ID",
				"default" => false
			],
			[
				"id"      => "DATE_INSERT",
				"name"    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_DATE_INSERT'),
				"sort"    => "DATE_INSERT",
				"default" => false
			],
			[
				"id"      => "TITLE",
				"name"    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_TITLE'),
				"sort"    => "TITLE",
				"default" => true
			],
			[
				"id"      => "USER",
				"name"    => Loc::getMessage('SENDER_LETTER_LIST_COMP_UI_COLUMN_USER'),
				"sort"    => "CREATED_BY",
				"default" => false,
			]
		];
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString  = $filterOptions->getSearchString();

		$filter = ['=IS_TRIGGER' => 'N'];
		if ($requestFilter['TITLE'])
		{
			$filter['TITLE'] = '%'.$requestFilter['TITLE'].'%';
		}
		if ($searchString)
		{
			Entity\Toloka::getSearchBuilder()
				->applyFilter($filter, $searchString);
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
		$defaultSort = ['ID' => 'DESC'];

		$gridOptions = new GridOptions($this->arParams['GRID_ID']);
		$sorting     = $gridOptions->getSorting(['sort' => $defaultSort]);

		$by    = key($sorting['sort']);
		$order = strtoupper(current($sorting['sort'])) === 'ASC'? 'ASC' : 'DESC';

		$list = [];
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

		return [$by => $order];
	}

	protected function setRowColumnUser(array &$data)
	{
		$data['USER']      = '';
		$data['USER_PATH'] = '';
		if (!$data['USER_ID'])
		{
			return;
		}

		$data['USER_PATH'] = str_replace('#id#', $data['USER_ID'], $this->arParams['PATH_TO_USER_PROFILE']);
		$data['USER']      = \CAllUser::FormatName(
			$this->arParams['NAME_TEMPLATE'],
			[
				'LOGIN'       => $data['USER_LOGIN'],
				'NAME'        => $data['USER_NAME'],
				'LAST_NAME'   => $data['USER_LAST_NAME'],
				'SECOND_NAME' => $data['USER_SECOND_NAME']
			],
			true,
			false
		);
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
}