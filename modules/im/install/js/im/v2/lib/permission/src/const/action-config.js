import { UserRole, ActionByRole } from 'im.v2.const';

export const MinimalRoleForAction = {
	[ActionByRole.readMessage]: UserRole.member,
	[ActionByRole.setReaction]: UserRole.member,
	[ActionByRole.openMessageMenu]: UserRole.member,
	[ActionByRole.openAvatarMenu]: UserRole.member,
	[ActionByRole.openSidebarMenu]: UserRole.member,
	[ActionByRole.subscribeToComments]: UserRole.member,

	[ActionByRole.openComments]: UserRole.guest,
	[ActionByRole.openSidebar]: UserRole.guest,
};
