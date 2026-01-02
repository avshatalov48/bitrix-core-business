import { Core } from 'im.v2.application.core';
import { FeatureManager, Feature } from 'im.v2.lib.feature';

import { SidebarConfig } from './classes/config';
import { SidebarPreset } from './classes/preset';
import { isChat, chatPreset } from './configs/chat';
import { isUser, userPreset } from './configs/user';
import { isBot, botPreset } from './configs/bot';
import { isNotes, notesPreset } from './configs/notes';
import { isLines, linesPreset } from './configs/lines';
import { isCollab, collabPreset } from './configs/collab';
import { isSupport, supportPreset } from './configs/support';
import { isAiAssistantBot, aiAssistantBotPreset } from './configs/ai-assistant-bot';
import { isComment, commentPreset } from './configs/comment';
import { isChannel, channelPreset } from './configs/channel';
import { isCopilot, copilotPreset } from './configs/copilot';
import { isTaskComments, taskCommentsPreset } from './configs/task-comments';

import type { ImModelChat } from 'im.v2.model';

export { SidebarConfig } from './classes/config';
export { SidebarPreset } from './classes/preset';

export type ContextCheckFunction = (context: ImModelChat) => boolean

export class SidebarManager
{
	static #instance = null;

	#defaultConfigMap: Map<ContextCheckFunction, SidebarPreset> = new Map();
	#customConfigMap: Map<ContextCheckFunction, SidebarPreset> = new Map();

	static getInstance(): SidebarManager
	{
		if (!this.#instance)
		{
			this.#instance = new SidebarManager();
		}

		return this.#instance;
	}

	constructor()
	{
		this.#checkMigrationStatus();
		this.#registerDefaultConfigs();
	}

	registerConfig(callback: ContextCheckFunction, sidebarPreset: SidebarPreset): void
	{
		this.#customConfigMap.set(callback, sidebarPreset);
	}

	getConfig(dialogId: string): SidebarConfig
	{
		const chat = Core.getStore().getters['chats/get'](dialogId, true);

		const allConfigEntries = [...this.#customConfigMap.entries(), ...this.#defaultConfigMap.entries()];
		for (const [callback, preset] of allConfigEntries)
		{
			if (callback(chat))
			{
				return new SidebarConfig(dialogId, preset);
			}
		}

		return (new SidebarConfig(dialogId));
	}

	#checkMigrationStatus()
	{
		const filesMigrated = FeatureManager.isFeatureAvailable(Feature.sidebarFiles);
		const linksMigrated = FeatureManager.isFeatureAvailable(Feature.sidebarLinks);
		void Core.getStore().dispatch('sidebar/setFilesMigrated', filesMigrated);
		void Core.getStore().dispatch('sidebar/setLinksMigrated', linksMigrated);
	}

	#registerDefaultConfigs()
	{
		// most specific configs first
		this.#defaultConfigMap.set(isTaskComments, taskCommentsPreset);
		this.#defaultConfigMap.set(isCopilot, copilotPreset);
		this.#defaultConfigMap.set(isChannel, channelPreset);
		this.#defaultConfigMap.set(isComment, commentPreset);
		this.#defaultConfigMap.set(isSupport, supportPreset);
		this.#defaultConfigMap.set(isAiAssistantBot, aiAssistantBotPreset);
		this.#defaultConfigMap.set(isBot, botPreset);
		this.#defaultConfigMap.set(isNotes, notesPreset);
		this.#defaultConfigMap.set(isLines, linesPreset);
		this.#defaultConfigMap.set(isCollab, collabPreset);
		this.#defaultConfigMap.set(isUser, userPreset);
		this.#defaultConfigMap.set(isChat, chatPreset);
	}
}
