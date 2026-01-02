export type NotificationItemRest = {
	id: number,
	chat_id: number,
	author_id: number,
	date: string,
	notify_type: number,
	notify_module: string,
	notify_event: string,
	notify_tag: string,
	notify_sub_tag: string,
	notify_title?: string,
	notify_read: string,
	setting_name: string,
	text: string,
	notify_buttons: string,
	params?: Object
};

export type NotificationGetRestResult = {
	chat_id: number,
	notifications: NotificationItemRest[],
	total_count: number,
	total_unread_count: number,
	users: [],
};
