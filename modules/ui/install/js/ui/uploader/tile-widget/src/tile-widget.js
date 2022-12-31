import { Type } from 'main.core';
import { UploaderOptions, VueUploaderWidget } from 'ui.uploader.core';

import { TileWidgetComponent } from './components/tile-widget-component';
import { TileWidgetOptions } from './tile-widget-options';

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

	getRootComponent(): ?Function
	{
		return TileWidgetComponent;
	}
}
