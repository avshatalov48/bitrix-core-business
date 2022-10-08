import { Type } from 'main.core';
import { UploaderOptions, VueUploader } from 'ui.uploader.core';

import { TileWidgetComponent } from './components/tile-widget-component';
import { TileWidgetOptions } from './tile-widget-options';

/**
 * @memberof BX.UI.Uploader
 */
export default class TileWidget extends VueUploader
{
	constructor(uploaderOptions: UploaderOptions, tileWidgetOptions: TileWidgetOptions)
	{
		super(uploaderOptions);
		const widgetOptions = Type.isPlainObject(tileWidgetOptions) ? Object.assign({}, tileWidgetOptions) : {};
	}

	getRootComponentId(): ?Function
	{
		return TileWidgetComponent;
	}
}
