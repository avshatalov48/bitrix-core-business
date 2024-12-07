import { Core } from 'im.v2.application.core';
import { QuickAccess } from 'im.v2.component.quick-access';
import { GetParameter } from 'im.v2.const';

import { Messenger } from 'im.public';

type QuickAccessApplicationParams = {
	node?: string | HTMLElement,
	preloadedList?: Object,
	loggerConfig?: Object,
}

export class QuickAccessApplication
{
	params: QuickAccessApplicationParams;
	inited: boolean = false;
	initPromise: Promise = null;
	initPromiseResolver: Function = null;
	rootNode: string | HTMLElement = null;
	vueInstance: Object = null;
	controller: Object = null;

	#applicationName = 'Sidebar';

	constructor(params: QuickAccessApplicationParams = {})
	{
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});

		this.params = params;

		this.rootNode = this.params.node || document.createElement('div');

		// eslint-disable-next-line promise/catch-or-return
		this.initCore()
			.then(() => this.initComponent())
			.then(() => this.initComplete())
			.then(() => this.checkGetParams())
		;
	}

	async initCore(): Promise
	{
		Core.setApplicationData(this.params);
		this.controller = await Core.ready();

		return true;
	}

	async initComponent(): Promise
	{
		this.vueInstance = await this.controller.createVue(this, {
			name: this.#applicationName,
			el: this.rootNode,
			components: { QuickAccess },
			template: '<QuickAccess />',
		});

		return true;
	}

	initComplete(): Promise
	{
		this.inited = true;
		this.initPromiseResolver(this);

		return Promise.resolve();
	}

	checkGetParams(): void
	{
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.has(GetParameter.openNotifications))
		{
			Messenger.openNotifications();
		}
		else if (urlParams.has(GetParameter.openHistory))
		{
			const dialogId = urlParams.get(GetParameter.openHistory);
			Messenger.openLinesHistory(dialogId);
		}
		else if (urlParams.has(GetParameter.openLines))
		{
			const dialogId = urlParams.get(GetParameter.openLines);
			Messenger.openLines(dialogId);
		}
		else if (urlParams.has(GetParameter.openChat))
		{
			const dialogId = urlParams.get(GetParameter.openChat);
			let messageId = urlParams.get(GetParameter.openMessage);
			messageId = messageId ? Number(messageId) : 0;
			Messenger.openChat(dialogId, messageId);
		}
		else if (urlParams.has(GetParameter.openSettings))
		{
			const settingsSection = urlParams.get(GetParameter.openSettings);
			Messenger.openSettings({ onlyPanel: settingsSection?.toLowerCase() });
		}
		else if (urlParams.has(GetParameter.openCopilotChat))
		{
			const dialogId = urlParams.get(GetParameter.openCopilotChat);
			Messenger.openCopilot(dialogId);
		}
	}

	ready(): Promise
	{
		if (this.inited)
		{
			return Promise.resolve(this);
		}

		return this.initPromise;
	}
}
