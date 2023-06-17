import {Core} from 'im.v2.application.core';
import {Messenger as MessengerComponent} from 'im.v2.component.messenger';
import {SidebarPullHandler} from 'im.v2.provider.pull';
import {ApplicationName} from 'im.v2.const';

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

		this.initCore()
			// .then(() => this.initComponent())
			.then(() => this.initPullHandlers())
			.then(() => this.initComplete())
		;
	}

	initCore()
	{
		return new Promise((resolve) => {
			Core.ready().then(controller => {
				this.controller = controller;
				Core.setApplicationData(ApplicationName.messenger, this.params);
				resolve();
			});
		});
	}

	initComponent(node)
	{
		this.unmountComponent();

		return this.controller.createVue(this, {
			name: 'Messenger',
			el: node || this.rootNode,
			components: {MessengerComponent},
			template: `<MessengerComponent />`,
		}).then(vue => {
			this.vueInstance = vue;

			return Promise.resolve();
		});
	}

	unmountComponent()
	{
		if (!this.vueInstance)
		{
			return false;
		}

		this.bitrixVue.unmount();
		this.vueInstance = null;
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
	}

	initPullHandlers()
	{
		this.controller.pullClient.subscribe(new SidebarPullHandler());

		return Promise.resolve();
	}

	ready()
	{
		if (this.inited)
		{
			return Promise.resolve(this);
		}

		return this.initPromise;
	}
}