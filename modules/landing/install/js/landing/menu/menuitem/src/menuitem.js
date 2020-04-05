import {Event, Text, Cache} from 'main.core';
import {MenuItemForm} from 'landing.ui.form.menuitemform';

/**
 * @memberOf BX.Landing.Menu
 */
export class MenuItem extends Event.EventEmitter
{
	constructor(options = {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.Menu.MenuItem');

		this.layout = options.layout;
		this.children = options.children;
		this.selector = options.selector;
		this.depth = Text.toNumber(options.depth);
		this.cache = new Cache.MemoryCache();
		this.nodes = options.nodes;
	}

	getForm(): MenuItemForm
	{
		return new MenuItemForm({
			selector: this.selector,
			depth: this.depth,
			fields: this.nodes.map((node) => node.getField()),
		});
	}
}