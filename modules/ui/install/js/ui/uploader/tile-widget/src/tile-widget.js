import { Type } from 'main.core';
import { VueUploaderWidget } from 'ui.uploader.vue';

import { TileWidgetComponent } from './components/tile-widget-component';

import type { UploaderOptions } from 'ui.uploader.core';
import type { TileWidgetOptions } from './tile-widget-options';

/**
 * @memberof BX.UI.Uploader
 */
export default class TileWidget extends VueUploaderWidget
{
	constructor(uploaderOptions: UploaderOptions, tileWidgetOptions: TileWidgetOptions)
	{
		const widgetOptions = Type.isPlainObject(tileWidgetOptions) ? Object.assign({}, tileWidgetOptions) : {};
		super(uploaderOptions, widgetOptions);
	}

	defineComponent(): ?Function
	{
		return TileWidgetComponent;
	}
}
