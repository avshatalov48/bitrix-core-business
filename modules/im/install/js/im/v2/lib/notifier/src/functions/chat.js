import { Loc } from 'main.core';

import { ErrorCode } from 'im.v2.const';

import { showNotification } from '../utils/notification';
import { extractRestErrorCode, type RestError } from '../utils/error';

import type { RunActionError } from 'im.v2.lib.rest';

export const ChatNotifier = {
	handleLoadError(error: RunActionError): void
	{
		// eslint-disable-next-line unicorn/prefer-switch
		if (error.code === ErrorCode.chat.notFound)
		{
			this.onNotFoundError();
		}
		else if (error.code === ErrorCode.chat.accessDenied)
		{
			this.onAccessDeniedError();
		}
		else if (error.code === ErrorCode.message.notFound)
		{
			this.onContextMessageNotFoundError();
		}
	},

	handleLeaveError(error: RestError): void
	{
		const errorCode = extractRestErrorCode(error);
		const NotificationTextByErrorCode = {
			[ErrorCode.user.invitedFromStructure]: Loc.getMessage('IM_NOTIFIER_LEAVE_CHAT_STRUCTURE_ERROR'),
			default: Loc.getMessage('IM_NOTIFIER_LEAVE_CHAT_ERROR'),
		};

		const notificationText = NotificationTextByErrorCode[errorCode] ?? NotificationTextByErrorCode.default;
		showNotification(notificationText);
	},

	handleUserKickError(error: RestError): void
	{
		const errorCode = extractRestErrorCode(error);
		const NotificationTextByErrorCode = {
			[ErrorCode.user.invitedFromStructure]: Loc.getMessage('IM_NOTIFIER_KICK_CHAT_STRUCTURE_ERROR'),
			default: Loc.getMessage('IM_NOTIFIER_KICK_CHAT_ERROR'),
		};

		const notificationText = NotificationTextByErrorCode[errorCode] ?? NotificationTextByErrorCode.default;
		showNotification(notificationText);
	},

	onNotFoundError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CHAT_ACCESS_ERROR'));
	},

	onAccessDeniedError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CHAT_ACCESS_ERROR'));
	},

	onContextMessageNotFoundError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CONTEXT_MESSAGE_NOT_FOUND_ERROR'));
	},

	onCreateError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CHAT_CREATE_ERROR'));
	},

	onUpdateError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CHAT_UPDATE_ERROR'));
	},

	onDeleteError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CHAT_DELETE_ERROR'));
	},

	onRenameError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_CHAT_RENAME_ERROR'));
	},

	onMessagesPinLimitError(pinLimit: number): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_MESSAGES_PIN_LIMIT_ERROR', {
			'#MAX_PINS#': pinLimit,
		}));
	},
};
