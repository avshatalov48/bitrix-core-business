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

	initCore(): Promise
	{
		return new Promise((resolve) => {
			// eslint-disable-next-line promise/catch-or-return
			Core.ready().then((controller) => {
				this.controller = controller;
				Core.setApplicationData(this.params);
				resolve();
			});
		});
	}

	initComponent(): Promise
	{
		return this.controller.createVue(this, {
			name: this.#applicationName,
			el: this.rootNode,
			components: { QuickAccess },
			template: '<QuickAccess :compactMode="true"/>',
		})
			.then((vue) => {
				this.vueInstance = vue;
			});
	}

	initComplete(): Promise
	{
		this.inited = true;
		this.initPromiseResolver(this);

		return Promise.resolve();
	}

	checkGetParams()
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
			Messenger.openChat(dialogId);
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
