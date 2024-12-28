export type UpdateCollabEntityCounterParams = {
	dialogId: string,
	chatId: number,
	entity: CollabEntity,
	counter: number,
};

type CollabEntity = 'tasks' | 'calendar';

export type UpdateCollabGuestCountParams = {
	dialogId: string,
	chatId: number,
	guestCount: number,
};
