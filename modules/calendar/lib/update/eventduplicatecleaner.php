<?php

namespace Bitrix\Calendar\Update;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class EventDuplicateCleaner extends Stepper
{
	const MAX_TOTAL_COUUNT = 1000;
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	/**
	 * @param array $result
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function execute(array &$result): bool
	{
		if (!Loader::includeModule("calendar"))
		{
			return self::FINISH_EXECUTION;
		}

		$totalCount = 0;
		// Clear duplications of child events
		$dataToClean = $this->getDuplicatedChildEntryList();
		if (count($dataToClean))
		{
			foreach ($dataToClean as $entryToClean)
			{
				$this->cleanDuplicates(
					(int)$entryToClean['PARENT_ID'],
					(int)$entryToClean['OWNER_ID'],
					(int)$entryToClean['FIRSTID'] === (int)$entryToClean['PARENT_ID']
						? (int)$entryToClean['FIRSTID']
						: (int)$entryToClean['LASTID']
				);
				$totalCount += $entryToClean['CNT'];
				if ($totalCount >= self::MAX_TOTAL_COUUNT)
				{
					return self::CONTINUE_EXECUTION;
				}
			}

			return self::CONTINUE_EXECUTION;
		}

		if ($this->getBogusLocationEntry())
		{
			$this->clearBogusLocationEntries();
			return self::CONTINUE_EXECUTION;
		}

		if ($this->getLocationEntriesWithEmptyParent())
		{
			$this->clearLocationEntriesWithEmptyParent();
			return self::CONTINUE_EXECUTION;
		}

		return self::FINISH_EXECUTION;
	}

	/**
	 * @return array
	 */
	private function getDuplicatedChildEntryList(): ?array
	{
		global $DB;
		$strSql = "select
				MAX(ID) as LASTID,
				MIN(ID) as FIRSTID,
				PARENT_ID, 
				OWNER_ID,
				COUNT(1) as CNT
			from 
				b_calendar_event 
			where 
				CAL_TYPE='user'
				and PARENT_ID is not null
			group by 
				PARENT_ID, OWNER_ID
			having CNT > 1
			order by ID desc
			limit 200";

		$entries = [];
		$res = $DB->Query($strSql);
		while ($entry = $res->Fetch())
		{
			$entries[] = $entry;
		}
		return $entries;
	}

	/**
	 * @return array|null
	 */
	private function getBogusLocationEntry(): ?array
	{
		global $DB;
		$strSql = "
			select ID from b_calendar_event 
				where 
					ID=PARENT_ID
					and CAL_TYPE = 'location'
					and OWNER_ID = '0'
				limit 1;
		";

		$res = $DB->Query($strSql);
		if ($entry = $res->Fetch())
		{
			return $entry;
		}
		return null;
	}

	/**
	 * @return void
	 */
	private function clearBogusLocationEntries(): void
	{
		global $DB;
		$DB->Query("
			delete from b_calendar_event 
				where 
					ID=PARENT_ID
					and CAL_TYPE = 'location'
					and OWNER_ID = '0'
				limit 1000;
		");
	}

	/**
	 * @param int $parentId parent id which will be used to delete duplicates
	 * @param int $ownerId ownerId
	 * @param int $entryToLeave id of entry which will not be deleted
	 * @return void
	 */
	private function cleanDuplicates(int $parentId, int $ownerId, int $entryToLeave): void
	{
		global $DB;

		$DB->Query("
			delete from 
				b_calendar_event 
				where 
					CAL_TYPE = 'user'
					and PARENT_ID = '".$parentId."' 
					and OWNER_ID = '".$ownerId."' 
					and ID != '".$entryToLeave."' 
				limit 1000
		");
	}

	/**
	 * @return array|null
	 */
	private function getLocationEntriesWithEmptyParent(): ?array
	{
		global $DB;
		$strSql = "
			select 
				ce.ID, ce.PARENT_ID, p.ID as PID
			from b_calendar_event ce
			left join b_calendar_event p
			on ce.PARENT_ID=p.ID
			where 
				ce.CAL_TYPE='location'
				and (p.ID is null or p.DELETED='Y')
			limit 1
		";

		$res = $DB->Query($strSql);
		if ($entry = $res->Fetch())
		{
			return $entry;
		}
		return null;
	}

	/**
	 * @return void
	 */
	private function clearLocationEntriesWithEmptyParent(): void
	{
		global $DB;
		$DB->Query("
			delete ce
			from b_calendar_event as ce
			left join b_calendar_event as p
			on ce.PARENT_ID=p.ID
			where 
				ce.CAL_TYPE='location'
				and (p.ID is null or p.DELETED='Y')
		");
	}
}