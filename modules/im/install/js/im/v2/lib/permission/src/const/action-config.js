import { UserRole, ChatActionType } from 'im.v2.const';

export const MinimalRoleForAction = {
	[ChatActionType.readMessage]: UserRole.member,
	[ChatActionType.setReaction]: UserRole.member,
	[ChatActionType.openMessageMenu]: UserRole.member,
	[ChatActionType.openAvatarMenu]: UserRole.member,
	[ChatActionType.openSidebarMenu]: UserRole.member,
	[ChatActionType.subscribeToComments]: UserRole.member,

	[ChatActionType.openComments]: UserRole.guest,
	[ChatActionType.openSidebar]: UserRole.guest,
};
