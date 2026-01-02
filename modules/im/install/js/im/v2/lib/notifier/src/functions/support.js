import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

export const SupportNotifier = {
	onVoteClosedError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_VOTE_CLOSED_ERROR'));
	},
};
