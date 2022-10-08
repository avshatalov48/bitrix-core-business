<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\UI\Filter;
use Bitrix\Mail\Internals\MailContactTable;

/**
 * Class AddressBookComponent
 */
class AddressBookComponent extends CBitrixComponent implements Controllerable
{
	public function configureActions(): array
	{
		return [];
	}

	protected $rowsCount = 20;

	private function getRowsCount(): int
	{
		return $this->rowsCount;
	}

	protected $gridId = 'MAIL_ADDRESSBOOK_LIST';

	private function canEdit(): bool
	{
		global $USER;
		if (!(is_object($USER) && $USER->IsAuthorized()))
		{
			return false;
		}

		return true;
	}

	private function getDataFilter(): array
	{
		$filterOptions = new Filter\Options($this->gridId);
		$requestFilter = $filterOptions->getFilter($this->arResult['FILTER']);
		$searchString = $filterOptions->getSearchString();

		$filter = [];
		if (isset($requestFilter['NAME']) && $requestFilter['NAME'])
		{
			$filter['NAME'] = "%".$requestFilter['NAME']."%";
		}
		if (isset($requestFilter['EMAIL']) && $requestFilter['EMAIL'])
		{
			$filter['EMAIL'] = "%".$requestFilter['EMAIL']."%";
		}

		global $USER;
		if ((is_object($USER) && $USER->IsAuthorized()))
		{
			$filter[] = [
				'USER_ID' => $USER->getId(),
			];
		}

		if ($searchString)
		{
			$filter[] = [
				'LOGIC' => 'OR',
				'NAME' => "%".$searchString."%",
				'EMAIL' => "%".$searchString."%",
			];
		}

		return $filter;
	}

	private function setUiFilter()
	{
		$this->arResult['FILTER'] = [
			[
				'id' => "NAME",
				'name' => Loc::getMessage('MAIL_ADDRESSBOOK_LIST_COLUMN_NAME'),
				'default' => true,
			],
			[
				'id' => "EMAIL",
				'name' => Loc::getMessage('MAIL_ADDRESSBOOK_LIST_COLUMN_EMAIL'),
				'default' => true,
			],
		];
	}

	private function setColumns()
	{
		$this->arResult['COLUMNS'] = [
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('MAIL_ADDRESSBOOK_LIST_COLUMN_NAME'),
				'sort' => 'NAME',
				'default' => true,
				'editable' => false
			],
			[
				'id' => 'EMAIL',
				'name' => Loc::getMessage('MAIL_ADDRESSBOOK_LIST_COLUMN_EMAIL'),
				'sort' => 'EMAIL',
				'default' => true,
				'editable' => false
			],
		];
	}

	private function setRows($gridSorting)
	{
		$pageNavigationObject = new PageNavigation("page");
		$pageNavigationObject->allowAllRecords(true)->setPageSize($this->getRowsCount())->initFromUri();

		$list = MailContactTable::getList(
			[
				'offset' => $pageNavigationObject->getOffset(),
				'limit' => $pageNavigationObject->getLimit(),
				'filter' => $this->getDataFilter(),
				'order' => $gridSorting,
				'select' => ['ID', 'NAME', 'EMAIL'],
				'count_total' => true,
			]
		);

		$count = $list->getCount();
		$this->arResult['ROWS_COUNT'] = $count;
		$pageNavigationObject->setRecordCount($count);
		$this->arResult['NAV_OBJECT'] = $pageNavigationObject;
		$this->arResult['ROWS'] = $list->fetchAll();
	}

	/**
	 * @return false|mixed|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function executeComponent()
	{
		global $USER;

		$this->setUiFilter();

		$this->arResult['canEdit'] = $this->canEdit();
		$this->arResult['IS_AUTHORIZED'] = true;
		$this->arResult['FILTER_ID'] = $this->gridId;
		$this->arResult['GRID_ID'] = $this->gridId;

		if (!Loader::includeModule('mail'))
		{
			return false;
		}

		if (!(is_object($USER) && $USER->IsAuthorized()))
		{
			$this->arResult['IS_AUTHORIZED'] = false;
			$this->includeComponentTemplate();

			return false;
		}

		$this->processGridRequests($this->arResult['GRID_ID']);
		$this->setColumns();

		$gridOptions = new \Bitrix\Main\Grid\Options($this->arResult['GRID_ID']);
		$gridSorting = $gridOptions->getSorting(
			["sort" => ["NAME" => "ASC"]]
		);
		$this->arResult['SORT'] = $gridSorting['sort'];

		$this->setRows($this->arResult['SORT']);
		$this->includeComponentTemplate();
	}

	private function removeContacts($idSet)
	{
		(new Bitrix\Mail\Controller\AddressBook)->removeContactsAction($idSet);
	}

	private function processGridRequests($gridId)
	{
		$request = $this->request;
		$postAction = 'action_button_'.$gridId;
		if ($request->isPost() && $request->getPost($postAction) && check_bitrix_sessid())
		{
			if ($request->getPost($postAction) == 'delete')
			{
				if ($request->getPost('ID'))
				{
					$this->removeContacts($request->getPost('ID'));
				}
			}
		}
	}
}