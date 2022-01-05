<?php

namespace Bitrix\Calendar\Update;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class LocationDuplicateCleaner extends Stepper
{
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

		if ($dataToClean = $this->getEntryDataToClean())
		{
			$this->cleanDuplicates((int)$dataToClean['PARENT_ID'], (int)$dataToClean['LASTID']);
			return self::CONTINUE_EXECUTION;
		}

		return self::FINISH_EXECUTION;
	}

	/**
	 * @return array|null
	 */
	private function getEntryDataToClean(): ?array
	{
		global $DB;
		$strSql = "select
				MAX(ID) as LASTID,
				PARENT_ID, 
				COUNT(1) as CNT
			from 
				b_calendar_event 
			where 
				CAL_TYPE='location'
			group by 
				PARENT_ID
			having CNT > 1
			order by ID desc
			limit 1";

		$res = $DB->Query($strSql);
		if ($entry = $res->Fetch())
		{
			return $entry;
		}
		return null;
	}

	/**
	 * @param int $parentId parent id which will be used to delete duplicates
	 * @param int $entryToLeave id of entry which will not be deleted
	 * @return void
	 */
	private function cleanDuplicates(int $parentId, int $entryToLeave): void
	{
		global $DB;
		$strSql = "delete from 
			b_calendar_event 
			where 
				CAL_TYPE = 'location' 
				and PARENT_ID = '".$parentId."' 
				and ID != '".$entryToLeave."' 
			limit 1000";

		$DB->Query($strSql);
	}
}