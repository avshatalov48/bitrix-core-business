import { DateTimeFormat } from './date-time-format';

import { Offset } from './timezone/offset';
import { BrowserTime } from './timezone/browser-time';
import { UserTime } from './timezone/user-time';
import { ServerTime } from './timezone/server-time';

//compatibility alias
const Date = DateTimeFormat;

const Timezone = Object.freeze({
	Offset,
	BrowserTime,
	UserTime,
	ServerTime,
});

export {
	DateTimeFormat,
	Date,
	Timezone,
};
