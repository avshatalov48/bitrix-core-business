<?

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\MailingTable;
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

class SenderTriggerListComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	protected function initParams()
	{
		$this->arParams['GRID_ID'] = isset($this->arParams['GRID_ID']) ? $this->arParams['GRID_ID'] : 'SENDER_TRIGGER_GRID';
		parent::initParams();
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
					Entity\TriggerCampaign::removeById($id);
				}
				break;
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var \CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_CAMPAIGN_COMP_TITLE'));
		}

		if (!Security\Access::getInstance()->canViewLetters())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ROWS'] = array();

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';

		// set ui filter
		$this->setUiFilter();

		// set ui grid columns
		$this->setUiGridColumns();

		// create nav
		$nav = new PageNavigation("page-sender-triggers");
		$nav->allowAllRecords(true)->setPageSize(20)->initFromUri();

		// get rows
		$sites = Entity\TriggerCampaign::getSites();
		$list = Entity\TriggerCampaign::getList([
			'select' => [
				'ID', 'NAME', 'DATE_INSERT',
				'ACTIVE', 'IS_PUBLIC', 'SITE_ID'
			],
			'filter' => $this->getDataFilter(),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			'count_total' => true,
			'order' => $this->getGridOrder()
		]);
		foreach ($list as $item)
		{
			// format user name
			$this->setRowColumnUser($item);

			$item['URLS'] = array(
				'EDIT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_EDIT']),
				'STAT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_STAT']),
				'CHAIN' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_CHAIN']),
				'RECIPIENT' => str_replace('#id#', $item['ID'], $this->arParams['PATH_TO_RECIPIENT']),
			);

			$item['SITE_ID'] = $sites[$item['SITE_ID']];
			$item['LETTERS'] = Model\LetterTable::getList([
				'select' => ['ID', 'TITLE'],
				'filter' => ['=IS_TRIGGER' => 'Y', '=CAMPAIGN_ID' => $item['ID']],
				'order' => ['PARENT_ID' => 'ASC']
			])->fetchAll();

			$this->arResult['ROWS'][] = $item;
		}

		$this->arResult['TOTAL_ROWS_COUNT'] = $list->getCount();

		// set rec count to nav
		$nav->setRecordCount($list->getCount());
		$this->arResult['NAV_OBJECT'] = $nav;


		$presets = MailingTable::getPresetMailingList();
		$this->arResult['PRESETS'] = [];
		foreach ($presets as $preset)
		{
			$this->arResult['PRESETS'][] = [
				'CODE' => $preset['CODE'],
				'NAME' => $preset['NAME'],
				'DESC' => $preset['DESC'],
			];
		}

		return true;
	}

	protected function getDataFilter()
	{
		$filterOptions = new FilterOptions($this->arParams['FILTER_ID']);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTERS']);
		$searchString = $filterOptions->getSearchString();

		$filter = [];
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['NAME'] = '%' . $requestFilter['NAME'] . '%';
		}
		if ($searchString)
		{
			$filter['NAME'] = '%' . $searchString . '%';
		}
		if (isset($requestFilter['DATE_INSERT_from']) && $requestFilter['DATE_INSERT_from'])
		{
			$filter['>=DATE_INSERT'] = $requestFilter['DATE_INSERT_from'];
		}
		if (isset($requestFilter['DATE_INSERT_to']) && $requestFilter['DATE_INSERT_to'])
		{
			$filter['<=DATE_INSERT'] = $requestFilter['DATE_INSERT_to'];
		}
		if (isset($requestFilter['ACTIVE']) && $requestFilter['ACTIVE'])
		{
			$filter['=ACTIVE'] = $requestFilter['ACTIVE'];
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
		return [
			[
				"id" => "ID",
				"name" => "ID",
				"sort" => "ID",
				"default" => false
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_DATE_INSERT'),
				"sort" => "DATE_INSERT",
				"default" => false
			],
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_NAME'),
				"sort" => "NAME",
				"default" => true
			],
			[
				"id" => "STATE",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_STATE'),
				"sort" => "ACTIVE",
				"default" => true
			],
			[
				"id" => "LETTER",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_LETTER'),
				"default" => true
			],
			[
				"id" => "STAT",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_STAT'),
				"default" => true
			],
		];
	}

	protected function setUiFilter()
	{
		$this->arResult['FILTERS'] = array(
			[
				"id" => "NAME",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_NAME'),
				"default" => true,
			],
			[
				"id" => "DATE_INSERT",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_DATE_INSERT'),
				"type" => "date",
				"default" => true
			],
			[
				"id" => "ACTIVE",
				"name" => Loc::getMessage('SENDER_CAMPAIGN_COMP_UI_COLUMN_ACTIVE'),
				"type" => "checkbox",
				"default" => true
			],
		);
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
		$data['USER'] = \CAllUser::FormatName(
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
		return ActionDictionary::ACTION_MAILING_EMAIL_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_MAILING_VIEW;
	}
}