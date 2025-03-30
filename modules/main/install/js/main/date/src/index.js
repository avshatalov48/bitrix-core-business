export { DateTimeFormat as Date } from './date-time-format'; // compatibility alias
export { DateTimeFormat } from './date-time-format';
export { DurationFormat } from './duration-format';

import { BrowserTime } from './timezone/browser-time';
import { Offset } from './timezone/offset';
import { ServerTime } from './timezone/server-time';
import { UserTime } from './timezone/user-time';

export const Timezone = Object.freeze({
	BrowserTime,
	Offset,
	ServerTime,
	UserTime,
});
