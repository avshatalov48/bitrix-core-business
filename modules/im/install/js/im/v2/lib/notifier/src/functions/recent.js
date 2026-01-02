import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

import type { RunActionError } from 'im.v2.lib.rest';

const RecentErrorCodes = {
	maxPinned: 'MAX_PINNED_CHATS_ERROR',
};

const MAX_PINS = 45;

export const RecentNotifier = {
	handlePinError(error: RunActionError): void
	{
		const maxPinnedMessage = Loc.getMessage('IM_NOTIFIER_RECENT_PIN_LIMIT_ERROR', {
			'#MAX_PINS#': MAX_PINS,
		});

		const NotificationTextByErrorCode = {
			[RecentErrorCodes.maxPinned]: maxPinnedMessage,
			default: Loc.getMessage('IM_NOTIFIER_RECENT_PIN_DEFAULT_ERROR'),
		};

		const notificationText = NotificationTextByErrorCode[error.code] ?? NotificationTextByErrorCode.default;
		showNotification(notificationText);
	},

	onUnpinError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_RECENT_UNPIN_DEFAULT_ERROR'));
	},
};
