export type SidebarMeetingItem = {
	id: number,
	messageId: ?number,
	chatId: number,
	authorId: number,
	date: Date,
	meeting: {
		id: number,
		title: string,
		dateFrom: Date,
		dateTo: Date,
		source: string
	}
};
