import {DateTimeFormat} from 'main.date';

import type {DateTemplateType} from './types/date-template-type';

export const DateCode = {
	shortTimeFormat: DateTimeFormat.getFormat('SHORT_TIME_FORMAT'),
	shortDateFormat: DateTimeFormat.getFormat('SHORT_DATE_FORMAT'),
	dayMonthFormat: DateTimeFormat.getFormat('DAY_MONTH_FORMAT'),
	longDateFormat: DateTimeFormat.getFormat('LONG_DATE_FORMAT'),
	dayOfWeekMonthFormat: DateTimeFormat.getFormat('DAY_OF_WEEK_MONTH_FORMAT'),
	fullDateFormat: DateTimeFormat.getFormat('FULL_DATE_FORMAT'),
	dayShortMonthFormat: DateTimeFormat.getFormat('DAY_SHORT_MONTH_FORMAT'),
	mediumDateFormat: DateTimeFormat.getFormat('MEDIUM_DATE_FORMAT'),
	defaultDateTime: DateTimeFormat.getFormat('FORMAT_DATETIME')
};

export const Interval = {
	tomorrow: 'tomorrow',
	today: 'today',
	yesterday: 'yesterday',
	week: 'week',
	year: 'year',
	olderThanYear: 'olderThanYear'
};

export const DateTemplate: {
	[templateName: string]: DateTemplateType
} = {
	notification: {
		[Interval.today]: `today, ${DateCode.shortTimeFormat}`,
		[Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
		[Interval.year]: `${DateCode.dayMonthFormat}, ${DateCode.shortTimeFormat}`,
		[Interval.olderThanYear]: `${DateCode.longDateFormat}, ${DateCode.shortTimeFormat}`
	},
	dateGroup: {
		[Interval.today]: 'today',
		[Interval.yesterday]: 'yesterday',
		[Interval.year]: DateCode.dayOfWeekMonthFormat,
		[Interval.olderThanYear]: DateCode.fullDateFormat
	},
	meeting: {
		[Interval.tomorrow]: `tomorrow, ${DateCode.shortTimeFormat}`,
		[Interval.today]: `today, ${DateCode.shortTimeFormat}`,
		[Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
		[Interval.year]: `${DateCode.dayShortMonthFormat}, ${DateCode.shortTimeFormat}`,
		[Interval.olderThanYear]: `${DateCode.mediumDateFormat}, ${DateCode.shortTimeFormat}`
	},
	recent: {
		[Interval.today]: DateCode.shortTimeFormat,
		[Interval.week]: 'D',
		[Interval.year]: DateCode.dayShortMonthFormat,
		[Interval.olderThanYear]: DateCode.mediumDateFormat
	},
	messageReadStatus: {
		[Interval.today]: `today, ${DateCode.shortTimeFormat}`,
		[Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
		[Interval.year]: `${DateCode.dayMonthFormat},  ${DateCode.shortTimeFormat}`,
		[Interval.olderThanYear]: `${DateCode.dayMonthFormat} Y, ${DateCode.shortTimeFormat}`
	}
};