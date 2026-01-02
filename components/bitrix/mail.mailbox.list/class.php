<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die;
}

use Bitrix\Mail\Grid\MailboxSettingsGrid\MailboxGrid;
use Bitrix\Mail\Grid\MailboxSettingsGrid\Settings\MailboxSettings;
use Bitrix\Mail\Helper\MailboxSettingsGridHelper;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;

class CMailMailboxListComponent extends CBitrixComponent
{
	protected string $filterId = 'MAIL_EMPLOYEE_MAILBOX_LIST';
	protected const DEFAULT_PAGE_SIZE = 20;
	private ?MailboxGrid $grid = null;
	private MailboxSettingsGridHelper $mailboxHelper;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->mailboxHelper = new MailboxSettingsGridHelper();
	}

	public function executeComponent(): void
	{
		$currentUser = CurrentUser::get();
		if (!$currentUser->isAdmin())
		{
			showError('access denied');

			return;
		}

		$this->arResult = $this->prepareData();
		$this->includeComponentTemplate();
	}

	protected function prepareData(): array
	{
		$result = [];
		$result['GRID_ID'] = $this->filterId;
		$result['FILTER_ID'] = $this->filterId;
		$result['TITLE'] = Loc::getMessage('MAIL_MAILBOX_LIST_TITLE');

		$grid = $this->getGrid();
		$grid->processRequest();

		$grid->setRawRowsWithLazyLoadPagination(function (array $ormParams) {
			$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId);
			$filterData = $filterOptions->getFilter();

			return $this->mailboxHelper->getGridDataWithOrmParams($ormParams, $filterData);
		});

		$result['GRID_PARAMS'] = \Bitrix\Main\Grid\Component\ComponentParams::get(
			$grid,
		);

		$result['GRID_FILTER'] = $grid->getFilter();
		$result['FILTER_PRESETS'] = $grid->getFilter()?->getFilterPresets();

		$result['GRID_PARAMS']['ALLOW_SORT'] = false;
		$result['GRID_PARAMS']['SHOW_PAGINATION'] = true;
		$result['GRID_PARAMS']['SHOW_TOTAL_COUNTER'] = false;
		$result['GRID_PARAMS']['SHOW_PAGESIZE'] = true;

		return $result;
	}

	private function getGrid(): MailboxGrid
	{
		if ($this->grid === null)
		{
			$settings = new MailboxSettings([
				'ID' => $this->filterId,
			]);

			$this->grid = new MailboxGrid($settings);
			$this->grid->setTotalCountCalculator(function () {
				return $this->mailboxHelper->getTotalCount();
			});
		}

		return $this->grid;
	}
}
