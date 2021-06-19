<?php
namespace Bitrix\Bizproc\Service;

use Bitrix\Main;

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
		$dbUsers = \CUser::GetList(
			'id', 'asc',
			['ID_EQUAL_EXACT' => $userId],
			[
				'FIELDS' => ['ID', 'EMAIL'],
				'SELECT' => [
					'EMAIL',
					'UF_SKYPE',
					'UF_TWITTER',
					'UF_FACEBOOK',
					'UF_LINKEDIN',
					'UF_XING',
					'UF_WEB_SITES',
					'UF_PHONE_INNER',
				]
			]
		);

		$info = is_object($dbUsers) ? $dbUsers->fetch() : null;
		return is_array($info) ? $info : null;
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
}