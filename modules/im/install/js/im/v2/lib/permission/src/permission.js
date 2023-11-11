import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { DialogType, ChatActionType, ChatActionGroup, UserRole } from 'im.v2.const';

import type { ImModelDialog } from 'im.v2.model';

type ChatTypeItem = $Keys<typeof DialogType>;
type ActionTypeItem = $Keys<typeof ChatActionType>;
type ActionGroupItem = $Keys<typeof ChatActionGroup>;
type ActionGroup = ActionTypeItem[];
type RoleItem = $Keys<typeof UserRole>;

type RawPermissions = {
	byChatType: PermissionsByChatType,
	actionGroups: {},
	actionGroupsDefaults: {},
};

type PermissionsByChatType = {
	[chatType: ChatTypeItem]: {
		[operation: ActionTypeItem]: RoleItem,
	}
};

const DEFAULT_TYPE = 'default';

export class PermissionManager
{
	static #instance: PermissionManager;

	#chatTypePermissions: PermissionsByChatType;
	#actionGroups: Object<ActionGroupItem, ActionGroup>;
	#actionGroupsDefaultRoles: Object<ActionGroupItem, RoleItem>;

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
		return this.#canPerformActionByChatType(actionType, dialogId)
			&& this.#canPerformActionByChatSettings(actionType, dialogId);
	}

	canPerformKick(dialogId: string, userId: string | number): boolean
	{
		return this.#canPerformActionByChatType(ChatActionType.kick, dialogId)
			&& this.#canPerformActionByChatSettings(ChatActionType.kick, dialogId)
			&& this.#canPerformKickByHierarchy(dialogId, userId);
	}

	getDefaultRolesForActionGroups(): Object<ActionGroupItem, RoleItem>
	{
		return this.#actionGroupsDefaultRoles;
	}

	#init(rawPermissions: RawPermissions)
	{
		const { byChatType, actionGroups, actionGroupsDefaults } = rawPermissions;
		this.#chatTypePermissions = this.#prepareChatTypePermissions(byChatType);
		this.#actionGroups = actionGroups;
		this.#actionGroupsDefaultRoles = actionGroupsDefaults;
	}

	#canPerformActionByChatType(rawActionType: ActionTypeItem, dialogId: string): boolean
	{
		let actionType = rawActionType;
		const dialog: ImModelDialog = this.#getDialog(dialogId);
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

		if (Type.isUndefined(this.#chatTypePermissions[chatType][actionType]))
		{
			return true;
		}

		const minimalRole = this.#chatTypePermissions[chatType][actionType];

		return this.#checkMinimalRole(minimalRole, userRole);
	}

	#canPerformActionByChatSettings(actionType: ActionTypeItem, dialogId: string): boolean
	{
		const { role: userRole, type: chatType, permissions: chatPermissions } = this.#getDialog(dialogId);
		if (chatType === DialogType.user)
		{
			return true;
		}

		if (actionType === ChatActionType.send)
		{
			return this.#checkMinimalRole(chatPermissions.canPost, userRole);
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

		preparedPermissions[DialogType.user] = {
			[ChatActionType.avatar]: UserRole.none,
			[ChatActionType.call]: UserRole.member,
			[ChatActionType.extend]: UserRole.member,
			[ChatActionType.leave]: UserRole.none,
			[ChatActionType.leaveOwner]: UserRole.none,
			[ChatActionType.mute]: UserRole.none,
			[ChatActionType.rename]: UserRole.none,
			[ChatActionType.send]: UserRole.member,
		};

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

	#canPerformKickByHierarchy(dialogId: string, userId: string | number): boolean
	{
		const preparedUserId = Number.parseInt(userId, 10);
		const { role: userRole } = this.#getDialog(dialogId);
		const targetUserRole = this.#getUserRole(dialogId, preparedUserId);

		return this.#checkMinimalRole(targetUserRole, userRole);
	}

	#getDialog(dialogId: string): ImModelDialog
	{
		return Core.getStore().getters['dialogues/get'](dialogId, true);
	}

	#getUserRole(dialogId: string, userId: number): RoleItem
	{
		const { owner, managerList } = this.#getDialog(dialogId);
		if (userId === owner)
		{
			return UserRole.owner;
		}

		if (managerList.includes(userId))
		{
			return UserRole.manager;
		}

		return UserRole.member;
	}
}
