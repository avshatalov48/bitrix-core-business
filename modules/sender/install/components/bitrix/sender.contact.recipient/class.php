<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\DataExport;
use Bitrix\Sender\Message;
use Bitrix\Sender\PostingRecipientTable;
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

class ContactRecipientSenderComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var  Entity\Letter $letter */
	protected $letter;

	protected function checkRequiredParams()
	{
		if (!$this->arParams['CONTACT_ID'] && !$this->arParams['LETTER_ID'] && !$this->arParams['CAMPAIGN_ID'])
		{
			$this->errors->setError(new Error('Parameter `CONTACT_ID` or `LETTER_ID` or `CAMPAIGN_ID` is required.'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_LETTER_RECIPIENT_GRID';

		parent::initParams();

		if (empty($this->arParams['CONTACT_ID']))
		{
			$this->arParams['CONTACT_ID'] = (int) $this->request->get('CONTACT_ID');
		}
		if (empty($this->arParams['LETTER_ID']))
		{
			$this->arParams['LETTER_ID'] = (int) $this->request->get('LETTER_ID');
		}
		if (empty($this->arParams['CAMPAIGN_ID']))
		{
			$this->arParams['CAMPAIGN_ID'] = (int) $this->request->get('CAMPAIGN_ID');
		}
	}

	protected function preparePost()
	{

	}
	protected function getCsvClosure()
	{
		$statusList = PostingRecipientTable::getStatusList();
		return function ($item) use ($statusList)
		{
			foreach (['IS_READ', 'IS_CLICK', 'IS_UNSUB'] as $key)
			{
				$item[$key] = $item[$key] === 'Y' ? Loc::getMessage('SENDER_LETTER_RCP_UI_YES') : null;
			}
			if
			(
				isset($item['DATE_SENT']) &&
				$item['STATUS'] === PostingRecipientTable::SEND_RESULT_NONE &&
				$item['CONSENT_STATUS'] !== ContactTable::CONSENT_STATUS_NEW &&
				Message\Adapter::create($item['MESSAGE_CODE'])->getTransport()->isConsentAvailable()
			)
			{
				switch ($item['CONSENT_STATUS'])
				{
					case ContactTable::CONSENT_STATUS_ACCEPT:
						$item['STATUS'] = Loc::getMessage("SENDER_CONTACT_CONSENT_APPLY");
						break;
					case ContactTable::CONSENT_STATUS_DENY:
						$item['STATUS'] = Loc::getMessage("SENDER_CONTACT_CONSENT_REJECT");
						break;
					case ContactTable::CONSENT_STATUS_WAIT:
						$item['STATUS'] = Loc::getMessage("SENDER_CONTACT_CONSENT_WAIT");
						break;
				}
			}
			else
			{
				$item['STATUS'] = $statusList[$item['STATUS']];
			}
			return $item;
		};
	}
	protected function prepareExport()
	{
		$list = PostingRecipientTable::getList([
			'select' => $this->getDataSelectedFields(),
			'filter' => $this->getDataFilter(),
			'order' => $this->getGridOrder()
		]);
		DataExport::toCsv(
			$this->getUiGridColumns(),
			$list,
			$this->getCsvClosure()
		);
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			$GLOBALS['APPLICATION']->SetTitle(
				$this->arParams['CONTACT_ID']
					?
					Loc::getMessage('SENDER_LETTER_RCP_LIST_TITLE_CONTACT')
					:
					Loc::getMessage('SENDER_LETTER_RCP_LIST_TITLE')
			);
		}

		if (!Security\Access::getInstance()->canViewSegments())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		if ($this->arParams['LETTER_ID'])
		{
			$this->letter = Entity\Letter::createInstanceById($this->arParams['LETTER_ID']);
		}
		elseif ($this->arParams['CONTACT_ID'])
		{
			$this->letter = Entity\Letter::createInstanceByContactId($this->arParams['CONTACT_ID']);
		}
		if (!$this->letter && !$this->arParams['CAMPAIGN_ID'])
		{
			Security\AccessChecker::addError($this->errors, Security\AccessChecker::ERR_CODE_NOT_FOUND);
			return false;
		}
		$this->letter = $this->letter ?: new Entity\Rc();

		$messageCode = $this->letter->getMessage()->getCode();
		$this->arParams['GRID_ID'] .= '_' . $messageCode;
		$this->arParams['FILTER_ID'] .= '_' . $messageCode;

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
		if ($this->request->get('export'))
		{
			$this->prepareExport();
		}

		// create nav
		$nav = new PageNavigation("page-sender-contact-recipient-" . $messageCode);
		$nav->allowAllRecords(false)->setPageSize(10)->initFromUri();

		// get rows
		$statusList = PostingRecipientTable::getStatusList();
		$ids =
			PostingRecipientTable::query()
				->setSelect(['ID'])
				->setFilter($this->getDataFilter())
				->setOffset($nav->getOffset())
				->setLimit($nav->getLimit())
				->setOrder($this->getGridOrder())
				->fetchAll()
		;

		$ids = array_column($ids, 'ID');

		$list = !empty($ids)
			? PostingRecipientTable::query()
				->setSelect($this->getDataSelectedFields())
				->whereIn('ID', $ids)
				->setOrder($this->getGridOrder())
				->fetchAll()
			: []
		;

		/**
		 * @var \Bitrix\Sender\EO_PostingRecipient $itemObject
		 */
		foreach ($list as $item)
		{
			foreach (['IS_READ', 'IS_CLICK', 'IS_UNSUB'] as $key)
			{
				$item[$key] = $item[$key] === 'Y' ? Loc::getMessage('SENDER_LETTER_RCP_UI_YES') : null;
			}
			if
			(
				isset($item['DATE_SENT']) &&
				$item['STATUS'] === PostingRecipientTable::SEND_RESULT_NONE &&
				$item['CONSENT_STATUS'] !== ContactTable::CONSENT_STATUS_NEW &&
				$this->letter->getMessage()->getTransport()->isConsentAvailable()
			)
			{
				switch ($item['CONSENT_STATUS'])
				{
					case ContactTable::CONSENT_STATUS_ACCEPT:
						$item['STATUS'] = Loc::getMessage("SENDER_CONTACT_CONSENT_APPLY");
						break;
					case ContactTable::CONSENT_STATUS_DENY:
						$item['STATUS'] = Loc::getMessage("SENDER_CONTACT_CONSENT_REJECT");
						break;
					case ContactTable::CONSENT_STATUS_WAIT:
						$item['STATUS'] = Loc::getMessage("SENDER_CONTACT_CONSENT_WAIT");
						break;
				}
			}
			else
			{
				$item['STATUS'] = $statusList[$item['STATUS']];
			}
			$message = Message\Factory::getMessage($item['MESSAGE_CODE']);
			$item['MESSAGE_CODE'] = $message ? $message->getName() : $item['MESSAGE_CODE'];
			$item['NAME'] = $item['NAME'] || $item['CODE'] ? $item['NAME'] : Loc::getMessage('SENDER_CONTACT_DELETED') ;
			$item['URLS'] = [
				'LETTER_EDIT' => in_array($item['MESSAGE_CODE'], Message\Factory::getMailingMessageCodes())
					?
					str_replace('#id#', $item['LETTER_ID'], $this->arParams['PATH_TO_LETTER_EDIT'])
					:
					null,
			];

			$this->arResult['ROWS'][] = $item;
		}
		$count =
			PostingRecipientTable::query()
				->setSelect(['CNT'])
				->registerRuntimeField('', new ExpressionField('CNT', 'COUNT(*)'))
				->setFilter($this->getDataFilter())
				->setOrder(['CNT' => 'DESC'])
				->exec()
				->fetch()['CNT']
		;

		$this->arResult['TOTAL_ROWS_COUNT'] = $count ?? 0;

		// set rec count to nav
		$nav->setRecordCount($this->arResult['TOTAL_ROWS_COUNT']);
		$this->arResult['NAV_OBJECT'] = $nav;

		return true;
	}

	protected function getDataSelectedFields()
	{
		return [
			'ID', 'NAME' => 'CONTACT.NAME', 'CODE' => 'CONTACT.CODE',
			'LETTER_TITLE' => 'POSTING.LETTER.TITLE',
			'LETTER_ID' => 'POSTING.LETTER.ID',
			'CONSENT_STATUS' => 'CONTACT.CONSENT_STATUS',
			'MESSAGE_CODE' => 'POSTING.LETTER.MESSAGE_CODE',
			'DATE_SENT', 'STATUS',
			'IS_READ', 'IS_CLICK', 'IS_UNSUB'
		];
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString = trim($filterOptions->getSearchString());

		$filter = [];
		if ($searchString)
		{
			$filter['CONTACT.NAME'] = '%' . $searchString . '%';

		}
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['CONTACT.NAME'] = '%' . $requestFilter['NAME'] . '%';
		}
		if (isset($requestFilter['CODE']) && $requestFilter['CODE'])
		{
			$filter['CONTACT.CODE'] = '%' . $requestFilter['CODE'] . '%';
		}
		if (isset($requestFilter['TYPE_ID']) && $requestFilter['TYPE_ID'])
		{
			$filter['=CONTACT.TYPE_ID'] = $requestFilter['TYPE_ID'];
		}
		if (isset($requestFilter['STATUS']) && $requestFilter['STATUS'])
		{
			if ($requestFilter['STATUS'] === 'APPLY')
			{
				$filter['!==DATE_SENT'] = null;
				$filter['=STATUS'] = PostingRecipientTable::SEND_RESULT_NONE ;
				$filter['=CONTACT.CONSENT_STATUS'] = ContactTable::CONSENT_STATUS_ACCEPT;
			}
			elseif($requestFilter['STATUS'] === 'REJECT')
			{
				$filter['!==DATE_SENT'] = null;
				$filter['=STATUS'] = PostingRecipientTable::SEND_RESULT_NONE;
				$filter['=CONTACT.CONSENT_STATUS'] = ContactTable::CONSENT_STATUS_DENY;
			}
			else
			{
				$filter['=STATUS'] = $requestFilter['STATUS'];
			}
		}
		if (isset($requestFilter['IS_READ']) && in_array($requestFilter['IS_READ'], ['Y', 'N']))
		{
			$filter['=IS_READ'] = $requestFilter['IS_READ'];
		}
		if (isset($requestFilter['IS_CLICK']) && in_array($requestFilter['IS_CLICK'], ['Y', 'N']))
		{
			$filter['=IS_CLICK'] = $requestFilter['IS_CLICK'];
		}
		if (isset($requestFilter['IS_UNSUB']) && in_array($requestFilter['IS_UNSUB'], ['Y', 'N']))
		{
			$filter['=IS_UNSUB'] = $requestFilter['IS_UNSUB'];
		}

		if (!empty($this->arParams['LETTER_ID']))
		{
			$postingData = $this->letter->getLastPostingData();
			$filter['=POSTING_ID'] = $postingData['POSTING_ID'];
		}

		if ($this->arParams['CAMPAIGN_ID'])
		{
			$filter['=POSTING.MAILING_ID'] = $this->arParams['CAMPAIGN_ID'];
		}

		if ($this->arParams['CONTACT_ID'])
		{
			$filter['=CONTACT_ID'] = $this->arParams['CONTACT_ID'];
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
		$list = [
			[
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			],
		];

		if ($this->arParams['CONTACT_ID'] || $this->arParams['CAMPAIGN_ID'])
		{
			$list[] = [
				"id" => "LETTER_TITLE",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_LETTER_TITLE'),
				"sort" => "LETTER_TITLE",
				"default" => true
			];
		}
		if (!$this->arParams['CONTACT_ID'])
		{
			$list[] = [
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_CODE'),
				"sort" => "CONTACT.CODE",
				"default" => true
			];
			$list[] = [
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_NAME'),
				"sort" => "CONTACT.NAME",
				"default" => true
			];
		}

		$list[] = [
			"id" => "STATUS",
			"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_STATUS'),
			"sort" => "STATUS",
			"default" => true
		];
		$list[] = [
			"id" => "DATE_SENT",
			"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_SENT'),
			"sort" => "DATE_SENT",
			"default" => !!$this->arParams['CONTACT_ID'],
		];

		if ($this->letter->getMessage()->hasStatistics())
		{
			$list[] = [
				"id" => "IS_READ",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_READ'),
				"sort" => "IS_READ",
				"default" => true,
			];
		}

		if ($this->letter->getMessage()->hasStatistics())
		{
			$list[] = [
				"id" => "IS_CLICK",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_CLICK'),
				"sort" => "IS_CLICK",
				"default" => true,
			];
		}

		if ($this->letter->getMessage()->hasStatistics())
		{
			$list[] = [
				"id" => "IS_UNSUB",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_UNSUB'),
				"sort" => "IS_UNSUB",
				"default" => true,
			];
		}

		return $list;
	}

	protected function setUiFilter()
	{
		$campaignList = [];
		$list = Entity\Campaign::getList([
			'select' => ['ID', 'NAME'],
			'order' => ['NAME' => 'ASC']
		])->fetchAll();
		if (count($list) > 0)
		{
			foreach ($list as $item)
			{
				$campaignList[$item['ID']] = $item['NAME'];
			}
		}

		$this->arResult['FILTERS'] = [
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_NAME'),
				"default" => true,
			],
			[
				"id" => "CODE",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_CODE'),
				"default" => true,
			],
			[
				"id" => "STATUS",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_STATUS'),
				"type" => "list",
				"default" => false,
				"items" => PostingRecipientTable::getStatusList()
				+ (!($this->letter->getMessage()->getTransport()->isConsentAvailable())?
					[]:
					[
						'REJECT' => Loc::getMessage("SENDER_CONTACT_CONSENT_REJECT"),
						'APPLY' => Loc::getMessage("SENDER_CONTACT_CONSENT_APPLY")
					])
			],
		];
		if ($this->letter->getMessage()->hasStatistics())
		{
			$this->arResult['FILTERS'][] = [
				"id" => "IS_READ",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_READ'),
				"type" => "checkbox",
				"default" => true,
			];
			$this->arResult['FILTERS'][] = [
				"id" => "IS_CLICK",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_CLICK'),
				"type" => "checkbox",
				"default" => true,
			];
			$this->arResult['FILTERS'][] = [
				"id" => "IS_UNSUB",
				"name" => Loc::getMessage('SENDER_LETTER_RCP_UI_COLUMN_UNSUB'),
				"type" => "checkbox",
				"default" => true,
			];
		}
	}

	protected function getUiFilterPresets()
	{
		$list = [];
		if ($this->letter->getMessage()->getTransport()->isConsentAvailable())
		{
			$list['filter_recipient_apply'] = [
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_APPLY'),
				'fields' => [
					'STATUS' => 'APPLY',
				]
			];
			$list['filter_recipient_reject'] = [
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_REJECT'),
				'fields' => [
					'STATUS' => 'REJECT'
				]
			];
		}
		if ($this->letter->getMessage()->hasStatistics())
		{
			$list['filter_recipient_read'] = [
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_READ'),
				'fields' => array(
					'IS_READ' => 'Y',
				)
			];
			$list['filter_recipient_click'] = [
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_CLICK'),
				'fields' => array(
					'IS_CLICK' => 'Y',
				)
			];
			$list['filter_recipient_unsub'] = [
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_UNSUB'),
				'fields' => array(
					'IS_UNSUB' => 'Y',
				)
			];
		}
		return $list + array(
			'filter_recipient_sent' => array(
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_SENT'),
				'fields' => array(
					'STATUS' => PostingRecipientTable::SEND_RESULT_SUCCESS,
				)
			),
			'filter_recipient_unsent' => array(
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_UNSENT'),
				'fields' => array(
					'STATUS' => PostingRecipientTable::SEND_RESULT_NONE,
				)
			),
			'filter_recipient_error' => array(
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_ERROR'),
				'fields' => array(
					'STATUS' => PostingRecipientTable::SEND_RESULT_ERROR,
				)
			),
			'filter_recipient_all' => array(
				'name' => Loc::getMessage('SENDER_LETTER_RCP_UI_PRESET_ALL'),
				'default' => true,
				'fields' => []
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

	protected function printErrors()
	{
		// temporary include main popup extension on show error
		Extension::load(["main.popup"]);
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		parent::executeComponent();

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_SEGMENT_CLIENT_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SEGMENT_CLIENT_VIEW;
	}
}