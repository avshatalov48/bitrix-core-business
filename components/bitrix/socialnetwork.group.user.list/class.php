<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Component\WorkgroupUserList;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Filter;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\Search;

Loader::includeModule('socialnetwork');

class CSocialnetworkGroupUserListComponent extends WorkgroupUserList
{
	protected $gridId = 'SOCIALNETWORK_WORKGROUP_USER_LIST';
	protected $filterId = 'SOCIALNETWORK_WORKGROUP_USER_LIST';

	protected $fieldsList = [
		'ID',
		'FULL_NAME',
		'NAME',
		'LAST_NAME',
		'SECOND_NAME',
		'PHOTO',
		'ROLE',
		'INITIATED_BY_TYPE',
		'DEPARTMENT',
		'AUTO_MEMBER',
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
		'DEPARTMENT',
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
					'id' => 'PHOTO',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_PHOTO'),
					'sort' => false,
					'default' => true,
					'editable' => false
				],
				[
					'id' => 'FULL_NAME',
					'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_COLUMN_FULL_NAME'),
					'sort' => 'FULL_NAME',
					'default' => true,
					'editable' => false
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
					!$column['default']
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
			'PHOTO',
			'FULL_NAME',
			'EMAIL',
		];
	}

	private function getFilterFields(\Bitrix\Main\Filter\Filter $entityFilter, array $usedFields = []): array
	{
		$result = [];

		$fields = $entityFilter->getFields();

		foreach ($fields as $fieldName => $field)
		{
			if (!in_array($fieldName, $usedFields, true))
			{
				continue;
			}

			$result[] = $field->toArray();
		}

		return $result;
	}

	private function getFilterPresets(\Bitrix\Main\Filter\Filter $entityFilter, $componentResult): array
	{
		$result = [];

		if (\Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability())
		{
			$result['company'] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_COMPANY'),
				'fields' => [
					'EXTRANET' => 'N',
					'FIRED' => 'N',
					'ROLE' => UserToGroupTable::getRolesMember(),
				],
				'default' => false,
			];

			$result['external'] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_EXTERNAL'),
				'fields' => [
					'EXTRANET' => 'Y',
					'FIRED' => 'N',
					'ROLE' => UserToGroupTable::getRolesMember(),
				],
				'default' => false,
			];
		}

		if (\Bitrix\Socialnetwork\Filter\UserToGroupDataProvider::getAutoMemberAvailability())
		{
			$result['auto'] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_AUTO'),
				'fields' => [
					'AUTO_MEMBER' => 'Y',
				],
				'default' => false,
			];
		}

		if ($componentResult['GROUP_PERMS']['UserCanProcessRequestsIn'])
		{
			$result['requests_in'] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_REQUESTS_IN'),
				'fields' => [
					'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
					'FIRED' => 'N',
					'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_USER,
				],
				'default' => false,
			];
		}

		if ($componentResult['GROUP_PERMS']['UserCanInitiate'])
		{
			$result['requests_out'] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_REQUESTS_OUT'),
				'fields' => [
					'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
					'FIRED' => 'N',
					'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				],
				'default' => false,
			];
		}

		return $result;
	}

	private function getOrder(\Bitrix\Main\Grid\Options $gridOptions): array
	{
		$result = [];
		$gridSort = $gridOptions->getSorting();

		$useDepartments = (
			ModuleManager::isModuleInstalled('intranet')
			&& \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability()
		);

		if (!empty($gridSort['sort']))
		{
			if ($useDepartments)
			{
				$result['AUTO_MEMBER'] = 'ASC';
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
			}
		}
		else
		{
			$result = [
				'ROLE' => 'ASC',
			];

			if ($useDepartments)
			{
				$result['AUTO_MEMBER'] = 'ASC';
				$result['USER.UF_DEPARTMENT'] = 'ASC';
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

	private function addQueryOrder(\Bitrix\Main\Entity\Query $query, \Bitrix\Main\Grid\Options $gridOptions): void
	{
		$orderFields = $this->getOrder($gridOptions);
		foreach ($orderFields as $fieldName => $value)
		{
			if (!$this->checkQueryFieldName($fieldName))
			{
				continue;
			}

			$query->addOrder($fieldName, $value);
		}
	}

	protected function addQueryFilter(\Bitrix\Main\Entity\Query $query, array $gridFilter): bool
	{
		$query->addFilter('=GROUP_ID', $this->arParams['GROUP_ID']);

		$filter = $this->getFilter($gridFilter);

		if (
			isset($filter['=INITIATED_BY_TYPE'])
			&& (
				empty($filter['=ROLE'])
				|| !empty(array_diff($filter['=ROLE'], [ UserToGroupTable::ROLE_REQUEST ]))
				|| !empty(array_diff([ UserToGroupTable::ROLE_REQUEST ], $filter['=ROLE']))
			)
		)
		{
			unset($filter['=INITIATED_BY_TYPE']);
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

			if ($fieldName === '=INITIATED_BY_TYPE')
			{
				$query->addFilter('=INITIATED_BY_TYPE', $value);

				if ($value === UserToGroupTable::INITIATED_BY_GROUP)
				{
					$initiatedByUser = true;

					if (
						!$this->arResult['CurrentUserPerms']['UserCanProcessRequestsIn']
						&& !Helper\Workgroup::isCurrentUserModuleAdmin()
					)
					{
						$query->addFilter('=INITIATED_BY_USER_ID', Helper\Workgroup::getCurrentUserId());
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
			&& !$this->arResult['CurrentUserPerms']['UserCanProcessRequestsIn']
			&& !Helper\Workgroup::isCurrentUserModuleAdmin()
		)
		{
			$query->addFilter(null, [
				'LOGIC' => 'OR',
				[
					'=ROLE' => UserToGroupTable::ROLE_REQUEST,
					'=INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
					'=INITIATED_BY_USER_ID' => Helper\Workgroup::getCurrentUserId(),
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

		return true;
	}

	private function addQuerySelect(\Bitrix\Main\Entity\Query $query, \Bitrix\Main\Grid\Options $gridOptions): void
	{
		$selectFields = $this->getSelect($gridOptions);
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

		if (in_array($fieldName, $this->fieldsList))
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
		$result = [
		];

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

		$integerFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'ID',
				'FIELD_NAME' => 'ID',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['ID'],
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
				'VALUE' => $gridFilter['NAME'] . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'LAST_NAME',
				'FIELD_NAME' => 'LAST_NAME',
				'OPERATION' => '%=',
				'VALUE' => $gridFilter['LAST_NAME'] . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'EMAIL',
				'FIELD_NAME' => 'EMAIL',
				'OPERATION' => '%=',
				'VALUE' => $gridFilter['EMAIL'] . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'ROLE',
				'FIELD_NAME' => 'ROLE',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['ROLE'],
			],
			[
				'FILTER_FIELD_NAME' => 'INITIATED_BY_TYPE',
				'FIELD_NAME' => 'INITIATED_BY_TYPE',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['INITIATED_BY_TYPE'],
			],
			[
				'FILTER_FIELD_NAME' => 'AUTO_MEMBER',
				'FIELD_NAME' => 'AUTO_MEMBER',
				'OPERATION' => '=',
				'VALUE' => $gridFilter['AUTO_MEMBER'],
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
				&& $field['VALUE'] <> ''
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

	protected function getSelect(\Bitrix\Main\Grid\Options $gridOptions): array
	{
		$result = [ 'USER.ID', 'ROLE', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID', 'AUTO_MEMBER' ];
		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$result[] = 'USER.USER_TYPE';
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
					break;
				case 'PHOTO':
					$result[] = 'USER.PERSONAL_PHOTO';
					$result[] = 'USER.PERSONAL_GENDER';
					break;
				case 'DEPARTMENT':
					$result[] = 'USER.UF_DEPARTMENT';
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


		return $params;
	}

	protected function prepareActions(): void
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

		$res = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'@USER_ID' => $idList,
			],
			'select' => [ 'ID', 'ROLE', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID', 'AUTO_MEMBER' ],
		]);
		$relations = $res->fetchCollection();

		foreach ($relations as $relation)
		{
			if ($this->actionData['NAME'] === 'delete')
			{
				$canRemoveModerator = Helper\Workgroup::canRemoveModerator([
					'relation' => $relation,
					'groupId' => $groupId,
				]);

				$canDeleteOutgoingRequest = Helper\Workgroup::canDeleteOutgoingRequest([
					'relation' => $relation,
					'groupId' => $groupId,
				]);

				$canExclude = Helper\Workgroup::canExclude([
					'relation' => $relation,
					'groupId' => $groupId,
				]);

				if (
					!$canDeleteOutgoingRequest
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
				\CSocNetUserToGroup::TransferModerator2Member(Helper\Workgroup::getCurrentUserId(), $groupId, $removeModeratorRelationIdList, Helper\Workgroup::isCurrentUserModuleAdmin());
			}
		}

	}

	protected function prepareData(): array
	{
		$result = [
			'GROUP_ACTION_MESSAGES' => [],
			'FIELDS_LIST' => $this->fieldsList,
		];

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
		$result['FILTER_PRESETS'] = $this->getFilterPresets($entityFilter, $result);

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

		$result['PROCESS_EXTRANET'] = (
			(
				isset($gridFilter['EXTRANET'])
				&& in_array($gridFilter['EXTRANET'], ['Y', 'N'])
			)
			|| !empty($gridFilter['DEPARTMENT'])
			|| $gridFilter['PRESET_ID'] === 'company'
				? 'N'
				: 'Y'
		);

		$query = new \Bitrix\Main\Entity\Query(UserToGroupTable::getEntity());
		$this->addQueryOrder($query, $gridOptions);
		if (!$this->addQueryFilter($query, $gridFilter))
		{
			return $result;
		}
		$this->addQuerySelect($query, $gridOptions);
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

		$rowsList = [];

		foreach ($records as $record)
		{
			$row['ROW_FIELDS'] = $record;

			$row['CAN_EDIT'] = false;
			$row['CAN_DELETE'] = false;

			$rowsList[] = [
				'id' => $record->getUser()->getId(),
				'data' => $row,
				'columns' => [],
				'editable' => true,
				'actions' => self::getActions([
					'RELATION' => $row['ROW_FIELDS'],
					'PATH_TO_USER' => $this->arParams['PATH_TO_USER'],
					'GROUP_ID' => $this->arParams['GROUP_ID'],
				]),
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

		$result['ROWS'] = $rowsList;
		$result['ROWS_COUNT'] = $res->getCount();

		$nav->setRecordCount($result['ROWS_COUNT']);
		$result['NAV_OBJECT'] = $nav;

		$result['GRID_COLUMNS'] = $gridColumns;

		return $result;
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

		$gridFilter = $filterOptions->getFilter($filter);
		if (
			empty($gridFilter)
			&& !empty($this->arParams['MODE'])
		)
		{
			switch (strtoupper($this->arParams['MODE']))
			{
				case 'MEMBERS':
					$gridFilter = [
						'ROLE' => UserToGroupTable::getRolesMember(),
					];
					break;
				case 'MODERATORS':
					$gridFilter = [
						'ROLE' => [ UserToGroupTable::ROLE_MODERATOR ],
					];
					break;
				case 'REQUESTS_IN':
					$gridFilter = [
						'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
						'FIRED' => 'N',
						'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_USER,
					];
					break;
				case 'REQUESTS_OUT':
					$gridFilter = [
						'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
						'FIRED' => 'N',
						'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
					];
					break;
				default:
			}
		}

		return $gridFilter;
	}

	protected static function extranetSite(): bool
	{
		static $result = null;

		if ($result === null)
		{
			$result = (
				Loader::includeModule('extranet')
				&& \CExtranet::isExtranetSite()
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

		$this->setTitle([
			'PROJECT' => $this->arResult['GROUP']['PROJECT'],
		]);

		$this->includeComponentTemplate();
	}

	protected function setTitle(array $params = []): void
	{
		global $APPLICATION;

		$project = (isset($params['PROJECT']) && $params['PROJECT'] === 'Y');

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			switch (strtoupper($this->arParams['MODE']))
			{
				case 'MEMBERS':
					$shortTitle = ($project ? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MEMBERS_PROJECT') : Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MEMBERS'));
					break;
				case 'MODERATORS':
					$shortTitle = ($project ? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MODERATORS_PROJECT') : Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_MODERATORS'));
					break;
				case 'REQUESTS_IN':
					$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_IN');
					break;
				case 'REQUESTS_OUT':
					$shortTitle = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TITLE_REQUESTS_OUT');
					break;
				default:
					$shortTitle = '';
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
		return \CSocNetGroup::getById($this->arParams['GROUP_ID']);
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

		$departmentsList = CIntranetUtils::getDepartmentsData($params['groupFields']['UF_SG_DEPT']);
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

	public function actAction(string $action = '', array $fields = []): array
	{
		if (!$this->checkAction($action))
		{
			throw new \Bitrix\Main\NotSupportedException(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ERROR_ACTION_NOT_SUPPORTED'));
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
				case self::AJAX_ACTION_EXCLUDE:
					$result = Helper\Workgroup::exclude([
						'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
						'userId' => (int)($fields['userId'] ?? 0),
					]);
					break;
				default:
					$result = false;
			}

		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return [
			'success' => $result,
		];
	}

	protected function checkAction(string $action = ''): bool
	{
		return in_array($action, [
			self::AJAX_ACTION_SET_OWNER,
			self::AJAX_ACTION_SET_MODERATOR,
			self::AJAX_ACTION_REMOVE_MODERATOR,
			self::AJAX_ACTION_EXCLUDE,
			self::AJAX_ACTION_DELETE_OUTGOING_REQUEST,
		], true);
	}

	public function disconnectDepartmentAction(array $fields = []): array
	{
		try
		{
			$result = Helper\Workgroup::disconnectDepartment([
				'groupId' => (int)($this->arParams['GROUP_ID'] ?? 0),
				'departmentId' => (int)($fields['id'] ?? 0),
			]);
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return [
			'success' => $result,
		];
	}

}
