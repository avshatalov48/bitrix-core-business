<?php
namespace Bitrix\Bizproc\Service;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Compatibility\Utils\DepartmentBackwardAccessCode;

class User extends \CBPRuntimeService
{
	protected const DEPARTMENT_MODULE_ID = 'intranet';
	protected const DEPARTMENT_OPTION_NAME = 'iblock_structure';

	protected array $users = [];

	public function getUserDepartments(int $userId): array
	{
		// it's OK for now to use old api
		return $this->getUserDepartmentsOld($userId);
	}

	public function getUserInfo(int $userId): ?array
	{
		if ($userId <= 0)
		{
			return null;
		}

		if (isset($this->users[$userId]))
		{
			return $this->users[$userId];
		}

		$userFields = $this->getUserUserFields();

		return $this->loadUser($userId, $userFields);
	}

	public function getUserExtendedFields(): array
	{
		$fields = $this->getUserUserFields();

		if ($this->canUseIntranet())
		{
			$fields['UF_DEPARTMENT'] = [
				'Name' => Loc::getMessage('BP_SERVICE_USER_DEPARTMENT'),
				'Type' => 'int',
				'Multiple' => true,
			];

			$fields['UF_DEPARTMENT_PRINTABLE'] = [
				'Name' => Loc::getMessage('BP_SERVICE_USER_DEPARTMENT_PRINTABLE'),
				'Type' => 'string',
				'Multiple' => true,
			];

			$fields['IS_ABSENT'] = [
				'Name' => Loc::getMessage('BP_SERVICE_USER_IS_ABSENT'),
				'Type' => 'bool',
			];

			if ($this->canUseIblockApi())
			{
				$fields['UF_HEAD'] = [
					'Name' => Loc::getMessage('BP_SERVICE_USER_HEAD'),
					'Type' => 'user',
					'Multiple' => true,
				];
			}
		}

		if ($this->canUseTimeman())
		{
			$fields['TIMEMAN_STATUS'] = [
				'Name' => Loc::getMessage('BP_SERVICE_USER_TIMEMAN_STATUS'),
				'Type' => 'select',
				'Options' => [
					'EXPIRED' => Loc::getMessage('BP_SERVICE_USER_TIMEMAN_STATUS_EXPIRED'),
					'OPENED' => Loc::getMessage('BP_SERVICE_USER_TIMEMAN_STATUS_OPENED'),
					'PAUSED' => Loc::getMessage('BP_SERVICE_USER_TIMEMAN_STATUS_PAUSED'),
					'CLOSED' => Loc::getMessage('BP_SERVICE_USER_TIMEMAN_STATUS_CLOSED'),
				],
			];
		}

		return $fields;
	}

	public function getUserBaseFields(): array
	{
		return [
			'ACTIVE' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_ACTIVE'),
				'Type' => 'bool',
			],
			'EMAIL' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_EMAIL'),
				'Type' => 'string',
			],
			'WORK_PHONE' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_WORK_PHONE'),
				'Type' => 'string',
			],
			'PERSONAL_MOBILE' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_PERSONAL_MOBILE'),
				'Type' => 'string',
			],
			'UF_PHONE_INNER' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_PHONE_INNER'),
				'Type' => 'string',
			],
			'LOGIN' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_LOGIN'),
				'Type' => 'string',
			],
			'NAME' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_NAME'),
				'Type' => 'string',
			],
			'LAST_NAME' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_LAST_NAME'),
				'Type' => 'string',
			],
			'SECOND_NAME' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_SECOND_NAME'),
				'Type' => 'string',
			],
			'WORK_POSITION' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_WORK_POSITION'),
				'Type' => 'string',
			],
			'PERSONAL_BIRTHDAY' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_PERSONAL_BIRTHDAY'),
				'Type' => 'date',
			],
			'PERSONAL_WWW' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_PERSONAL_WWW'),
				'Type' => 'string',
			],
			'PERSONAL_CITY' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_PERSONAL_CITY'),
				'Type' => 'string',
			],
			'UF_DEPARTMENT' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_DEPARTMENT'),
				'Type' => 'int',
				'Multiple' => true,
			],
			'UF_SKYPE' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_SKYPE'),
				'Type' => 'string',
			],
			'UF_TWITTER' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_TWITTER'),
				'Type' => 'string',
			],
			'UF_FACEBOOK' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_FACEBOOK'),
				'Type' => 'string',
			],
			'UF_LINKEDIN' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_LINKEDIN'),
				'Type' => 'string',
			],
			'UF_XING' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_XING'),
				'Type' => 'string',
			],
			'UF_WEB_SITES' => [
				'Name' => Loc::getMessage('BP_SERVICE_USER_UF_WEB_SITES'),
				'Type' => 'string',
			],
		];
	}

	public function getUserDepartmentChains(int $userId): array
	{
		$chains = [];

		foreach ($this->getUserDepartments($userId) as $departmentId)
		{
			$chains[] = $this->getDepartmentChain($departmentId);
		}

		return $chains;
	}

	public function getDepartmentChain(int $departmentId): array
	{
		if (!$this->canUseHumanResources())
		{
			return $this->getDepartmentChainOld($departmentId);
		}

		$chain = [];
		$node = $this->getDepartmentNode($departmentId);

		if ($node)
		{
			$nodes = Container::getNodeRepository()->getParentOf(
				$node,
				\Bitrix\HumanResources\Enum\DepthLevel::FULL,
			);
			foreach ($nodes as $parent)
			{
				$chain[] = DepartmentBackwardAccessCode::extractIdFromCode($parent->accessCode);
			}
		}

		return $chain;
	}

	public function getUserHeads(int $userId): array
	{
		$heads = [];
		$userDepartments = $this->getUserDepartmentChains($userId);

		foreach ($userDepartments as $chain)
		{
			foreach ($chain as $deptId)
			{
				$departmentHead = $this->getDepartmentHead($deptId);

				if (!$departmentHead || $departmentHead === $userId)
				{
					continue;
				}

				$heads[] = $departmentHead;
				break;
			}
		}

		return array_unique($heads);
	}

	public function getDepartmentHead(int $departmentId): ?int
	{
		if (!$this->canUseHumanResources())
		{
			return $this->getDepartmentHeadOld($departmentId);
		}

		$node = $this->getDepartmentNode($departmentId);
		if (!$node)
		{
			return null;
		}

		$head = current(Container::getNodeMemberService()->getDefaultHeadRoleEmployees($node->id)->getItemMap());

		return $head->entityId ?? null;
	}

	public function getUserSchedule(int $userId): Sub\UserSchedule
	{
		return new Sub\UserSchedule($userId);
	}

	protected function getDepartmentIblockId(): int
	{
		return (int) Main\Config\Option::get(
			static::DEPARTMENT_MODULE_ID,
			static::DEPARTMENT_OPTION_NAME
		);
	}

	private function canUseHumanResources(): bool
	{
		return Main\Loader::includeModule('humanresources');
	}

	private function canUseIblockApi()
	{
		return Main\Loader::includeModule('iblock');
	}

	private function canUseIntranet()
	{
		return Main\Loader::includeModule('intranet');
	}

	private function canUseTimeman()
	{
		return \CBPHelper::isWorkTimeAvailable();
	}

	public function getUserUserFields(): array
	{
		static $fields;

		if (isset($fields))
		{
			return $fields;
		}

		$fields = [];

		$userFields = Main\UserFieldTable::getList([
			'select' => array_merge(
				['ID', 'FIELD_NAME', 'USER_TYPE_ID', 'MULTIPLE'],
				Main\UserFieldTable::getLabelsSelect()
			),
			'filter' => [
				'=ENTITY_ID' => 'USER',
				'%=FIELD_NAME' => 'UF_USR_%',
			],
			'runtime' => [
				Main\UserFieldTable::getLabelsReference('LABELS', \LANGUAGE_ID),
			],
		])->fetchAll();


		foreach ($userFields as $field)
		{
			$fieldName = $field['FIELD_NAME'];
			$fieldType = FieldType::convertUfType($field['USER_TYPE_ID']) ?? "UF:{$field['USER_TYPE_ID']}";

			$name = empty($field['LIST_COLUMN_LABEL']) ? $field['FIELD_NAME'] : $field['LIST_COLUMN_LABEL'];

			$fields[$fieldName] = [
				'Name' => $name,
				'Type' => $fieldType,
				'Multiple' => $field['MULTIPLE'] === 'Y',
			];

			if ($fields[$fieldName]['Type'] === 'select')
			{
				$fieldData = Main\UserFieldTable::getFieldData($field['ID']);
				$fields[$fieldName]['Options'] = array_combine(
					array_column($fieldData['ENUM'], 'XML_ID'),
					array_column($fieldData['ENUM'], 'VALUE'),
				);
				$fields[$fieldName]['Settings'] = ['ENUM' => $fieldData['ENUM']];
			}
		}

		return $fields;
	}

	public function extractUsersFromDepartment(int $departmentId, bool $recursive = false): ?array
	{
		if (!$this->canUseHumanResources())
		{
			return $this->extractUsersFromDepartmentOld($departmentId, $recursive);
		}

		$node = $this->getDepartmentNode($departmentId);
		if (!$node)
		{
			return null;
		}

		$employeesCollection = Container::getNodeMemberService()->getPagedEmployees($node->id, $recursive);

		return array_unique(
			array_map(static fn(NodeMember $item) => $item->entityId, [...$employeesCollection->getItemMap()])
		);
	}

	private function convertValues(array &$values, array $userFields): void
	{
		foreach ($userFields as $id => $field)
		{
			if ($field['Type'] === 'bool')
			{
				$values[$id] = \CBPHelper::getBool($values[$id]) ? 'Y' : 'N';
			}
			elseif ($field['Type'] === 'select')
			{
				$values[$id] = $this->convertSelectValue($values[$id], $field);
			}
		}

		if (!empty($values['UF_DEPARTMENT']))
		{
			$values['UF_DEPARTMENT_PRINTABLE'] = $this->loadDepartmentNames($values['UF_DEPARTMENT']);
		}
	}

	private function convertSelectValue($value, $field)
	{
		$enumIds = array_combine(
			array_column($field['Settings']['ENUM'], 'XML_ID'),
			array_column($field['Settings']['ENUM'], 'ID'),
		);

		if (is_array($value))
		{
			$xmlIds = [];
			foreach ($value as $val)
			{
				$xmlIds[] = array_search($val, $enumIds);
			}

			return array_filter($xmlIds, fn($id) => $id !== false);
		}

		$xmlId = array_search($value, $enumIds);

		return $xmlId !== false ? $xmlId : '';
	}

	private function convertUserValue($value): array
	{
		$users = [];

		$value = is_array($value) ? $value : [$value];
		foreach ($value as $userId)
		{
			if (is_int($userId))
			{
				$users[] = 'user_' . $userId;
			}
		}

		return $users;
	}

	private function loadUser(int $userId, array $fields): ?array
	{
		$dbUsers = \CUser::GetList(
			'id',
			'asc',
			['ID_EQUAL_EXACT' => $userId],
			[
				'FIELDS' => [
					'ID',
					'EMAIL',
					'WORK_PHONE',
					'PERSONAL_MOBILE',
					'PERSONAL_BIRTHDAY',
					'LOGIN',
					'ACTIVE',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'WORK_POSITION',
					'PERSONAL_WWW',
					'PERSONAL_CITY',
				],
				'SELECT' => [
					'UF_DEPARTMENT',
					'UF_SKYPE',
					'UF_TWITTER',
					'UF_FACEBOOK',
					'UF_LINKEDIN',
					'UF_XING',
					'UF_WEB_SITES',
					'UF_PHONE_INNER',
					...array_keys($fields),
				],
			]
		);

		$user = $dbUsers->fetch();

		if (is_array($user))
		{
			$this->convertValues($user, $fields);
			$this->users[$userId] = $user;

			$schedule = $this->getUserSchedule($userId);
			$this->users[$userId]['IS_ABSENT'] = $schedule->isAbsent();
			$this->users[$userId]['TIMEMAN_STATUS'] = $schedule->getWorkDayStatus();
			$this->users[$userId]['UF_HEAD'] = $this->convertUserValue($this->getUserHeads($userId));

			return $this->users[$userId];
		}

		return null;
	}

	private function loadDepartmentNames(array $ids): array
	{
		if (!$this->canUseHumanResources())
		{
			return $this->loadDepartmentNamesOld($ids);
		}

		$codes = array_map(
			static fn($id) => DepartmentBackwardAccessCode::makeById($id),
			$ids
		);

		$collection = Container::getNodeRepository()->findAllByAccessCodes($codes);
		$names = [];
		foreach ($collection as $node)
		{
			$names[DepartmentBackwardAccessCode::extractIdFromCode($node->accessCode)] = $node->name;
		}

		return array_values(array_filter(
			array_map(
				static fn($id) => $names[$id] ?? null,
				$ids
			)
		));
	}

	private function getDepartmentNode(int $departmentId): ?\Bitrix\HumanResources\Item\Node
	{
		return Container::getNodeRepository()->getByAccessCode(DepartmentBackwardAccessCode::makeById($departmentId));
	}

	/**
	 * @deprecated
	 * @param int $departmentId
	 * @param bool $recursive
	 * @return array|null
	 */
	private function extractUsersFromDepartmentOld(int $departmentId, bool $recursive = false): ?array
	{
		if (!$this->canUseIntranet() || !$this->canUseIblockApi())
		{
			return null;
		}

		$iblockId = $this->getDepartmentIblockId();
		$departmentIds = [$departmentId];

		if ($recursive)
		{
			$iterator = \CIBlockSection::GetList(
				['ID' => 'ASC'],
				['=IBLOCK_ID' => $iblockId, 'ID' => $departmentId],
				false,
				['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL']
			);
			$section = $iterator->fetch();
			$filter = [
				'=IBLOCK_ID' => $iblockId,
				">LEFT_MARGIN" => $section["LEFT_MARGIN"],
				"<RIGHT_MARGIN" => $section["RIGHT_MARGIN"],
				">DEPTH_LEVEL" => $section['DEPTH_LEVEL'],
			];
			$iterator = \CIBlockSection::GetList(["left_margin" => "asc"], $filter, false, ['ID']);
			while ($section = $iterator->fetch())
			{
				$departmentIds[] = $section['ID'];
			}
			unset($iterator, $section, $filter);
		}
		$result = [];
		$iterator = \CUser::GetList("departmentId", "asc",
			['ACTIVE' => 'Y', 'UF_DEPARTMENT' => $departmentIds],
			['FIELDS' => ['ID']]
		);
		while ($user = $iterator->fetch())
		{
			$result[] = $user['ID'];
		}

		return $result;
	}

	/**
	 * @deprecated
	 * @param array $ids
	 * @return array
	 * @throws Main\LoaderException
	 */
	private function loadDepartmentNamesOld(array $ids): array
	{
		$names = [];

		if (!Main\Loader::includeModule('intranet') || !Main\Loader::includeModule('iblock'))
		{
			return $names;
		}

		$iblockId = $this->getDepartmentIblockId();

		$iterator = \CIBlockSection::GetList(
			['ID' => 'ASC'],
			[
				'=IBLOCK_ID' => $iblockId,
				'ID' => $ids,
				'CHECK_PERMISSIONS' => 'N',
			],
			false,
			['ID', 'NAME']
		);

		while ($row = $iterator->fetch())
		{
			$names[$row['ID']] = $row['NAME'];
		}

		return array_values(array_filter(
			array_map(
				fn($id) => $names[$id] ?? null,
				$ids
			)
		));
	}

	/**
	 * @deprecated
	 * @param int $userId
	 * @return array
	 */
	private function getUserDepartmentsOld(int $userId): array
	{
		if (isset($this->users[$userId]['UF_DEPARTMENT']))
		{
			return is_array($this->users[$userId]['UF_DEPARTMENT']) ? $this->users[$userId]['UF_DEPARTMENT'] : [];
		}

		$departments = [];
		$result = \CUser::getList(
			'id', 'asc',
			['ID_EQUAL_EXACT' => $userId],
			['FIELDS' => ['ID'], 'SELECT' => ['UF_DEPARTMENT']]
		);

		if ($user = $result->fetch())
		{
			if (isset($user['UF_DEPARTMENT']))
			{
				$user['UF_DEPARTMENT'] = (array) $user['UF_DEPARTMENT'];
				foreach ($user['UF_DEPARTMENT'] as $dpt)
				{
					$departments[] = (int) $dpt;
				}
			}
		}

		return $departments;
	}

	/**
	 * @deprecated
	 * @param int $departmentId
	 * @return array
	 */
	private function getDepartmentChainOld(int $departmentId): array
	{
		$chain = [];

		if (!$this->canUseIblockApi())
		{
			return $chain;
		}

		$departmentIblockId = $this->getDepartmentIblockId();
		$chain = \CIBlockSection::getNavChain($departmentIblockId, $departmentId, ['ID'], true);

		$chain = array_map(
			static fn($value) => (int)$value['ID'],
			$chain
		);

		return array_reverse($chain);
	}

	/**
	 * @deprecated
	 * @param int $departmentId
	 * @return int|null
	 */
	private function getDepartmentHeadOld(int $departmentId): ?int
	{
		if (!$this->canUseIblockApi())
		{
			return null;
		}

		$departmentIblockId = $this->getDepartmentIblockId();
		$sectionResult = \CIBlockSection::GetList(
			[],
			['IBLOCK_ID' => $departmentIblockId, 'ID' => $departmentId],
			false,
			['ID', 'UF_HEAD']
		);
		$section = $sectionResult->fetch();

		return $section ? (int) $section['UF_HEAD'] : null;
	}
}
