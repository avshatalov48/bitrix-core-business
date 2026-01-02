import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

export const InviteNotifier = {
	onResendComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_INVITE_RESEND_COMPLETE'), {
			autoHideDelay: 2000,
		});
	},

	onCancelComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_INVITE_CANCEL_COMPLETE'), {
			autoHideDelay: 2000,
		});
	},
};
