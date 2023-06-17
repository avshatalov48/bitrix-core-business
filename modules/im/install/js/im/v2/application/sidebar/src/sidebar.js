import {Core} from 'im.v2.application.core';
import {RecentList as RecentListComponent} from 'im.v2.component.old-chat-embedding.recent-list';

type SidebarApplicationParams = {
	node?: string | HTMLElement,
	preloadedList?: Object,
	loggerConfig?: Object
}

export class SidebarApplication
{
	params: SidebarApplicationParams;
	inited: boolean = false;
	initPromise: Promise = null;
	initPromiseResolver: Function = null;
	rootNode: string | HTMLElement = null;
	vueInstance: Object = null;
	controller: Object = null;

	#applicationName = 'Sidebar';

	constructor(params: SidebarApplicationParams = {})
	{
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});

		this.params = params;

		this.rootNode = this.params.node || document.createElement('div');

		this.initCore()
			.then(() => this.initComponent())
			.then(() => this.initComplete())
		;
	}

	initCore()
	{
		return new Promise((resolve) => {
			Core.ready().then(controller => {
				this.controller = controller;
				resolve();
			});
		});
	}

	initComponent()
	{
		return this.controller.createVue(this, {
			name: this.#applicationName,
			el: this.rootNode,
			components: {RecentListComponent},
			template: `<RecentListComponent :compactMode="true"/>`,
		})
		.then(vue => {
			this.vueInstance = vue;

			return Promise.resolve();
		});
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
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