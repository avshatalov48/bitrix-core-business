<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text\BbCode\Timestamp;

/**
 * @see main/install/js/main/date/config.php
 */
enum DateFormat: string
{
	// examples in the comments below are from EN (USA), they will be different in different cultures
	case FormatDate = 'FORMAT_DATE'; // 12/31/2019
	case FormatDatetime = 'FORMAT_DATETIME'; // 12/31/2019 11:36:49 pm
	case ShortDateFormat = 'SHORT_DATE_FORMAT'; // 12/31/2019
	case MediumDateFormat = 'MEDIUM_DATE_FORMAT'; // Dec 31, 2019
	case LongDateFormat = 'LONG_DATE_FORMAT'; // December 31, 2019
	case DayMonthFormat = 'DAY_MONTH_FORMAT'; // December 31
	case DayShortMonthFormat = 'DAY_SHORT_MONTH_FORMAT'; // Dec 31
	case ShortDayOfWeekMonthFormat = 'SHORT_DAY_OF_WEEK_MONTH_FORMAT'; // Tue, December 31
	case ShortDayOfWeekShortMonthFormat = 'SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT'; // Tue, Dec 31
	case DayOfWeekMonthFormat = 'DAY_OF_WEEK_MONTH_FORMAT'; // Tuesday, December 31
	case FullDateFormat = 'FULL_DATE_FORMAT'; // Tuesday, December 31, 2019
	case ShortTimeFormat = 'SHORT_TIME_FORMAT'; // 2:05 pm
	case LongTimeFormat = 'LONG_TIME_FORMAT'; // 2:05:15 pm
}
