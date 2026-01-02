import { RecentCallStatus } from 'im.v2.const';

import type { Call } from 'im.v2.model';

export type CallItem = {
	dialogId: string,
	name: string,
	call: Call,
	state: $Values<typeof RecentCallStatus>
};
