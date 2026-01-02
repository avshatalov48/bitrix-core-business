import { Core } from 'im.v2.application.core';
import { QuickAccess } from 'im.v2.component.quick-access';

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

	ready(): Promise
	{
		if (this.inited)
		{
			return Promise.resolve(this);
		}

		return this.initPromise;
	}
}
