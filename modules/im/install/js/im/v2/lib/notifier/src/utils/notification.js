import 'ui.notification';

export type NotificationAction = {
	title: string,
	events: { [eventName: string]: () => void },
}

type NotificationParams = {
	autoHideDelay?: number,
	actions?: NotificationAction[],
};

export const showNotification = (text: string, params: NotificationParams): void => {
	BX.UI.Notification.Center.notify({ content: text, ...params });
};
