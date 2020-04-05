<?

namespace Bitrix\Main\UI\Filter;

class DateType
{
	const NONE = "none";
	const TODAY = "today";
	const YESTERDAY = "yesterday";
	const THIS_WEEK = "week";
	const LAST_WEEK = "week_ago";
	const THIS_MONTH = "month";
	const LAST_MONTH = "month_ago";
	const DAYS = "days";
	const SINGLE = "exact";
	const RANGE = "interval";
	const BEFORE = "before";
	const AFTER = "after";
	const QUARTER = "quarter";

	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}