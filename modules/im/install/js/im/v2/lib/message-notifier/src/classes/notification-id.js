type MessageIdPrefix = typeof NotificationIdPrefix.chat
	| typeof NotificationIdPrefix.lines
	| typeof NotificationIdPrefix.taskComments;

type MessageIdPayload = {
	prefix: MessageIdPrefix,
	dialogId: string,
	messageId: string,
};

type NotifyIdPayload = {
	prefix: typeof NotificationIdPrefix.notify,
	notifyId: string,
};

type NotificationIdPayload = MessageIdPayload | NotifyIdPayload;

export const NotificationIdPrefix = {
	chat: 'im_chat',
	taskComments: 'im_task_comments',
	lines: 'im_lines',
	notify: 'im_notify',
};

const ID_SEPARATOR = '-';

export const NotificationId = {
	build(payload: NotificationIdPayload): string
	{
		const { prefix } = payload;
		if (prefix === NotificationIdPrefix.notify)
		{
			const { notifyId } = payload;

			return `${prefix}${ID_SEPARATOR}${notifyId}`;
		}

		const { dialogId, messageId } = payload;

		return `${prefix}${ID_SEPARATOR}${dialogId}${ID_SEPARATOR}${messageId}`;
	},

	parse(notificationId: string): NotificationIdPayload
	{
		const parts = notificationId.split(ID_SEPARATOR);
		const [prefix] = parts;

		if (prefix === NotificationIdPrefix.notify)
		{
			const [, notifyId] = parts;

			return { prefix, notifyId };
		}

		const [, dialogId, messageId] = parts;

		return { prefix, dialogId, messageId };
	},
};
