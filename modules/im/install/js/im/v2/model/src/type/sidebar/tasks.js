export type SidebarTaskItem = {
	id: number,
	messageId: ?number,
	chatId: number,
	authorId: number,
	date: Date,
	task: {
		id: number,
		title: string,
		creatorId: number,
		responsibleId: number,
		status: number,
		statusTitle: string,
		deadline: ?Date,
		state: string,
		color: string,
		source: string
	}
};