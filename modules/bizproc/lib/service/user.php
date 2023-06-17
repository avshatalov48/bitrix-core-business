<?php
namespace Bitrix\Bizproc\Service;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class User extends \CBPRuntimeService
{
	protected const DEPARTMENT_MODULE_ID = 'intranet';
	protected const DEPARTMENT_OPTION_NAME = 'iblock_structure';

	public function getUserDepartments(int $userId): array
	{
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

	public function getUserInfo(int $userId): ?array
	{
		if ($userId <= 0)
		{
			return null;
		}

		$userFields = $this->getUserUserFields();
		$user = $this->loadUser($userId, $userFields);

		if (!$user)
		{
			return null;
		}

		$this->convertValues($user, $userFields);

		$schedule = $this->getUserSchedule($userId);
		$user['IS_ABSENT'] = $schedule->isAbsent();
		$user['TIMEMAN_STATUS'] = $schedule->getWorkDayStatus();
		$user['UF_HEAD'] = $this->convertUserValue($this->getUserHeads($userId));

		return $user;
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
		$chain = [];

		if (!$this->canUseIblockApi())
		{
			return $chain;
		}

		$departmentIblockId = $this->getDepartmentIblockId();
		$pathResult = \CIBlockSection::getNavChain($departmentIblockId, $departmentId);
		while ($path = $pathResult->fetch())
		{
			$chain[] = (int) $path['ID'];
		}

		return array_reverse($chain);
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

		$userFieldIds = Main\UserFieldTable::getList([
			'select' => ['ID'],
			'filter' => [
				'ENTITY_ID' => 'USER',
				'%=FIELD_NAME' => 'UF_USR_%',
			],
		])->fetchAll();

		foreach ($userFieldIds as $fieldId)
		{
			$field = Main\UserFieldTable::getFieldData($fieldId['ID']);
			$fieldName = $field['FIELD_NAME'];
			$fieldType = FieldType::convertUfType($field['USER_TYPE_ID']) ?? "UF:{$field['USER_TYPE_ID']}";

			$name = in_array(\LANGUAGE_ID, $field['LANGUAGE_ID'])
				? $field['LIST_COLUMN_LABEL'][\LANGUAGE_ID]
				: $field['FIELD_NAME']
			;

			$fields[$fieldName] = [
				'Name' => $name,
				'Type' => $fieldType,
				'Multiple' => $field['MULTIPLE'] === 'Y',
			];

			if ($fields[$fieldName]['Type'] === 'select')
			{
				$fields[$fieldName]['Options'] = array_combine(
					array_column($field['ENUM'], 'XML_ID'),
					array_column($field['ENUM'], 'VALUE'),
				);
				$fields[$fieldName]['Settings'] = ['ENUM' => $field['ENUM']];
			}
		}

		return $fields;
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
					...array_keys($fields)
				]
			]
		);

		$user = $dbUsers->fetch();

		return is_array($user) ? $user : null;
	}

	private function loadDepartmentNames(array $ids): array
	{
		$names = [];

		if (!Main\Loader::includeModule('intranet') || !Main\Loader::includeModule('iblock'))
		{
			return $names;
		}

		$iblockId = Main\Config\Option::get('intranet', 'iblock_structure');

		$iterator = \CIBlockSection::GetList(
			['ID' => 'ASC'],
			[
				'=IBLOCK_ID' => $iblockId,
				'ID' => $ids
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
}
