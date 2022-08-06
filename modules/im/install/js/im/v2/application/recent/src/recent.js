import {Core} from 'im.v2.application.core';
import {RecentList as RecentListComponent} from 'im.v2.component.recent-list';
import {RecentPullHandler} from 'im.v2.provider.pull';

type RecentApplicationParams = {
	node?: string | HTMLElement,
	compactMode?: boolean
}

export class RecentApplication
{
	params: RecentApplicationParams;
	inited: boolean = false;
	initPromise: Promise = null;
	initPromiseResolver: Function = null;
	rootNode: string | HTMLElement = null;
	vueInstance: Object = null;
	controller: Object = null;

	#applicationName = 'RecentList';

	constructor(params: RecentApplicationParams = {})
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
		this.controller.pullClient.subscribe(
			new RecentPullHandler({
				store: this.controller.getStore(),
				controller: this.controller,
				application: this
			})
		);

		return Promise.resolve();
	}

	initComponent()
	{
		return this.controller.createVue(this, {
			name: this.#applicationName,
			el: this.rootNode,
			components: {RecentListComponent},
			template: `<RecentListComponent />`,
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