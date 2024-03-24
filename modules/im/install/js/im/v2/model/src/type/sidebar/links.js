export type SidebarLinkItem = {
	id: number,
	messageId: number,
	chatId: number,
	authorId: number,
	source: string,
	date: Date,
	richData: {
		id: ?number,
		description: ?string,
		link: ?string,
		name: ?string,
		previewUrl: ?string,
		type: ?string,
	},
};
