import { BitrixVue, VueCreateAppResult } from 'ui.vue3';

import { SelectableViewWidget as SelectableViewWidgetComponent } from './components/selectable-view-widget';
import type { SelectableViewWidgetOptions } from './types';

export class SelectableViewWidget
{
	#app: VueCreateAppResult;

	constructor(options: SelectableViewWidgetOptions)
	{
		this.#app = BitrixVue.createApp(SelectableViewWidgetComponent, options);
	}

	mount(node: HTMLElement): Object
	{
		return this.#app.mount(node);
	}
}
