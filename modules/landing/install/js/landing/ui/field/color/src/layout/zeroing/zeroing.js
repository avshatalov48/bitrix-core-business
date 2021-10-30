import {EventEmitter} from 'main.core.events';
import {Cache, Tag, Event} from 'main.core';

import './css/zeroing.css';

export default class Zeroing extends EventEmitter
{
	constructor()
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Transparent');
		Event.bind(this.getLayout(), 'click', () => this.onClick());
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`<div class="landing-ui-field-color-preset-item landing-ui-field-color-transparent"></div>`;
		});
	}

	onClick()
	{
		this.emit('onChange', {color: null});
	}
}
