<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Component\WorkgroupUserList;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Filter;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\AccessManager;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\Search;
use Bitrix\Socialnetwork\WorkgroupTable;

Loader::includeModule('socialnetwork');

class CSocialnetworkGroupUserListComponent extends WorkgroupUserList
{
	protected const PRESET_ALL = 'all';
	protected const PRESET_COMPANY = 'company';
	protected const PRESET_REQUESTS_IN = 'requests_in';
	protected const PRESET_REQUESTS_OUT = 'requests_out';
	protected const PRESET_EXTERNAL = 'external';
	protected const PRESET_AUTO = 'auto';

	protected $gridId = 'SOCIALNETWORK_WORKGROUP_USER_LIST';
	protected $filterId = 'SOCIALNETWORK_WORKGROUP_USER_LIST';

	protected $fieldsList = [
		'ID',
		'FULL_NAME',
		'NAME',
		'LAST_NAME',
		'EMAIL',
		'SECOND_NAME',
		'ROLE',
		'INITIATED_BY_TYPE',
		'DEPARTMENT',
		'AUTO_MEMBER',
		'ACTIONS',
	];

	protected $availableEntityFields = [
		'ID',
		'NAME',
		'LAST_NAME',
		'SECOND_NAME',
		'PERSONAL_PHOTO',
		'ROLE',
		'AUTO_MEMBER',
		'INITIATED_BY_TYPE',
		'INITIATED_BY_USER_ID',
		'USER_ID',
		'GROUP_ID',
		'DEPARTMENT',
		'DATE_CREATE',
		'IS_SCRUM_MASTER',
		'AUTO_MEMBER_DEPARTMENT',
	];

	protected $actionData;

	protected function listKeysSignedParameters()
	{
		return [
			'GROUP_ID',
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
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_ID'),
					'sort' => 'ID',
					'editable' => false
				],
				[
					'id' => 'FULL_NAME',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_PERSON'),
					'sort' => 'FULL_NAME',
					'default' => true,
					'editable' => false,
					'prevent_default' => false,
				],
				[
					'id' => 'ROLE',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_ROLE'),
					'sort' => 'ROLE',
					'default' => true,
					'editable' => false
				],
			];

			if (ModuleManager::isModuleInstalled('intranet'))
			{
				$columns[] = [
					'id' => 'DEPARTMENT',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_DEPARTMENT'),
					'sort' => false,
					'default' => false,
					'editable' => false,
					'prevent_default' => true,
				];
			}

			$columns[] = [
				'id' => 'ACTIONS',
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_ACTIONS'),
				'default' => true,
				'editable' => false,
				'prevent_default' => false,
			];

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

	private function getDefaultGridHeaders(): array
	{
		$result = [];
		$gridHeaders = $this->getGridHeaders();
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
			'FULL_NAME',
			'EMAIL',
		];
	}

	private function clearFilter($componentResult): void
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId, $componentResult['FILTER_PRESETS']);
		$currentPresetId = $componentResult['CURRENT_PRESET_ID'];
		$customFilterData = $componentResult['CUSTOM_FILTER'];

		if (!empty($customFilterData))
		{
			$filterSettings = [
				'fields' => $customFilterData,
			];
		}
		else
		{
			$filterSettings = $filterOptions->getFilterSettings($currentPresetId);
			if (isset($componentResult['FILTER_PRESETS'][$currentPresetId]))
			{
				$filterSettings['fields'] = $componentResult['FILTER_PRESETS'][$currentPresetId]['fields'];
			}
		}

		$filterOptions->setFilterSettings($currentPresetId, $filterSettings, true, false);
		$filterOptions->save();
	}

	private function getCurrentPresetId(): string
	{
		switch ($this->arParams['MODE'])
		{
			case 'MEMBERS':
				$result = self::PRESET_COMPANY;
				break;
			case 'REQUESTS_IN':
				$result = self::PRESET_REQUESTS_IN;
				break;
			case 'REQUESTS_OUT':
				$result = self::PRESET_REQUESTS_OUT;
				break;
			default:
				$result = 'tmp_filter';
		}

		return $result;
	}

	private function getCounter(): string
	{
		switch ($this->arParams['MODE'])
		{
			case 'REQUESTS_IN':
				$result = CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN;
				break;
			case 'REQUESTS_OUT':
				$result = CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT;
				break;
			default:
				$result = '';
		}

		return $result;
	}

	private function getCurrentCustomFilter(): array
	{
		switch ($this->arParams['MODE'])
		{
			case 'MODERATORS':
				$result = [
					'ROLE' => [ UserToGroupTable::ROLE_MODERATOR ],
				];
				break;
			default:
				$result = [];
		}

		return $result;
	}

	private function getFilterPresets($componentResult): array
	{
		$result = [];

		$currentPresetId = $componentResult['CURRENT_PRESET_ID'];
		$extranetAvailable = \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability();

		$result[self::PRESET_ALL] = [
			'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_ALL'),
			'fields' => [],
			'default' => ($currentPresetId === self::PRESET_ALL),
		];

		$companyFilter = [
			'FIRED' => 'N',
			'ROLE' => UserToGroupTable::getRolesMember(),
		];
		if ($extranetAvailable)
		{
			$companyFilter['EXTRANET'] = 'N';
		}

		$result[self::PRESET_COMPANY] = [
			'name' => (
				$extranetAvailable
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_COMPANY')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_MEMBERS')
			),
			'fields' => $companyFilter,
			'default' => ($currentPresetId === self::PRESET_COMPANY),
		];

		if ($extranetAvailable)
		{
			$result[self::PRESET_EXTERNAL] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_EXTERNAL'),
				'fields' => [
					'EXTRANET' => 'Y',
					'FIRED' => 'N',
					'ROLE' => UserToGroupTable::getRolesMember(),
				],
				'default' => false,
			];
		}

		if ($componentResult['GROUP_PERMS']['UserCanProcessRequestsIn'] ?? null)
		{
			$result[self::PRESET_REQUESTS_IN] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_REQUESTS_IN'),
				'fields' => [
					'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
					'FIRED' => 'N',
					'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_USER,
				],
				'default' => ($currentPresetId === self::PRESET_REQUESTS_IN),
			];
		}

		if ($componentResult['GROUP_PERMS']['UserCanInitiate'])
		{
			$result[self::PRESET_REQUESTS_OUT] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_REQUESTS_OUT'),
				'fields' => [
					'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
					'FIRED' => 'N',
					'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				],
				'default' => ($currentPresetId === self::PRESET_REQUESTS_OUT),
			];
		}

		return $result;
	}

	private function getOrder(\Bitrix\Main\Grid\Options $gridOptions, array $componentResult = []): array
	{
		$result = [];
		$gridSort = $gridOptions->getSorting();

		$useDepartments = ModuleManager::isModuleInstalled('intranet');

		if (!empty($gridSort['sort']))
		{
			if ($useDepartments)
			{
				$result['AUTO_MEMBER'] = 'ASC';
				$result['AUTO_MEMBER_DEPARTMENT'] = 'ASC';
			}

			foreach ($gridSort['sort'] as $by => $order)
			{
				switch ($by)
				{
					case 'FULL_NAME':
						$by = 'USER.LAST_NAME';
						break;
					default:
				}
				$result[$by] = mb_strtoupper($order);

				if (
					$by === 'ROLE'
					&& $componentResult['SCRUM'] === 'Y'
				)
				{
					$result['IS_SCRUM_MASTER'] = 'DESC';
				}
			}
		}
		else
		{
			$result = [
				'ROLE' => 'ASC',
			];

			if ($componentResult['SCRUM'] === 'Y')
			{
				$result['IS_SCRUM_MASTER'] = 'DESC';
			}

			if ($useDepartments)
			{
				$result['AUTO_MEMBER'] = 'ASC';
				$result['AUTO_MEMBER_DEPARTMENT'] = 'ASC';
			}

			$result['USER.LAST_NAME'] = 'ASC';
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

	private function addQueryRuntime(\Bitrix\Main\Entity\Query $query, array $componentResult = []): void
	{
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$query->registerRuntimeField('AUTO_MEMBER_DEPARTMENT',
				new ExpressionField(
					'AUTO_MEMBER_DEPARTMENT',
					"(CASE WHEN %s = 'Y' THEN %s ELSE 0 END)",
					[ 'AUTO_MEMBER', 'USER.UF_DEPARTMENT' ]
				)
			);
		}

		if ($componentResult['SCRUM'] === 'Y')
		{
			$query->registerRuntimeField('IS_SCRUM_MASTER',
				new ExpressionField(
					'IS_SCRUM_MASTER',
					'(CASE WHEN %s = %s THEN 1 ELSE 0 END)',
					[ 'GROUP.SCRUM_MASTER_ID', 'USER.ID' ]
				)
			);
		}
	}

	private function addQueryOrder(\Bitrix\Main\Entity\Query $query, \Bitrix\Main\Grid\Options $gridOptions, array $componentResult = []): void
	{
		$orderFields = $this->getOrder($gridOptions, $componentResult);
		foreach ($orderFields as $fieldName => $value)
		{
			if (!$this->checkQueryFieldName($fieldName))
			{
				continue;
			}

			$query->addOrder($fieldName, $value);
		}
	}

	protected function addQueryFilter(\Bitrix\Main\Entity\Query $query, array $gridFilter, $componentResult)
	{
		$query->addFilter('=GROUP_ID', $this->arParams['GROUP_ID']);

		$filter = $this->getFilter($gridFilter);

		if (!empty($filter['=INITIATED_BY_TYPE']))
		{
			$filter['=ROLE'] = UserToGroupTable::ROLE_REQUEST;
		}

		$departmentId = 0;
		$initiatedByUser = false;

		foreach ($filter as $fieldName => $value)
		{
			if (
				$fieldName === 'DEPARTMENT'
				&& $value === false
			)
			{
				$query->addFilter('=USER.UF_DEPARTMENT', false);
				unset($filter[$fieldName]);
				continue;
			}

			if (
				$fieldName === '!DEPARTMENT'
				&& $value === false
			)
			{
				$query->addFilter('!=USER.UF_DEPARTMENT', false);
				unset($filter[$fieldName]);
				continue;
			}

			if ($fieldName === '=DEPARTMENT')
			{
				$departmentId = (int)$value;
				unset($filter[$fieldName]);
				continue;
			}

			if (preg_match('/([%=]*)(ID|LAST_NAME|NAME|EMAIL)/i', $fieldName, $matches))
			{
				[ , $operation, $fieldName ] = $matches;
				$query->addFilter($operation . 'USER.' . $fieldName, $value);
				continue;
			}

			if ($fieldName === '=INITIATED_BY_TYPE')
			{
				$query->addFilter('=INITIATED_BY_TYPE', $value);

				if ($value === UserToGroupTable::INITIATED_BY_GROUP)
				{
					$initiatedByUser = true;

					if (
						!$componentResult['GROUP_PERMS']['UserCanProcessRequestsIn']
						&& !Helper\Workgroup::isCurrentUserModuleAdmin()
					)
					{
						$query->addFilter('=INITIATED_BY_USER_ID', Helper\User::getCurrentUserId());
					}
				}

				unset($filter[$fieldName]);
				continue;
			}

			if (!$this->checkQueryFieldName($fieldName))
			{
				continue;
			}

			$query->addFilter($fieldName, $value);
		}

		if (
			!$initiatedByUser
			&& !($componentResult['GROUP_PERMS']['UserCanProcessRequestsIn'] ?? null)
			&& !Helper\Workgroup::isCurrentUserModuleAdmin()
		)
		{
			$query->addFilter(null, [
				'LOGIC' => 'OR',
				[
					'=ROLE' => UserToGroupTable::ROLE_REQUEST,
					'=INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
					'=INITIATED_BY_USER_ID' => Helper\User::getCurrentUserId(),
				],
				[
					'!=ROLE' => UserToGroupTable::ROLE_REQUEST,
				],
				[
					'!=INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				],
			]);
		}

		if ($departmentId > 0)
		{
			$iblockId = (int)Option::get('intranet', 'iblock_structure', 0);
			if (
				$iblockId > 0
				&& Loader::includeModule('iblock')
			)
			{
				$section = \Bitrix\Iblock\SectionTable::getList([
					'filter' => [
						'=IBLOCK_ID' => $iblockId,
						'=ID' => $departmentId
					],
					'select' => [ 'LEFT_MARGIN', 'RIGHT_MARGIN' ],
					'limit' => 1
				])->fetch();

				if ($section)
				{
					$query->registerRuntimeField(
						'',
						new \Bitrix\Main\Entity\ReferenceField(
							'DEPARTMENT',
							\Bitrix\Iblock\SectionTable::getEntity(),
							[
								'=ref.ID' => 'this.USER.UF_DEPARTMENT_SINGLE',
							],
							[ 'join_type' => 'INNER' ]
						)
					);
					$query->addFilter('=DEPARTMENT.IBLOCK_ID', $iblockId);
					$query->addFilter('>=DEPARTMENT.LEFT_MARGIN', $section['LEFT_MARGIN']);
					$query->addFilter('<=DEPARTMENT.RIGHT_MARGIN', $section['RIGHT_MARGIN']);
				}
			}
		}
	}

	private function addQuerySelect(\Bitrix\Main\Entity\Query $query, \Bitrix\Main\Grid\Options $gridOptions, array $componentResult = []): void
	{
		$selectFields = $this->getSelect($gridOptions, $componentResult);
		foreach ($selectFields as $fieldName)
		{
			if (!$this->checkQueryFieldName($fieldName))
			{
				continue;
			}

			$query->addSelect($fieldName);
		}
	}

	private function addFilterInteger(&$filter, array $params = []): void
	{
		$filterFieldName = ($params['FILTER_FIELD_NAME'] ?? '');
		$value = ($params['VALUE'] ?? '');

		if (
			$filterFieldName === ''
			|| (int)$value <= 0
		)
		{
			return;
		}

		$fieldName = (isset($params['FIELD_NAME']) && $params['FIELD_NAME'] !== '' ? $params['FIELD_NAME'] : $filterFieldName);
		$operation = ($params['OPERATION'] ?? '=');

		if (in_array($fieldName, $this->fieldsList, true))
		{
			$filter[$operation . $fieldName] = $value;
		}
	}

	private function addFilterString(&$filter, array $params = []): void
	{
		$filterFieldName = ($params['FILTER_FIELD_NAME'] ?? '');
		$value = ($params['VALUE'] ?? '');

		if ($filterFieldName === '')
		{
			return;

		}
		if (
			!is_array($value)
			&& trim($value, '%') === ''
		)
		{
			return;
		}

		if (
			is_array($value)
			&& empty(array_filter($value, static function($item) {
				return trim($item, '%') !== '';
			}))
		)
		{
			return;
		}

		$fieldName = (isset($params['FIELD_NAME']) && $params['FIELD_NAME'] !== '' ? $params['FIELD_NAME'] : $filterFieldName);
		$operation = ($params['OPERATION'] ?? '%=');

		if (in_array($fieldName, $this->fieldsList, true))
		{
			$filter[$operation . $fieldName] = $value;
		}
	}

	private function addFilterDateTime(&$filter, array $params = []): void
	{
		$filterFieldName = ($params['FILTER_FIELD_NAME'] ?? '');
		$valueFrom = ($params['VALUE_FROM'] ?? '');
		$valueTo = ($params['VALUE_TO'] ?? '');

		if (
			$filterFieldName === ''
			|| (
				$valueFrom === ''
				&& $valueTo === ''
			)
		)
		{
			return;
		}

		$fieldName = (isset($params['FIELD_NAME']) && $params['FIELD_NAME'] !== '' ? $params['FIELD_NAME'] : $filterFieldName);

		if (in_array($fieldName, $this->fieldsList, true))
		{
			if ($valueFrom !== '')
			{
				$filter['>=' . $fieldName] = $valueFrom;
			}
			if ($valueTo !== '')
			{
				$filter['<=' . $fieldName] = $valueTo;
			}
		}

	}

	/**
	 * Get filter for getList.
	 * @param array $gridFilter
	 * @return array
	 */
	private function getFilter(array $gridFilter): array
	{
		$result = [];

		if (!\Bitrix\Main\Filter\UserDataProvider::getFiredAvailability())
		{
			$result['=USER.ACTIVE'] = 'Y';
		}
		elseif (!empty($gridFilter['FIRED']))
		{
			if ($gridFilter['FIRED'] === 'Y')
			{
				$result['=USER.ACTIVE'] = 'N';
			}
			elseif ($gridFilter['FIRED'] === 'N')
			{
				$result['=USER.ACTIVE'] = 'Y';
			}
		}

		if (
			!empty($gridFilter['EXTRANET'])
			&& \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability()
		)
		{
			if ($gridFilter['EXTRANET'] === 'Y')
			{
				$result['=USER.UF_DEPARTMENT'] = false;
			}
			elseif ($gridFilter['EXTRANET'] === 'N')
			{
				$result['!=USER.UF_DEPARTMENT'] = false;
			}
		}

		$gridFilter['DEPARTMENT'] = ($gridFilter['DEPARTMENT'] ?? '');

		$integerFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'ID',
				'FIELD_NAME' => 'ID',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['ID'] ?? '',
			],
			[
				'FILTER_FIELD_NAME' => 'DEPARTMENT',
				'FIELD_NAME' => 'DEPARTMENT',
				'OPERATION' => '=',
				'VALUE' => (
					$gridFilter['DEPARTMENT']
					&& preg_match('/^DR(\d+)$/', $gridFilter['DEPARTMENT'], $matches)
						? $matches[1]
						: ($gridFilter['DEPARTMENT'] && (int)($gridFilter['DEPARTMENT'] > 0) ? $gridFilter['DEPARTMENT'] : false)
				),
			],
			[
				'FILTER_FIELD_NAME' => 'DEPARTMENT',
				'FIELD_NAME' => 'USER.UF_DEPARTMENT',
				'OPERATION' => '=',
				'VALUE' => (
					$gridFilter['DEPARTMENT']
					&& preg_match('/^D(\d+)$/', $gridFilter['DEPARTMENT'], $matches)
						? $matches[1]
						: false
				),
			]
		];

		$stringFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'NAME',
				'FIELD_NAME' => 'NAME',
				'OPERATION' => '%=',
				'VALUE' => ($gridFilter['NAME'] ?? '') . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'LAST_NAME',
				'FIELD_NAME' => 'LAST_NAME',
				'OPERATION' => '%=',
				'VALUE' => ($gridFilter['LAST_NAME'] ?? '') . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'EMAIL',
				'FIELD_NAME' => 'EMAIL',
				'OPERATION' => '%=',
				'VALUE' => ($gridFilter['EMAIL'] ?? '') . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'ROLE',
				'FIELD_NAME' => 'ROLE',
				'OPERATION' => '=',
				'VALUE' => ($gridFilter['ROLE'] ?? ''),
			],
			[
				'FILTER_FIELD_NAME' => 'INITIATED_BY_TYPE',
				'FIELD_NAME' => 'INITIATED_BY_TYPE',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['INITIATED_BY_TYPE'] ?? '',
			],
			[
				'FILTER_FIELD_NAME' => 'AUTO_MEMBER',
				'FIELD_NAME' => 'AUTO_MEMBER',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['AUTO_MEMBER'] ?? '',
			],
		];

		$dateFieldsList = [
		];

		foreach ($integerFieldsList as $field)
		{
			$value = false;

			if (
				is_array($field['VALUE'])
				&& !empty($field['VALUE'])
			)
			{
				$value = $field['VALUE'];
			}
			elseif (
				!is_array($field['VALUE'])
				&& (string)$field['VALUE'] !== ''
			)
			{
				$value = (int)$field['VALUE'];
			}

			if ($value !== false)
			{
				$this->addFilterInteger($result, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'OPERATION' => ($field['OPERATION'] ?? '='),
					'VALUE' => $value,
				]);
			}
		}

		foreach ($stringFieldsList as $field)
		{
			if ($field['VALUE'] !== '')
			{
				$this->addFilterString($result, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'OPERATION' => ($field['OPERATION'] ?? '%='),
					'VALUE' => $field['VALUE'],
				]);
			}
		}

		foreach ($dateFieldsList as $field)
		{
			if (
				!empty($field['VALUE_FROM'])
				|| !empty($field['VALUE_TO'])
			)
			{
				$this->addFilterDateTime($result, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'VALUE_FROM' => ($field['VALUE_FROM'] ?? $gridFilter[$field['FILTER_FIELD_NAME']]),
					'VALUE_TO' => ($field['VALUE_TO'] ?? $gridFilter[$field['FILTER_FIELD_NAME']]),
				]);
			}
		}

		if (
			isset($gridFilter['FIND'])
			&& $gridFilter['FIND']
		)
		{
			$findFilter = self::getFindFilter($gridFilter['FIND']);
			if (!empty($findFilter))
			{
				$result = array_merge($result, $findFilter);
			}
		}

		return $result;
	}

	protected function getSelect(\Bitrix\Main\Grid\Options $gridOptions, array $componentResult = []): array
	{
		$result = [
			'GROUP_ID', 'USER_ID',
			'ROLE', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID', 'AUTO_MEMBER', 'DATE_CREATE',
			'USER.ID', 'USER.CONFIRM_CODE',
		];
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$result[] = 'USER.USER_TYPE';
			$result[] = 'USER.UF_DEPARTMENT';
		}

		if ($componentResult['SCRUM'] === 'Y')
		{
			$result[] = 'GROUP.SCRUM_MASTER_ID';
		}

		$gridColumns = $gridOptions->getVisibleColumns();

		if (empty($gridColumns))
		{
			$gridColumns = $this->getDefaultGridHeaders();
		}

		foreach ($gridColumns as $column)
		{
			switch($column)
			{
				case 'EMAIL':
					$result[] = 'USER.EMAIL';
					break;
				case 'FULL_NAME':
					$result[] = 'USER.NAME';
					$result[] = 'USER.LAST_NAME';
					$result[] = 'USER.SECOND_NAME';
					$result[] = 'USER.LOGIN';
					$result[] = 'USER.PERSONAL_PHOTO';
					$result[] = 'USER.PERSONAL_GENDER';
					$result[] = 'USER.WORK_POSITION';
					break;
				case 'DEPARTMENT':
					break;
				default:
					$result[] = $column;
			}
		}

		return $result;
	}

	public function onPrepareComponentParams($params): array
	{
		$params['GROUP_ID'] = (int)($params['GROUP_ID'] ?? 0);
		$params['GROUP_USE_BAN'] = 'N';

		if (empty($params['PATH_TO_DEPARTMENT']))
		{
			$params['PATH_TO_DEPARTMENT'] = ($params['PATH_TO_CONPANY_DEPARTMENT'] ?? '');
		}
		if (empty($params['PATH_TO_DEPARTMENT']))
		{
			$params['PATH_TO_DEPARTMENT'] = Helper\Path::get('department_path_template');
		}

		if (empty($params['PATH_TO_USER']))
		{
			$params['PATH_TO_USER'] = Helper\Path::get('user_profile');
		}

		if (empty($params['PATH_TO_GROUP_INVITE']))
		{
			$params['PATH_TO_GROUP_INVITE'] = Helper\Path::get('group_invite_path_template');
		}

		if (empty($params['PATH_TO_GROUP_USERS']))
		{
			$params['PATH_TO_GROUP_USERS'] = Helper\Path::get('group_users_path_template');
		}

		if (empty($params['PATH_TO_GROUP_REQUESTS']))
		{
			$params['PATH_TO_GROUP_REQUESTS'] = Helper\Path::get('group_requests_path_template');
		}

		if (empty($params['PATH_TO_GROUP_REQUESTS_OUT']))
		{
			$params['PATH_TO_GROUP_REQUESTS_OUT'] = Helper\Path::get('group_requests_out_path_template');
		}

		return $params;
	}

	protected function prepareActions(): void
	{
		$this->actionData = [ 'ACTIVE' => false ];

		if (!check_bitrix_sessid())
		{
			return;
		}

		$request = Context::getCurrent()->getRequest();
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

	protected function processAction(): void
	{
		if (!$this->actionData['ACTIVE'])
		{
			return;
		}

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
			return;
		}

		$groupId = $this->arParams['GROUP_ID'];

		$deleteRelationIdList = [];
		$removeModeratorRelationIdList = [];
		$rejectIncomingRequestRelationIdList = [];

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [
				'ID',
				'CLOSED',
				'PROJECT',
				'SCRUM_MASTER_ID',
				'INITIATE_PERMS',
			],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => Helper\User::getCurrentUserId(),
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'INITIATED_BY_TYPE' ],
		])->fetchObject();

		$res = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'@USER_ID' => $idList,
			],
			'select' => [
				'ID',
				'GROUP_ID',
				'USER_ID',
				'USER',
				'ROLE',
				'INITIATED_BY_TYPE',
				'INITIATED_BY_USER_ID',
				'AUTO_MEMBER',
			],
		]);
		$relations = $res->fetchCollection();

		foreach ($relations as $relation)
		{
			$accessManager = new AccessManager(
				$group,
				$relation,
				$currentUserRelation,
			);

			if ($this->actionData['NAME'] === 'delete')
			{
				$canRemoveModerator = $accessManager->canRemoveModerator();
				$canDeleteOutgoingRequest = $accessManager->canDeleteOutgoingRequest();
				$canExclude = $accessManager->canExclude();
				$canProcessIncomingRequest = $accessManager->canProcessIncomingRequest();

				if (
					!$canDeleteOutgoingRequest
					&& !$canProcessIncomingRequest
					&& !$canRemoveModerator
					&& !$canExclude
				)
				{
					continue;
				}

				if ($canRemoveModerator)
				{
					$removeModeratorRelationIdList[] = $relation->getId();
				}
				elseif ($canProcessIncomingRequest)
				{
					$rejectIncomingRequestRelationIdList[] = $relation->getId();
				}
				else
				{
					$deleteRelationIdList[] = $relation->getId();
				}
			}
		}

		if ($this->actionData['NAME'] === 'delete')
		{
			if (
				empty($deleteRelationIdList)
				&& empty($rejectIncomingRequestRelationIdList)
				&& empty($removeModeratorRelationIdList)
			)
			{
				$this->addError(new Error(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_GROUP_ACTION_EMPTY_LIST')));
				return;
			}

			foreach ($deleteRelationIdList as $relationId)
			{
				Helper\Workgroup::deleteRelation([
					'relationId' => $relationId,
				]);
			}

			if (!empty($removeModeratorRelationIdList))
			{
				CSocNetUserToGroup::TransferModerator2Member(
					Helper\User::getCurrentUserId(),
					$groupId,
					$removeModeratorRelationIdList
				);
			}

			if (!empty($rejectIncomingRequestRelationIdList))
			{
				CSocNetUserToGroup::RejectRequestToBeMember(
					Helper\User::getCurrentUserId(),
					$groupId,
					$rejectIncomingRequestRelationIdList
				);
			}
		}
	}

	protected function prepareData(): array
	{
		$result = [
			'GROUP_ACTION_MESSAGES' => [],
			'FIELDS_LIST' => $this->fieldsList,
		];

		$group = Workgroup::getById($this->arParams['GROUP_ID']);

		if ($group && $group->isScrumProject())
		{
			$result['SCRUM'] = 'Y';
			$result['PROJECT'] = 'Y';
		}
		elseif ($group && $group->isProject())
		{
			$result['SCRUM'] = 'N';
			$result['PROJECT'] = 'Y';
		}
		else
		{
			$result['SCRUM'] = 'N';
			$result['PROJECT'] = 'N';
		}

		$this->initPageFilterData();

		$entityFilter = Filter\Factory::createEntityFilter(
			UserToGroupTable::getUfId(),
			[
				'ID' => $this->filterId,
			]
		);

		$result['GROUP'] = $this->getGroup();

		if (empty($result['GROUP']))
		{
			$this->addError(new Error(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_NO_GROUP')));
			return $result;
		}

		$result['GROUP_PERMS'] = Helper\Workgroup::getPermissions([
			'groupId' => $result['GROUP']['ID'],
		]);

		if (
			!$result['GROUP_PERMS']
			|| !$result['GROUP_PERMS']['UserCanViewGroup']
		)
		{
			$this->addError(new Error(
				$result['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_NO_GROUP_PERMS_PROJECT')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_NO_GROUP_PERMS')
			));
			return $result;
		}

		$result['CONNECTED_DEPARTMENTS_LIST'] = $this->getConnectedDepartmentsList([
			'groupFields' => $result['GROUP'],
			'pathToDepartment' => $this->arParams['PATH_TO_DEPARTMENT'],
		]);

		$result['CONNECTED_SUBDEPARTMENTS_LIST'] = $this->getConnectedSubDepartmentsList([
			'groupFields' => $result['GROUP'],
		]);

		$result['EXTRANET_SITE'] = (static::extranetSite() ? 'Y' : 'N');
		$result['HideArchiveLinks'] = (
			$result['GROUP']['CLOSED'] === 'Y'
			&& Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
		);

		$result['GROUP_USE_BAN'] = (
			$this->arParams['GROUP_USE_BAN'] === 'Y'
			&& $result['EXTRANET_SITE'] !== 'Y'
			&& !$result['HideArchiveLinks']
				? 'Y'
				: 'N'
		);

		$result['GRID_ID'] = $this->gridId;
		$result['FILTER_ID'] = $this->filterId;
		$result['CURRENT_PRESET_ID'] = $this->getCurrentPresetId();
		$result['CURRENT_COUNTER'] = $this->getCounter();
		$result['CUSTOM_FILTER'] = $this->getCurrentCustomFilter();

		$result['FILTER_PRESETS'] = $this->getFilterPresets($result);

		$request = Context::getCurrent()->getRequest();

		if (
			($request->getPost('apply_filter') !== 'Y')
			&& ($request->get('grid_action') !== 'pagination')
		)
		{
			$this->clearFilter($result);
		}

		$gridOptions = new \Bitrix\Main\Grid\Options($result['GRID_ID']);

		$navParams = $gridOptions->getNavParams();
		$pageSize = $navParams['nPageSize'];

		$nav = new \Bitrix\Main\UI\PageNavigation('page');

		$nav->allowAllRecords(false)
			->setPageSize($pageSize)
			->initFromUri();

		$result['HEADERS'] = $this->getGridHeaders();
		$result['FILTER'] = $entityFilter->getFieldArrays();
		$result['ROWS'] = [];

		$gridFilter = $this->getGridFilter([
			'FILTER_PRESETS' => $result['FILTER_PRESETS'],
			'FILTER' => $result['FILTER'],
		]);

		$query = new \Bitrix\Main\Entity\Query(UserToGroupTable::getEntity());
		$this->addQueryRuntime($query, $result);
		$this->addQueryOrder($query, $gridOptions, $result);
		$this->addQueryFilter($query, $gridFilter, $result);
		$this->addQuerySelect($query, $gridOptions, $result);
		$query->countTotal(true);
		$query->setOffset($nav->getOffset());
		$query->setLimit($nav->getLimit());
		$query->disableDataDoubling();
		$res = $query->exec();
		$records = $res->fetchCollection();

		$gridColumns = $gridOptions->getVisibleColumns();

		if (empty($gridColumns))
		{
			$gridColumns = $this->getDefaultGridHeaders();
		}
		else
		{
			$availableGridColumns = array_map(static function($gridColumn) {
				return $gridColumn['id'];
			}, $this->getGridHeaders());
			$gridColumns = array_filter($gridColumns, static function ($key) use ($availableGridColumns) {
				return in_array($key, $availableGridColumns, true);
			});
		}

		$rowsList = [];
		$showActionsColumn = false;

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => (int)$this->arParams['GROUP_ID'],
				'=USER_ID' => Helper\User::getCurrentUserId(),
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'INITIATED_BY_TYPE' ],
		])->fetchObject();

		$group = \Bitrix\Socialnetwork\WorkgroupTable::getList([
			'filter' => [
				'=ID' => (int)$this->arParams['GROUP_ID'],
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID', 'INITIATE_PERMS' ],
		])->fetchObject();

		foreach ($records as $record)
		{
			$row['ROW_FIELDS'] = $record;

			$row['CAN_EDIT'] = false;
			$row['CAN_DELETE'] = false;

			$actions = self::getActions([
				'RELATION' => $row['ROW_FIELDS'],
				'PATH_TO_USER' => $this->arParams['PATH_TO_USER'],
				'GROUP_ID' => $this->arParams['GROUP_ID'],
				'GROUP' => $group,
				'CURRENT_RELATION' => $currentUserRelation,
			]);

			if (
				!$showActionsColumn
				&& !empty(array_intersect($actions, $this->getViewableActionList()))
			)
			{
				$showActionsColumn = true;
			}

			$rowsList[] = [
				'id' => $record->getUser()->getId(),
				'data' => $row,
				'columns' => [],
				'editable' => true,
				'actions' => $actions,
				'columnClasses' => (
					ModuleManager::isModuleInstalled('intranet')
					&& $record->getUser()->getUserType() === 'extranet'
						? [
							'FULL_NAME' => 'socialnetwork-group-user-list-full-name-extranet'
						]
						: []
				)
			];
		}

		if (!$showActionsColumn)
		{
			$gridColumns = array_filter($gridColumns, static function($item) { return $item !== 'ACTIONS'; });
			$result['HEADERS'] = array_filter($result['HEADERS'], static function($item) { return $item['id'] !== 'ACTIONS'; });
		}

		$result['ROWS'] = $rowsList;
		$result['ROWS_COUNT'] = $res->getCount();

		$nav->setRecordCount($result['ROWS_COUNT']);
		$result['NAV_OBJECT'] = $nav;

		$result['GRID_COLUMNS'] = $gridColumns;

		return $result;
	}

	private function getViewableActionList(): array
	{
		return [
			self::AVAILABLE_ACTION_PROCESS_INCOMING_REQUEST,
			self::AVAILABLE_ACTION_REINVITE,
		];
	}

	protected function initPageFilterData(): void
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
	}

	protected function getGridFilter(array $params = []): array
	{
		$filterPresets = ($params['FILTER_PRESETS'] ?? []);
		$filter = ($params['FILTER'] ?? []);
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($this->filterId, $filterPresets);

		return $filterOptions->getFilter($filter);

	}

	protected static function extranetSite(): bool
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

	/**
	 * @param string $value
	 * @return array
	 */
	public static function getFindFilter(string $value): array
	{
		$result = [];

		$value = trim($value);

		$value = (
			Search\Content::isIntegerToken($value)
				? Search\Content::prepareIntegerToken($value)
				: Search\Content::prepareStringToken($value)
		);

		if (Search\Content::canUseFulltextSearch($value, Search\Content::TYPE_MIXED))
		{
			$result['*USER.INDEX.SEARCH_ADMIN_CONTENT'] = $value;
		}

		return $result;
	}


	public function executeComponent()
	{
		$this->setTitle();

		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		$this->prepareActions();

		if ($this->actionData['ACTIVE'])
		{
			$this->processAction();
		}

		$this->arResult = $this->prepareData();

		if (!empty($this->errorCollection->getValues()))
		{
			if ($this->actionData['ACTIVE'])
			{
				foreach ($this->errorCollection->getValues() as $error)
				{
					$this->arResult['GROUP_ACTION_MESSAGES'][] = $error->getMessage();
				}
			}
			else
			{
				$this->printErrors();
				return;
			}
		}

		$this->includeComponentTemplate();
	}

	protected function setTitle(array $params = []): void
	{
		global $APPLICATION;

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$group = Workgroup::getById($this->arParams['GROUP_ID']);

			if ($group && $group->isScrumProject())
			{
				$type = 'scrum';
			}
			elseif ($group && $group->isProject())
			{
				$type = 'project';
			}
			else
			{
				$type = 'group';
			}

			switch ($type)
			{
				case 'scrum':
					$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MEMBERS_SCRUM');
					break;
				case 'project':
					$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MEMBERS_PROJECT');
					break;
				default:
					$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MEMBERS');
			}

			if (SITE_TEMPLATE_ID !== 'bitrix24')
			{
				if ($this->arParams['MODE'] === 'REQUESTS_IN')
				{
					switch ($type)
					{
						case 'scrum':
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_IN_SCRUM');
							break;
						case 'project':
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_IN_PROJECT');
							break;
						default:
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_IN');
					}
				}
				elseif ($this->arParams['MODE'] === 'REQUESTS_OUT')
				{
					switch ($type)
					{
						case 'scrum':
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_OUT_SCRUM');
							break;
						case 'project':
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_OUT_PROJECT');
							break;
						default:
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_OUT');
					}
				}
				elseif ($this->arParams['MODE'] === 'MODERATORS')
				{
					switch ($type)
					{
						case 'scrum':
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MODERATORS_SCRUM');
							break;
						case 'project':
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MODERATORS_PROJECT');
							break;
						default:
							$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MODERATORS');
					}
				}
			}

			$title = \Bitrix\Socialnetwork\ComponentHelper::getWorkgroupPageTitle([
				'WORKGROUP_ID' => $this->arParams['GROUP_ID'],
				'TITLE' => $shortTitle
			]);

			$APPLICATION->SetPageProperty('title', $title);
			$APPLICATION->SetTitle($shortTitle);
		}
	}

	protected function checkRequiredParams(): bool
	{
		if ($this->arParams['GROUP_ID'] <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_NO_GROUP')));
			return false;
		}

		return true;
	}

	protected function getGroup()
	{
		$result = \CSocNetGroup::getById($this->arParams['GROUP_ID']);
		if (!empty($result['UF_SG_DEPT']))
		{
			$result['UF_SG_DEPT'] = array_map(static function($value) {
				return (int)$value;
			}, $result['UF_SG_DEPT']);
		}

		return $result;
	}

	protected function getConnectedDepartmentsList(array $params = []): array
	{
		$result = [];

		if (
			empty($params['groupFields'])
			|| !is_array($params['groupFields'])
			|| empty($params['groupFields']['UF_SG_DEPT'])
			|| !is_array($params['groupFields']['UF_SG_DEPT'])
			|| !Loader::includeModule('intranet')
		)
		{
			return $result;
		}

		$departmentsList = CIntranetUtils::getDepartmentsData(CIntranetUtils::GetIBlockSectionChildren($params['groupFields']['UF_SG_DEPT']));
		if (empty($departmentsList))
		{
			return $result;
		}

		foreach ($departmentsList as $departmentId => $departmentName)
		{
			$departmentId = (int)$departmentId;
			if ($departmentId <= 0)
			{
				continue;
			}

			$result[] = [
				'ID' => $departmentId,
				'NAME' => $departmentName,
				'URL' => str_replace('#ID#', $departmentId, (string)($params['pathToDepartment'] ?? '')),
			];
		}

		return $result;
	}

	protected function getConnectedSubDepartmentsList(array $params = []): array
	{
		$result = [];

		if (
			empty($params['groupFields'])
			|| !is_array($params['groupFields'])
			|| empty($params['groupFields']['UF_SG_DEPT'])
			|| !is_array($params['groupFields']['UF_SG_DEPT'])
			|| !Loader::includeModule('intranet')
		)
		{
			return $result;
		}

		foreach ($params['groupFields']['UF_SG_DEPT'] as $departmentId)
		{
			$result[$departmentId] = $this->getSubDepartmentsList((int)$departmentId);
		}

		return $result;
	}

	protected function getSubDepartmentsList(int $departmentId = 0): array
	{
		$result = [];
		if ($departmentId <= 0)
		{
			return $result;
		}

		$subDepartmentsList = \CIntranetUtils::getSubDepartments($departmentId);
		if (empty($subDepartmentsList))
		{
			return $result;
		}

		$result = array_map(static function($value) {
			return (int)$value;
		}, $subDepartmentsList);

		foreach ($subDepartmentsList as $subDepartmentId)
		{
			$result = array_merge($result, $this->getSubDepartmentsList((int)$subDepartmentId));
		}

		return $result;
	}

	public function actAction(string $action = '', array $fields = []): ?array
	{
		if (!$this->checkAction($action))
		{
			$this->errorCollection->setError(
				new Error(
					Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_ACTION_NOT_SUPPORTED')
				)
			);

			return null;
		}

		try
		{
			switch ($action)
			{
				case self::AJAX_ACTION_SET_OWNER:
					$result = Helper\Workgroup::setOwner([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_SET_SCRUM_MASTER:
					$result = Helper\Workgroup::setScrumMaster([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_SET_MODERATOR:
					$result = Helper\Workgroup::setModerator([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_REMOVE_MODERATOR:
					$result = Helper\Workgroup::removeModerator([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_DELETE_OUTGOING_REQUEST:
					$result = Helper\Workgroup::deleteOutgoingRequest([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_DELETE_INCOMING_REQUEST:
					$result = Helper\Workgroup::deleteIncomingRequest([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_EXCLUDE:
					$result = Helper\Workgroup::exclude([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_ACCEPT_INCOMING_REQUEST:
					$result = Helper\Workgroup::acceptIncomingRequest([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_REJECT_INCOMING_REQUEST:
					$result = Helper\Workgroup::rejectIncomingRequest([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				case self::AJAX_ACTION_REINVITE:
					$result = false;

					if (
						(int)$fields['userId'] > 0
						&& Loader::includeModule('intranet')
					)
					{
						if (
							Loader::includeModule('extranet')
							&& !\CExtranet::IsIntranetUser()
						)
						{
							$result = \CIntranetInviteDialog::reinviteExtranetUser(SITE_ID, (int)$fields['userId']);
						}
						else
						{
							$result = \CIntranetInviteDialog::reinviteUser(SITE_ID, (int)$fields['userId']);
						}
					}
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

	protected function checkAction(string $action = ''): bool
	{
		return in_array($action, [
			self::AJAX_ACTION_SET_OWNER,
			self::AJAX_ACTION_SET_SCRUM_MASTER,
			self::AJAX_ACTION_SET_MODERATOR,
			self::AJAX_ACTION_REMOVE_MODERATOR,
			self::AJAX_ACTION_EXCLUDE,
			self::AJAX_ACTION_DELETE_OUTGOING_REQUEST,
			self::AJAX_ACTION_DELETE_INCOMING_REQUEST,
			self::AJAX_ACTION_ACCEPT_INCOMING_REQUEST,
			self::AJAX_ACTION_REJECT_INCOMING_REQUEST,
			self::AJAX_ACTION_REINVITE,
		], true);
	}

	public function disconnectDepartmentAction(array $fields = []): ?array
	{
		try
		{
			$result = Helper\Workgroup::disconnectDepartment([
				'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
				'departmentId' => (int)($fields['id'] ?? 0),
			]);
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

}
