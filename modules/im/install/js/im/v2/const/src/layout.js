export const Layout = {
	chat: 'chat',
	createChat: 'createChat',
	updateChat: 'updateChat',
	channel: 'channel',
	notification: 'notification',
	openlines: 'openlines',
	openlinesV2: 'openlinesV2',
	conference: 'conference',
	settings: 'settings',
	copilot: 'copilot',
	collab: 'collab',
	market: 'market',
	taskComments: 'tasksTask',
};

export type LayoutType = $Values<typeof Layout>;
