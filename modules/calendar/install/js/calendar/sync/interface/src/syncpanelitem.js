// @flow
'use strict';

import 'ui.tilegrid';
import 'ui.forms';
import {Reflection, Type} from "main.core";

export default class SyncPanelItem extends BX.TileGrid.Item
{
	constructor(options)
	{
		super(options);
		this.options = options;
	}

	getContent()
	{
		if (this.options.className)
		{
			const itemClass = Reflection.getClass(this.options.className);

			if (Type.isFunction(itemClass))
			{
				const item = new itemClass(this.options);
				return item.getInnerContent();
			}

			return '';
		}
	}
}
