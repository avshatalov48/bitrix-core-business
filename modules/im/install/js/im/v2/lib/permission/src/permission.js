import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { ChatType, ChatActionType, ChatActionGroup, UserRole } from 'im.v2.const';

import { MinimalRoleForAction } from './const/action-config';

import type { ImModelChat } from 'im.v2.model';

type ChatTypeItem = $Keys<typeof ChatType>;
type ActionTypeItem = $Keys<typeof ChatActionType>;
type ActionGroupItem = $Keys<typeof ChatActionGroup>;
type ActionGroup = ActionTypeItem[];
type RoleItem = $Keys<typeof UserRole>;

type RawPermissions = {
	byChatType: PermissionsByChatType,
	actionGroups: {},
	actionGroupsDefaults: {},
};

type PermissionsByRole = {
	[operation: ActionTypeItem]: RoleItem,
};

type PermissionsByChatType = {
	[chatType: ChatTypeItem | 'default']: {
		[operation: ActionTypeItem]: RoleItem,
	}
};

type PermissionsGroupDefaults = {
	[chatType: ChatTypeItem]: {
		[group: ActionGroupItem]: RoleItem,
	}
};

const DEFAULT_TYPE = 'default';

export class PermissionManager
{
	static #instance: PermissionManager;

	#rolePermissions: PermissionsByRole = {};
	#chatTypePermissions: PermissionsByChatType = {};
	#actionGroups: Object<ActionGroupItem, ActionGroup> = {};
	#actionGroupsDefaultRoles: PermissionsGroupDefaults = {};

	static getInstance(): PermissionManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		PermissionManager.getInstance();
	}

	constructor()
	{
		const { permissions } = Core.getApplicationData();
		Logger.warn('PermissionManager: permission from server', permissions);
		this.#init(permissions);
	}

	canPerformAction(actionType: ActionTypeItem, dialogId: string): boolean
	{
		return this.#canPerformActionByRole(actionType, dialogId)
			&& this.#canPerformActionByChatType(actionType, dialogId)
			&& this.#canPerformActionByChatSettings(actionType, dialogId);
	}

	getDefaultRolesForActionGroups(chatType?: ChatTypeItem): Object<ActionGroupItem, RoleItem>
	{
		if (!this.#actionGroupsDefaultRoles[chatType])
		{
			return this.#actionGroupsDefaultRoles[DEFAULT_TYPE];
		}

		return this.#actionGroupsDefaultRoles[chatType];
	}

	#init(rawPermissions: RawPermissions)
	{
		this.#rolePermissions = MinimalRoleForAction;
		if (!rawPermissions)
		{
			return;
		}
		const { byChatType, actionGroups, actionGroupsDefaults } = rawPermissions;
		this.#chatTypePermissions = this.#prepareChatTypePermissions(byChatType);
		this.#actionGroups = actionGroups;
		this.#actionGroupsDefaultRoles = actionGroupsDefaults;
	}

	#canPerformActionByRole(actionType, dialogId): boolean
	{
		const { role: userRole }: ImModelChat = this.#getDialog(dialogId);
		if (Type.isUndefined(this.#rolePermissions[actionType]))
		{
			return true;
		}

		const minimalRole = this.#rolePermissions[actionType];

		return this.#checkMinimalRole(minimalRole, userRole);
	}

	#canPerformActionByChatType(rawActionType: ActionTypeItem, dialogId: string): boolean
	{
		let actionType = rawActionType;
		const dialog: ImModelChat = this.#getDialog(dialogId);
		const { role: userRole, owner: chatOwner } = dialog;
		let { type: chatType } = dialog;

		if (Type.isUndefined(this.#chatTypePermissions[chatType]))
		{
			chatType = DEFAULT_TYPE;
		}

		// for kick check if users can leave this type of chat
		if (actionType === ChatActionType.kick)
		{
			actionType = ChatActionType.leave;
		}

		const isOwner = chatOwner === Core.getUserId();
		if (actionType === ChatActionType.leave && isOwner)
		{
			actionType = ChatActionType.leaveOwner;
		}

		if (Type.isUndefined(this.#chatTypePermissions[chatType]?.[actionType]))
		{
			return true;
		}

		const minimalRole = this.#chatTypePermissions[chatType][actionType];

		return this.#checkMinimalRole(minimalRole, userRole);
	}

	#canPerformActionByChatSettings(actionType: ActionTypeItem, dialogId: string): boolean
	{
		const { role: userRole, type: chatType, permissions: chatPermissions } = this.#getDialog(dialogId);
		if (chatType === ChatType.user)
		{
			return true;
		}

		const actionGroup = this.#getGroupByAction(actionType);
		if (!actionGroup)
		{
			return true;
		}

		let minimalRoleForGroup = chatPermissions[actionGroup];
		if (!minimalRoleForGroup)
		{
			minimalRoleForGroup = UserRole.member;
		}

		return this.#checkMinimalRole(minimalRoleForGroup, userRole);
	}

	#getGroupByAction(actionType: ActionTypeItem): ?ActionGroupItem
	{
		const searchResult = Object.entries(this.#actionGroups).find(([_, groupActions]) => {
			return groupActions.includes(actionType);
		});

		if (!searchResult)
		{
			return null;
		}

		const [groupName] = searchResult;

		return groupName;
	}

	#prepareChatTypePermissions(permissionsByChatType: PermissionsByChatType): PermissionsByChatType
	{
		const preparedPermissions = { ...permissionsByChatType };

		const SERVER_USER_CHAT_TYPE = 'private';
		preparedPermissions[ChatType.user] = preparedPermissions[SERVER_USER_CHAT_TYPE];

		return preparedPermissions;
	}

	#checkMinimalRole(minimalRole: RoleItem, roleToCheck: RoleItem): boolean
	{
		if (minimalRole === UserRole.none)
		{
			return false;
		}

		const roleWeights = {};
		Object.values(UserRole).forEach((role, index) => {
			roleWeights[role] = index;
		});

		return roleWeights[roleToCheck] >= roleWeights[minimalRole];
	}

	#getDialog(dialogId: string): ImModelChat
	{
		return Core.getStore().getters['chats/get'](dialogId, true);
	}
}
