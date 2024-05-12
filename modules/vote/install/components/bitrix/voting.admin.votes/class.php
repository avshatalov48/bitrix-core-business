<?php

use Bitrix\Vote;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class VotingAdminVotesComponent extends \CBitrixComponent implements Controllerable, Errorable
{
	protected Main\ErrorCollection $errorCollection;
	private static array $readChannels;
	private static array $adminChannels;

	private bool $canWrite = false;

	protected string $gridId = 'vote_admin_votes';
	protected string $filterId = 'vote_admin_votes_filter';

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new Main\ErrorCollection();
	}

	public function onPrepareComponentParams($arParams)
	{
		if (!Loader::includeModule('vote'))
		{
			$this->errorCollection->setError(new Error('Module "vote" is not installed.'));
		}

		global $APPLICATION;
		if (!(($moduleRights = $APPLICATION->GetGroupRight('vote')) && $moduleRights > 'D'))
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('ACCESS_DENIED')));
		}
		else
		{
			$this->canWrite = $moduleRights >= 'W';
		}

		$arParams['GRID_ID'] = $this->gridId;
		$arParams['FILTER_ID'] = $this->getFilterId();

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		if ($this->errorCollection->isEmpty())
		{
			$this->processGetAction() && $this->processGridAction();
			$this->prepareResult();
		}
		$this->arResult['ERRORS'] = $this->errorCollection->getValues();

		$this->includeComponentTemplate();
	}

	protected function prepareResult()
	{
		$this->arResult['COLUMNS'] = $this->getColumns();
		$this->arResult['FILTERS'] = $this->getFilters();
		$this->arResult['CHANNELS'] = $this->getReadChannels();
		$this->arResult['ADMIN_CHANNELS'] = $this->getAdminChannels();
		$this->arResult['FILTER'] = $this->getFilter();

		$nav = new PageNavigation('page');
		$nav->allowAllRecords(false)->setPageSize(20)->initFromUri();

		$result = Vote\VoteTable::getList([
			'select' => ['*', 'AUTHOR', 'LAMP'],
			'filter' => $this->arResult['FILTER'],
			'order' => $this->getOrder($this->gridId),
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit() + 1,
		]);

		$votes = $result->fetchCollection();
		if ($votes->count() > $nav->getLimit())
		{
			$allVotes = $votes->getAll();
			$votes->remove(end($allVotes));
		}

		$nav->setRecordCount($nav->getOffset() + $votes->count() + 1);

		$this->arResult['ITEMS'] = $votes;
		$this->arResult['ROWS'] = [];
		$this->arResult['NAV_OBJECT'] = $nav;
		$this->arResult['ACTION_PANEL'] = $this->getGroupActions();
	}

	protected function getReadChannels(): array
	{
		if (!isset(self::$readChannels))
		{
			global $USER;
			$channels = [];
			if ($dbRes = Vote\Channel::getList([
				'select' => ['ID', 'TITLE'],
				'filter' => (!$this->canWrite ? [
					'ACTIVE' => 'Y',
					'HIDDEN' => 'N',
					'>=PERMISSION.PERMISSION' => 1,
					'PERMISSION.GROUP_ID' => $USER->GetUserGroupArray()
				] : []),
				'order' => [
					'TITLE' => 'ASC'
				],
				'group' => ['ID']
			]))
			{
				while ($res = $dbRes->Fetch())
				{
					$channels[$res['ID']] = $res['TITLE'];
				}
			}
			self::$readChannels = $channels;
		}

		return self::$readChannels;
	}

	protected function getAdminChannels(): array
	{
		if (!isset(self::$adminChannels))
		{
			if ($this->canWrite)
			{
				self::$adminChannels = $this->getReadChannels();
			}
			else
			{
				global $USER;
				$channels = [];
				if ($dbRes = Vote\Channel::getList([
					'select' => ['ID', 'TITLE'],
					'filter' => [
						'ACTIVE' => 'Y',
						'HIDDEN' => 'N',
						'>=PERMISSION.PERMISSION' => 4,
						'PERMISSION.GROUP_ID' => $USER->GetUserGroupArray()
					],
					'order' => ['TITLE' => 'ASC'],
					'group' => ['ID']
				]))
				{
					while ($res = $dbRes->Fetch())
					{
						$channels[$res['ID']] = $res['TITLE'];
					}
				}
				self::$adminChannels = $channels;
			}
		}

		return self::$adminChannels;
	}

	protected function getColumns()
	{
		$entity = Vote\VoteTable::getEntity();

		return [
			['id' => 'ID', 'name' => $entity->getField('ID')->getTitle(), 'sort' => 'ID', 'default' => true],
			['id' => 'LAMP', 'name' => $entity->getField('LAMP')->getTitle(), 'default' => true],
			['id' => 'TITLE', 'name' => $entity->getField('TITLE')->getTitle(), 'sort' => 'title',  'default' => true],
			[
				'id' => 'DATE_START',
				'name' => $entity->getField('DATE_START')->getTitle(),
				'editable' => [
					'type' => Main\Grid\Column\Type::CALENDAR,
				],
				'sort' => 'DATE_START',
				'default' => true
			],
			[
				'id' => 'DATE_END',
				'name' => $entity->getField('DATE_END')->getTitle(),
				'editable' => [
					'type' => Main\Grid\Column\Type::CALENDAR,
				],
				'sort' => 'DATE_END',
				'default' => true
			],
			[
				'id' => 'AUTHOR_ID',
				'name' => $entity->getField('AUTHOR_ID')->getTitle(),
				'sort' => 'AUTHOR_ID',
				'default' => true
			],
			[
				'id' => 'CHANNEL_ID',
				'name' => $entity->getField('CHANNEL_ID')->getTitle(),
				'necessary' => true,
				'editable' => [
					'TYPE' => Main\Grid\Editor\Types::DROPDOWN,
					'items' => $this->getReadChannels(),
				],
				'multiple' => false,
				'sort' => 'channel_id',
				'default' => false
			],
			[
				'id' => 'ACTIVE',
				'name' => $entity->getField('ACTIVE')->getTitle(),
				'necessary' => true,
				'editable' => [
					'TYPE' => Main\Grid\Editor\Types::CHECKBOX,
					'VALUE' => 'Y'
				],
				'multiple' => false,
				'sort' => 'active',
				'default' => false
			],
			[
				'id' => 'C_SORT',
				'name' => $entity->getField('C_SORT')->getTitle(),
				'sort' => 'C_SORT',
				'editable' => [
					'TYPE' => Main\Grid\Editor\Types::NUMBER,
				],
				'default' => false
			],
			[
				'id' => 'COUNTER',
				'name' => $entity->getField('COUNTER')->getTitle(),
				'sort' => 'counter',
				'default' => true
			],
		];
	}

	protected function getFilters()
	{
		$entity = Vote\VoteTable::getEntity();

		return [
			[
				'id' => 'VOTE_ID',
				'default' => true,
				'name' => $entity->getField('ID')->getTitle(),
			],
		];
	}

	protected function getFilterToEdit(): array
	{
		return $this->canWrite ? [] : ['@CHANNEL_ID' => array_keys($this->getAdminChannels())];
	}

	protected function getFilterId(): string
	{
		$channelId = $this->request->getQuery('find_channel_id');
		return $this->filterId . ($channelId ? ( '_' . $channelId) : '');
	}

	protected function getFilter(): array
	{
		$filterId = $this->getFilterId();
		$filterOptions = new FilterOptions($filterId);

		$requestFilter = $filterOptions->getFilter($this->getFilters());

		$filter = $this->canWrite ? [] : ['@CHANNEL_ID' => array_keys($this->getReadChannels())];

		if (!empty($requestFilter['FIND']))
		{
			$filter[] = [
				'LOGIC' => 'OR',
				'%=TITLE' => '%' . $requestFilter['FIND'] . '%',
				'%=DESCRIPTION' => '%' . $requestFilter['FIND'] . '%',
			];
		}
		if (!empty($requestFilter['CHANNEL_ID']))
		{
			$filter['=CHANNEL_ID'] = $requestFilter['CHANNEL_ID'];
		}

		$channelId = $filter['=CHANNEL_ID'] ?? 0;
		$needChannelId = $this->request->getQuery('find_channel_id') ?? 0;
		if ($needChannelId > 0 && $channelId != $needChannelId)
		{
			$filterOptions->setFilterSettings(
				$filterId,
				[
					'name' => Vote\VoteTable::getEntity()->getField('CHANNEL_ID')->getTitle()
						. ': ' . ($this->getReadChannels()[$needChannelId] ?? $needChannelId),
					'fields' => [
						'CHANNEL_ID' => $needChannelId
					],
				],
				true,
				false
			);
			$filterOptions->save();
			$filter['=CHANNEL_ID'] = $needChannelId;
		}
		if (!empty($requestFilter['VOTE_ID']))
		{
			$filter['=ID'] = $requestFilter['VOTE_ID'];
		}

		return $filter;
	}

	/**
	 * @return array
	 */
	protected function getOrder(string $gridId)
	{
		$defaultSort = ['ID' => 'desc'];
		$gridOptions = new GridOptions($gridId);
		$sorting = $gridOptions->getSorting(['sort' => $defaultSort]);

		$by = key($sorting['sort']);
		$order = $sorting['sort'][$by] === 'asc' ? 'asc' : 'desc';

		foreach ($this->getColumns() as $column)
		{
			if (isset($column['sort']) && $column['sort'] === $by)
			{
				return [$by => $order];
			}
		}

		return $defaultSort;
	}

	protected function getGroupActions(): array
	{
		$snippet = new Main\Grid\Panel\Snippet();

		return ['GROUPS' => [['ITEMS' => [
			$snippet->getEditButton(),
			$snippet->getRemoveButton()
		]]]];
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * Getting array of errorCollection.
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/** Comes from neighbour pages  */
	private function processGetAction(): bool
	{
		$request = $this->request;

		if (check_bitrix_sessid() && $request->isAdminSection())
		{
			if ($request->getPost('action') === 'delete')
			{
				$id = $request->get('ID');
				if ($id > 0 && !$this->deleteRowAction($id))
				{
					$this->errorCollection->setError(new Main\Error(
						Loc::getMessage('VOTE_VOTE_IS_NOT_DELETED'),
						$id
					));
				}
			}
			else if ($request->getQuery('reset_id') > 0)
			{
				/* Here we have to redirect to the source page */
				$id = $request->getQuery('reset_id');

				if ($this->resetRowAction($id))
				{
					$url = (new \Bitrix\Main\Web\Uri($request->getRequestUri()))
						->deleteParams(array('reset_id', 'sessid'))
						->getLocator();
					LocalRedirect($url);
				}
				else
				{
					$this->errorCollection->setError(new Main\Error(
						Loc::getMessage('VOTE_VOTE_IS_NOT_NULLED'),
						$id
					));
				}
			}
		}

		return $this->errorCollection->isEmpty();
	}

	private function processGridAction()
	{
		$request = $this->request;

		if (
			$request->isPost() &&
			check_bitrix_sessid() &&
			Main\Grid\Context::isInternalRequest() &&
			$request->get('grid_id') == $this->gridId
		)
		{
			$id = $request->getPost('id');
			if (
				$request->getPost('action') === Main\Grid\Actions::GRID_DELETE_ROW
				&& !$this->deleteRowAction($id)
			)
			{
				$this->errorCollection->setError(new Main\Error(
					Loc::getMessage('VOTE_VOTE_IS_NOT_DELETED'),
					$id
				));
			}

		}

		return $this->errorCollection->isEmpty();
	}

	public function deleteRowAction(int $id): bool
	{
		if (Vote\VoteTable::getList([
			'select' => ['ID'],
			'filter' => $this->getFilterToEdit() + ['=ID' => $id],
		])->fetch())
		{
			CVote::Delete($id);
			return true;
		}

		return false;
	}

	public function resetRowAction(int $id): bool
	{
		if (Vote\VoteTable::getList([
			'select' => ['ID'],
			'filter' => $this->getFilterToEdit() + ['=ID' => $id],
		])->fetch())
		{
			CVote::Reset($id);
			return true;
		}

		return false;
	}
}
