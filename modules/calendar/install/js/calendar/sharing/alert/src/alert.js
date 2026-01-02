import { Type } from 'main.core';
import { BitrixVue } from 'ui.vue3';
import { Application } from './components/application';

export class Alert
{
	constructor(options = {})
	{
		this.link = options.link;
		this.rootNode = BX('calendar-sharing-alert');

		if (Type.isDomNode(this.rootNode))
		{
			this.buildView();
		}
	}

	buildView()
	{
		this.application = BitrixVue.createApp(Application, {
			link: this.link,
		}).mount(this.rootNode);
	}
}
