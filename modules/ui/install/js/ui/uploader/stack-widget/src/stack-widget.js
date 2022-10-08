import { Type } from 'main.core';
import { UploaderOptions, VueUploader } from 'ui.uploader.core';

import { StackWidgetSize } from './stack-widget-size';
import { StackWidgetComponent } from './components/stack-widget-component';

import type { StackWidgetOptions } from './stack-widget-options';

/**
 * @memberof BX.UI.Uploader
 */
export default class StackWidget extends VueUploader
{
	constructor(uploaderOptions: UploaderOptions, stackWidgetOptions: StackWidgetOptions)
	{
		const widgetOptions = Type.isPlainObject(stackWidgetOptions) ? Object.assign({}, stackWidgetOptions) : {};
		const size =
			Object.values(StackWidgetSize).includes(widgetOptions.size) ? widgetOptions.size : StackWidgetSize.MEDIUM
		;

		const vueOptions = {
			data()
			{
				return {
					widget: {
						size,
					},
				};
			}
		};

		super(uploaderOptions, vueOptions);
	}

	getRootComponentId(): Function
	{
		return StackWidgetComponent;
	}
}
