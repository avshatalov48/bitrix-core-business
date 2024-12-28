import { Loc } from 'main.core';

import { ChatType, ChatActionGroup } from 'im.v2.const';

export const BlocksByChatType = {
	[ChatType.channel]: new Set([
		ChatActionGroup.manageUsersAdd,
		ChatActionGroup.manageUsersDelete,
		ChatActionGroup.manageMessages,
	]),
	default: new Set([
		ChatActionGroup.manageUsersAdd,
		ChatActionGroup.manageUsersDelete,
		ChatActionGroup.manageUi,
		ChatActionGroup.manageMessages,
	]),
};

export const CanAddUsersCaptionByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_RIGHTS_SECTION_MANAGE_USERS_ADD'),
	default: Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS_ADD'),
};

export const CanKickUsersCaptionByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_RIGHTS_SECTION_MANAGE_USERS_DELETE'),
	default: Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_USERS_DELETE'),
};

export const CanSendMessageCaptionByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_RIGHTS_SECTION_MANAGE_SENDING'),
	default: Loc.getMessage('IM_CREATE_CHAT_RIGHTS_SECTION_MANAGE_SENDING_MSGVER_1'),
};

export const OwnerHintByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_OWNER_HINT'),
	default: Loc.getMessage('IM_CREATE_CHAT_OWNER_HINT'),
};

export const ManagerHintByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_MANAGER_HINT'),
	default: Loc.getMessage('IM_CREATE_CHAT_MANAGER_HINT'),
};

export const AddUsersHintByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_USERS_ADD_HINT'),
	default: Loc.getMessage('IM_CREATE_CHAT_MANAGE_USERS_ADD_HINT'),
};

export const DeleteUsersHintByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_USERS_DELETE_HINT'),
	default: Loc.getMessage('IM_CREATE_CHAT_MANAGE_USERS_DELETE_HINT'),
};

export const ManageUiHintByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_UI_HINT'),
	default: Loc.getMessage('IM_CREATE_CHAT_MANAGE_UI_HINT'),
};

export const SendMessagesHintByChatType = {
	[ChatType.channel]: Loc.getMessage('IM_CREATE_CHANNEL_MANAGE_SENDING_HINT'),
	default: Loc.getMessage('IM_CREATE_CHAT_MANAGE_SENDING_HINT'),
};