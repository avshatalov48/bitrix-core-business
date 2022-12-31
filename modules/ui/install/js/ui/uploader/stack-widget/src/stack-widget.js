import { Type } from 'main.core';
import { UploaderOptions, VueUploaderWidget } from 'ui.uploader.core';
import { StackWidgetComponent } from './components/stack-widget-component';
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

	getRootComponent(): Function
	{
		return StackWidgetComponent;
	}
}
