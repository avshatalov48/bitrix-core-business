type EventCategoryPermissions = {
	edit: boolean,
	delete: boolean,
};

type ChannelInfo = {
	id: number,
	title: string,
	avatar: string,
};

type CategoryDto = {
	id: number,
	closed: boolean,
	name: string,
	description: string,
	eventsCount: number,
	permissions: EventCategoryPermissions,
	channelId: number,
	channel?: ChannelInfo,
	isMuted: boolean,
	isBanned: boolean,
	newCount: number,
	updatedAt: number,
};

type CreateCategoryDto = {
	closed: boolean,
	name: string,
	description: string,
	attendees: number[],
	departmentIds: number[],
	channelId: number,
};

type UpdateCategoryDto = {
	id: number,
	name: string,
	description: string,
};
