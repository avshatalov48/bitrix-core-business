import { DialogType, UserRole } from 'im.v2.const';

export type RoleItem = $Keys<typeof UserRole>;
export type ChatConfig = {
	title: string,
	avatar: File,
	members: number[],
	ownerId: number,
	managers: number[],
	manageUsers: RoleItem,
	manageUi: RoleItem,
	manageSettings: RoleItem,
	canPost: RoleItem,
	isAvailableInSearch: boolean,
	description: string,
	type?: string,
	conferencePassword: string,
};

type ChatTypeItem = $Keys<typeof DialogType>
export type RestChatConfig = {
	users: number[],
	entityType?: ChatTypeItem,
	title?: string,
	avatar?: string,
	description?: string,
	managers?: number[],
	ownerId?: number,
	searchable?: 'Y' | 'N',
	manageUsers?: RoleItem,
	manageUi?: RoleItem,
	manageSettings?: RoleItem,
	canPost?: RoleItem,
	conferencePassword?: string,
};
