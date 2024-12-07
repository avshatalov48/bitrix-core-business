<?php
namespace Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Integration\Bitrix24\FeatureDictionary;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Main\LoaderException;

class PermissionManager
{
	/**
	 * @deprecated
	 * @param $operation
	 *
	 * @return boolean
	 */
	public static function checkTypePermission($operation): bool
	{
		$userId = \CCalendar::GetUserId();

		if(!\CCalendarType::CanDo($operation, Manager::TYPE, $userId))
		{
			return false;
		}

		return true;
	}

	/**
	 * @deprecated
	 * @param $operation
	 * @param $sectionId
	 *
	 * @return boolean
	 */
	public static function checkSectionPermission($operation, $sectionId): bool
	{
		$userId = \CCalendar::GetUserId();

		if(!\CCalendarSect::CanDo($operation, $sectionId, $userId))
		{
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isLocationFeatureEnabled(): bool
	{
		return Bitrix24Manager::isFeatureEnabled(FeatureDictionary::CALENDAR_LOCATION);
	}

	/**
	 * @return array
	 */
	public static function getAvailableOperations(): ?array
	{
		$result = \CCalendarType::GetList([
			'arFilter' => [
				'XML_ID' => 'location',
			],
		]);

		return $result[0]['PERM'] ?? null;
	}
}
