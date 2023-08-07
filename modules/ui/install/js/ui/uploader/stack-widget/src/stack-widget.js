import { Type } from 'main.core';
import { VueUploaderWidget } from 'ui.uploader.vue';

import { StackWidgetComponent } from './components/stack-widget-component';

import type { UploaderOptions } from 'ui.uploader.core';
import type { StackWidgetOptions } from './stack-widget-options';

/**
 * @memberof BX.UI.Uploader
 */
export default class StackWidget extends VueUploaderWidget
{
	constructor(uploaderOptions: UploaderOptions, stackWidgetOptions: StackWidgetOptions)
	{
		const widgetOptions = Type.isPlainObject(stackWidgetOptions) ? Object.assign({}, stackWidgetOptions) : {};
		super(uploaderOptions, widgetOptions);
	}

	defineComponent(): Function
	{
		return StackWidgetComponent;
	}
}
