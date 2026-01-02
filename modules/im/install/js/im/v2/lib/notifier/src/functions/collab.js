import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

import type { RunActionError } from 'im.v2.lib.rest';

const CollabErrorCode = {
	emptyName: 'name',
	duplicateName: 'ERROR_GROUP_NAME_EXISTS',
	urlInName: 'ERROR_NAME_CONTAINS_URL',
	tasksNotEmpty: 'TASKS_NOT_EMPTY',
	diskNotEmpty: 'DISK_NOT_EMPTY',
	calendarNotEmpty: 'CALENDAR_NOT_EMPTY',
};

const NotEmptyCollabErrorCodes = new Set([
	CollabErrorCode.tasksNotEmpty, CollabErrorCode.diskNotEmpty, CollabErrorCode.calendarNotEmpty,
]);

export const CollabNotifier = {
	onBeforeDelete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_DELETE_PROGRESS'));
	},

	onUpdateLinkComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_LINK_UPDATE_COMPLETE'));
	},

	handleCreateError(error: RunActionError): void
	{
		const NotificationTextByErrorCode = {
			[CollabErrorCode.emptyName]: Loc.getMessage('IM_NOTIFIER_COLLAB_EMPTY_NAME_ERROR'),
			[CollabErrorCode.duplicateName]: Loc.getMessage('IM_NOTIFIER_COLLAB_DUPLICATE_NAME_ERROR'),
			[CollabErrorCode.urlInName]: Loc.getMessage('IM_NOTIFIER_COLLAB_URL_IN_NAME_ERROR'),
			default: Loc.getMessage('IM_NOTIFIER_CHAT_CREATE_ERROR'),
		};

		const notificationText = NotificationTextByErrorCode[error.code] ?? NotificationTextByErrorCode.default;
		showNotification(notificationText);
	},

	handleUpdateError(error: RunActionError): void
	{
		const NotificationTextByErrorCode = {
			[CollabErrorCode.emptyName]: Loc.getMessage('IM_NOTIFIER_COLLAB_EMPTY_NAME_ERROR'),
			[CollabErrorCode.duplicateName]: Loc.getMessage('IM_NOTIFIER_COLLAB_DUPLICATE_NAME_ERROR'),
			[CollabErrorCode.urlInName]: Loc.getMessage('IM_NOTIFIER_COLLAB_URL_IN_NAME_ERROR'),
			default: Loc.getMessage('IM_NOTIFIER_CHAT_UPDATE_ERROR'),
		};

		const notificationText = NotificationTextByErrorCode[error.code] ?? NotificationTextByErrorCode.default;
		showNotification(notificationText);
	},

	handleDeleteError(error: RunActionError): void
	{
		if (NotEmptyCollabErrorCodes.has(error.code))
		{
			showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_DELETE_ENTITIES_ERROR'));

			return;
		}

		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_DELETE_ERROR'));
	},

	onLeaveError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_LEAVE_ERROR'));
	},

	onKickUserError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_KICK_ERROR'));
	},

	onCollaberNotAcceptInvitation(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_COLLABER_NOT_ACCEPT_INVITATION'));
	},

	onCopyLinkError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_COLLAB_COPY_LINK_FORBIDDEN_ERROR'));
	},
};
