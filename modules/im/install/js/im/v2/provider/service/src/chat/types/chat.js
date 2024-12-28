import { ChatType, UserRole } from 'im.v2.const';

import type { JsonObject } from 'main.core';

export type RoleItem = $Keys<typeof UserRole>;
export type MemberEntity = [string, number | string];
export type ChatConfig = {
	title: string,
	avatar: File,
	members: number[],
	memberEntities: [string, number | string][],
	ownerId: number,
	managers: number[],
	manageUsersAdd: RoleItem,
	manageUsersDelete: RoleItem,
	manageUi: RoleItem,
	manageSettings: RoleItem,
	manageMessages: RoleItem,
	isAvailableInSearch: boolean,
	description: string,
	entityType?: string,
	type?: string,
	conferencePassword: string,
	copilotMainRole: string,
};

export type UpdateChatConfig = {
	addedMemberEntities?: MemberEntity[],
	deletedMemberEntities?: MemberEntity[],
	addedManagers?: number[],
	deletedManagers?: number[],
} & ChatConfig;

type ChatTypeItem = $Keys<typeof ChatType>
export type RestChatConfig = {
	users: number[],
	memberEntities?: MemberEntity[],
	type?: ChatTypeItem,
	entityType?: ChatTypeItem,
	title?: string,
	avatar?: string,
	description?: string,
	managers?: number[],
	ownerId?: number,
	searchable?: 'Y' | 'N',
	manageUsersAdd?: RoleItem,
	manageUsersDelete?: RoleItem,
	manageUi?: RoleItem,
	manageSettings?: RoleItem,
	manageMessages?: RoleItem,
	conferencePassword?: string,
};

export type RestUpdateChatConfig = {
	avatar?: string,
	title?: string,
	description?: string,
	ownerId?: number,
	searchable?: 'Y' | 'N',
	manageUi?: RoleItem,
	manageUsersAdd?: RoleItem,
	manageUsersDelete?: RoleItem,
	manageMessages?: RoleItem,
	addedMemberEntities?: MemberEntity[],
	deletedMemberEntities?: MemberEntity[],
	addedManagers?: number[],
	deletedManagers?: number[],
};

export type RestCreateCollabConfig = {
	title: string,
	description?: string,
	avatar?: File,
	ownerId: number,
	moderatorMembers: number[],
	permissions: JsonObject,
	options: JsonObject
};

export type UpdateCollabConfig = {
	title: string,
	description?: string,
	avatar?: File,
	groupSettings: {
		ownerId: number,
		addModeratorMembers: number[],
		deleteModeratorMembers: number[],
		permissions: JsonObject,
		options: JsonObject
	},
};

export type RestUpdateCollabConfig = {
	title: string,
	description?: string,
	avatar?: File,
	ownerId: number,
	addModeratorMembers: number[],
	deleteModeratorMembers: number[],
	permissions: JsonObject,
	options: JsonObject
};

export type GetMemberEntitiesConfig = {
	memberEntities: Array<[string, number]>,
	userCount: number,
	areUsersCollapsed: boolean,
};
