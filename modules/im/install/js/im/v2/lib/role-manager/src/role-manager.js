import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { UserRole } from 'im.v2.const';

type RoleItem = $Keys<typeof UserRole>;

type RawChatConfig = {
	ownerId?: number,
	owner?: number,
	managers?: number[],
	manager_list?: number[],
}

type ChatConfig = {
	ownerId: number,
	managers: number[],
}

export function getChatRoleForUser(rawChatConfig: RawChatConfig): RoleItem
{
	const chatConfig = prepareChatConfig(rawChatConfig);
	const userId = Core.getUserId();
	if (chatConfig.ownerId === userId)
	{
		return UserRole.owner;
	}

	if (chatConfig.managers.includes(userId))
	{
		return UserRole.manager;
	}

	return UserRole.member;
}

function prepareChatConfig(rawChatConfig: RawChatConfig): ChatConfig
{
	const result = {
		ownerId: 0,
		managers: [],
	};

	if (Type.isNumber(rawChatConfig.ownerId))
	{
		result.ownerId = rawChatConfig.ownerId;
	}

	if (Type.isNumber(rawChatConfig.owner))
	{
		result.ownerId = rawChatConfig.owner;
	}

	if (Type.isArray(rawChatConfig.managers))
	{
		result.managers = rawChatConfig.managers;
	}

	if (Type.isArray(rawChatConfig.manager_list))
	{
		result.managers = rawChatConfig.manager_list;
	}

	return result;
}
