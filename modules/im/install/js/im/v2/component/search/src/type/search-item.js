export type ImSearchItem = {
	id: number,
	entityId: string,
	entityType: string,
	title: string,
	customData: {
		imChat?: Object,
		imUser?: Object,
		imBot?: Object,
		email?: string,
		lastName?: string,
		login?: string,
		name?: string,
		position?: string,
		secondName?: string,
	},
	avatar: string,
	badges?: Array<Object>,
	tabs?: Array<string>,
	globalSort?: number,
	contextSort?: number,
};

export const EntityIdTypes = Object.freeze({
	user: 'user',
	bot: 'im-bot',
	chat: 'im-chat',
	department: 'department',
});