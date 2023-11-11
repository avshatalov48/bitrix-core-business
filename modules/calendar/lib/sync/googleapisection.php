<?php


namespace Bitrix\Calendar\Sync;

use Bitrix\Calendar\Internals;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;

/**
 * @deprecated
 */
class GoogleApiSection
{
	/**
	 * @var array
	 */
	private $connection;

	/**
	 * @param array $connection
	 * @return GoogleApiSection
	 */
	public static function createInstance(): GoogleApiSection
	{
		return new self();
	}

	public function __construct()
	{
	}

	/**
	 * @return array|bool
	 */
	public function createSection(array $section): ?array
	{
		$googleApiSync = new GoogleApiSync($section['OWNER_ID']);
		return $googleApiSync->createCalendar($section);
	}
	
	public function deleteSection(array $section): void
	{
		(new GoogleApiSync((int )$section['OWNER_ID']))->deleteCalendar((string)$section['GAPI_CALENDAR_ID']);
	}

	public function updateSection(string $gApiCalendarId, array $section): void
	{
		$googleApiSync = new GoogleApiSync($section['OWNER_ID']);
		$googleApiSync->updateCalendar($gApiCalendarId, $section);
	}

	/**
	 * @param string $gApiCalendarId
	 * @param array $section
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function updateSectionList(string $gApiCalendarId, array $section): array
	{
		$googleApiSync = new GoogleApiSync($section['OWNER_ID']);
		return $googleApiSync->updateCalendarList($gApiCalendarId, $section);
	}
}