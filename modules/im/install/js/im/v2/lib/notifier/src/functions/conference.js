import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

export const ConferenceNotifier = {
	onCopyLinkComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CONFERENCE_LINK_COPY_COMPLETE'));
	},

	onPasswordError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CONFERENCE_PASSWORD_ERROR'));
	},
};
