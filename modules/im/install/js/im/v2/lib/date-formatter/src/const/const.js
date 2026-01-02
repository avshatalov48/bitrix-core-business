import { DateTimeFormat } from 'main.date';

import type { DateTemplateType } from '../types/date-template-type';

const Interval = {
	tomorrow: 'tomorrow',
	today: 'today',
	yesterday: 'yesterday',
	week: 'week',
	year: 'year',
	olderThanYear: 'olderThanYear',
};

// camelCase versions of main formats
// main/install/js/main/date/config.php
const DateFormat = {
	shortTimeFormat: 'SHORT_TIME_FORMAT',
	longTimeFormat: 'LONG_TIME_FORMAT',
	shortDateFormat: 'SHORT_DATE_FORMAT',
	dayMonthFormat: 'DAY_MONTH_FORMAT',
	longDateFormat: 'LONG_DATE_FORMAT',
	dayOfWeekMonthFormat: 'DAY_OF_WEEK_MONTH_FORMAT',
	shortDayOfWeekMonthFormat: 'SHORT_DAY_OF_WEEK_MONTH_FORMAT',
	shortDayOfWeekShortMonthFormat: 'SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT',
	fullDateFormat: 'FULL_DATE_FORMAT',
	dayShortMonthFormat: 'DAY_SHORT_MONTH_FORMAT',
	mediumDateFormat: 'MEDIUM_DATE_FORMAT',
	formatDatetime: 'FORMAT_DATETIME',
	formatDate: 'FORMAT_DATE',
};

type DateFormatKey = $Keys<typeof DateFormat>;

// string codes for provided format
// shortTimeFormat: 'H:i'
const DateCode: {
	[format: DateFormatKey]: string,
} = {};

Object.keys(DateFormat).forEach((format) => {
	DateCode[format] = DateTimeFormat.getFormat(DateFormat[format]);
});

const DateTemplate: {
	[templateName: string]: DateTemplateType
} = {
	notification: {
		[Interval.today]: `today, ${DateCode.shortTimeFormat}`,
		[Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
		[Interval.year]: `${DateCode.dayMonthFormat}, ${DateCode.shortTimeFormat}`,
		[Interval.olderThanYear]: `${DateCode.longDateFormat}, ${DateCode.shortTimeFormat}`,
	},
	dateGroup: {
		[Interval.today]: 'today',
		[Interval.yesterday]: 'yesterday',
		[Interval.year]: DateCode.dayOfWeekMonthFormat,
		[Interval.olderThanYear]: DateCode.fullDateFormat,
	},
	meeting: {
		[Interval.tomorrow]: `tomorrow, ${DateCode.shortTimeFormat}`,
		[Interval.today]: `today, ${DateCode.shortTimeFormat}`,
		[Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
		[Interval.year]: `${DateCode.dayShortMonthFormat}, ${DateCode.shortTimeFormat}`,
		[Interval.olderThanYear]: `${DateCode.mediumDateFormat}, ${DateCode.shortTimeFormat}`,
	},
	recent: {
		[Interval.today]: DateCode.shortTimeFormat,
		[Interval.week]: 'D',
		[Interval.year]: DateCode.dayShortMonthFormat,
		[Interval.olderThanYear]: DateCode.mediumDateFormat,
	},
	messageReadStatus: {
		[Interval.today]: `today, ${DateCode.shortTimeFormat}`,
		[Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
		[Interval.year]: `${DateCode.dayMonthFormat},  ${DateCode.shortTimeFormat}`,
		[Interval.olderThanYear]: `${DateCode.dayMonthFormat} Y, ${DateCode.shortTimeFormat}`,
	},
};

export { Interval, DateFormat, DateCode, DateTemplate };
