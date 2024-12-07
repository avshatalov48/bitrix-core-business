import { DateTimeFormat } from './date-time-format';
import { BrowserTime } from './timezone/browser-time';

import { Offset } from './timezone/offset';
import { ServerTime } from './timezone/server-time';
import { UserTime } from './timezone/user-time';

// compatibility alias
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
