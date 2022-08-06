import {Core} from 'im.v2.application.core';
import {RecentList as RecentListComponent} from 'im.v2.component.recent-list';
import {RecentPullHandler} from 'im.v2.provider.pull';
import {PullHandlers} from 'im.v2.const';

type SidebarApplicationParams = {
	node?: string | HTMLElement,
	preloadedList?: Object
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
			.then(() => this.initPullHandler())
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

	initPullHandler()
	{
		if (this.controller.pullHandlers.includes(PullHandlers.recent))
		{
			return Promise.resolve();
		}
		this.controller.pullClient.subscribe(
			new RecentPullHandler({
				store: this.controller.getStore(),
				controller: this.controller,
				application: this
			})
		);
		this.controller.pullHandlers.push(PullHandlers.recent);

		return Promise.resolve();
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