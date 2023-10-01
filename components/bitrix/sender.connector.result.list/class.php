<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Posting\SegmentDataBuilder;
use Bitrix\Sender\Recipient;
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

class SenderConnectorResultListComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (empty($this->arParams['ENDPOINT']['CODE']))
		{
			$this->errors->setError(new Error('Empty connector endpoint.'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_CONTACT_LIST_GRID';
		$this->arParams['FILTER_ID'] = isset($this->arParams['FILTER_ID']) ? $this->arParams['FILTER_ID'] : $this->arParams['GRID_ID'] . '_FILTER';
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] === 'Y' : true;

		if (!isset($this->arParams['ENDPOINT']))
		{
			$endpoint = [];
			$code = $this->request->get('code');
			$fields = $this->request->get('fields');
			if ($code)
			{
				$code = explode('_', $code);
				if (!empty($code[0]))
				{
					$endpoint['MODULE_ID'] = $code[0];
				}
				array_shift($code);
				$endpoint['CODE'] = implode('_', $code);
			}
			if ($fields)
			{
				$endpoint['FIELDS'] = \Bitrix\Main\Web\Json::decode($fields);
			}
			$this->arParams['ENDPOINT'] = $endpoint;
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_CONTACT_LIST_TITLE1'));
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		/*
		$endpoint = [
			'MODULE_ID' => 'sender',
			'CODE' => 'crm_client',
			'FIELDS' => [
				'SENDER_SELECT_ALL' => 'Y',
				'BX_PRESET_ID' => 'Y',
				'STATUS_SEMANTIC_ID' => 'P'
			],
		];
		*/
		$endpoint = $this->arParams['ENDPOINT'];
		$connector = Connector\Manager::getConnector($this->arParams['ENDPOINT']);
		if (!$connector || !$connector->isResultViewable())
		{
			$this->errors->setError(new Error('Unsupportable connector.'));
			return false;
		}

		// set ui filter
		$this->setUiFilter();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page-sender-connector-result-list");
		$nav->allowAllRecords(false)->setPageSize(10)->initFromUri();

		/** @var Connector\Base $connector */
		$connector->getResultView()->modifyColumns($this->arResult['COLUMNS']);
		$connector->getResultView()->modifyFilter($this->arResult['FILTERS']);
		$connector->getResultView()->setNav($nav);
		$title = $connector->getResultView()->getTitle();
		if ($title)
		{
			$GLOBALS['APPLICATION']->SetTitle($title);
		}


		// get rows
		if (is_array($endpoint['FIELDS']))
		{
			$connector->setFieldValues($endpoint['FIELDS'] + $this->getDataFilter());
			$connector->setDataTypeId($this->getDataTypeId());

			$segmentBuilder = null;
			$groupId = $this->request->get('groupId');
			$filterId = $this->request->get('filterId');
			$isIncrementally = $connector instanceof Connector\IncrementallyConnector && $groupId && $filterId;

			if ($isIncrementally)
			{
				$segmentBuilder = new SegmentDataBuilder(
					$this->request->get('groupId'),
					$this->request->get('filterId'),
					$endpoint
				);
				$this->arResult['BUILDING_COMPLETED'] = $segmentBuilder->isBuildingCompleted();
			}

			$result = !$segmentBuilder
				? $connector->getResult()
				: new Connector\Result(
					$segmentBuilder
						->setDataFilter($this->getDataFilter())
						->getData($nav)
				)
			;

			$result->setDataTypeId($connector->getDataTypeId());
			$fetchedCount = 0;
			while ($item = $result->fetchPlain())
			{
				$connector->getResultView()->onDraw($item);
				$this->arResult['ROWS'][] = $item;
				$fetchedCount++;
				if ($fetchedCount >= $nav->getLimit())
				{
					break;
				}
			}
			$connector->getResultView()->setNav(null);
			$this->arResult['TOTAL_ROWS_COUNT'] = !$segmentBuilder
				? $connector->getDataCount()
				: $segmentBuilder->getDataCount();
		}
		else
		{
			$this->arResult['TOTAL_ROWS_COUNT'] = 0;
		}

		// set rec count to nav
		$nav->setRecordCount($this->arResult['TOTAL_ROWS_COUNT']);
		$this->arResult['NAV_OBJECT'] = $nav;

		return true;
	}

	protected function getDataTypeId()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		if (empty($requestFilter['SENDER_RECIPIENT_TYPE_ID']))
		{
			return null;
		}

		$typeId = (int) $requestFilter['SENDER_RECIPIENT_TYPE_ID'];
		if (!in_array($typeId, Recipient\Type::getList()))
		{
			return null;
		}

		return $typeId ?: null;
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);

		$filter = [];
		foreach ($this->arResult['FILTERS'] as $item)
		{
			if (empty($requestFilter[$item['id']]))
			{
				continue;
			}

			$filter[$item['id']] = $requestFilter[$item['id']];
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
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_NAME'),
				"default" => true
			),
			array(
				"id" => "EMAIL",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_EMAIL'),
				"default" => true
			),
			array(
				"id" => "PHONE",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_PHONE'),
				"default" => true
			),
		);
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = array(
			array(
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_NAME'),
				"default" => true,
			),
			array(
				"id" => "EMAIL",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_EMAIL'),
				"default" => true,
			),
			array(
				"id" => "PHONE",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_PHONE'),
				"default" => true,
			),
			array(
				"id" => "SENDER_RECIPIENT_TYPE_ID",
				"name" => Loc::getMessage('SENDER_CONTACT_LIST_UI_COLUMN_RECIPIENT_TYPE_ID'),
				"default" => true,
				"type" => "list",
				"items" => Recipient\Type::getNamedList()
			),
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
	
	public function getEditAction()
	{
		return ActionDictionary::ACTION_SEGMENT_CLIENT_VIEW;
	}
	
	public function getViewAction()
	{
		return ActionDictionary::ACTION_SEGMENT_CLIENT_VIEW;
	}
}