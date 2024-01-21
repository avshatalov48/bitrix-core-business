import { Core } from 'im.v2.application.core';
import { Messenger as MessengerComponent } from 'im.v2.component.messenger';
import { SidebarPullHandler } from 'im.v2.provider.pull';

type MessengerApplicationParams = {
	node?: string | HTMLElement
}

export class MessengerApplication
{
	params: MessengerApplicationParams;
	inited: boolean = false;
	initPromise: Promise = null;
	initPromiseResolver: Function = null;
	rootNode: string | HTMLElement = null;
	vueInstance: Object = null;
	controller: Object = null;
	bitrixVue: Object = null;

	#applicationName = 'Messenger';

	constructor(params: MessengerApplicationParams = {})
	{
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});

		this.params = params;

		this.rootNode = this.params.node || document.createElement('div');

		// eslint-disable-next-line promise/catch-or-return
		this.initCore()
			.then(() => this.initPullHandlers())
			.then(() => this.initComplete())
		;
	}

	async initCore(): Promise
	{
		Core.setApplicationData(this.params);
		this.controller = await Core.ready();

		return true;
	}

	initPullHandlers(): Promise
	{
		this.controller.pullClient.subscribe(new SidebarPullHandler());

		return Promise.resolve();
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
	}

	async initComponent(node): Promise
	{
		this.unmountComponent();

		this.vueInstance = await this.controller.createVue(this, {
			name: this.#applicationName,
			el: node || this.rootNode,
			components: { MessengerComponent },
			template: '<MessengerComponent />',
		});

		return true;
	}

	unmountComponent()
	{
		if (!this.vueInstance)
		{
			return;
		}

		this.bitrixVue.unmount();
		this.vueInstance = null;
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