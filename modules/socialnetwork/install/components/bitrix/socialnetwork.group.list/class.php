<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Filter;
use Bitrix\Socialnetwork\Component\WorkgroupList\EntityManager;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Internals\EventService\Push\PullDictionary;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupFavoritesTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupTagTable;
use Bitrix\Socialnetwork\WorkgroupViewTable;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Tasks\Internals\Task\ProjectLastActivityTable;

Loader::includeModule('socialnetwork');

class CSocialnetworkGroupListComponent extends WorkgroupList
{
	protected const PRESET_ACTIVE = 'active';
	protected const PRESET_MY = 'my';
	protected const PRESET_FAVORITES = 'favorites';
	protected const PRESET_EXTRANET = 'extranet';
	protected const PRESET_ARCHIVE = 'archive';

	protected string $gridId = 'SONET_GROUP_LIST';
	protected string $filterId = 'SONET_GROUP_LIST';

	protected array $gridColumns;

	protected string $currentPresetId = '';
	protected bool $isExtranetSite = false;
	protected array $gridFilter = [];
	protected array $filterPresets = [];
	protected array $filter = [];
	protected int $pageSize = 10;
	protected bool $hasAccessToTasksCounters = false;

	protected Query $query;
	protected string $queryInitAlias = '';

	protected $fieldsList = [
		'ID',
		'NAME',
		'PROJECT',
		'SCRUM_MASTER_ID',
		'CLOSED',
		'SCRUM',
		'LANDING',
		'PROJECT_DATE_START',
		'PROJECT_DATE_FINISH',
		'ROLE',
		'DATE_RELATION',
		'OWNER_ID',
		'MEMBER_ID',
		'TAG',
		'FAVORITES',
		'DATE_CREATE',
		'DATE_UPDATE',
		'DATE_ACTIVITY',
		'DATE_VIEW',
		'NUMBER_OF_MEMBERS',
		'MEMBERS',
		'TAGS',
		'PRIVACY_TYPE',
		'EFFICIENCY',
		'ACTIVITY_DATE',
	];

	protected $availableEntityFields = [
		'ID',
		'NAME',
		'OPENED',
		'CLOSED',
		'VISIBLE',
		'PROJECT',
		'SCRUM_MASTER_ID',
		'LANDING',
		'PROJECT_DATE_START',
		'PROJECT_DATE_FINISH',
		'SEARCH_INDEX',
		'OWNER_ID',
		'DATE_CREATE',
		'DATE_UPDATE',
		'DATE_ACTIVITY',
		'NUMBER_OF_MEMBERS',
		'IMAGE_ID',
		'AVATAR_TYPE',
		'SCRUM',
		'ACTIVITY_DATE',
		'IS_PINNED',
	];

	protected $actionData;

	protected function listKeysSignedParameters()
	{
		return [
			'FILTER_ID',
			'USER_ID',
			'PATH_TO_USER',
			'PATH_TO_GROUP',
			'PATH_TO_GROUP_EDIT',
			'PATH_TO_GROUP_DELETE',
			'PATH_TO_GROUP_TASKS',
			'PATH_TO_JOIN_GROUP',
			'PATH_TO_LEAVE_GROUP',
			'NAME_TEMPLATE',
			'PAGE',
			'MODE',
			'SUBJECT_ID',
		];
	}

	private function getGridHeaders(): array
	{
		static $result = null;

		if ($result === null)
		{
			$result = [];

			$columns = [
				[
					'id' => 'ID',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_ID'),
					'sort' => 'ID',
					'editable' => false
				],
				[
					'id' => 'NAME',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_NAME'),
					'sort' => 'NAME',
					'default' => true,
					'editable' => false,
				],
				[
					'id' => 'DATE_CREATE',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_CREATE'),
					'sort' => 'DATE_CREATE',
					'default' => ($this->arParams['MODE'] === self::MODE_COMMON),
					'editable' => false,
				],
				[
					'id' => 'PRIVACY_TYPE',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_PRIVACY_TYPE'),
					'default' => true,
					'editable' => false,
				],
				[
					'id' => 'DATE_ACTIVITY',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_ACTIVITY'),
					'sort' => 'DATE_ACTIVITY',
					'default' => ($this->arParams['MODE'] === self::MODE_COMMON),
					'editable' => false,
				],
				[
					'id' => 'NUMBER_OF_MEMBERS',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_NUMBER_OF_MEMBERS'),
					'sort' => 'NUMBER_OF_MEMBERS',
					'default' => false,
					'editable' => false,
				],
				[
					'id' => 'MEMBERS',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_MEMBERS'),
					'default' => true,
					'editable' => false,
				],
				[
					'id' => 'TAGS',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_TAGS'),
					'default' => false,
					'editable' => true,
					'type' => \Bitrix\Main\Grid\Column\Type::TAGS,
				],
			];

			if ($this->arParams['USER_ID'] > 0)
			{
				$columns[] = [
					'id' => 'ROLE',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_ROLE'),
					'default' => true,
					'editable' => false,
				];
				$columns[] = [
					'id' => 'DATE_RELATION',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_RELATION'),
					'sort' => 'DATE_RELATION',
					'default' => false,
					'editable' => false,
				];
				$columns[] = [
					'id' => 'DATE_VIEW',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_VIEW'),
					'sort' => 'DATE_VIEW',
					'default' => false,
					'editable' => false,
				];
			}

			foreach ($columns as $column)
			{
				$fieldId = ($column['fieldId'] ?? $column['id']);
				if (in_array($fieldId, $this->fieldsList, true))
				{
					$result[] = $column;
				}
			}

			$defaultSelectedGridHeaders = $this->getDefaultGridSelectedHeaders();

			foreach ($result as $key => $column)
			{
				if (
					!($column['default'] ?? false)
					&& in_array($column['id'], $defaultSelectedGridHeaders, true)
				)
				{
					$result[$key]['default'] = true;
				}
			}
		}

		return $result;
	}

	private function getTasksGridHeaders(): array
	{
		static $tasksResult = null;

		if ($tasksResult === null)
		{
			$tasksResult = [];

			$columns = [
				[
					'id' => 'ID',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_ID'),
					'sort' => 'ID',
					'editable' => false
				],
				[
					'id' => 'NAME',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_NAME'),
					'sort' => 'NAME',
					'default' => true,
					'editable' => false,
				],
				[
					'id' => 'ACTIVITY_DATE',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_ACTIVITY_DATE'),
					'sort' => 'ACTIVITY_DATE',
					'first_order' => 'desc',
					'default' => true,
					'editable' => false,
				],
			];

			if ($this->arParams['MODE'] !== self::MODE_TASKS_SCRUM)
			{
				$columns[] = [
					'id' => 'EFFICIENCY',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_EFFICIENCY'),
					'default' => true,
					'editable' => false,
				];
			}

			$columns[] = [
				'id' => 'MEMBERS',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_MEMBERS'),
				'default' => true,
				'editable' => false,
			];

			if ($this->arParams['USER_ID'] > 0)
			{
				$columns[] = [
					'id' => 'ROLE',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_ROLE'),
					'default' => true,
					'editable' => false,
				];
			}

			$columns[] = [
				'id' => 'TAGS',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_TAGS'),
				'default' => false,
				'editable' => true,
				'type' => \Bitrix\Main\Grid\Column\Type::TAGS,
			];
			$columns[] = [
				'id' => 'PRIVACY_TYPE',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_PRIVACY_TYPE'),
				'default' => true,
				'editable' => false,
			];

			if ($this->arParams['USER_ID'] > 0)
			{
				$columns[] = [
					'id' => 'DATE_RELATION',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_RELATION'),
					'sort' => 'DATE_RELATION',
					'default' => false,
					'editable' => false,
				];
				$columns[] = [
					'id' => 'DATE_VIEW',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_VIEW'),
					'sort' => 'DATE_VIEW',
					'default' => false,
					'editable' => false,
				];
			}

			if ($this->arParams['MODE'] === self::MODE_TASKS_PROJECT)
			{
				$columns[] = [
					'id' => 'PROJECT_DATE_START',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_PROJECT_DATE_START'),
					'sort' => 'PROJECT_DATE_START',
					'default' => false,
					'editable' => false,
				];

				$columns[] = [
					'id' => 'PROJECT_DATE_FINISH',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_PROJECT_DATE_FINISH'),
					'sort' => 'PROJECT_DATE_FINISH',
					'default' => false,
					'editable' => false,
				];
			}

			$columns[] = [
				'id' => 'DATE_CREATE',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_CREATE'),
				'sort' => 'DATE_CREATE',
				'default' => ($this->arParams['MODE'] === self::MODE_COMMON),
				'editable' => false,
			];
			$columns[] = [
				'id' => 'DATE_ACTIVITY',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_DATE_ACTIVITY'),
				'sort' => 'DATE_ACTIVITY',
				'default' => ($this->arParams['MODE'] === self::MODE_COMMON),
				'editable' => false,
			];
			$columns[] = [
				'id' => 'NUMBER_OF_MEMBERS',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_COLUMN_NUMBER_OF_MEMBERS'),
				'sort' => 'NUMBER_OF_MEMBERS',
				'default' => false,
				'editable' => false,
			];

			foreach ($columns as $column)
			{
				$fieldId = ($column['fieldId'] ?? $column['id']);
				if (in_array($fieldId, $this->fieldsList, true))
				{
					$tasksResult[] = $column;
				}
			}

			$defaultSelectedGridHeaders = $this->getDefaultGridSelectedHeaders();

			foreach ($tasksResult as $key => $column)
			{
				if (
					!($column['default'] ?? false)
					&& in_array($column['id'], $defaultSelectedGridHeaders, true)
				)
				{
					$tasksResult[$key]['default'] = true;
				}
			}
		}

		return $tasksResult;
	}

	private function getStub(): array
	{
		if ($this->isFilterApplied())
		{
			return [
				'title' => Loc::getMessage('SGL_GRID_STUB_NO_FILTER_DATA_TITLE'),
				'description' => Loc::getMessage('SGL_GRID_STUB_NO_FILTER_DATA_DESCRIPTION'),
			];
		}

		if ($this->arParams['MODE'] === self::MODE_TASKS_PROJECT)
		{
			return [
				'title' => Loc::getMessage('SGL_TASKS_PROJECTS_GRID_STUB_TITLE'),
				'description' => Loc::getMessage('SGL_TASKS_PROJECTS_GRID_STUB_DESCRIPTION'),
			];
		}
		else if ($this->arParams['MODE'] === self::MODE_TASKS_SCRUM)
		{
			return [
				'title' => Loc::getMessage('SGL_TASKS_SCRUM_GRID_STUB_TITLE'),
				'description' => Loc::getMessage('SGL_TASKS_SCRUM_GRID_STUB_DESCRIPTION'),
				'migrationTitle' => Loc::getMessage('SGL_TASKS_SCRUM_GRID_STUB_MIGRATION_TITLE'),
				'migrationButton' => Loc::getMessage('SGL_TASKS_SCRUM_GRID_STUB_MIGRATION_BUTTON'),
				'migrationOther' => Loc::getMessage('SGL_TASKS_SCRUM_GRID_STUB_MIGRATION_OTHER'),
			];
		}

		return [
			'title' => Loc::getMessage('SGL_GRID_STUB_TITLE'),
			'description' => Loc::getMessage('SGL_GRID_STUB_DESCRIPTION'),
		];
	}

	private function getDefaultGridHeaders(): array
	{
		$result = [];

		if (in_array($this->arParams['MODE'], self::getTasksModeList(), true))
		{
			$gridHeaders = $this->getTasksGridHeaders();
		}
		else
		{
			$gridHeaders = $this->getGridHeaders();
		}

		foreach ($gridHeaders as $header)
		{
			if (!empty($header['default']))
			{
				$result[] = $header['id'];
			}
		}

		return $result;
	}

	private function getDefaultGridSelectedHeaders(): array
	{
		return [
			'ID',
			'NAME',
		];
	}

	private function getCounter(): string
	{
		return '';
	}

	private function getFilterPresets(): array
	{
		$extranetAvailable = \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability();
		$extranetSiteId = ($extranetAvailable ? Option::get('extranet', 'extranet_site') : '');

		return \Bitrix\Socialnetwork\Integration\Main\UIFilter\Workgroup::getFilterPresetList([
			'currentUserId' => $this->currentUserId,
			'contextUserId' => $this->arParams['USER_ID'],
			'extranetSiteId' => $extranetSiteId,
			'mode' => $this->arParams['MODE'],
		]);
	}

	private function getOrder(): array
	{
		$result = [];

		if (in_array('IS_PINNED', $this->runtimeFieldsManager->get(), true))
		{
			$result['IS_PINNED'] = 'DESC';
		}

		$gridSort = $this->gridOptions->getSorting();

		if (!empty($gridSort['sort']))
		{
			foreach ($gridSort['sort'] as $by => $order)
			{
				$result[$by] = mb_strtoupper($order);
			}
		}
		else
		{
			$result['NAME'] = 'ASC';
		}

		return $result;
	}

	private function checkQueryFieldName($fieldName = ''): bool
	{
		$fieldName = trim($fieldName, '!=<>%*');
		return (
			in_array($fieldName, $this->availableEntityFields)
			|| mb_strpos($fieldName, '.') !== false
		);
	}

	private function addQueryRuntime(): void
	{
		$userId = (int)$this->arParams['USER_ID'];

		if ($userId > 0)
		{
			$joinOn = Join::on('this.ID', 'ref.GROUP_ID')
				->where('ref.USER_ID', $userId);

			$this->query->registerRuntimeField(
				new Reference(
					'CONTEXT_RELATION',
					UserToGroupTable::class,
					$joinOn,
					['join_type' => 'LEFT']
				)
			);
			$this->runtimeFieldsManager->add('CONTEXT_RELATION');
		}

		$this->query->registerRuntimeField(
			new Reference(
				'SITE',
				WorkgroupSiteTable::class,
				Join::on('this.ID', 'ref.GROUP_ID'),
				[ 'join_type' => 'INNER' ]
			)
		);
		$this->runtimeFieldsManager->add('SITE');

		if (ModuleManager::isModuleInstalled('tasks'))
		{
			$this->query->registerRuntimeField(
				'SCRUM',
				new ExpressionField(
					'SCRUM',
					"(CASE WHEN %s = 'Y' AND %s > 0 THEN 'Y' ELSE 'N' END)",
					[ 'PROJECT', 'SCRUM_MASTER_ID' ]
				)
			);
			$this->runtimeFieldsManager->add('SCRUM');
		}

		if ($this->currentUserId > 0)
		{
			$this->query->registerRuntimeField(
				new Reference(
					'FAVORITES',
					WorkgroupFavoritesTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $this->currentUserId),
					['join_type' => 'LEFT']
				)
			);
			$this->runtimeFieldsManager->add('FAVORITES');
		}

		if (
			$this->currentUserId > 0
			&& $userId === $this->currentUserId
		)
		{
			$join = Join::on('this.ID', 'ref.GROUP_ID')
				->where('ref.USER_ID', $this->currentUserId);

			if ((string)$this->arParams['MODE'] === '')
			{
				$join = $join->where(Query::filter()
					->logic('or')
					->whereNull('ref.CONTEXT')
					->where('ref.CONTEXT', '')
				);
			}
			else
			{
				$join = $join->where('ref.CONTEXT', (string)$this->arParams['MODE']);
			}

			$this->query->registerRuntimeField(
				new Reference(
					'PIN',
					WorkgroupPinTable::class,
					$join,
					['join_type' => 'LEFT']
				)
			);
			$this->runtimeFieldsManager->add('PIN');

			$this->query->registerRuntimeField(
				'IS_PINNED',
				new ExpressionField(
					'IS_PINNED',
					WorkgroupPinTable::getSelectExpression(),
					['ID', 'PIN.USER_ID', 'PIN.CONTEXT']
				)
			);
			$this->runtimeFieldsManager->add('IS_PINNED');

		}
	}

	private function addQueryOrder(): void
	{
		$orderFields = $this->getOrder();
		foreach ($orderFields as $fieldName => $value)
		{
			if (!$this->checkQueryFieldName($fieldName))
			{
				continue;
			}

			$this->query->addOrder($fieldName, $value);
		}
	}

	private function addQueryFilter(): void
	{
		$this->addQueryPermissionsFilter();

		$filter = $this->getFilter();

		foreach ($filter as $fieldName => $value)
		{
			if (
				$fieldName === '=MEMBER_ID'
				&& (int)$value > 0
			)
			{
				$this->query->registerRuntimeField(
					new Reference(
						'MEMBER',
						UserToGroupTable::class,
						Join::on('this.ID', 'ref.GROUP_ID'),
						['join_type' => 'INNER']
					)
				);
				$this->runtimeFieldsManager->add('MEMBER');

				$this->query->addFilter('=MEMBER.USER_ID', (int)$value);
				$this->query->addFilter('<=MEMBER.ROLE', UserToGroupTable::ROLE_USER);

				unset($filter[$fieldName]);
				continue;
			}

			if (
				$fieldName === 'INCLUDED_COUNTER'
				&& $this->runtimeFieldsManager->has('TASKS_COUNTER')
			)
			{
				$this->query->where(
					Query::filter()
						->whereNotNull('CONTEXT_RELATION.ID')
						->whereIn('CONTEXT_RELATION.ROLE', UserToGroupTable::getRolesMember())
				);

				$condition = Query::filter()->whereIn('TASKS_COUNTER.TYPE', $value);

				if ($this->runtimeFieldsManager->has('EXCLUDED_COUNTER_EXISTS'))
				{
					$condition->whereNull('EXCLUDED_COUNTER_EXISTS');
				}

				$this->query->where($condition);

				unset($filter[$fieldName]);
				continue;
			}

			if (
				$fieldName === '%=TAG'
				&& (string)$value !== ''
			)
			{
				$this->query->registerRuntimeField(
					new Reference(
						'TAG',
						WorkgroupTagTable::class,
						Join::on('this.ID', 'ref.GROUP_ID'),
						['join_type' => 'INNER']
					)
				);
				$this->runtimeFieldsManager->add('TAG');

				$this->query->addFilter('%=TAG.NAME', (string)$value);
				unset($filter[$fieldName]);
				continue;
			}

			if (
				$fieldName === '=SCRUM'
				&& (string)$value !== ''
				&& $this->runtimeFieldsManager->has('SCRUM')
			)
			{
				$this->query->addFilter('=SCRUM', (string)$value);
				unset($filter[$fieldName]);
				continue;
			}

			if (!$this->checkQueryFieldName($fieldName))
			{
				continue;
			}

			$this->query->addFilter($fieldName, $value);
		}

		if ($this->arParams['SUBJECT_ID'] > 0)
		{
			$this->query->addFilter('=SUBJECT_ID', $this->arParams['SUBJECT_ID']);
		}
	}

	private function addQueryPermissionsFilter(): void
	{
		if ($this->currentUserId > 0)
		{
			$this->query->registerRuntimeField(
				new Reference(
					'CURRENT_RELATION',
					UserToGroupTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $this->currentUserId),
					['join_type' => 'LEFT']
				)
			);
			$this->runtimeFieldsManager->add('CURRENT_RELATION');
		}

		if ($this->currentUserId <= 0)
		{
			$this->query->addFilter('=VISIBLE', 'Y');
		}
		elseif (!CSocNetUser::isCurrentUserModuleAdmin())
		{
			$this->query->addFilter(null, [
				'LOGIC' => 'OR',
				[
					'=VISIBLE' => 'Y',
				],
				[
					'<=CURRENT_RELATION.ROLE' => UserToGroupTable::ROLE_USER,
				],
			]);
		}
	}

	private function addQuerySelect(): void
	{
		$selectFields = $this->getSelect();

		foreach ($selectFields as $fieldName)
		{
			if (is_array($fieldName))
			{
				$alias = array_key_first($fieldName);
				$realFieldName = $fieldName[$alias];
			}
			else
			{
				$realFieldName = $fieldName;
			}

			if (preg_match('/^VIEW\.([a-z_]+)$/i', $realFieldName, $matches))
			{
				$this->query->registerRuntimeField(
					new Reference(
						'VIEW',
						WorkgroupViewTable::class,
						Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $this->currentUserId),
						['join_type' => 'LEFT']
					)
				);
				$this->runtimeFieldsManager->add('VIEW');
			}
			elseif ($realFieldName === 'ACTIVITY_DATE')
			{
				$this->addActivityDateField();
			}
			elseif (in_array($realFieldName, ['MEMBERS', 'TAGS', 'EFFICIENCY'], true))
			{
				$this->selectFieldsManager->add($realFieldName);
			}
/*
			if ($userId > 0)
			{
				$this->query->registerRuntimeField(
					new Reference(
						'CONTEXT_RELATION',
						UserToGroupTable::class,
						Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $userId),
						[ 'join_type' => 'LEFT' ]
					)
				);
			}
*/
			if (!$this->checkQueryFieldName($realFieldName))
			{
				continue;
			}

			$this->query->addSelect($realFieldName);
		}

		if (!$this->runtimeFieldsManager->has('ACTIVITY_DATE'))
		{
			$orderFields = $this->getOrder();
			if (array_key_exists('ACTIVITY_DATE', $orderFields))
			{
				$this->addActivityDateField();
			}
		}
	}

	private function addActivityDateField()
	{
		$helper = Application::getConnection()->getSqlHelper();

		if (Loader::includeModule('tasks'))
		{
			$this->query->registerRuntimeField(
				new Reference(
					'PLA',
					ProjectLastActivityTable::class,
					Join::on('this.ID', 'ref.PROJECT_ID'),
					['join_type' => 'LEFT']
				)
			);

			$this->query->registerRuntimeField(
				null,
				new ExpressionField(
					'ACTIVITY_DATE',
					$helper->getIsNullFunction('%s', '%s'),
					['PLA.ACTIVITY_DATE', 'DATE_UPDATE']
				)
			);
			$this->runtimeFieldsManager->add('ACTIVITY_DATE');
		}
	}

	/**
	 * Get filter for getList.
	 *
	 * @return array
	 */
	private function getFilter(): array
	{
		$filterManager = new WorkgroupList\FilterManager($this->query, $this->runtimeFieldsManager, [
			'fieldsList' => $this->fieldsList,
			'gridFilter' => $this->gridFilter,
			'currentUserId' => $this->currentUserId,
			'contextUserId' => $this->arParams['USER_ID'],
			'mode' => $this->arParams['MODE'],
			'hasAccessToTasksCounters' => $this->hasAccessToTasksCounters,
		]);

		return $filterManager->getFilter();
	}

	private function getSelect(): array
	{
		$result = [
			'ID',
			'PROJECT',
			'SCRUM_MASTER_ID',
			'OPENED',
			'CLOSED',
			'VISIBLE',
			'NUMBER_OF_MODERATORS',
			'NUMBER_OF_MEMBERS',
			'DATE_UPDATE',
			'DATE_ACTIVITY',
		];

		if ($this->runtimeFieldsManager->has('CURRENT_RELATION'))
		{
			$result[] = [ 'CURRENT_RELATION_ID' => 'CURRENT_RELATION.ID' ];
			$result[] = [ 'CURRENT_RELATION_USER_ID' => 'CURRENT_RELATION.USER_ID' ];
			$result[] = [ 'CURRENT_RELATION_GROUP_ID' => 'CURRENT_RELATION.GROUP_ID' ];
			$result[] = [ 'CURRENT_RELATION_ROLE' => 'CURRENT_RELATION.ROLE' ];
			$result[] = [ 'CURRENT_RELATION_INITIATED_BY_TYPE' => 'CURRENT_RELATION.INITIATED_BY_TYPE' ];
			$result[] = [ 'CURRENT_RELATION_INITIATED_BY_USER_ID' => 'CURRENT_RELATION.INITIATED_BY_USER_ID' ];
			$result[] = [ 'CURRENT_RELATION_AUTO_MEMBER' => 'CURRENT_RELATION.AUTO_MEMBER' ];
		}

		if ($this->runtimeFieldsManager->has('CONTEXT_RELATION'))
		{
			$result[] = [ 'CONTEXT_RELATION_ID' => 'CONTEXT_RELATION.ID' ];
			$result[] = [ 'CONTEXT_RELATION_ROLE' => 'CONTEXT_RELATION.ROLE' ];
			$result[] = [ 'CONTEXT_RELATION_ROLE_AUTO_MEMBER' => 'CONTEXT_RELATION.AUTO_MEMBER' ];
			$result[] = [ 'CONTEXT_RELATION_ROLE_INITIATED_BY_TYPE' => 'CONTEXT_RELATION.INITIATED_BY_TYPE' ];
			$result[] = [ 'CONTEXT_RELATION_ROLE_DATE_UPDATE' => 'CONTEXT_RELATION.DATE_UPDATE' ];
		}

		if ($this->runtimeFieldsManager->has('FAVORITES'))
		{
			$result[] = 'FAVORITES.USER_ID';
			$result[] = 'FAVORITES.GROUP_ID';
			$result[] = 'FAVORITES.DATE_ADD';
		}

		if ($this->runtimeFieldsManager->has('SCRUM'))
		{
			$result[] = 'SCRUM';
		}

		if ($this->runtimeFieldsManager->has('PIN'))
		{
			$result[] = 'PIN.ID';
		}

		if ($this->runtimeFieldsManager->has('IS_PINNED'))
		{
			$result[] = 'IS_PINNED';
		}

		foreach ($this->gridColumns as $column)
		{
			switch($column)
			{
				case 'NAME':
					$result[] = 'NAME';
					$result[] = 'IMAGE_ID';
					$result[] = 'AVATAR_TYPE';
					break;
				case 'OWNER_FULL_NAME':
					$result[] = 'OWNER.NAME';
					$result[] = 'OWNER.LAST_NAME';
					$result[] = 'OWNER.SECOND_NAME';
					$result[] = 'OWNER.LOGIN';
					$result[] = 'OWNER.PERSONAL_PHOTO';
					$result[] = 'OWNER.PERSONAL_GENDER';
					$result[] = 'OWNER.WORK_POSITION';
					break;
				case 'DATE_VIEW':
					$result[] = [ 'VIEW_USER_ID' => 'VIEW.USER_ID' ]; // needed for wakeUp
					$result[] = [ 'VIEW_GROUP_ID' => 'VIEW.GROUP_ID' ]; // needed for wakeUp
					$result[] = [ 'VIEW_DATE_VIEW' => 'VIEW.DATE_VIEW' ];
					break;
				case 'ROLE':
					$result[] = 'PROJECT';
					$result[] = 'SCRUM_MASTER_ID';
					break;
				default:
					$result[] = $column;
			}
		}

		return $result;
	}

	public function onPrepareComponentParams($params): array
	{
		global $USER;

		$params['USER_ID'] = (int)($params['USER_ID'] ?? $USER->getId());
		$params['SUBJECT_ID'] = (int)($params['SUBJECT_ID'] ?? 0);

		if (!isset($params['PAGE']) || (string)$params['PAGE'] === '')
		{
			$params['PAGE'] = 'user_groups';
		}

		if (!isset($params['NAME_TEMPLATE']) || (string)$params['NAME_TEMPLATE'] === '')
		{
			$params['NAME_TEMPLATE'] = CSite::getNameFormat();
		}

		if (!isset($params['SHOW_LOGIN']) || $params['SHOW_LOGIN'] !== 'N')
		{
			$params['SHOW_LOGIN'] = 'Y';
		}

		WorkgroupList\Param::fillUrls($params);

		if (!isset($params['SET_TITLE']) || $params['SET_TITLE'] !== 'N')
		{
			$params['SET_TITLE'] = 'Y';
		}

		if (!isset($params['MODE']))
		{
			$params['MODE'] = self::MODE_COMMON;
		}

		if (
			!isset($params['GRID_ID'])
			|| (string)$params['GRID_ID'] === ''
		)
		{
			switch ($params['MODE'])
			{
				case self::MODE_USER:
					$params['GRID_ID'] = 'SONET_GROUP_LIST_USER';
					break;
				case self::MODE_TASKS_PROJECT:
					$params['GRID_ID'] = 'SONET_GROUP_LIST_PROJECT';
					break;
				case self::MODE_TASKS_SCRUM:
					$params['GRID_ID'] = 'SONET_GROUP_LIST_SCRUM';
					break;
				default:
					$params['GRID_ID'] = 'SONET_GROUP_LIST';
			}
		}

		if (
			!isset($params['FILTER_ID'])
			|| (string)$params['FILTER_ID'] === ''
		)
		{
			$params['FILTER_ID'] = $params['GRID_ID'];
		}

		return $params;
	}

	/**
	 * prepare group actions
	 */
	private function prepareActions(): void
	{
		$this->actionData = [ 'ACTIVE' => false ];

		if (!check_bitrix_sessid())
		{
			return;
		}

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$postAction = 'action_button_' .$this->gridId;

		if (
			$request->getRequestMethod() === 'POST'
			&& !empty($request->getPost($postAction))
		)
		{
			$this->actionData['NAME'] = $request->getPost($postAction);
			unset($_POST[$postAction], $_REQUEST[$postAction]);

			if (!empty($request->getPost('ID')))
			{
				$this->actionData['ID'] = $request->getPost('ID');
				unset($_POST['ID'], $_REQUEST['ID']);
			}

			$this->actionData['ACTIVE'] = true;
		}
	}

	private function processGroupAction(): void
	{
		global $APPLICATION;

		if (
			!$this->actionData['ACTIVE']
			|| $this->currentUserId <= 0
		)
		{
			return;
		}

		if (
			!isset($this->actionData['NAME'])
			|| !in_array($this->actionData['NAME'], [
				self::GROUP_ACTION_ADD_TO_ARCHIVE,
				self::GROUP_ACTION_REMOVE_FROM_ARCHIVE,
//				self::GROUP_ACTION_DELETE,
			], true))
		{
			$this->addError(
				new Error(
					Loc::getMessage('SOCIALNETWORK_GROUP_LIST_ERROR_ACTION_NOT_SUPPORTED')
				)
			);

			return;
		}

		$action = $this->actionData['NAME'];

		$idList = [];

		if (
			isset($this->actionData['ID'])
			&& is_array($this->actionData['ID'])
		)
		{
			$idList = $this->actionData['ID'];
		}
		else
		{
			$idList[] = $this->actionData['ID'];
		}

		if (empty($idList))
		{
			$this->addError(
				new Error(
					Loc::getMessage('SOCIALNETWORK_GROUP_LIST_ERROR_GROUP_ACTION_EMPTY_LIST')
				)
			);

			return;
		}

		$groupIdList = [];

		$query = new Query(WorkgroupTable::getEntity());

		$query->registerRuntimeField(
			new Reference(
				'CURRENT_RELATION',
				UserToGroupTable::class,
				Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $this->currentUserId),
				[ 'join_type' => 'LEFT' ]
			)
		);
		$query->addFilter('ID', $idList);
		if ($this->arParams['SUBJECT_ID'] > 0)
		{
			$query->addFilter('=SUBJECT_ID', $this->arParams['SUBJECT_ID']);
		}

		$query->addSelect('ID');
		$query->addSelect('SCRUM_MASTER_ID');
		$query->addSelect('PROJECT');
		$query->addSelect('CLOSED');
		$query->addSelect('CURRENT_RELATION.GROUP_ID');
		$query->addSelect('CURRENT_RELATION.ROLE');
		$query->addSelect('CURRENT_RELATION.USER_ID');

		$records = $query->exec()->fetchCollection();

		foreach ($records as $group)
		{
			$currentUserRelation = $group->get('CURRENT_RELATION');

			$accessManager = new \Bitrix\Socialnetwork\Item\Workgroup\AccessManager(
				$group,
				null,
				$currentUserRelation
			);

			switch ($action)
			{
				case self::GROUP_ACTION_ADD_TO_ARCHIVE;
					if ($accessManager->canAddToArchive())
					{
						$groupIdList[] = $group->getId();
					}
					break;
				case self::GROUP_ACTION_REMOVE_FROM_ARCHIVE;
					if ($accessManager->canRemoveFromArchive())
					{
						$groupIdList[] = $group->getId();
					}
					break;
/*
				case self::GROUP_ACTION_DELETE;
					if ($accessManager->canDelete())
					{
						$groupIdList[] = $group->getId();
					}
					break;
*/
				default:
			}
		}

		if (empty($groupIdList))
		{
			$this->addError(
				new Error(
					Loc::getMessage('SOCIALNETWORK_GROUP_LIST_ERROR_GROUP_ACTION_EMPTY_LIST')
				)
			);

			return;
		}

		foreach ($groupIdList as $groupId)
		{
			$APPLICATION->ResetException();

			if (in_array($action, [ self::GROUP_ACTION_ADD_TO_ARCHIVE, self::GROUP_ACTION_REMOVE_FROM_ARCHIVE ], true))
			{
				try
				{
					Helper\Workgroup::setArchive([
						'groupId' => $groupId,
						'archive' => ($action === self::AJAX_ACTION_ADD_TO_ARCHIVE),
					]);
				}
				catch (SystemException $e)
				{
					$this->addError(
						new Error($e->getMessage(), $e->getCode())
					);

					return;
				}
			}
/*
			elseif ($action === self::GROUP_ACTION_DELETE)
			{
				if (!\CSocNetGroup::delete($groupId))
				{
					$errorMessage = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_ERROR_GROUP_ACTION_FAILED');
					if ($e = $APPLICATION->getException())
					{
						$errorMessage = $e->getString();
					}

					$this->addError(new \Bitrix\Main\Error($errorMessage));
					return;
				}
			}
*/
		}
	}

	private function prepareData(): array
	{
		$result = [
			'ACTION_MESSAGES' => [],
			'FIELDS_LIST' => $this->fieldsList,
			'CURRENT_USER_ID' => $this->currentUserId,
		];

		$result['TOURS'] = (
			isset($this->arParams['TOURS'])
			&& is_array($this->arParams['TOURS'])
				? $this->arParams['TOURS']
				: []
		);

		$result['CURRENT_COUNTER'] = $this->getCounter();

		$this->init();

		$result['GRID_ID'] = $this->gridId;
		$result['FILTER_ID'] = $this->filterId;

		$result['EXTRANET_SITE'] = ($this->isExtranetSite ? 'Y' : 'N');
		$result['CURRENT_PRESET_ID'] = $this->currentPresetId;
		$result['FILTER'] = $this->filter;
		$result['FILTER_PRESETS'] = $this->filterPresets;
		$result['FILTER_DATA'] = $this->gridFilter;
		$result['SORT'] = $this->gridOptions->getSorting($this->getDefaultGridSorting())['sort'];
		$result['PAGE_SIZE'] = $this->pageSize;

		$nav = new \Bitrix\Main\UI\PageNavigation('page');

		$nav->allowAllRecords(false)
			->setPageSize($result['PAGE_SIZE'])
			->initFromUri();

		$result['CURRENT_PAGE'] = $nav->getCurrentPage();

		if (in_array($this->arParams['MODE'], self::getTasksModeList(), true))
		{
			$gridHeaders = $this->getTasksGridHeaders();
		}
		else
		{
			$gridHeaders = $this->getGridHeaders();
		}

		$result['HEADERS'] = $gridHeaders;
		$result['STUB'] = $this->getStub();

		$query = $this->query;

		$this->addQueryOrder();
		$this->addQuerySelect();
		$query->countTotal(true);

		$query->setOffset($nav->getOffset());
		$query->setLimit($nav->getLimit());
		$query->disableDataDoubling();

		$res = $query->exec();
		$this->queryInitAlias = mb_strtoupper($query->getInitAlias());
		$rowsList = $this->prepareRowList($res->fetchAll());

		$this->gridColumns = array_filter($this->gridColumns, static function($item) { return $item !== 'ACTIONS'; });
		$result['HEADERS'] = array_filter($result['HEADERS'], static function($item) { return $item['id'] !== 'ACTIONS'; });

		$result['ROWS'] = $rowsList;
		$result['ROWS_COUNT'] = $res->getCount();
		$result['GROUP_ID_LIST'] = array_column($rowsList, 'id');

		$nav->setRecordCount($result['ROWS_COUNT']);
		$result['NAV_OBJECT'] = $nav;

		$result['GRID_COLUMNS'] = $this->gridColumns;

		$result['ENTITY_RUNTIME_FIELDS'] = $this->runtimeFieldsManager->get();
		$result['ENTITY_SELECT_FIELDS'] = $this->selectFieldsManager->get();
		$result['HAS_ACCESS_TO_TASKS_COUNTERS'] = $this->hasAccessToTasksCounters;
		$result['TASKS_COUNTERS_SCOPE'] = (
			$result['HAS_ACCESS_TO_TASKS_COUNTERS']
				? $this->getTasksCountersScope()
				: ''
		);

		$result['TASKS_COUNTERS'] = (
			$result['HAS_ACCESS_TO_TASKS_COUNTERS']
				? $this->getTasksCounters()
				: ''
		);

		return $result;
	}

	private function getDefaultGridSorting(): array
	{
		return [
			'sort' => [ 'NAME' => 'ASC'],
			'vars' => [
				'by' => 'by',
				'order' => 'order',
			],
		];
	}

	private function getViewableActionList(): array
	{
		return [
/*
			self::AVAILABLE_ACTION_PROCESS_INCOMING_REQUEST,
			self::AVAILABLE_ACTION_REINVITE,
*/
		];
	}

	private function initPageFilterData(): void
	{
		if (!empty($this->arParams['FILTER_ID']))
		{
			$this->filterId = $this->arParams['FILTER_ID'];
			$this->gridId = (
				!empty($this->arParams['GRID_ID'])
					? $this->arParams['GRID_ID']
					: $this->arParams['FILTER_ID']
			);
		}

		if ($this->arParams['USER_ID'] !== $this->currentUserId)
		{
			$this->gridId .= '_NOT_CURRENT';
			$this->filterId .= '_NOT_CURRENT';
		}
	}

	private static function extranetSite(): bool
	{
		static $result = null;

		if ($result === null)
		{
			$result = (
				Loader::includeModule('extranet')
				&& CExtranet::isExtranetSite()
			);
		}

		return $result;
	}

	public function executeComponent()
	{
		$this->initPageFilterData();

		$this->setTitle();
		$this->addNavChain();

		$this->prepareActions();

		if ($this->actionData['ACTIVE'])
		{
			$this->processGroupAction();
		}

		$this->arResult = $this->prepareData();

		if (!empty($this->errorCollection->getValues()))
		{
			if ($this->actionData['ACTIVE'])
			{
				foreach ($this->errorCollection->getValues() as $error)
				{
					$this->arResult['ACTION_MESSAGES'][] = $error->getMessage();
				}
			}
			else
			{
				$this->printErrors();
				return;
			}
		}

		$this->subscribePull();

		$this->includeComponentTemplate();
	}

	private function setTitle(): void
	{
		global $APPLICATION;

		if ($this->arParams['SET_TITLE'] !== 'Y')
		{
			return;
		}

		$pageTitle = (
			ModuleManager::isModuleInstalled('intranet')
				? Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUPS_AND_PROJECTS_PAGE_TITLE')
				: Loc::getMessage('SOCIALNETWORK_GROUP_LIST_PAGE_TITLE')
		);
		if ($this->arParams['MODE'] === self::MODE_TASKS_PROJECT)
		{
			$pageTitle = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_PAGE_PROJECTS_TITLE');
		}
		elseif ($this->arParams['MODE'] === self::MODE_TASKS_SCRUM)
		{
			$pageTitle = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_PAGE_SCRUM_TITLE');
		}

		if (
			$this->arParams['PAGE'] === 'groups_subject'
			&& $this->arParams['SUBJECT_ID'] > 0
		)
		{
			$pageTitle = Helper\Workgroup\Subject::getName($this->arParams['SUBJECT_ID']);
		}
		elseif ($this->arParams['PAGE'] === 'user_groups')
		{
			if ($this->arParams['USER_ID'] === $this->currentUserId)
			{
				$pageTitle = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_PAGE_MY_GROUPS_TITLE');
			}
			else
			{
				$formattedNameData = Helper\User::getUserListNameFormatted([ $this->arParams['USER_ID'] ]);
				$pageTitle = Loc::getMessage('SOCIALNETWORK_GROUP_LIST_PAGE_USER_GROUPS_TITLE', [
					'#USER_NAME#' => htmlspecialcharsEx($formattedNameData[$this->arParams['USER_ID']] ?? ''),
				]);
			}
		}

		$APPLICATION->SetTitle($pageTitle);
	}

	private function addNavChain(): void
	{
		global $APPLICATION;

		if (($this->arParams['SET_NAV_CHAIN'] ?? '') === 'N')
		{
			return;
		}

		if (
			$this->arParams['PAGE'] === 'user_groups'
			&& $this->arParams['USER_ID'] > 0
		)
		{
			$formattedNameData = Helper\User::getUserListNameFormatted([ $this->arParams['USER_ID'] ]);

			$path = \CComponentEngine::makePathFromTemplate(
				$this->arParams['PATH_TO_USER'],
				[
					'user_id' => $this->arParams['USER_ID'],
				]
			);

			$APPLICATION->AddChainItem(
				htmlspecialcharsEx($formattedNameData[$this->arParams['USER_ID']] ?? ''),
				$path
			);
		}

		$APPLICATION->AddChainItem(ModuleManager::isModuleInstalled('intranet')
			? Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUPS_AND_PROJECTS_PAGE_TITLE')
			: Loc::getMessage('SOCIALNETWORK_GROUP_LIST_PAGE_TITLE')
		);
	}

	public function actAction(string $action = '', array $fields = []): ?array
	{
		if (!$this->checkAjaxAction($action))
		{
			$this->errorCollection->setError(
				new Error(
					Loc::getMessage('SOCIALNETWORK_GROUP_LIST_ERROR_ACTION_NOT_SUPPORTED')
				)
			);

			return null;
		}

		$result = true;

		try
		{
			switch ($action)
			{
				case self::AJAX_ACTION_JOIN:
					Helper\Workgroup::join([
						'groupId' => (int)($fields['groupId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_SET_OWNER:
					$result = Helper\Workgroup::setOwner([
						'groupId' => (int)($fields['groupId'] ?? 0),
						'userId' => $this->currentUserId,
					]);
					break;
				case self::AJAX_ACTION_SET_SCRUM_MASTER:
					$result = Helper\Workgroup::setScrumMaster([
						'groupId' => (int)($fields['groupId'] ?? 0),
						'userId' => $this->currentUserId,
					]);
					break;
				case self::AJAX_ACTION_DELETE_INCOMING_REQUEST:
					$result = Helper\Workgroup::deleteIncomingRequest([
						'groupId' => (int)($fields['groupId'] ?? 0),
						'userId' => $this->currentUserId,
					]);
					break;
				case self::AJAX_ACTION_REJECT_OUTGOING_REQUEST:
					$result = Helper\Workgroup::rejectOutgoingRequest([
						'groupId' => (int) ($fields['groupId'] ?? 0),
						'userId' => $this->currentUserId,
					]);
					break;
				case self::AJAX_ACTION_ADD_TO_ARCHIVE:
				case self::AJAX_ACTION_REMOVE_FROM_ARCHIVE:
					$result = Helper\Workgroup::setArchive([
						'groupId' => (int)($fields['groupId'] ?? 0),
						'archive' => ($action === self::AJAX_ACTION_ADD_TO_ARCHIVE),
					]);
					break;
				default:
					$result = false;
			}

		}
		catch (\Exception $e)
		{
			$this->errorCollection->setError(new Error($e->getMessage()));

			return null;
		}

		return [
			'success' => $result,
		];
	}

	private function subscribePull(): void
	{
		if (
			$this->request->isAjaxRequest()
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		\CPullWatch::Add($this->arParams['USER_ID'], PullDictionary::PULL_WORKGROUPS_TAG, true);

		if (
			in_array($this->arParams['MODE'], self::getTasksModeList(), true)
			&& Loader::includeModule('tasks')
		)
		{
			\CPullWatch::Add(
				$this->arParams['USER_ID'],
				\Bitrix\Tasks\Internals\Project\Pull\PullDictionary::PULL_PROJECTS_TAG,
				true
			);
		}
	}

	public function checkExistenceAction(array $groupIdList): ?array
	{
		$result = [];

		if (empty($groupIdList))
		{
			return $result;
		}

		$result = array_fill_keys($groupIdList, false);

		$this->initPageFilterData();
		$this->init();

		$query = $this->query;

		$query->addFilter('ID', $groupIdList);
		$this->addQuerySelect();

		$res = $query->exec();
		$this->queryInitAlias = mb_strtoupper($query->getInitAlias());
		$rowsList = $this->prepareRowList($res->fetchAll());

		array_walk($rowsList, static function($value) use (&$result) {
			$result[$value['id']] = $value;
		});

		return $result;
	}

	public function findWorkgroupPlaceAction(int $groupId, int $currentPage): ?array
	{
		$result = [
			'workgroupBefore' => false,
			'workgroupAfter' => false,
		];

		if ($groupId <= 0)
		{
			return $result;
		}

		$this->initPageFilterData();
		$this->init();

		$query = $this->query;
		$this->addQuerySelect();
		$this->addQueryOrder();

		$nav = new \Bitrix\Main\UI\PageNavigation('page');

		$currentPage = ($currentPage >= 1) ? $currentPage : 1;
		$nav->allowAllRecords(false)
			->setPageSize($this->pageSize)
			->setCurrentPage($currentPage);

		$query->setOffset($nav->getOffset());
		$query->setLimit($nav->getLimit());

		$rows = $query->exec()->fetchAll();

		$workgroupIdList = array_map(static function($row) {
			return (int)$row['ID'];
		}, $rows);

		if (
			empty($workgroupIdList)
			|| ($index = array_search($groupId, $workgroupIdList, true)) === false
		)
		{
			return $result;
		}

		$result['workgroupBefore'] = ($index === 0 ? 0 : $workgroupIdList[$index - 1]);
		$result['workgroupAfter'] = ($index === count($workgroupIdList) - 1 ? 0 : $workgroupIdList[$index + 1]);

		return $result;
	}

	private function init(): void
	{
		$this->currentPresetId = 'tmp_filter';
		$this->isExtranetSite = static::extranetSite();

		$entityFilter = Filter\Factory::createEntityFilter(
			WorkgroupTable::getUfId(),
			[
				'ID' => $this->filterId,
			],
			[
				'MODE' => $this->arParams['MODE'],
				'CONTEXT_USER_ID' => $this->arParams['USER_ID'],
			]
		);

		$this->filter = $entityFilter->getFieldArrays();
		$this->filterPresets = $this->getFilterPresets();
		$this->hasAccessToTasksCounters = $this->getAccessToTasksCounters();

		$this->filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId, $this->filterPresets);

		if (
			$this->arParams['MODE'] === WorkgroupList::MODE_USER
			&& $this->request->get('grid_id') === null
		)
		{
			if ($this->arParams['USER_ID'] === $this->currentUserId)
			{
				$this->setMyPresetToCurrent($this->filterPresets);
			}
			else
			{
				$this->setActivePresetToCurrent($this->filterPresets);
			}
		}

		$this->gridFilter = $this->filterOptions->getFilter($this->filter);

		$this->query = new Query(WorkgroupTable::getEntity());
		$this->addQueryRuntime();
		$this->addQueryFilter();

		$this->gridOptions = new \Bitrix\Main\Grid\Options($this->gridId);

		$this->gridColumns = $this->gridOptions->getVisibleColumns();

		if (empty($this->gridColumns))
		{
			$this->gridColumns = $this->getDefaultGridHeaders();
		}
		else
		{
			if (in_array($this->arParams['MODE'], self::getTasksModeList(), true))
			{
				$gridHeaders = $this->getTasksGridHeaders();
			}
			else
			{
				$gridHeaders = $this->getGridHeaders();
			}

			$availableGridColumns = array_map(static function($gridColumn) {
				return $gridColumn['id'];
			}, $gridHeaders);

			$this->gridColumns = array_filter($this->gridColumns, static function ($key) use ($availableGridColumns) {
				return in_array($key, $availableGridColumns, true);
			});
		}

		$navParams = $this->gridOptions->getNavParams();
		$this->pageSize = (int)$navParams['nPageSize'];
	}

	private function setMyPresetToCurrent(array $filterPresets): void
	{
		$myPreset = ($filterPresets['my'] ?? []);

		if (!empty($myPreset))
		{
			$this->filterOptions->setFilterSettings(
				'my',
				$myPreset,
				true,
				false
			);

			$this->filterOptions->save();
		}
	}

	private function setActivePresetToCurrent(array $filterPresets): void
	{
		$activePreset = ($filterPresets['active'] ?? []);

		if (!empty($activePreset))
		{
			$this->filterOptions->setFilterSettings(
				'active',
				$activePreset,
				true,
				false
			);

			$this->filterOptions->save();
		}
	}

	private function prepareRowList(array $records): array
	{
		$rowsList = [];
		$groupIdList = [];
		$scrumIdList = [];
		$scrumMasterIdList = [];
		$members = [];

		$entityManagerInstance = new EntityManager([
			'queryInitAlias' => $this->queryInitAlias,
		]);

		foreach ($records as $record)
		{
			$group = $entityManagerInstance->wakeUpWorkgroupEntityObject($record);
			if ($group === null)
			{
				continue;
			}

			$row['ROW_FIELDS'] = $record;

			$row['CAN_EDIT'] = false;
			$row['CAN_DELETE'] = false;
			$row['USERS'] = [];

			$actions = self::getActions($this->runtimeFieldsManager, [
				'GROUP' => $row['ROW_FIELDS'],
				'PATH_TO_GROUP' => (
					in_array($this->arParams['MODE'], self::getTasksModeList(), true)
						? $this->arParams['PATH_TO_GROUP_TASKS']
						: $this->arParams['PATH_TO_GROUP']
				),
				'QUERY_INIT_ALIAS' => $this->queryInitAlias,
			]);

			$cellActions = self::getCellActions($this->runtimeFieldsManager, [
				'GROUP' => $row['ROW_FIELDS'],
				'GRID_ID' => $this->gridId,
				'QUERY_INIT_ALIAS' => $this->queryInitAlias,
			]);

			$rowsList[] = [
				'id' => $group->get('ID'),
				'data' => $row,
				'columns' => [],
				'editable' => true,
				'actions' => $actions,
				'cellActions' => $cellActions,
				'columnClasses' => (
					false
//					ModuleManager::isModuleInstalled('intranet')
//					&& $record->getUser()->getUserType() === 'extranet'
						? [
							'FULL_NAME' => 'socialnetwork-group-user-list-full-name-extranet'
						]
						: []
				)
			];

			$groupIdList[] = $group->getId();

			if ($record['SCRUM'] === 'Y')
			{
				$scrumIdList[] = $group->getId();

				if ($this->runtimeFieldsManager->has('SCRUM'))
				{
					$scrumMasterIdList[$group->get('ID')] = $group->get('SCRUM_MASTER_ID');
				}
			}
		}

		$groupUrl = (
			in_array($this->arParams['MODE'], self::getTasksModeList(), true)
				? $this->arParams['PATH_TO_GROUP_TASKS']
				: $this->arParams['PATH_TO_GROUP']
		);

		if (!empty($groupIdList))
		{
			if ($this->selectFieldsManager->has('MEMBERS'))
			{
				if (empty($members))
				{
					$members = WorkgroupList\User::fillUsers([
						'groupIdList' => $groupIdList,
						'scrumMasterIdList' => $scrumMasterIdList,
					]);
				}

				foreach ($rowsList as $key => $value)
				{
					if (!isset($members[$value['id']]))
					{
						continue;
					}

					$rowsList[$key]['data']['MEMBERS'] = $members[$value['id']];
				}
			}

			if ($this->selectFieldsManager->has('TAGS'))
			{
				$tags = WorkgroupList\Tag::fillTags([
					'groupIdList' => $groupIdList,
				]);

				foreach ($rowsList as $key => $value)
				{
					if (!isset($tags[$value['id']]))
					{
						continue;
					}

					$rowsList[$key]['data']['TAGS'] = $tags[$value['id']];
				}
			}

			if ($this->selectFieldsManager->has('EFFICIENCY'))
			{
				$efficiencies = WorkgroupList\Efficiency::fillEfficiency([
					'groupIdList' => $groupIdList,
				]);

				foreach ($rowsList as $key => $value)
				{
					if (!isset($efficiencies[$value['id']]))
					{
						continue;
					}

					$rowsList[$key]['data']['EFFICIENCY'] = $efficiencies[$value['id']];
				}
			}

			if ($this->currentUserId === $this->arParams['USER_ID'])
			{
				if (!in_array($this->arParams['MODE'], self::getTasksModeList(), true))
				{
					$counters = [];
				}
				else
				{
					if (empty($members))
					{
						$members = WorkgroupList\User::fillUsers([
							'groupIdList' => $groupIdList,
							'scrumMasterIdList' => $scrumMasterIdList,
						]);
					}

					$groupIds = $this->getGroupIdsForCounters(
						$this->currentUserId,
						$groupIdList,
						$members
					);

					$this->counterData = WorkgroupList\Counter::getCounterData([
						'mode' => $this->arParams['MODE'],
						'groupIdList' => $groupIds,
						'scrumIdList' => $scrumIdList,
					]);

					$counters = WorkgroupList\Counter::fillCounters([
						'counterData' => $this->counterData,
						'groupIdList' => $groupIds,
						'scrumIdList' => $scrumIdList,
						'mode' => $this->arParams['MODE'],
						'groupUrl' => $groupUrl,
						'livefeedCounterSliderOptions' => WorkgroupList\Counter::getLivefeedCounterSliderOptions(),
					]);
				}

				foreach ($rowsList as $key => $value)
				{
					if (!isset($counters[$value['id']]))
					{
						continue;
					}

					$rowsList[$key]['counters'] = $counters[$value['id']];
				}
			}
		}

		$this->formatRowList($rowsList);

		return $rowsList;
	}

	private function formatRowList(array &$rowsList = []): void
	{
		$entityManager = new EntityManager([
			'queryInitAlias' => $this->queryInitAlias,
		]);

		foreach ($rowsList as $key => $row)
		{
			$processedRow = $row;

			$groupFields = $row['data']['ROW_FIELDS'];
			$group = $entityManager->wakeUpWorkgroupEntityObject($groupFields);
			if ($group === null)
			{
				continue;
			}

			$relationItem = (
				$this->arParams['USER_ID'] > 0
				&& $this->runtimeFieldsManager->has('CONTEXT_RELATION')
					? $entityManager->wakeUpContextRelationEntityObject($groupFields)
					: null
			);

			$currentUserRelationItem = (
				$this->runtimeFieldsManager->has('CURRENT_RELATION')
					? $entityManager->wakeUpCurrentRelationEntityObject($groupFields)
					: null
			);

			$viewItem = (
				$this->arParams['USER_ID'] > 0
				&& $this->runtimeFieldsManager->has('VIEW')
					? $entityManager->wakeUpViewEntityObject($groupFields)
					: null
			);

			$groupId = $group->getId();

			$groupUrl = str_replace(
				[ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ],
				$groupId,
				(
					in_array($this->arParams['MODE'], self::getTasksModeList(), true)
						? $this->arParams['PATH_TO_GROUP_TASKS']
							: $this->arParams['PATH_TO_GROUP']
				)
			);

			$groupType = '';
			if ($group->getProject())
			{
				$groupType = 'project';
				if ((int)$group->getScrumMasterId() > 0)
				{
					$groupType = 'scrum';
				}
			}

			if ($groupType === 'scrum')
			{
				$groupUrl = (new Uri($groupUrl))
					->addParams(['scrum' => 'Y'])
					->getUri()
				;
			}

			foreach ($this->gridColumns as $column)
			{
				switch ($column)
				{
					case 'ID':
						$processedRow['columns'][$column] = (string)$groupId;
						break;
					case 'NAME':
						$result = '
							' . Helper\UI\Grid\Workgroup\Avatar::getValue($group) . '
							<a class="sonet-group-grid-name-text" href="' . $groupUrl . '">' .
								htmlspecialcharsEx($group->getName()) .
							'</a>
						';
						$result = '<div class="sonet-group-grid-name-container">' . $result . '</div>';
						$processedRow['columns'][$column] = $result;
						break;
					case 'ROLE':
						$value = '';
						if ($relationItem !== null)
						{
							$value = Helper\UI\Grid\Workgroup\Role::getRoleValue([
								'RELATION' => [
									'USER_ID' => $this->arParams['USER_ID'],
									'ROLE' => $relationItem->getRole(),
									'AUTO_MEMBER' => $relationItem->getAutoMember(),
									'INITIATED_TYPE' => $relationItem->getInitiatedByType(),
								],
								'GROUP' => [
									'ID' => $group->getId(),
									'PROJECT' => $group->getProject(),
									'SCRUM_MASTER_ID' => $group->getScrumMasterId(),
								],
								'GRID_ID' => $this->gridId,
							]);
						}
						elseif (
							$this->arParams['USER_ID'] ===  $this->currentUserId
							&& isset($row['actions'])
							&& in_array(self::AVAILABLE_ACTION_JOIN, $row['actions'], true)
						)
						{
							$value = Helper\UI\Grid\Workgroup\Role::getJoinValue([
								'OPENED' => $group->getOpened(),
								'PATH_TO_JOIN_GROUP' => str_replace(
									[ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ],
									$groupId,
									$this->arParams['PATH_TO_JOIN_GROUP']
								),
								'ONCLICK' => 'BX.Socialnetwork.WorkgroupList.Manager.getById("' .
									$this->gridId . '").getActionManager().act({
										action: "' . WorkgroupList::AJAX_ACTION_JOIN . '",
										groupId: "' . $groupId . '",
									}, event)',
							]);
						}
						$processedRow['columns'][$column] = $value;
						break;
					case 'DATE_CREATE':
					case 'DATE_UPDATE':
					case 'DATE_ACTIVITY':
					case 'PROJECT_DATE_START':
					case 'PROJECT_DATE_FINISH':
						$processedRow['columns'][$column] = Helper\UI\DateTime::getDateValue($group->get($column));
						break;
					case 'DATE_VIEW':
						$processedRow['columns'][$column] = (
							$viewItem !== null
								? Helper\UI\DateTime::getDateValue($viewItem->getDateView())
								: ''
						);
						break;
					case 'DATE_RELATION':
						$processedRow['columns'][$column] = (
							$relationItem !== null
								? Helper\UI\DateTime::getDateValue($relationItem->getDateUpdate())
								: ''
						);
						break;
					case 'ACTIVITY_DATE':
						$value = $groupFields[$column];

						$processedRow['columns'][$column] = (
							!empty($value)
								? Helper\UI\DateTime::getDateValue(\Bitrix\Main\Type\DateTime::createFromTimestamp(MakeTimeStamp($value)))
								: ''
						);
						break;
					case 'MEMBERS':
						if ($groupType === 'scrum')
						{
							$processedRow['columns'][$column] = Helper\UI\Grid\Workgroup\ScrumMembers::getValue(
								$row['data']['MEMBERS'],
								[
									'GRID_ID' => $this->gridId,
									'GROUP_ID' => $group->get('ID'),
									'GROUP_TYPE' => $groupType,
									'NUMBER_OF_MODERATORS' => $group->get('NUMBER_OF_MODERATORS'),
									'NUMBER_OF_MEMBERS' => $group->get('NUMBER_OF_MEMBERS'),
								]
							);
						}
						else
						{
							$processedRow['columns'][$column] = Helper\UI\Grid\Workgroup\Members::getValue(
								$row['data']['MEMBERS'],
								[
									'GRID_ID' => $this->gridId,
									'GROUP_ID' => $group->get('ID'),
									'GROUP_TYPE' => $groupType,
									'NUMBER_OF_MODERATORS' => $group->get('NUMBER_OF_MODERATORS'),
									'NUMBER_OF_MEMBERS' => $group->get('NUMBER_OF_MEMBERS'),
								]
							);
						}
						break;
					case 'TAGS':
						$processedRow['columns'][$column] = Helper\UI\Grid\Workgroup\Tags::getValue(($row['data']['TAGS'] ?? []), [
							'GRID_ID' => $this->gridId,
							'GROUP' => $group,
							'CURRENT_USER_RELATION' => $currentUserRelationItem,
							'FILTER_FIELD' => 'TAG',
							'FILTER_DATA' => $this->gridFilter,
						]);
						break;
					case 'PRIVACY_TYPE':
						$confidentialityCode = Helper\Workgroup::getConfidentialityTypeCodeByParams([
							'fields' => [
								'OPENED' => $group->get('OPENED') ? 'Y' : 'N',
								'VISIBLE' => $group->get('VISIBLE') ? 'Y' : 'N',
							],
						]);

						$processedRow['columns'][$column] = Helper\UI\Grid\Workgroup\Confidentiality::getValue([
							'code' => $confidentialityCode
						]);
						break;
					case 'EFFICIENCY':
						$processedRow['columns'][$column] = Helper\UI\Grid\Workgroup\Efficiency::getEfficiencyValue((int)$row['data']['EFFICIENCY']);
						break;
					default:
						$processedRow['columns'][$column] = htmlspecialcharsEx($group[$column]);
				}
			}

			$processedRow['attrs'] = [
				'data-type' => 'workgroup',
				'data-scrum' => ($groupType === 'scrum' ? 'Y' : 'N'),
			];

			if (isset($this->counterData[$groupId]))
			{
				$processedRow['attrs']['data-counters'] = array_map(static function($item) {
					return (int)$item['VALUE'];
				}, $this->counterData[$groupId]);
			}

			$processedRow['actions'] = Helper\UI\Grid\Workgroup\Actions::getActions($group, $currentUserRelationItem, $row['actions'], [
				'GRID_ID' => $this->gridId,
				'PATH_TO_GROUP' => (
					in_array($this->arParams['MODE'], self::getTasksModeList(), true)
						? $this->arParams['PATH_TO_GROUP_TASKS']
						: $this->arParams['PATH_TO_GROUP']
				),
				'PATH_TO_GROUP_EDIT' => $this->arParams['PATH_TO_GROUP_EDIT'],
				'PATH_TO_GROUP_DELETE' => $this->arParams['PATH_TO_GROUP_DELETE'],
				'PATH_TO_JOIN_GROUP' => $this->arParams['PATH_TO_JOIN_GROUP'],
				'PATH_TO_LEAVE_GROUP' => $this->arParams['PATH_TO_LEAVE_GROUP'],
			]);

			unset($processedRow['data']['ROW_FIELDS']);

			$rowsList[$key] = $processedRow;
		}
	}

	private function getGroupIdsForCounters(int $userId, array $groupIdList, array $members): array
	{
		$groupIds = [];

		foreach ($groupIdList as $groupId)
		{
			if (!isset($members[$groupId]))
			{
				continue;
			}

			$listUserIds = array_merge(
				array_keys($members[$groupId]['HEADS']),
				array_keys($members[$groupId]['MEMBERS'])
			);
			if (!in_array($userId, $listUserIds))
			{
				continue;
			}

			$groupIds[] = $groupId;
		}

		return $groupIds;
	}
}