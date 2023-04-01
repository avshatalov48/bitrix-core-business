<?php

namespace Bitrix\Calendar\Watcher\Membership\Handler;

use Bitrix\Main\Loader;

class Department extends Handler
{
	public const DO_UPDATE_ALL_EVENTS = true;

	/**
	 * @param $arFields
	 * @return void
	 */
	public static function onAfterUserAdd($arFields)
	{
		if (!($arFields['RESULT'] ?? null) || !Loader::includeModule("iblock"))
		{
			return;
		}

		$departmentIBlockId = (int)\Bitrix\Main\Config\Option::get('intranet', 'iblock_structure', 0);
		if ($departmentIBlockId <= 0)
		{
			return;
		}

		if(!empty($arFields['UF_DEPARTMENT']))
		{
			$arFieldsDepartments =
				is_array($arFields['UF_DEPARTMENT']) ? $arFields['UF_DEPARTMENT'] : [$arFields['UF_DEPARTMENT']]
			;

			self::$storedData = self::getAffectedDepartments($arFieldsDepartments, $departmentIBlockId);
		}

		self::sendBatchOfMessagesToQueue(self::prepareBatchOfMessagesData());
	}

	/**
	 * @param $arFields
	 * @return void
	 */
	public static function onBeforeUserUpdate($arFields): void
	{
		if (
			(!isset($arFields['UF_DEPARTMENT']) && !isset($arFields['ACTIVE']))
			|| empty($arFields['ID'])
			|| !Loader::includeModule("iblock")
		)
		{
			return;
		}

		$departmentIBlockId = (int)\Bitrix\Main\Config\Option::get('intranet', 'iblock_structure', 0);
		if ($departmentIBlockId <= 0)
		{
			return;
		}

		$user = \CUser::GetByID($arFields['ID'])->Fetch();

		if (!isset($user['UF_DEPARTMENT']))
		{
			$oldDepartments = [];
		}
		elseif (is_array($user['UF_DEPARTMENT']))
		{
			$oldDepartments = $user['UF_DEPARTMENT'];
		}
		else
		{
			$oldDepartments = [$user['UF_DEPARTMENT']];
		}

		if (is_array($arFields['UF_DEPARTMENT']))
		{
			$newDepartments = $arFields['UF_DEPARTMENT'];
		}
		else
		{
			$newDepartments = [$arFields['UF_DEPARTMENT']];
		}

		if (!self::isUserDepartmentsUpdated($oldDepartments, $newDepartments))
		{
			return;
		}

		$departments = array_unique(
			array_merge(
				$newDepartments,
				$oldDepartments,
			)
		);

		self::$storedData = self::getAffectedDepartments($departments, $departmentIBlockId);
	}

	private static function isUserDepartmentsUpdated(array $oldValue, array $newValue): bool
	{
		return self::convertArrayValuesToInteger($oldValue) !== self::convertArrayValuesToInteger($newValue);
	}

	private static function convertArrayValuesToInteger(array $array): array
	{
		return array_map(static function($value){
			return (int)$value;
		}, $array);
	}

	/**
	 * @param $arFields
	 * @return void
	 */
	public static function onAfterUserUpdate($arFields): void
	{
		if (empty(self::$storedData) || !($arFields['RESULT'] ?? null)  ||!Loader::includeModule("iblock"))
		{
			return;
		}

		self::sendBatchOfMessagesToQueue(self::prepareBatchOfMessagesData());
	}

	/**
	 * @param int $userId
	 * @return void
	 */
	public static function onBeforeUserDelete(int $userId): void
	{
		if ($userId <= 0 || !\Bitrix\Main\Loader::includeModule("iblock"))
		{
			return;
		}

		$user = \CUser::GetByID($userId)->Fetch();

		$departmentIBlockId = (int)\Bitrix\Main\Config\Option::get('intranet', 'iblock_structure', 0);
		if ($departmentIBlockId > 0 && !empty($user['UF_DEPARTMENT']))
		{
			self::$storedData = self::getAffectedDepartments($user['UF_DEPARTMENT'], $departmentIBlockId);
		}
	}

	/**
	 * @param $userId
	 * @return void
	 */
	public static function onAfterUserDelete($userId): void
	{
		if (!Loader::includeModule("iblock") || \CUser::GetByID($userId)->Fetch())
		{
			return;
		}

		self::sendBatchOfMessagesToQueue(self::prepareBatchOfMessagesData());
	}

	/**
	 * @param $arFields
	 * @return void
	 */
	public static function OnAfterIBlockSectionAdd($arFields): void
	{
		if (
			empty($arFields['IBLOCK_ID'])
			|| empty($arFields['IBLOCK_SECTION_ID'])
			|| !Loader::includeModule("iblock")
			|| !self::isDepartmentIBlock($arFields['IBLOCK_ID'])
		)
		{
			return;
		}

		self::$storedData = self::getAffectedDepartments($arFields['IBLOCK_SECTION_ID'], $arFields['IBLOCK_ID']);

		self::sendBatchOfMessagesToQueue(self::prepareBatchOfMessagesData());
	}

	/**
	 * @param $arFields
	 * @return void
	 */
	public static function onBeforeIBlockSectionUpdate($arFields): void
	{
		if (
			empty($arFields['ID'])
			||empty($arFields['IBLOCK_ID'])
			|| empty($arFields['IBLOCK_SECTION_ID'])
			|| !Loader::includeModule("iblock")
			|| !self::isDepartmentIBlock($arFields['IBLOCK_ID'])
		)
		{
			return;
		}

		$updatingDepartment = \CIBlockSection::GetByID($arFields['ID'])->Fetch();
		if (($arFields['IBLOCK_SECTION_ID']) === $updatingDepartment['IBLOCK_SECTION_ID'])
		{
			return;
		}

		$departments = array_unique(
			array_merge(
				[$arFields['IBLOCK_SECTION_ID']],
				[$updatingDepartment['IBLOCK_SECTION_ID']],
			)
		);

		self::$storedData = self::getAffectedDepartments($departments, $arFields['IBLOCK_ID']);
	}

	/**
	 * @param $arFields
	 * @return void
	 */
	public static function onAfterIBlockSectionUpdate($arFields): void
	{
		if (empty(self::$storedData) || !($arFields['RESULT'] ?? null) || !Loader::includeModule("iblock"))
		{
			return;
		}

		self::sendBatchOfMessagesToQueue(self::prepareBatchOfMessagesData());
	}

	/**
	 * @param $departments
	 * @param int $departmentIBlockId
	 * @return array
	 */
	private static function getAffectedDepartments($departments, int $departmentIBlockId): array
	{
		if (!is_array($departments))
		{
			$departments = [$departments];
		}

		$affectedDepartments = [];

		foreach ($departments as $departmentId)
		{
			$result = \CIBlockSection::GetNavChain($departmentIBlockId, $departmentId, ['ID'], true);
			foreach ($result as $affectedDepartment)
			{
				if(!in_array($affectedDepartment, $affectedDepartments))
				{
					$affectedDepartments[] = $affectedDepartment;
				}
			}
		}

		return $affectedDepartments;
	}

	/**
	 * @return array
	 */
	private static function prepareBatchOfMessagesData(): array
	{
		$data = [];
		foreach (self::$storedData as $affectedDepartment)
		{
			if (!empty($affectedDepartment['ID']))
			{
				$data[] = [
					'entityType' => self::DEPARTMENT_TYPE,
					'entityId' => $affectedDepartment['ID'],
				];
			}
		}

		if(self::DO_UPDATE_ALL_EVENTS)
		{
			$data[] = [
				'entityType' => self::ALL_USERS_TYPE,
			];
		}

		return $data;
	}

	/**
	 * @param int $IBlockId
	 * @return bool
	 */
	private static function isDepartmentIBlock(int $IBlockId): bool
	{
		$departmentIBlockId = (int)\Bitrix\Main\Config\Option::get('intranet', 'iblock_structure', 0);

		return $departmentIBlockId > 0 && $departmentIBlockId === $IBlockId;
	}
}