import { Text } from 'main.core';

import { Core } from 'im.v2.application.core';
import { SidebarPullHandler } from 'im.v2.provider.pull';

import { TaskChatOpener } from './components/task-chat-opener';
import { TaskChatPlaceholder } from './components/placeholder/placeholder';

import type { RunActionError } from 'im.v2.lib.rest';

const APP_NAME = 'TaskChatApplication';
const PLACEHOLDER_APP_NAME = 'TaskChatPlaceholderApplication';

type MountPayload = {
	rootContainer: string | HTMLElement,
	chatId: number,
	onError: (RunActionError[]) => void,
	type: string,
};

type MountPlaceholderPayload = {
	rootContainer: string | HTMLElement,
	taskId: number | string,
};

export class TaskApplication
{
	#initPromise: Promise<TaskApplication>;

	constructor()
	{
		this.#initPromise = this.#init();
		this.#initPull();
	}

	ready(): Promise
	{
		return this.#initPromise;
	}

	async mount(payload: MountPayload): Promise
	{
		await this.ready();

		const { rootContainer, chatId, onError, type } = payload;
		if (!rootContainer)
		{
			return Promise.reject(new Error('Provide node or selector for root container'));
		}

		if (!type)
		{
			return Promise.reject(new Error('Provide custom chat type name for task chat'));
		}

		const preparedChatType = Text.toCamelCase(type);

		return Core.createVue(this, {
			name: APP_NAME,
			el: rootContainer,
			onError,
			components: { TaskChatOpener },
			template: `<TaskChatOpener :chatId="${chatId}" chatType="${preparedChatType}" />`,
		});
	}

	async mountPlaceholder(payload: MountPlaceholderPayload): Promise
	{
		await this.ready();

		const { rootContainer, taskId } = payload;
		if (!rootContainer)
		{
			return Promise.reject(new Error('Provide node or selector for root container'));
		}

		return Core.createVue(this, {
			name: PLACEHOLDER_APP_NAME,
			el: rootContainer,
			components: { TaskChatPlaceholder },
			template: `<TaskChatPlaceholder :taskId="${taskId}" />`,
		});
	}

	async #init(): Promise<TaskApplication>
	{
		await Core.ready();

		return this;
	}

	async #initPull(): void
	{
		await this.ready();

		Core.getPullClient().subscribe(new SidebarPullHandler());
	}
}
